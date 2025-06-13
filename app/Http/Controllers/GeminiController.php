<?php

namespace App\Http\Controllers;

use Gemini\Data\Blob;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function analyzeFile(Request $request) {

        // return "test";
        // dd($request->all());

        $validated = $request->validate([
            'file' => 'file|required'
        ]);

        // dd($request);

        // dd($validated['file']->extension() == 'jpg');

        // dd([$validated['file']->getClientMimeType(), MimeType::IMAGE_JPEG]);

        $fileMime = null;
        if ($validated['file']->extension() == 'jpg') {
            $fileMime = MimeType::IMAGE_JPEG;
        } else if ($validated['file']->extension() == 'png') {
            $fileMime = MimeType::IMAGE_PNG;
        } else if ($validated['file']->extension() == 'pdf') {
            $fileMime = MimeType::APPLICATION_PDF;
        } else {
            return response()->json([
                'success' => false,
                'error' => "expected png, jpg, or pdf files"
            ]);
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
                data: base64_encode($validated['file']->get())
            )
        ]);

        // dd($response->text());

        $reply = $response->text();

        $pattern = '/{.*}/s';

        // dd(json_decode($cleaned));
        preg_match($pattern, $reply, $match);
        $cleaned = str_replace("\n", "", $match[0]);
        // dd(json_decode($cleaned));
        return response()->json([
            'success' => true,
            'data' => [json_decode($cleaned)]]);

        // return $response->text();
        // return view('empty', ['reply' => $response->text()]);
    }

    public function test() {
        $pattern = '/{.*}/s';

        $subject = '```json
{
"Nama": "Nadin",
"Umur": "27",
"Pekerjaan": "Human Resources",
"NIK": "120321",
"Alamat": "Jl. Raya bojong seang",
"Nama Dokter": "Ardra",
"Tambahan": "Pasien memerlukan istirahat selama 57 hari, dari tanggal 01 Agustus 2025 s/d 27 September 2025, karena
sakit."
}
```';

        // dd(json_decode($cleaned));
        preg_match($pattern, $subject, $match);
        $cleaned = str_replace("\n", "", $match[0]);
        // dd(json_decode($cleaned));
        return response()->json(json_decode($cleaned));
    }
}
