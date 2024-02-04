<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignPdfFormRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class SignPdfController extends Controller
{
    function signPdf(SignPdfFormRequest $request)
    {
        $pdfFiles = [];
        if ($request->hasFile('pdf_file') && $request->hasFile('excel_file')) {
            $fileExcel = $request->file('excel_file');
            $filePdf = $request->file('pdf_file');
            $pdfPath = $filePdf->path();
            $filePdfName = pathinfo($filePdf->getClientOriginalName(), PATHINFO_FILENAME);
            $data = Excel::toArray([], $fileExcel);
            foreach ($data[0] as $row) {
                $text = trim($row[0]);
                if (!empty($text)) {
                    $outputFileName = $filePdfName . '_' . $text . '.pdf';
                    $this->fillPdfFile($pdfPath, $outputFileName, $text);
                    $pdfFiles[] = $outputFileName;
                }
            }
        }

        return response()->json([
            'status'  => true,
            'pdfFiles' => $pdfFiles
        ], 200);
    }

    public function fillPdfFile($file, $outputFileName, $text)
    {
        $tempDir = base_path('public/tmp');
        $mpdf = new Mpdf(['tempDir' => $tempDir]);
        $count = $mpdf->setSourceFile($file);
        for ($i = 1; $i <= $count; $i++) {
            $template = $mpdf->importPage($i);
            $size = $mpdf->getTemplateSize($template);
            $mpdf->AddPageByArray([
                'orientation' => $size['orientation'],
                'sheet-size' => [
                    $size['width'],
                    $size['height']
                ],
            ]);
            $mpdf->useTemplate($template);
            $mpdf->SetFont('times', '', 12);
            $mpdf->SetTextColor(242,185,195);
            $textWidth = $mpdf->GetStringWidth($text);
            $pageWidth = $size['width'];
            $left = $pageWidth - $textWidth - $mpdf->lMargin;
            $top = $mpdf->tMargin;
            $mpdf->WriteText($left, $top, $text);
        }

        return $mpdf->Output($outputFileName, 'F');
    }

    public function deletePdf(Request $request)
    {
        $pdfFiles = $request->pdfFiles;
        foreach ($pdfFiles as $pdfFile) {
            if (file_exists($pdfFile)) {
                unlink($pdfFile);
            }
        }

        return response()->json(['message' => 'PDF files deleted successfully.']);
    }
}
