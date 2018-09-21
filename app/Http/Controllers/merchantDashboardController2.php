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

class merchantDashboardController extends Controller
{
  public function __construct(Request $request)
  {

  }

  public function getData(Request $request)
  {
    $query_get_data     = DB::select("spVMonitoringReport_GetTotalBranch");
    $total_branch       = $query_get_data[0]->total_branch;

    $query_get_data     = DB::select("spVMonitoringReport_GetTotalStore");
    $total_store          = $query_get_data[0]->total_store;

    $query_get_data     = DB::select("spVMonitoringReport_GetTotalTerminal");
    $total_terminal      = $query_get_data[0]->total_terminal;

    $res['total_branch'] = $total_branch;
    $res['total_store'] = $total_store;
    $res['total_terminal'] = $total_terminal;

    return response($res);
  }

  public function getMonthlyBranchTransactionTop5(Request $request)
  {
    try
    {
      $data = DB::select("[spVMonitoringReport_MonthlyBranchTransactionTop5] '201807' ");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $res['success'] = true;
      $res['result'] = $data;

      return response($data);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getMonthlyBranchTransactionLow5(Request $request)
  {
    try
    {
      $data = DB::select("[spVMonitoringReport_MonthlyBranchTransactionLow5] '201807' ");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $res['success'] = true;
      $res['result'] = $data;

      return response($data);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getMonthlyStoreTransactionTop5(Request $request)
  {
    try
    {
      $data = DB::select("[spVMonitoringReport_MonthlyStoreTransactionHigh5]  '201807' ");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $res['success'] = true;
      $res['result'] = $data;

      return response($data);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getMonthlyStoreTransactionLow5(Request $request)
  {
    try
    {
      $data = DB::select("[spVMonitoringReport_MonthlyStoreTransactionLow5]  '201807' ");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $res['success'] = true;
      $res['result'] = $data;

      return response($data);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }
}
