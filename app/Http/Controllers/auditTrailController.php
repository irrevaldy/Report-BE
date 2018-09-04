<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Session;
use Exception;

class auditTrailController extends Controller
{
  public function __construct(Request $request){

  }

  public function getAuditTrail(Request $request)
  {
    try
    {
      $now = $request->now;
      $past = $request->past;


      $data = DB::select("[spVDWH_ViewAuditTrail] '$past', '$now' ");

      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }
}
