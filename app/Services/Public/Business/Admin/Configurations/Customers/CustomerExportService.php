<?php

namespace App\Services\Public\Business\Admin\Configurations\Customers;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class CustomerExportService
{
    private function columnValue(object $record, string $column): string
    {
        if ($column === 'is_active') {
            return $record->is_active ? t('Active', 'সক্রিয়') : t('Inactive', 'নিষ্ক্রিয়');
        }
        $value = $record->getAttribute($column);
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i');
        }
        return (string) ($value ?? '');
    }

    private function excelColumn(int $index): string
    {
        $name = '';
        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $name = chr(65 + ($index - 1) % 26).$name;
        }
        return $name;
    }

    private function xml(string $value): string
    {
        $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    public function excel(Builder $query, array $columns): BinaryFileResponse
    {
        abort_unless(class_exists(ZipArchive::class), 500, 'Enable PHP zip extension (extension=zip) to export Excel files.');
        $archive = tempnam(sys_get_temp_dir(), 'customers_xlsx_');
        $sheet = tempnam(sys_get_temp_dir(), 'customers_sheet_');
        abort_unless($archive !== false && $sheet !== false, 500, 'Cannot create temporary Excel export.');
        $file = fopen($sheet, 'wb');
        abort_unless($file !== false, 500, 'Cannot write temporary Excel export.');
        $count = 1;
        $lastColumn = $this->excelColumn(count($columns) - 1);

        try {
            fwrite($file, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
            fwrite($file, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetFormatPr defaultRowHeight="18"/><cols>');
            foreach (array_keys($columns) as $index => $column) {
                $width = in_array($column, ['address', 'address_bn', 'notes'], true) ? 38 : (in_array($column, ['id', 'is_active'], true) ? 13 : 23);
                fwrite($file, '<col min="'.($index + 1).'" max="'.($index + 1).'" width="'.$width.'" customWidth="1"/>');
            }
            fwrite($file, '</cols><sheetData><row r="1" ht="26" customHeight="1">');
            $headerIndex = 0;
            foreach ($columns as $meta) {
                $col = $this->excelColumn($headerIndex++);
                fwrite($file, '<c r="'.$col.'1" t="inlineStr" s="1"><is><t>'.$this->xml(t($meta['label'], $meta['label_bn'] ?? $meta['label'])).'</t></is></c>');
            }
            fwrite($file, '</row>');
            foreach ($query->cursor() as $record) {
                $count++;
                fwrite($file, '<row r="'.$count.'">');
                $index = 0;
                foreach (array_keys($columns) as $column) {
                    $col = $this->excelColumn($index++);
                    $value = $this->columnValue($record, $column);
                    if ($column === 'id') {
                        fwrite($file, '<c r="'.$col.$count.'"><v>'.(int) $record->id.'</v></c>');
                    } else {
                        fwrite($file, '<c r="'.$col.$count.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($value).'</t></is></c>');
                    }
                }
                fwrite($file, '</row>');
            }
            fwrite($file, '</sheetData><autoFilter ref="A1:'.$lastColumn.$count.'"/></worksheet>');
            fclose($file);
            $file = false;

            $zip = new ZipArchive();
            abort_unless($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Cannot initialize Excel archive.');
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Customers" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF5547D9"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs></styleSheet>');
            $zip->addFile($sheet, 'xl/worksheets/sheet1.xml');
            abort_unless($zip->close(), 500, 'Could not finalize Excel export.');
        } catch (\Throwable $e) {
            @unlink($archive);
            throw $e;
        } finally {
            if (is_resource($file)) {
                fclose($file);
            }
            @unlink($sheet);
        }

        return response()->download($archive, 'customers-'.now()->format('Ymd-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
        ])->deleteFileAfterSend(true);
    }

    public function pdf(Builder $query, Business $tenant): Response|\Illuminate\Http\RedirectResponse
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            return back()->with('error', t('PDF export requires dompdf. Run: composer require dompdf/dompdf', 'PDF ডাউনলোড করতে dompdf লাগবে। চালান: composer require dompdf/dompdf'));
        }
        if ((clone $query)->count() > 3000) {
            return back()->with('error', t('PDF exports are limited to 3,000 rows. Please narrow your filters.', 'PDF সর্বোচ্চ ৩০০০ রেকর্ড। ফিল্টার কমিয়ে নিন।'));
        }
        $records = $query->get();
        $html = view('public.business.admin.configurations.customers.export-pdf', [
            'records' => $records, 'business' => $tenant,
        ])->render();
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', public_path());
        $pdf = new \Dompdf\Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('a4', 'landscape');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="customers-'.now()->format('Ymd-His').'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
