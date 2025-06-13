<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function getByNIK(Request $request, $nik) {
        // $validated = $request->validate([
        //     'nik' => 'required'
        // ]);

        return User::where('nik', '=', $nik)->get();
    }
    public function getAll(Request $request) {
        return User::all();
    }
}
