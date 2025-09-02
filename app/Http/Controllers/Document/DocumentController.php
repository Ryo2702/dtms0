<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    private $documents = [
        [
            'title' => "Mayor's Clearance",
            'file'  => 'Mayors_Clearance.docx',
            'template' => 'Mayors_Clearance.docx',
        ],
        [
            'title' => "MPOC Sample",
            'file'  => 'MPOC_Sample.docx',
            'template' => 'MPOC_Sample.docx',
        ],
    ];

    public function index()
    {
        $documents = $this->documents;
        return view('documents.index', compact('documents'));
    }

    // new: show the form to fill fields before generating document
    public function form($file)
    {
        $docInfo = collect($this->documents)->firstWhere('file', $file);
        if (!$docInfo) {
            abort(404, "Document not found.");
        }

        // Route to specific view based on document type
        if ($file === 'Mayors_Clearance.docx') {
            return view('documents.mayors-clearance');
        }

        if ($file === 'MPOC_Sample.docx') {
            return view('documents.mpoc-sample');
        }

        // Default form for other documents
        $defaults = [
            'name' => optional(Auth::user())->name ?? '',
            'address' => '',
            'fee' => '',
            'or_number' => '',
            'date' => now()->format('F d, Y'),
        ];

        return view('documents.fill', [
            'doc' => $docInfo,
            'defaults' => $defaults,
        ]);
    }

    // Helper function to format date with ordinal suffix
    private function formatDateWithOrdinal($date)
    {
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = date('F', $timestamp);
        $year = date('Y', $timestamp);

        // Add ordinal suffix to day
        $suffix = 'th';
        if ($day % 10 == 1 && $day != 11) {
            $suffix = 'st';
        } elseif ($day % 10 == 2 && $day != 12) {
            $suffix = 'nd';
        } elseif ($day % 10 == 3 && $day != 13) {
            $suffix = 'rd';
        }

        return "{$day}{$suffix} day of {$month}, {$year}";
    }

    // modified: accept Request so we can receive the filled fields
    public function download(Request $request, $file)
    {
        $docInfo = collect($this->documents)->firstWhere('file', $file);

        if (!$docInfo) {
            abort(404, "Document not found.");
        }

        // Enhanced validation rules based on document type
        $rules = [];

        if ($file === 'Mayors_Clearance.docx') {
            $rules = [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:1000',
                'fee' => 'nullable|string|max:100',
                'or_number' => 'nullable|string|max:100',
                'date' => 'nullable|string|max:50',
                'purpose' => 'required|string|max:255',
            ];
        } elseif ($file === 'MPOC_Sample.docx') {
            $rules = [
                'barangay_chairman' => 'required|string|max:255',
                'barangay_name' => 'required|string|max:255',
                'barangay_clearance_date' => 'required|date',
                'resident_name' => 'required|string|max:255',
                'resident_barangay' => 'required|string|max:255',
                'certification_date' => 'nullable|date',
                'requesting_party' => 'required'
            ];
        } else {
            // Default rules for other documents
            $rules = [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:1000',
                'fee' => 'nullable|string|max:100',
                'or_number' => 'nullable|string|max:100',
                'date' => 'nullable|string|max:50',
            ];
        }

        $data = $request->validate($rules);

        $templatePath = storage_path("app/public/templates/{$docInfo['template']}");
        if (!file_exists($templatePath)) {
            abort(404, "Template not found.");
        }

        // Generate document ID
        $documentId = "DT-" . now()->format('Ymd') . "-" . strtoupper(Str::random(6));

        // Get current user and their employee_id
        $user = Auth::user();
        $employeeId = $user?->employee_id ?? 'N/A';
        $userName = $user?->name ?? 'Guest';
        $department = $user?->department?->name ?? null;

        // Build QR text
        $qrText = "Document ID: {$documentId}\n";
        $qrText .= "Title: {$docInfo['title']}\n";
        $qrText .= "Employee ID: {$employeeId}\n";
        $qrText .= "Name: {$userName}\n";
        if ($department) {
            $qrText .= "Department: {$department}\n";
        }
        $qrText .= "Issued at Bansud, Oriental Mindoro";

        $qr = QrCode::create($qrText)->setSize(72);
        $writer = new PngWriter();
        $qrPath = storage_path("app/public/qrcode_{$documentId}.png");
        $writer->write($qr)->saveToFile($qrPath);

        $templateProcessor = new TemplateProcessor($templatePath);

        // Replace common placeholders
        $templateProcessor->setValue('title', $docInfo['title']);
        $templateProcessor->setValue('issued_at', now()->format('F d, Y'));
        $templateProcessor->setValue('document_id', $documentId);
        $templateProcessor->setValue('employee_id', $employeeId);

        if ($department) {
            $templateProcessor->setValue('department', $department);
        }

        // Replace document-specific placeholders
        if ($file === 'Mayors_Clearance.docx') {
            $templateProcessor->setValue('name', $data['name']);
            $templateProcessor->setValue('address', $data['address']);
            $templateProcessor->setValue('fee', $data['fee'] ?? '');
            $templateProcessor->setValue('or_number', $data['or_number'] ?? '');
            $templateProcessor->setValue('date', $data['date'] ?? now()->format('F d, Y'));
            $templateProcessor->setValue('purpose', $data['purpose']);
        } elseif ($file === 'MPOC_Sample.docx') {
            // Format dates with ordinal suffixes for MPOC
            $barangayClearanceDate = $this->formatDateWithOrdinal($data['barangay_clearance_date']);
            $certificationDate = $data['certification_date']
                ? $this->formatDateWithOrdinal($data['certification_date'])
                : $this->formatDateWithOrdinal(now()->format('Y-m-d'));
            $templateProcessor->setValue('date', $data['date'] ?? now()->format('F d, Y'));
            $templateProcessor->setValue('barangay_chairman', $data['barangay_chairman']);
            $templateProcessor->setValue('barangay_name', $data['barangay_name']);
            $templateProcessor->setValue('barangay_clearance_date', $barangayClearanceDate);
            $templateProcessor->setValue('resident_name', $data['resident_name']);
            $templateProcessor->setValue('resident_barangay', $data['resident_barangay']);
            $templateProcessor->setValue('certification_date', $certificationDate);
            $templateProcessor->setValue('requesting_party', $data['requesting_party']);
        }

        // Insert QR code
        $templateProcessor->setImageValue('qrcode', [
            'path' => $qrPath,
            'width' => 72,
            'height' => 72,
        ]);

        // Save temp file
        $outputPath = storage_path("app/public/generated_{$file}");
        $templateProcessor->saveAs($outputPath);

        return response()->download($outputPath, $docInfo['file'])->deleteFileAfterSend(true);
    }
}
