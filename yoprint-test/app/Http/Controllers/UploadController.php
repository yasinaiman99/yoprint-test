<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Jobs\ProcessCsvUpload;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function index()
    {
        $uploads = Upload::latest()->get();
        return view('uploads.index', compact('uploads'));
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads', $filename);

        $upload = Upload::create([
            'filename' => $filename,
            'status' => 'pending'
        ]);

        ProcessCsvUpload::dispatch($upload, $path);

        return redirect()->back()->with('success', 'File uploaded! Processing started.');
    }

    public function status()
    {
        $uploads = Upload::orderBy('created_at', 'desc')->get()->map(function($u) {
            return [
                'id' => $u->id,
                'filename' => $u->filename,
                'status' => $u->status,
                'created_at_formatted' => $u->created_at->format('Y-m-d h:i A'),
                'created_at_human' => $u->created_at->diffForHumans(),
            ];
        });

        return response()->json($uploads);
    }

}

