<?php

namespace App\Http\Controllers;

use App\Models\HRD;
use App\Http\Requests\StoreHRDRequest;
use App\Http\Requests\UpdateHRDRequest;
use Illuminate\Http\Request;

class HRDController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required']);
        
        return HRD::insertGetId(["user_id" => $validated['user_id']]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required']);

        return HRD::where('user_id', '=', $validated['user_id'])->delete();
    }

    public function isHrd(Request $request) {
        return HRD::where(['user_id' => $request->user()->id])->exists();
    }
}
