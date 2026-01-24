<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class PublicDocumentController extends Controller
{
    /**
     * Display publicly available documents (published + public confidentiality).
     */
    public function index(Request $request)
    {
        $query = Document::with('documentType')
            ->where('status', Document::STATUS_PUBLISHED)
            ->where('confidentiality', Document::CONF_PUBLIC);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        $documents = $query->latest('published_at')->paginate(12)->withQueryString();

        return view('public.documents.index', [
            'documents' => $documents,
            'search' => $request->input('search', ''),
        ]);
    }
}
