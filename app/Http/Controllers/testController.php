<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class testController extends Controller
{
  public function index(Request $request)
  {
    echo env('APP_DEBUG');
  }
}
