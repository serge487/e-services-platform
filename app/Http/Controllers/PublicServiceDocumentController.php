<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;
use Illuminate\Support\Facades\Storage;

class PublicServiceDocumentController extends Controller
{
    public function download(RequestDocument $document)
    {
        abort_unless($document->type === 'official_response', 404);
        abort_unless(Storage::disk('private')->exists($document->file_path), 404);

        return Storage::disk('private')->download(
            $document->file_path,
            basename($document->file_path)
        );
    }
}
