param([string]$Domain = "saas.test")
$ErrorActionPreference = 'Stop'
if ($Domain -notmatch '^[a-z0-9.-]+$') { throw 'Invalid DNS domain.' }
$hostsPath = Join-Path $env:ProgramFiles 'Acrylic DNS Proxy\AcrylicHosts.txt'
if (!(Test-Path $hostsPath)) {
    $hostsPath = Join-Path ${env:ProgramFiles(x86)} 'Acrylic DNS Proxy\AcrylicHosts.txt'
}
if (!(Test-Path $hostsPath)) { throw 'Install Acrylic DNS Proxy first: https://mayakron.altervista.org/support/acrylic/Home.htm' }
$entry = "127.0.0.1 >$Domain"
$existing = Get-Content -LiteralPath $hostsPath
if ($existing -notcontains $entry) {
    Copy-Item -LiteralPath $hostsPath -Destination ($hostsPath + '.backup') -Force
    Add-Content -LiteralPath $hostsPath -Value $entry
    Write-Host "Added wildcard mapping: $entry"
} else {
    Write-Host "Wildcard mapping already exists: $entry"
}
$service = Get-Service -Name 'AcrylicDNSProxySvc' -ErrorAction SilentlyContinue
if ($null -ne $service) {
    Restart-Service -Name $service.Name -ErrorAction Stop
    Write-Host 'Acrylic DNS service restarted.'
} else {
    Write-Warning 'Restart Acrylic DNS Proxy manually using its UI.'
}
ipconfig /flushdns | Out-Null
Write-Host 'ONE-TIME MANUAL STEP: Change the active Windows network adapter Preferred IPv4 DNS to 127.0.0.1.'
Write-Host 'Do not change your DNS settings until Acrylic is running, otherwise DNS queries may fail.'
Write-Host "Check: Resolve-DnsName admin.newstore.$Domain -Server 127.0.0.1"
