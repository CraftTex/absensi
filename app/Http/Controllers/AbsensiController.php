<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AbsensiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class AbsensiController extends Controller
{
    public function create(Request $request) {
        // dd(Carbon::now()->toTimeString());
        $validated = $request->validate([
            'lokasi' => 'required'
        ]);

        $user = $request->user();
        $absensi = new Absensi;

        $absensi->user_id = $user->id;
        $absensi->tanggal = Carbon::now()->toDateString();
        $absensi->lokasi = $validated['lokasi'];

        if (Absensi::where(['user_id' => $user->id, 'tanggal' => Carbon::now()->toDateString()])->exists()) {
            return response()->json([
                'success' => false,
                'data' => 'Error: duplicate entry'
            ])->setStatusCode(400);
        }


        if ($absensi->save()) {
            return response()->json([
                'success' => true,
                'data' => Absensi::where(['id' => $absensi->id])->get()
            ]);
        }

        return response()->json([
            'success' => false,
            'data' => 'Error: insert failed'
        ])->setStatusCode(500);
    }

    public function getTodayUserAbsensi(Request $request) {
        // $absensi = Absensi::where([
        //         'tanggal' => Carbon::now()->toDateString(),
        //         'user_id' => $request->user()->id])
        //         ->first();
        
        // $absensi->masuk_details = AbsensiDetail::where([
        //     'id' => $absensi->masuk_details])->first();
        // $absensi->keluar_details = AbsensiDetail::where([
        //     'id' => $absensi->keluar_details])->first();
        
        return response()->json([
            'success' => true,
            'data' => Absensi::with(['masuk_detail', 'keluar_detail'])->where([
                'tanggal' => Carbon::now()->toDateString(),
                'user_id' => $request->user()->id])
                ->get()
        ]);
    }

    public function getAllUserAbsensi(Request $request) {
        return response()->json([
            'success' => true,
            'data' => Absensi::with(['masuk_detail', 'keluar_detail'])
            ->where(['user_id' => $request->user()->id])
            ->get()
        ]);
    }
    public function getAllAbsensi(Request $request) {
        if (Absensi::exists()) {
            return response()->json([
                    'success' => true,
                    'data' => Absensi::with(['masuk_detail', 'keluar_detail'])
                    ->get()
                ]);
        }
        return response()->json([
                    'success' => true,
                    'data' => Absensi::get()
                ]);
        
    }
    public function getAllLocationAbsensi(Request $request, $location) {
        return response()->json([
            'success' => true,
            'data' => Absensi::with(['masuk_detail', 'keluar_detail'])
            ->where(['lokasi' => $location])
            ->get()
        ]);
    }

    public function checkIn(Request $request) {
        $currTime = Carbon::now()->toTimeString();
        
        $user = $request->user();

        $validated = $request->validate([
            'feeling' => 'required',
            'kondisi' => 'present'
        ]);

        // dd(![]);

        $conditions = [
                'user_id' => $user->id,
                'tanggal' => Carbon::now()->toDateString()
        ];

        $absensi = Absensi::where($conditions)
        ->whereNull('masuk_details')->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'data' => 'Error: valid absensi not found failed'
            ])->setStatusCode(500);
        }


        $checkin = new AbsensiDetail;

        $checkin->waktu = $currTime;
        $checkin->feeling = $validated['feeling'];
        $checkin->kondisi = $validated['kondisi'];

        if (! $checkin->save()) {
            return response()->json([
                'success' => false,
                'data' => 'Error: checkin insert failed'
            ])->setStatusCode(500);
        }
        
        $absensi->masuk_details = $checkin->id;
        if ($absensi->save()) {
            $absensi->masuk_details = $checkin;
            return response()->json([
                'success' => true,
                'data' => [$absensi]
            ]);
        }

        $checkin->delete();

        return response()->json([
            'success' => false,
            'data' => 'Error: absensi update failed'
        ])->setStatusCode(500);

    }
    public function checkOut(Request $request) {
        $currTime = Carbon::now()->toTimeString();
        
        $user = $request->user();

        $validated = $request->validate([
            'feeling' => 'required',
            'kondisi' => 'present'
        ]);

        // dd(![]);

        $conditions = [
                'user_id' => $user->id,
                'tanggal' => Carbon::now()->toDateString()
        ];

        $absensi = Absensi::where($conditions)
        ->whereNull('keluar_details')->whereNotNull('masuk_details')->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'data' => 'Error: valid absensi not found'
            ])->setStatusCode(500);
        }


        $checkout = new AbsensiDetail;

        $checkout->waktu = $currTime;
        $checkout->feeling = $validated['feeling'];
        $checkout->kondisi = $validated['kondisi'];

        if (! $checkout->save()) {
            return response()->json([
                'success' => false,
                'data' => 'Error: checkout insert failed'
            ])->setStatusCode(500);
        }
        
        $absensi->keluar_details = $checkout->id;
        if ($absensi->save()) {
            $absensi->masuk_details = AbsensiDetail::where(['id' => $absensi->masuk_details])->first();
            $absensi->keluar_details = $checkout;
            return response()->json([
                'success' => true,
                'data' => [$absensi]
            ]);
        }

        $checkout->delete();

        return response()->json([
            'success' => false,
            'data' => 'Error: absensi update failed'
        ])->setStatusCode(500);

    }
}
