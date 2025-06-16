<?php

namespace App\Http\Controllers;

use App\Models\Storage;
use finfo;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class StorageController extends Controller
{
    public function test(Request $request) {
        $path = $request->file('image')->getRealPath();
        // dd($blob_file);
        $actual_file = file_get_contents($path);
        $blob_file = base64_encode($actual_file);
        $storage = new Storage;
        $storage->file = $blob_file;
        $storage->file_name = Uuid::uuid4();
        if ($storage->save()) {
            return response()->json($storage);
        }
        return response()->json([
            'success' => false,
            'data' => [$storage]
        ]);
    }
    public function testGet(Request $request) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $storage = Storage::where(['file_name' => $request->name])->first();
        $document = stream_get_contents($storage->file);
        $file = base64_decode($document);
        $mimeType = $finfo->buffer($file);
        return response($file)
    ->header('Cache-Control', 'no-cache private')
    ->header('Content-Description', 'File Transfer')
    ->header('Content-Type', $mimeType)
    ->header('Content-length', strlen($file))
    ->header('Content-Disposition', 'attachment; filename=' . $storage->file_name)
    ->header('Content-Transfer-Encoding', 'binary');
    }
}

