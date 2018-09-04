<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class chartController extends Controller
{
  public function __construct(Request $request)
  {

  }

  public function barChart(Request $request)
  {
    try
    {
      // $devlist = DB::table('TEDC_MERCHANT')
      //       ->select(DB::raw('FMERCH_ID'), DB::raw("count(FMERCH_ID) as total"))
      //       ->groupBy('FMERCH_ID')
      //      ->get();

       $devlist = DB::select("[spVDWH_GetCountEDC_FMERCHID]");

			return response($devlist);
		}
    catch(QueryException $ex){
			$res['success'] = 'error';
			$res['result'] = 'Query Exception.. Please Check Database!';

			return response($res);
		}
  }

}
