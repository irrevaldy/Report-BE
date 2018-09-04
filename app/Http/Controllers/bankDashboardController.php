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

class bankDashboardController extends Controller
{
    public function __construct(Request $request)
    {

    }

    public function getdata(Request $request)
    {
        // $current_month = date("Ym");
        // $current_month->modify('-1 month');
        // $current_month = date("Ym")."%";
        $last_month = date('m')-01;

        // return $current_month;

        $query_get_data     = DB::select("spFBankMonitoring_GetTotalAmount");
        $total_amount       = $query_get_data[0]->total_amount;

        $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrx");
        $total_trx          = $query_get_data[0]->total_trx;

        $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrxSuccess");
        $total_trx_success  = $query_get_data[0]->total_trx;

        $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrxFailed");
        $total_trx_failed   = $query_get_data[0]->total_trx;

        $query_get_data     = DB::select("spFBankMonitoring_GetTop5HighestAmount");
        $query_get_data     = json_encode($query_get_data);
        $top5high_amount    = json_decode($query_get_data, true);

        $query_get_data     = DB::select("spFBankMonitoring_GetTop5LowestAmount");
        $query_get_data     = json_encode($query_get_data);
        $top5low_amount     = json_decode($query_get_data, true);

        $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrxByCardtype");
        $query_get_data     = json_encode($query_get_data);
        $total_trx_cardtype = json_decode($query_get_data, true);

        $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrxByTrxtype");
        $query_get_data     = json_encode($query_get_data);
        $total_trx_type     = json_decode($query_get_data, true);


        $resultBE['last_month'] = $last_month;
        $resultBE['total_amount'] = $total_amount;    
        $resultBE['total_trx'] = $total_trx;
        $resultBE['total_trx_success'] = $total_trx_success;
        $resultBE['total_trx_failed'] = $total_trx_failed;
        $resultBE['top5high_amount'] = $top5high_amount;
        $resultBE['top5low_amount'] = $top5low_amount;
        $resultBE['total_trx_cardtype'] = $total_trx_cardtype;
        $resultBE['total_trx_type'] = $total_trx_type;

        return response($resultBE);
        // DB::beginTransaction();

        // try{ 
        //     $q_get_total_amount = DB::statement("");

        //     if(!$q_get_total_amount){
        //         $resultBE[] = false;
        //         DB::rollBack();   
        //         return response($resultBE);     
        //     }else{
        //         $resultBE[] = true;
        //         DB::commit();
        //         return response($resultBE);
        //     }
            

        // }
        // catch(Exception $e){

        //     $resultBE[] = false;
        //     DB::rollBack();
        //     return response($resultBE);
        // }
    }
}
