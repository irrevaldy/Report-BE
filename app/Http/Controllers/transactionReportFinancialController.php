<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Session;

class transactionReportFinancialController extends Controller
{
  public function __construct(){

  }

  public function insertAuditTrail(Request $request){

    date_default_timezone_set('Asia/Jakarta');
    $now = date("YmdHis");

    $user_id = $request->user_id;
    $username = $request->username;
    $name = $request->name;

    $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '13', '$user_id', '$username', '$name', $now, 'Generate Transaction Report Financial Laravel'");

    $res['success'] = true;
    $res['result'] = 'Audit Trail success';

    return $res;
  }
}
