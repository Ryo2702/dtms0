<?php

namespace App\Http\Controllers\Document;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Endroid\QrCode\Writer\PngWriter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    private $documents = [
        [
            'title' => "Mayor's Clearance",
            'file'  => 'M_Clearance.docx',
            'template' => 'M_Clearance.docx',
        ],
        [
            'title' => "MPOC",
            'file'  => 'MPOC.docx',
            'template' => 'MPOC.docx',
        ],
    ];


    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        //check permission for document generate
        if (!$this->canGenerateDocuments($user)) {
            abort(403, 'You do not have permission to generate documents');
        }
        $documents = $this->documents;

        $permissions = [
            'can_generate_for_others' => $user->isAdmin() || $user->isHead(),
            'can_generate_own' => true,
            'user_type' => $user->type,
            'department_name' => $user->department->name ?? 'N/A'
        ];

        $departmentUsers = [];
        if ($user->isHead()) {
            $departmentUsers = User::where('department_id', $user->department_id)
                ->where('status', 1)
                ->select('id', 'name', 'municipal_id', 'type')
                ->get();
        } elseif ($user->isAdmin()) {
            $departmentUsers = User::with('department')
                ->where('status', 1)
                ->select('id', 'name', 'municipal_id', 'type', 'department_id')
                ->get();
        }
        return view('documents.index', compact('documents', 'permissions', 'departmentUsers'));
    }

    public function canGenerateDocuments($user): bool
    {
        return $user && $user->status && in_array($user->type, ['Admin', 'Head', 'Staff']);
    }

    public function download(Request $request, $file)
    {
        $docInfo = collect($this->documents)->firstWhere('file', $file);

        if (!$docInfo) {
            abort(404, "Document not found.");
        }

        $currentUser = Auth::user();
        if (!$currentUser) {
            abort(401, "User not Authenticated");
        }

        $templatePath = storage_path("app/public/templates/{$docInfo['template']}");
        if (!file_exists($templatePath)) {
            abort(404, "Template not found.");
        }

        $documentId = "DOC-" . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        $municipalId = $currentUser->municipal_id;

        $department = $currentUser->department;
        $departmentName = $department ? $department->name : 'N/A';
        $departmentCode = $department ? $department->code : 'N/A';

        $qr = QrCode::create("Document ID: {$documentId}\nTitle: {$docInfo['title']}\nIssued at Bansud, Oriental Mindoro")
            ->setSize(80); //1.2 inch
        $writer = new PngWriter();
        $qrPath = storage_path("app/public/qrcode.png");
        $writer->write($qr)->saveToFile($qrPath);


        $templateProcessor = new TemplateProcessor($templatePath);

        //placeholder in document
        $templateProcessor->setValue('title', $docInfo['title']);
        $templateProcessor->setValue('issued_at', now()->format('F d, Y'));
        $templateProcessor->setValue('document_id', $documentId);

        // Embed QR code
        $templateProcessor->setImageValue('qrcode', [
            'path' => $qrPath,
            'width' => 80,
            'height' => 80,
        ]);

        // Save temp file
        $outputPath = storage_path("app/public/document/generated_{$file}");
        $templateProcessor->saveAs($outputPath);

        return response()->download($outputPath, $docInfo['file'])->deleteFileAfterSend(true);
    }
}
