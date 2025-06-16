<?php

namespace App\Http\Controllers;

use App\Models\Clearance;
use App\Models\Storage as ModelsStorage;
use Carbon\Carbon;
use finfo;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class ClearanceController extends Controller
{
    public function test() {
        $path = Storage::path('vC6qZTGLvCIZutC5kbPcGIPECl0iAs2Hzx2vsqwJ.jpg');
        return response()->file($path);
    }

    public static function analyzeFile($file) {

        // return "test";
        // dd($request->all());

        // dd($request);

        // dd($validated['file']->extension() == 'jpg');

        // dd([$validated['file']->getClientMimeType(), MimeType::IMAGE_JPEG]);

        $fileMime = null;
        if ($file->extension() == 'jpg') {
            $fileMime = MimeType::IMAGE_JPEG;
        } else if ($file->extension() == 'png') {
            $fileMime = MimeType::IMAGE_PNG;
        } else if ($file->extension() == 'pdf') {
            $fileMime = MimeType::APPLICATION_PDF;
        } else {
            return false;
        }
        
        // dd($validated['file']->get());
        // dd($fileMime);

        $response = Gemini::generativeModel(model: 'gemini-2.0-flash')
        // ->withGenerationConfig(
        //     generationConfig: new GenerationConfig(
        //         responseMimeType: ResponseMimeType::APPLICATION_JSON,
        //         responseSchema: new Schema(
        //                 type: DataType::OBJECT,
        //                 properties: [
        //                     'Nama' => new Schema(type: DataType::STRING),
        //                     'Umur' => new Schema(type: DataType::INTEGER),
        //                     'Pekerjaan' => new Schema(type: DataType::STRING),
        //                     'NIK' => new Schema(type: DataType::STRING),
        //                     'Alamat' => new Schema(type: DataType::STRING),
        //                     'Nama Dokter' => new Schema(type: DataType::STRING)
        //                 ]
        //             )
        //         )
        //     )
        ->generateContent([
            'Dari file surat keterangan dokter berikut, gunakan POV seperti anda mengisi formulir yang berisikan kolom Nama, Umur, Pekerjaan, NIK, Alamat, Nama Dokter, dan tambahan. kolom tambahan berisikan keterangan penting lainnya yang belum terdata pada kolom-kolom tadi dengan maksimum kata sebanyak 50, keluarkan hanya hasil saja dengan format JSON',
            new Blob(
                mimeType: $fileMime,
                data: base64_encode($file->get())
            )
        ]);

        // dd($response->text());

        $reply = $response->text();

        $pattern = '/{.*}/s';

        // dd(json_decode($cleaned));
        preg_match($pattern, $reply, $match);
        $cleaned = str_replace("\n", "", $match[0]);
        // dd(json_decode($cleaned));
        return $cleaned;

        // return $response->text();
        // return view('empty', ['reply' => $response->text()]);
    }

    public static function storeFile($fileData) {
        $path = $fileData->getRealPath();
        // dd($blob_file);
        $actual_file = file_get_contents($path);
        $blob_file = base64_encode($actual_file);
        $storage = new ModelsStorage;
        $storage->file = $blob_file;
        $storage->file_name = Uuid::uuid4();

        if ($storage->save()) {
            return $storage->file_name;
        }
        return false;
    }

    public function create(Request $request) {
        $validated = $request->validate([
            'jenis' => 'required',
            'alasan' => 'required',
            'tanggal_mulai' => 'required|date_format:Y-m-d',
            'tanggal_akhir' => 'required|date_format:Y-m-d',
            'bukti' => 'required|file',
        ]);

        
        $clearance = new Clearance();
        
        $clearance->user_id = $request->user()->id;
        $clearance->jenis = $request->jenis;
        $clearance->alasan = $request->alasan;
        $clearance->tanggal_mulai = $request->tanggal_mulai;
        $clearance->tanggal_akhir = $request->tanggal_akhir;
        $clearance->tanggal_pengajuan = Carbon::now()->toDateString();
        
        $path = ClearanceController::storeFile($request->file('bukti'));
        if ($path) {
            $clearance->bukti = $path;
            $reply = $this->analyzeFile($validated['bukti']);
            if ($reply) {
                $clearance->details = $reply;
                if ($clearance->save()) {
                    $clearance->details = json_decode($reply);
                    return response()->json([
                        'success' => true,
                        'data' => [$clearance]
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'error' => 'failed to save data'
                ])->setStatusCode(500);
            }
            return response()->json([
                'success' => false,
                'error' => 'invalid file type please use PDF, JPG, or PNG'
            ])->setStatusCode(400);
        }
        
        return response()->json([
                'success' => false,
                'error' => 'failed to store file'
            ])->setStatusCode(500);

        // return $reply->json();
        // $path = $request->file('bukti')->store();

        // return $path;


    }

    public function updateStatus(Request $request, $id) {
        $validated = $request->validate([
            'status' => 'required'
        ]);

        $clearance = Clearance::where(['id' => $id])->first();

        if ($clearance) {
            $clearance->status = $validated['status'];
            if ($clearance->save()) {
                $clearance->details = json_decode($clearance->details);
                return response()->json([
                    'success' => true,
                    'data' => $clearance
                ]);
            }   
            return response()->json([
                    'success' => false,
                    'error' => 'failed to update data'
                ])->setStatusCode(500);

        }

        return response()->json([
            'success' => false,
            'error' => 'clearance not found'
        ])->setStatusCode(400);
    }

    public function retrieveBukti(Request $request, $id) {
        $clearance = Clearance::where(['id' => $id])->first();
        if (! $clearance) {
            return response()->json([
                'success' => false,
                'error' => 'no clearance matches the id'
            ]);
        }
        $path = $clearance->bukti;
        // dd($path);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $storage = ModelsStorage::where(['file_name' => $path])->first();
        // dd($storage);
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
        // return response()->file($path);
    }

    public function getAll() {
        return response()->json([
            'success' => true,
            'data' => Clearance::get()
        ]);
    }
    public function getAllUser(Request $request) {
        return response()->json([
            'success' => true,
            'data' => Clearance::where(['user_id' => $request->user()->id])->get()
        ]);
    }
    public function getDetailed($id) {
        $clearance = Clearance::where(['id' => $id])->first();
        $clearance->details = json_decode($clearance->details);
        
        return response()->json([
            'success' => true,
            'data' => $clearance
        ]);
    }


}
