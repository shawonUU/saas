<?php
/**
 * SaaS source-code update: Basic subscription + 12 separate Configuration CRUDs.
 * Place THIS SINGLE FILE in your existing Laravel project root (beside artisan).
 *
 *  php update_saas_changes.php          # apply code changes only (safe default)
 *  php update_saas_changes.php --seed   # apply and sync existing Basic plan data
 *  php update_saas_changes.php --check  # preview compatibility / conflicts
 *  php update_saas_changes.php --force  # override modified-file conflicts (BACKED UP)
 *  php update_saas_changes.php --rollback=saas_patch_backup_....json.gz
 *
 * This script does NOT install Laravel, run migrations, alter schemas or move folders.
 * Based on the exact differences between supplied app(4).zip and revised project ZIP.
 * Before writing, it verifies existing file hashes. Unknown edits are NOT overwritten
 * without explicit --force. Backups go to the existing storage/app directory.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only.' . PHP_EOL); }
error_reporting(E_ALL);

function say(string $message): void { echo $message, PHP_EOL; }
function failPatch(string $message, int $code = 1): never { fwrite(STDERR, 'ERROR: '.$message.PHP_EOL); exit($code); }
function shaOf(?string $contents): ?string { return $contents === null ? null : hash('sha256', $contents); }
function fileContents(string $absolute): ?string {
    if (!file_exists($absolute)) return null;
    if (!is_file($absolute) || is_link($absolute)) failPatch('Not a regular file: '.$absolute);
    $bytes = file_get_contents($absolute);
    if ($bytes === false) failPatch('Cannot read: '.$absolute);
    return $bytes;
}
function targetFile(string $root, string $relative): string {
    if (!preg_match('~^[a-zA-Z0-9_.-]+(?:/[a-zA-Z0-9_.-]+)*$~D', $relative) || str_contains($relative, '..'))
        failPatch('Invalid internal patch path: '.$relative);
    $absolute = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $realParent = realpath(dirname($absolute));
    if ($realParent === false || !str_starts_with(str_replace('\\','/',$realParent).'/', str_replace('\\','/',$root).'/'))
        failPatch('Missing / unsafe directory: '.$relative);
    if (is_link($absolute)) failPatch('Refusing to follow symlink: '.$relative);
    return $absolute;
}
function safeWrite(string $absolute, string $contents, ?int $mode = null): void {
    $temp = tempnam(dirname($absolute), '.saaspatch_');
    if ($temp === false) throw new RuntimeException('Temporary file could not be created for '.$absolute);
    try {
        if (file_put_contents($temp, $contents, LOCK_EX) !== strlen($contents))
            throw new RuntimeException('Could not write '.$temp);
        if ($mode !== null) @chmod($temp, $mode & 0777);
        if (!@rename($temp, $absolute)) {
            // Windows can occasionally refuse replacing an existing file via rename.
            if (!@copy($temp, $absolute)) throw new RuntimeException('Could not replace '.$absolute);
            @unlink($temp);
        }
    } finally { if (is_file($temp)) @unlink($temp); }
}
function restorePrevious(string $root, array $items): void {
    foreach (array_reverse($items, true) as $relative => $previous) {
        $absolute = targetFile($root, $relative);
        if ($previous['exists']) {
            $bytes = base64_decode($previous['data'], true);
            if ($bytes === false) throw new RuntimeException('Invalid backup data: '.$relative);
            safeWrite($absolute, $bytes, isset($previous['mode']) ? (int)$previous['mode'] : null);
        } else if (is_file($absolute) && !@unlink($absolute)) {
            throw new RuntimeException('Cannot remove file: '.$relative);
        }
    }
}
function runArtisan(string $root, array $arguments): int {
    $cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg($root.DIRECTORY_SEPARATOR.'artisan');
    foreach ($arguments as $arg) $cmd .= ' '.escapeshellarg($arg);
    say('> php artisan '.implode(' ', $arguments));
    if (!function_exists('passthru')) { say('WARNING: passthru() disabled; command not run.'); return 127; }
    passthru($cmd, $exitStatus);
    return $exitStatus;
}

$root = realpath(__DIR__);
if (!$root || !is_file($root.'/artisan') || !is_file($root.'/composer.json'))
    failPatch('Put update_saas_changes.php in Laravel project root, beside artisan and composer.json.');
$dry = in_array('--check', $argv, true) || in_array('--dry-run', $argv, true);
$force = in_array('--force', $argv, true);
$seed = in_array('--seed', $argv, true);
$rollbacks = array_values(array_filter(array_slice($argv, 1), fn($a) => str_starts_with($a, '--rollback=')));
if (count($rollbacks) > 1) failPatch('Use only one --rollback option.');
if ($seed && !is_file($root.'/vendor/autoload.php')) failPatch('For --seed, install composer dependencies first (vendor/autoload.php missing). No files changed.');
if ($rollbacks && ($dry || $seed)) failPatch('Rollback cannot be combined with --seed / --check.');

// A backup lives inside existing storage/app; no existing folder is moved or renamed.
$backupDirectory = $root.'/storage/app';
if (!is_dir($backupDirectory)) failPatch('storage/app directory not found.');

if ($rollbacks) {
    $name = substr($rollbacks[0], strlen('--rollback='));
    if (!preg_match('/^saas_patch_backup_[0-9]{8}_[0-9]{6}_[a-f0-9]{8}\\.json\\.gz$/D', $name))
        failPatch('Specify the backup FILE NAME shown by this updater (not an arbitrary path).');
    $rawBackup = fileContents($backupDirectory.'/'.$name);
    if ($rawBackup === null) failPatch('Backup not found: '.$name);
    $backupText = @gzdecode($rawBackup);
    $backup = is_string($backupText) ? json_decode($backupText, true) : null;
    if (!is_array($backup) || ($backup['patch'] ?? '') !== 'saas-basic-config-20261117' || !isset($backup['files']) || !is_array($backup['files']))
        failPatch('Invalid or incompatible backup.');
    $conflicts = [];
    foreach ($backup['files'] as $relative => $previous) {
        if (!is_array($previous) || !array_key_exists('after', $previous)) failPatch('Invalid backup record.');
        $actual = shaOf(fileContents(targetFile($root, $relative)));
        if ($actual !== $previous['after']) $conflicts[] = $relative;
    }
    if ($conflicts && !$force) failPatch('Rollback conflict: '.implode(', ', $conflicts).'. Use --force only after reviewing later edits.');
    try { restorePrevious($root, $backup['files']); }
    catch (Throwable $e) { failPatch('Rollback failed: '.$e->getMessage()); }
    say('RESTORED from storage/app/'.$name.' ('.count($backup['files']).' files).');
    exit(0);
}

// The payload is embedded below: nothing is downloaded from the internet.
$payload = <<<'PATCH_DATA'
H4sIAAAAAAAC/+y9WXfiSLM2+l/6tr59ShOD9lr7wmCDwTYuYyOGc6URbMRQBhvjb53/fiJyUqaQQFS5++3urQsvGyylUpmRkTE8
8eT//WPtbv3ZH//9x8Z1N//luZtn/7/81TJ6nv6XoRlVXddrf/yfP6LnONz88d//7//Fy/Fqd73+fr3drr83V8vt6yqOw9fN9x9v
Xvzsf2+8bZ6X4Wbz/SJYPC/xCmjt7dXdPq+Wm+8NdxMqXyUt/D/r2Roe5oXR6jWEh9SM0DVNw3LDqh1Z1brmanUjqkeWYURatVqt
R1UTLtbqplkLKtBbLzKsINTCilatBWEd2nKjbfj6x38v3+L4//wB77UNl1v68f/7P1/xMq/uMsh/Ac2ManWjFtUMs2JbWujrlapf
DWzd03wtME07tCPf1HTX9SxXq8EbGXZkVwzP8oKg7rrJC/xRjSxX1/xQ8zTXMmv1asUMIzswdRibmlcNvapR16x6xTV8y4IR0yLD
NKJI12GIdD2M/pBe/48fl/bOvb64aS4qM2/ofPrt1svksdHwrxv+43Vf81sfl55R0fyFvfHazt7XPx6CYXfjDnv+w9L5dIeV2Dfh
72F/C3/7D4b9NlnEy2DUnQXt+N1b3tWa07XuG4Ppw6ixG13NtADaxuuC6+671/6I/WVP/fyyuQlGvbhz1drB83ue0Y+9a/l50Cb8
zfv5NLTnk+HH5+iqC8+J3yaj3qcD9+e2c9BHbLs78xYPSrsDw9kHi/gF2pPf9X48mqwnRktzh/bb/Y6+2+PwYxMM9bW3aGkT56MT
XPd3o1Y39kcO3CPa7cL76d4wfhuP+vGo1dPhOe/+su/3F62X8bAfw/g2glF/db9b3YyNjxm865T2ra/MQ6ftWEHbeZtcw//lsZuu
a83ni2mn2djBtdpk2IOfh+kYroF+TAfah9PfX9idZvDsL1p4/xKeBddfTH1oA9p8gZ95p93DcdE6rX7XufpodS61aVfr7sfDytzf
+0ef4VzFg6crJ3qYW9MfjxdL68bdWDdhD37v2O93+PkGPyv2edb93NxgHzrXjb1n9uMx/EyajRd458+g2Wg/Dpy7fusO2mt49Nn0
p2ug3A6W0L9vnRZroyn//2M2XjibLrzzj+eLJZvn6dOitZ08+hu5LfoTbMbDbuy17Gfv2Z/+eLKm3W/Nxet9c/kSNhcb9vsVvnt/
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
aMfAekow+fPTdQacKv2xQeuhlBoplepXUI/T4xj2d82sIwd4DZSopRD64AQdPMeYT3Px1vwIhbw6i7y6ALXulxxpDnL4dg5NuYrT
5bUf0PbnBdGRd49q/cRXU5fn0csT+RsiLTPVTwlfyDn1HD0yViOjv56Af6/Iz1fXbhzUEjgv7lCfgb49SYMP+x/Ijg59iF/O6+Nk
44E98DtU8bR2/8Q4XiO9ZBDB/qHBul5193Y4avdrvWtti7/vluL3/JbI4rF3cK7Yke6SPv3FuhIT8UdIeVlgjOFaxBrkHYOQxAaD
AHwd2h9HK1B3UqDvx2r6jrZ9sM+wZ+Uf9XFkjxGU6EXsJIqJ3lH69H3OsR8ZY0rw0y02bqLGgmBD01jINYnHYF+W/XevbZuEH+V0
3whV9G1T1DKsb0SdEa1z+LEXdOxVQdWeooBX6M6TeGN2/QdcOzIpzhttxV7OMShyXZpMTT38vNrR+3bTuxfcZwbM5mykdKtSh/HR
O8BdcUpuEvMR60rCbif4dTK+KFfYHs2hy9TwAnfSouM64PthMxU7S6gyDbBXTaSkZz78mxR3PkkvK8f8FJxgMzNfJfn5nCpc8utN
4tfz2BuPRSyTWASPvSWcDFl5BnfU33CfRq1nGGzE/wfc70zRtJMjhLOp0am/m9Qh8KOWSFyV2ky876IG4pbmMtj88ncWNRA5tRXU
tpLo3hP7LDluilOtJ/Y3O2KB10Iw2n5WS8Tagr8z8y1Crnm7Um0PpWiX4mfSNVJcKKvdFJ6c3Id6her36f/WegOBt/gn1RrQ2Cb4
DVgfs+yJo527eNQM4ojB34q4njc/tuPRtAp6Uc/OKcs5f1jjWCdA/EanODU8q/XPqAGgPvHlej9Z9E69F8gTzpvALh/ty93lxS47
v15BvNBvtnHIHSLzv0Xt5DimvOMDZBk5qDWgeyLxJ4j/iPhYJxW707WcfYJdXzD2y+jJvzz2+1diUAUHVh4G9RcprlO5sSeBP8Oc
IHvmDV/nzQ3mpdIxtdTeSTnhcvlfMnMrLP7J/sc5Vcbicx/8N557B9neT9ejPCw1HuXUxlw9OVYDdFH3PSDv8GfobKWu8mANKWMl
9mopx/x4lBL8kseMf5USXIkV77+AAvya5x3/XfhuXmNRBN/NcZYlVX1JVf93pqqX9fhvcmuKsciscVnwPk1/R6aukFfid2Tq9a25
nIasboUc0cHkBmsWXq2CR3SsQa7+53++pg7hyf3IL0GoVzTTtmxb1ypRLTRt2zVqZhRpXs0KjCCohHXXtSq+bxumpblBWKtoRs0z
Ay0MKmalakslCF5Ur2l+4FbcUA89rWppnqVFul61vLrlV2tWra6bpq0Ztm9qtuV6XliNqrame5YBt7plCUJGCYLTBjP9b0Q132S0
tJ1WfxZeEXWyJ6eHgduGaTZQR+DSN5S5aS6PQvaJCUPUFJhhIboLxyH+7OQ4EmobjEez+BdKDaIUrTen+bb+qvICnNfOFb2nMJU2
0rsrdO+M/v1VofL7J1K97xNa3SJQ9y45Ae6mOVifAevmZYe7TnMa3zyehuNKaY3/OG07OZnZdDYMohjJEIUCsOVLcHNeYBuj+uH5
jFKWkMI7xLEDCnyCHTPwc1qgdEWkL758LHNgJHHY7m3QpEI3n41DS/4u/wSz+G1sfKBuTUIOmvLd5tT8peH8iSwilMWxDtpn9KZr
DqdJ4PW0fIVTVizS74RwqRGj8zQZpOWS/Q0/1rV0TMTD8sQ6JOkDfxHvmJwUla+r1H2F5euVvScvlVqVMPkSJl/C5EuYfAmTL2Hy
JUy+hMmXMPkSJl/C5P8VMHkSyyqPBCiPBCiPBCiPBCiPBCiPBCiPBCiPBCiPBCiPBCiPBCiPBCiPBCiPBDh2JADqlfYHo3bOzFcT
eOUAYZeL2JKOAkTI7cmSDVh3Eaz3nUdwJtPlLS29jTKOrs2hPZehYJlHRSs0vnlxLQlLopSB/ND7s1AqiyFl4YRG/kQOSYLxyfos
gfINVpn5PelYewVuXRjK3pVi4Qq0XpT/RQh/fVntGAT8I4+S/ljuW4WCx2/3Czm3+qHkS0vq/JI6/+up8/souyVEvYSolxB1BaJO
18WfBPmVyr1YjmYpl59NlT1Vzf9g/7JpzMURB6z9vGMmxovWi8tzqaP+iqwXlmMjR7U0p1LcvSH+N8H9+nr+Rt6dxd1/Mnry11DC
V3H5IzH324QieB0xquBxQRjxUR2PWB/EaFb+ZUc6zCmE+/SRDiwfUVLj/+XU+IPl8zYfkx5Yft0IKlW3UqvbXq1eqQZW1fXqgW1o
daNaq0Vm1XW1qF6rh7Ug1P0qrAM9CgNXC0M/lGnxLdO06rZhB6FlWWZYr3qa4VWrXmD41aAe2nZY04LAMKLQ8uAa39O0uq/7kWVU
Pbuul5j0TEz6sLIOLpXrn2CfMOA9kaY+wSJplVkA91K73cqmumfvKPyhy2zsOi0PxrX/AXtDb4Z2++iqpYM9gGP2An4brJeG4y1i
TRmf50YcEv3f/+yoePWb8CitPTnmh8SrAnzf63No8J032BeK4dhJaT3qwYjm8ldLpu8xz1o//syrSbc/+LgakP1W0XGK/1YAv0nG
7Ww8+kF/v//Tceif4VB/Bv/qjGMC4i3sSefQi/9k+IQdO6bpArEU/6AxKjHCJUa4xAiXGOESI1xihEuMcIkRLjHCJUa4xAiXGOGv
wQiT2MKqyvEWNwrm83QMObXeeJ4b15CC+aM4QJUaNxvvIajlEHeD8TBOnbu7a2ZRIybP7BAcip+DR0pRMst0xIZTAf3O9xiG3+ys
ZHq9ycKaClrtJtH9UxlzkoehZTjjhIaXYge5bylRkO2ko7Z27Oi5/gxlntl61I9Y9Cr08ywOmwrWcJ2FfzzAsMm+EMbtH+dTEv+R
MLm3B5Tjx/bTK9n2eJH009qllJZIJ/qWYLnz6EXpzwTxNMMWYr5XaVw33eM53nFOnw3XOxlYyPOf30jtuSrdqYrjzZFdY0woJ0c5
FJ6YN+Y40gQ/QrBHCk32MIVFTWGIuZyucf8D2x3jXESGZewxzmGCb1H7o4yx1LY6vgzfw/B1WRhF6b0+CWYA14yg3T5JXS2wvB1G
sa/Q7qtYNIGNpditzi4TE89o9QU9d6IPTuCVGa7x8SKXwpdj/POou/OophXb10io887A0ar4Y04nDm3fUR35odYKfDW2Nhf/TORv
QrBgOxlDVDmHInxM9KITTdox2BwPivx8NR14mp56MgQbddjanKSobqO/U4Hxc0BO787q42Tx8T4e/RaWmezhp8bRR2yBbm/BrnjB
+MitXvEn1xstetTx9z75/fBO9/Ej7zDglGGSPv1FjDPBcoEdWWSM4VrtJh+nnxzVqOse78+oAC6+SN+PHRNxtO2DfYbVM+TXouTv
MQlmt4idRGh2Sb3Q8GGZV5eSMaYEf8jGTdB2E3/nM43Xiff4P+xL0LY3nomU+QVsOIpl3Ah6bNzjmDww6uzvCV54JbDEKVtSweMm
tQ2ZtN14beRT6mC0FfWc8ZDrHSTsZMO7eyI2JuKD90il3XtiNmcrpVtlam+4Pk2NxjGj1FcV6wq/i/059lVQIpPxRXsb+03zbgMZ
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
DAfPyGEdPrRmY95zttVZLqFmMbJ4T9TuY0r9SSo+awH9I4ny8H1P7A8e9yfv957fT/DOvlHgYoR52cXd/Dbr9SLxEA5jJzkgK9aF
fYpB1SX+3t7u744WMd8B6emA+b8atMV7wDM6iPdWrM/UKR5i/15UO4lrBHmXz1oLY3qsf5iFCijE4vRRlnKSMbeJYsQQy/qQel2Z
oDljwNjq3jgTJvvfJJAl123a7eXqzIQp1GHb9/0nEzoCf9aa7/wzrLN6HqMx7t5mc7qSwBLYAetzE9eA3kK46FKReBYb5c7o1+ua
j0itM071iD29WTmWHzVQq4eB2t1H5APdM8ERFpcysSohLFdPztXze5ZNUxmslZ2Hb4fPwjhw5RPugcZmaXs2mq+IyP6huY37EXKT
VGEbAnMT8XIRFzAHfXKDtrNw62fEXBPxDhXyEJoNmC+EvfU8gud2CH7s9HUT1hbWdgVrWwzU0gF8mo3Zb8xYXeehr3V3fY3tMzWq
b4F5cfJZd9srCIjMz+NA1ZBcZpwxx6NCUVcU1VBMc5Qv5LSSNtLMTHaUzY3HxayaM4s5LVcyC2NllMnBn+KkOFHVTCYjq4YUcvpo
NNYmmqqNlawBH6ZPMhlNn5h5tVCYFLTJpDTOTcbaqJBTCqWJOZoYBcWcFPMFI6Nl8qlqSKoakqqGpKohqWpIqhqSqoakqiGpakiq
GpKqhqSqIalqSKoaEqwa0t+tOFGUU96JgkQlVQJJlUBSJZBUCSRVArmhEshuvO6eKRlJUD5xnaHNNlJ1j1TdI1X3SNU9UnWPVN0j
VfdI1T1SdY9U3SNV90jVPVJ1j1TdI1X3SNU9UnWPVN0jVfdI1T1SdY9U3SNV90jVPVJ1j1TdI1X3SNU9UnWPVN0jVfdI1T1SdY9U
3SNV90jVPVJ1j1TdI1X3SNU9UnWPVN0jVfdI1T1SdY9U3SNV90jVPVJ1j1TdI1X3SNU9UnWPVN0jVfdI1T1SdY9U3SNV9/ivU/cg
PcR+/cOpYkeq2JEqdqSKHalix/+kYgfh+9QfOLOR1T+QvhHOepnwer4t78X+iwnkTjUA8rd93jH1DkF0RpK8OyfK60mREzSGPV9f
m4R/+LL4UP6AMsMUuZVhtGjvOWAn/Et9Xw926j71Q38z/g/zQ2EPFP5nJHUMUgMLMeiw92DZGIf6gGwv3d+LOu7IA63/IHk9rjbQ
+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
+POf//x///f//7//D9wfCRhcGQgA
PATCH_DATA;
$compressed = base64_decode(preg_replace('/\\s+/', '', $payload), true);
$decoded = $compressed !== false ? @gzdecode($compressed) : false;
$manifest = is_string($decoded) ? json_decode($decoded, true) : null;
if (!is_array($manifest) || ($manifest['patch'] ?? '') !== 'saas-basic-config-20261117' || !isset($manifest['files']))
    failPatch('Internal patch payload failed verification.');

$planned = []; $conflicts = []; $skipped = 0;
foreach ($manifest['files'] as $item) {
    $relative = $item['path'];
    $absolute = targetFile($root, $relative);
    $current = fileContents($absolute);
    $currentSha = shaOf($current);
    $newBytes = $item['content'] === null ? null : base64_decode($item['content'], true);
    if ($item['content'] !== null && ($newBytes === false || shaOf($newBytes) !== $item['after']))
        failPatch('Embedded content checksum failed for '.$relative);
    if ($currentSha === $item['after']) { $skipped++; continue; }
    if ($currentSha !== $item['before']) $conflicts[] = $relative;
    $planned[$relative] = ['absolute'=>$absolute, 'content'=>$newBytes, 'after'=>$item['after']];
}
say('SaaS update: Basic Subscription + Configuration CRUD (2026-11-17)');
say('Needed: '.count($planned).' | Already updated: '.$skipped.' | Conflicting: '.count($conflicts));
if ($conflicts) {
    foreach ($conflicts as $path) say('  CONFLICT: '.$path);
    if (!$force) failPatch('These files differ from the uploaded original. No changes applied. Use --force ONLY if overwriting your local edits is acceptable.', 2);
}
if ($dry) { say('CHECK ONLY: no files or database were changed.'); exit(0); }
if (!$planned) {
    say('All patch files already match the updated project.');
} else {
    $backup = ['patch'=>'saas-basic-config-20261117', 'created_at'=>date(DATE_ATOM), 'files'=>[]];
    foreach ($planned as $relative => $item) {
        $before = fileContents($item['absolute']);
        $backup['files'][$relative] = [
            'exists'=>$before !== null,
            'data'=>$before === null ? null : base64_encode($before),
            'mode'=>$before === null ? null : (fileperms($item['absolute']) & 0777),
            'after'=>$item['after'],
        ];
    }
    $backupName = 'saas_patch_backup_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.json.gz';
    $backupFile = $backupDirectory.'/'.$backupName;
    $backupGzip = gzencode(json_encode($backup, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), 9);
    if ($backupGzip === false || file_put_contents($backupFile, $backupGzip, LOCK_EX) !== strlen($backupGzip))
        failPatch('Cannot write rollback backup; no source files changed.');
    say('Backup: storage/app/'.$backupName);
    $touched = [];
    try {
        foreach ($planned as $relative => $item) {
            $touched[$relative] = $backup['files'][$relative];
            if ($item['content'] === null) {
                if (is_file($item['absolute']) && !@unlink($item['absolute']))
                    throw new RuntimeException('Delete failed: '.$relative);
            } else {
                safeWrite($item['absolute'], $item['content'], $backup['files'][$relative]['mode']);
            }
            if (shaOf(fileContents($item['absolute'])) !== $item['after'])
                throw new RuntimeException('Post-write hash mismatch: '.$relative);
        }
    } catch (Throwable $e) {
        say('Write failed; restoring previous files...');
        try { restorePrevious($root, $touched); }
        catch (Throwable $restoreError) { failPatch($e->getMessage().' | Automatic restoration failed: '.$restoreError->getMessage().' | Backup: '.$backupName); }
        failPatch($e->getMessage().' | Previous files restored.');
    }
    say('SUCCESS: '.count($planned).' source changes applied. No migration or schema change.');
}
if (is_file($root.'/vendor/autoload.php')) {
    $status = runArtisan($root, ['optimize:clear']);
    if ($status !== 0) say('WARNING: Could not clear Laravel cache. You may run: php artisan optimize:clear');
} else {
    say('NOTE: vendor/autoload.php missing; skipped Laravel cache clear.');
}
if ($seed) {
    // The updated seeder only synchronizes Basic plans with the category mappings
    // already in the database; it does not run migrations or change table structure.
    $status = runArtisan($root, ['db:seed', '--class=SubscriptionPlanSeeder', '--force']);
    if ($status !== 0) failPatch('Source files updated, but Basic subscription seeding failed. Check DB/ENV. Backup is retained.', 3);
    say('SUCCESS: existing category-wise Basic subscription plans synchronized.');
} else {
    say('To refresh existing Basic plans WITHOUT migrations, run: php update_saas_changes.php --seed');
}
say('IMPORTANT: Delete this updater PHP file after completing the update.');
