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

class providerDashboardController extends Controller
{
    public function __construct(Request $request)
    {

    }

    public function getdata(Request $request)
    {
        // $current_month = date("Ym");
        // $current_month->modify('-1 month');
        // $current_month = date("Ym")."%";
        $last_day = date('Ymd')-01;

        // return $current_month;

        // $query_get_data     = DB::select("spFBankMonitoring_GetTotalAmount");
        // $total_amount       = $query_get_data[0]->total_amount;

        // $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrx");
        // $total_trx          = $query_get_data[0]->total_trx;

        // $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrxSuccess");
        // $total_trx_success  = $query_get_data[0]->total_trx;

        // $query_get_data     = DB::select("spFBankMonitoring_GetTotalTrxFailed");
        // $total_trx_failed   = $query_get_data[0]->total_trx;

        $query_get_data             = DB::select("spProvider_GetTop5HighestAmount_Acquirer");
        $query_get_data             = json_encode($query_get_data);
        $top5high_amount_bank       = json_decode($query_get_data, true);

        $query_get_data             = DB::select("spProvider_GetTop5LowestAmount_Acquirer");
        $query_get_data             = json_encode($query_get_data);
        $top5low_amount_bank        = json_decode($query_get_data, true);

        $query_get_data             = DB::select("spProvider_GetTop5HighestAmount_Merchant");
        $query_get_data             = json_encode($query_get_data);
        $top5high_amount_merchant   = json_decode($query_get_data, true);

        $query_get_data             = DB::select("spProvider_GetTop5LowestAmount_Merchant");
        $query_get_data             = json_encode($query_get_data);
        $top5low_amount_merchant   = json_decode($query_get_data, true);

        // $query_get_data             = DB::select("SELECT * FROM TTOTAL_TRX ");
        // $query_get_data             = json_encode($query_get_data);
        // $top5low_amount_merchant   = json_decode($query_get_data, true);

        // $query_get_data     = DB::select("spProvider_GetTotalTrxByCardtype");
        // $query_get_data     = json_encode($query_get_data);
        // $total_trx_cardtype = json_decode($query_get_data, true);

        // $query_get_data     = DB::select("spFProviderMonitoring_GetTotalTrxByAcquirer");
        // $query_get_data     = json_encode($query_get_data);
        // $total_trx_acquirer = json_decode($query_get_data, true);


        $resultBE['last_day'] = $last_day;
        // $resultBE['total_trx_acquirer'] = $total_trx_acquirer;
        // $resultBE['total_amount'] = $total_amount;    
        // $resultBE['total_trx'] = $total_trx;
        // $resultBE['total_trx_success'] = $total_trx_success;
        // $resultBE['total_trx_failed'] = $total_trx_failed;
        $resultBE['top5high_amount_bank'] = $top5high_amount_bank;
        $resultBE['top5low_amount_bank'] = $top5low_amount_bank;
        $resultBE['top5high_amount_merchant'] = $top5high_amount_merchant;
        $resultBE['top5low_amount_merchant'] = $top5low_amount_merchant;
        // $resultBE['total_trx_cardtype'] = $total_trx_cardtype;
        // $resultBE['total_trx_type'] = $total_trx_type;

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
