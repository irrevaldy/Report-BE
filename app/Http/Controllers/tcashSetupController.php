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

class tcashSetupController extends Controller
{
  public function __construct(Request $request){

  }

  public function setTcashLimit(Request $request)
  {
    try
    {
      $storeCode = $request->storeCode;
      $newLimit = $request->newLimit;

      $data = DB::statement("[spVDWH_setTcashLimit] '$storeCode', '$newLimit' ");

      $res['success'] = true;
      $res['result'] = "sukses";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function checkLimit(Request $request)
  {
    try
    {

      $storeCode = $request->storeCode;
      $branchCode = $request->branchCode;
      $merch_id = $request->merch_id;

      $data = DB::select("[spVDWH_checkLimit] '$storeCode', '$branchCode', '$merch_id' ");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $limit = $data[0]['FTCASHTOPUPLIMIT'];

      if($limit == '')
      {
        $limit = '0';

        $limit_data['status'] = "not found";
        $limit_data['limit'] = $limit;
      }
      else
      {
        $limit_data['status'] = "found";
        $limit_data['limit'] = $limit;
      }

      $res['success'] = true;
      $res['result'] = $limit_data;

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
