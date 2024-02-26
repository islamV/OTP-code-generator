<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TowFactorAuth extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    return view("auth.otp");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
    $user = Auth::user();
    if($request->input("code") ==  $user->code) {
        $user->resetCode() ;
        return redirect('dashboard') ;
    }
        return redirect()->back()->withErrors(['code' => 'الكود الذي ادخلته غير صحيح!']) ;
    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
