<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Hello extends Controller
{
   public function create()
    {
        return view('hello.create');
    }
}
