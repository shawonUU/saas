<?php
namespace App\Services\Public\Business\Admin\Shared;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ListExportService
{
    private function text(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) return $value->format('d/m/Y H:i');
        if (is_bool($value)) return $value ? t('Yes', 'হ্যাঁ') : t('No', 'না');
        if (is_array($value) || is_object($value)) return is_scalar($value) ? (string)$value : '';
        return (string)($value ?? '');
    }

    private function xml(string $value): string
    {
        $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function label(mixed $meta, string $fallback): string
    {
        return is_array($meta) ? t($meta['label'] ?? $fallback, $meta['label_bn'] ?? ($meta['label'] ?? $fallback)) : ucwords(str_replace('_', ' ', $fallback));
    }

    private function column(int $index): string
    {
        $name = '';
        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) $name = chr(65 + ($index - 1) % 26).$name;
        return $name;
    }

    public function download(Builder $query, array $columns, string $title, string $format): mixed
    {
        abort_unless(in_array($format, ['excel','pdf'], true), 404);
        abort_if(!count($columns), 422);
        $max = $format === 'pdf' ? 2000 : 50000;
        if ((clone $query)->reorder()->limit($max + 1)->count() > $max) {
            return back()->with('error', t('Too many records to export. Narrow your filters.', 'রেকর্ড বেশি হয়েছে। ফিল্টার ব্যবহার করুন।'));
        }
        $filename = Str::slug($title) ?: 'records';
        return $format === 'excel' ? $this->excel($query, $columns, $filename) : $this->pdf($query, $columns, $title, $filename);
    }

    private function excel(Builder $query, array $columns, string $filename): BinaryFileResponse
    {
        abort_unless(class_exists(ZipArchive::class), 500, 'PHP zip extension is required for XLSX export.');
        $archive = tempnam(sys_get_temp_dir(), 'saas-list-');
        $sheet = tempnam(sys_get_temp_dir(), 'saas-sheet-');
        abort_unless($archive && $sheet, 500, 'Temporary export files cannot be created.');
        $handle = fopen($sheet, 'wb');
        abort_unless($handle, 500, 'Temporary export cannot be written.');
        try {
            fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>');
            $row = 1;
            fwrite($handle, '<row r="1">');
            $idx = 0;
            foreach ($columns as $field=>$meta) {
                $cell = $this->column($idx++).'1';
                fwrite($handle, '<c r="'.$cell.'" t="inlineStr"><is><t>'.$this->xml($this->label($meta, $field)).'</t></is></c>');
            }
            fwrite($handle, '</row>');
            foreach ($query->cursor() as $record) {
                $row++; $idx=0; fwrite($handle, '<row r="'.$row.'">');
                foreach (array_keys($columns) as $field) {
                    $cell = $this->column($idx++).$row;
                    $value = $this->xml($this->text(data_get($record,$field)));
                    fwrite($handle, '<c r="'.$cell.'" t="inlineStr"><is><t xml:space="preserve">'.$value.'</t></is></c>');
                }
                fwrite($handle, '</row>');
            }
            fwrite($handle, '</sheetData><autoFilter ref="A1:'.$this->column(count($columns)-1).$row.'"/></worksheet>');
            fclose($handle);$handle=null;
            $zip=new ZipArchive();
            abort_unless($zip->open($archive,ZipArchive::CREATE|ZipArchive::OVERWRITE)===true,500,'XLSX archive unavailable.');
            $zip->addFromString('[Content_Types].xml','<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            $zip->addFromString('_rels/.rels','<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml','<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Records" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels','<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
            $zip->addFile($sheet, 'xl/worksheets/sheet1.xml');
            abort_unless($zip->close(),500,'XLSX could not be finalized.');
        } catch (\Throwable $e) { @unlink($archive); throw $e; }
        finally { if (is_resource($handle)) fclose($handle); @unlink($sheet); }
        return response()->download($archive, $filename.'-'.now()->format('Ymd-His').'.xlsx', ['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','Cache-Control'=>'private, no-store'])->deleteFileAfterSend(true);
    }

    private function pdf(Builder $query, array $columns, string $title, string $filename): mixed
    {
        if (!class_exists(\Dompdf\Dompdf::class)) {
            return back()->with('error', t('Install dompdf to download PDF: composer require dompdf/dompdf', 'PDF ডাউনলোডের জন্য dompdf ইনস্টল করুন।'));
        }
        $rows = [];
        foreach ($query->get() as $record) {
            $item = [];
            foreach (array_keys($columns) as $field) $item[] = $this->text(data_get($record, $field));
            $rows[] = $item;
        }
        $labels=[];
        foreach ($columns as $field=>$meta) $labels[]=$this->label($meta,$field);
        $html=view('public.business.admin.shared.list-ui.export-pdf',compact('title','labels','rows'))->render();
        $options=new \Dompdf\Options();$options->set('isRemoteEnabled',false);$options->set('defaultFont','DejaVu Sans');
        $pdf=new \Dompdf\Dompdf($options);$pdf->loadHtml($html,'UTF-8');$pdf->setPaper('a4','landscape');$pdf->render();
        return response($pdf->output(),200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="'.$filename.'-'.now()->format('Ymd-His').'.pdf"','Cache-Control'=>'private, no-store']);
    }
}
