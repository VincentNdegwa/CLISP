<?php

namespace App\Http\Controllers\Paddle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaddleDisplayController extends Controller
{
    public function show(Request $request){
        return view('paddle');
    }
}
