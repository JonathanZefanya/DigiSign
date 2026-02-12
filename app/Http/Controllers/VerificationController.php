<?php

namespace App\Http\Controllers;

use App\Models\Document;

class VerificationController extends Controller
{
    /**
     * Show the public verification page.
     */
    public function show(string $hash)
    {
        $document = Document::with('user')->where('document_hash', $hash)->first();

        return view('public.verify', [
            'document' => $document,
            'hash' => $hash,
            'isValid' => $document && $document->isSigned(),
        ]);
    }
}
