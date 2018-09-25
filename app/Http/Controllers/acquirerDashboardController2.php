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

class acquirerDashboardController extends Controller
{
    public function __construct(Request $request)
    {

    }

    public function getData(Request $request, $user_id)
    {

        //get user data
        $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
        $username = $q_get_user_data[0]->user_name;

        $get_current_month = date("Ym")."00000000";
        $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
        $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

	
        //total terminal
        $q_get_total_summary = DB::select("spVDWH_GetTotalSummary '$username'");
        $total_acquirer 	= $q_get_total_summary[0]->total_acquirer;
        $total_corporate 	= $q_get_total_summary[0]->total_corporate;
        $total_merchant 	= $q_get_total_summary[0]->total_merchant;
        $total_branch 	    = $q_get_total_summary[0]->total_branch;
        $total_store 	    = $q_get_total_summary[0]->total_store;
        
        $total_terminal 	= $q_get_total_summary[0]->total_terminal;
        $terminal_active 	= $q_get_total_summary[0]->total_active;
        $terminal_inactive 	= $q_get_total_summary[0]->total_inactive;
        $total_active_trx 	= $q_get_total_summary[0]->total_active_trx;

        $total_trx_volume 	= $q_get_total_summary[0]->total_trx_volume;
        $total_trx_success 	= $q_get_total_summary[0]->total_trx_success;
        $total_trx_failed 	= $q_get_total_summary[0]->total_trx_failed;
        $total_trx_count 	= $total_trx_success+$total_trx_failed;
        
        // $res['total_acquirer'] 		= $total_acquirer;
        // $res['total_corporate']     = $total_corporate;
        // $res['total_merchant'] 		= $total_merchant;
        // $res['total_branch'] 		= $total_branch;
        // $res['total_store'] 		= $total_store;
        $res['total_terminal'] 		= $total_terminal;
        $res['terminal_active'] 	= $terminal_active;
        $res['terminal_inactive'] 	= $terminal_inactive;
        $res['total_active_trx'] 	= $total_active_trx;
        $res['total_trx_volume'] 	= $total_trx_volume;
        $res['total_trx_success'] 	= $total_trx_success;
        $res['total_trx_failed'] 	= $total_trx_failed;
        $res['total_trx_count'] 	= $total_trx_count;


        /*----------------------- ON-US OFF-US ------------------------*/
        // $q_get_onusoffus_trx = DB::select("spVDWH_GetOnUsOffUsTrxData '20170300000000', '20170400000000', '$username'");
        $q_get_onusoffus_trx = DB::select("spVDWH_GetOnUsOffUsTrxData '$get_past1_month', '$get_current_month', '$username'");
        $q_get_onusoffus_trx = json_encode($q_get_onusoffus_trx);
        $q_get_onusoffus_trx = json_decode($q_get_onusoffus_trx, true);

        $get_offus_trxcount = "0";
        $get_offus_trxvolume = "0";
        $get_onus_trxcount = "0";
        $get_onus_trxvolume = "0";
        
        foreach( $q_get_onusoffus_trx as $value ) {
        
        if($value['FOFFUS'] == "1"){
            
            $get_offus_trxcount = $value['TRX_COUNT'];
            $get_offus_trxvolume = $value['TRX_VOLUME'];
        }
        else if($value['FOFFUS'] == "0"){
            
            $get_onus_trxcount = $value['TRX_COUNT'];
            $get_onus_trxvolume = $value['TRX_VOLUME'];
        }
        }

        $res['offus_trxcount'] 	  = $get_offus_trxcount;
        $res['offus_trxvolume'] 	= $get_offus_trxvolume;
        $res['onus_trxcount'] 	  = $get_onus_trxcount;
        $res['onus_trxvolume'] 	  = $get_onus_trxvolume;


        $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        /*========================================================================================================
        ---------------------------------------------TOP 5 MERCHANT---------------------------------------------
        ========================================================================================================
        */
        $q_get_top5_merchant = DB::select("spVDWH_Top5MerchantHighestTrx '$get_past3_month', '$get_current_month', '$username'");
        // $q_get_top5_merchant = json_encode($q_get_top5_merchant);
        // $q_get_top5_merchant = json_decode($q_get_top5_merchant, true);
        
        
        $data_top5merchant_trx_volume 							            = array();
        $data_top5merchant_trx_volume['label']					        = array();
        $data_top5merchant_trx_volume['dataset_list']['label']	= array();
        $data_top5merchant_trx_count 							            = array();
        $data_top5merchant_trx_count['label']					        = array();
        $data_top5merchant_trx_count['dataset_list']['label']	= array();
        
        foreach( $q_get_top5_merchant as $key => $value ) {
        
            //TOP 5 MERCHANT TRX VOLUME
            if($value->DATA_TYPE == "VOLUME"){
                
                $label = substr($value->TRX_MONTHS, 4);
            
                if (substr($label, 0, 1) == "0") {
                $label = substr($label, 1);
                }

                $month = $arr_mon[$label];
                $year = substr($value->TRX_MONTHS, 2, 2);
                $text_month = $month." '".$year;

                
                if( !in_array($text_month, $data_top5merchant_trx_volume['label']) ) {
                $data_top5merchant_trx_volume['label'][] = $text_month;
                }
                
                if( !in_array( $value->MERCHANT_NAME, $data_top5merchant_trx_volume['dataset_list']['label']) ) {
                $data_top5merchant_trx_volume['dataset_list']['label'][]	= $value->MERCHANT_NAME;
                }
                
                $data_top5merchant_trx_volume['data'][ $value->MERCHANT_NAME ][] = $value->TOTAL;


            }//TOP 5 MERCHANT TRX COUNT
            else if($value->DATA_TYPE == "COUNT"){
            
                $label = substr($value->TRX_MONTHS, 4);
            
                if (substr($label, 0, 1) == "0") {
                $label = substr($label, 1);
                }

                $month = $arr_mon[$label];
                $year = substr($value->TRX_MONTHS, 2, 2);
                $text_month = $month." '".$year;

                
                if( !in_array($text_month, $data_top5merchant_trx_count['label']) ) {
                $data_top5merchant_trx_count['label'][] = $text_month;
                }
                
                if( !in_array( $value->MERCHANT_NAME, $data_top5merchant_trx_count['dataset_list']['label']) ) {
                $data_top5merchant_trx_count['dataset_list']['label'][]	= $value->MERCHANT_NAME;
                }
                
                $data_top5merchant_trx_count['data'][ $value->MERCHANT_NAME ][] = $value->TOTAL;
            }
        
        }
        
        $dataset_volume_merchant = array();
        $dataset_count_merchant = array();
        //red, orange, yellow, green, blue  
        $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
        $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];
        $i = 0;

        foreach( $data_top5merchant_trx_volume['dataset_list']['label'] as $key => $value ) {
            $data_trx_volume = array(
                "label" 			=> $value,
                // "fill" 				=> false,
                //"backgroundColor"	=> "rgba(122,224,119,0.0)",
                //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
                "backgroundColor"	=> $bg_color[$i],
                "borderColor"		=> $color[$i],
                "data"				=> $data_top5merchant_trx_volume['data'][ $value ]
            );
            $i++;
            $dataset_volume_merchant[] = $data_trx_volume;
        }
        
        $data_top5merchant_trx_volume['dataset_list'] 	= $dataset_volume_merchant;

        $i = 0;
        foreach( $data_top5merchant_trx_count['dataset_list']['label'] as $key => $value ) {
            $data_trx_count = array(
                "label" 			=> $value,
                // "fill" 				=> false,
                //"backgroundColor"	=> "rgba(122,224,119,0.0)",
                //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
                "backgroundColor"	=> $bg_color[$i],
                "borderColor"		=> $color[$i],
                "data"				=> $data_top5merchant_trx_count['data'][ $value ]
            );
            $i++;
            $dataset_count_merchant[] = $data_trx_count;
        }
        
        $data_top5merchant_trx_count['dataset_list'] 	= $dataset_count_merchant;
        /*========================================================================================================*/

        /*========================================================================================================
        ---------------------------------------------TOP 5 CARDTYPE---------------------------------------------
        ========================================================================================================
        */
        $q_get_top5_cardtype = DB::select("spVDWH_Top5CardtypeHighestTrx '$get_past3_month', '$get_current_month', '$username'");
        // $q_get_top5_cardtype = json_encode($q_get_top5_cardtype);
        // $q_get_top5_cardtype = json_decode($q_get_top5_cardtype, true);
        
        
        $data_top5cardtype_trx_volume 							            = array();
        $data_top5cardtype_trx_volume['label']					        = array();
        $data_top5cardtype_trx_volume['dataset_list']['label']	= array();
        $data_top5cardtype_trx_count 							              = array();
        $data_top5cardtype_trx_count['label']					          = array();
        $data_top5cardtype_trx_count['dataset_list']['label']	  = array();
        
        foreach( $q_get_top5_cardtype as $key => $value ) {
        
            //TOP 5 CARDTYPE TRX VOLUME
            if($value->DATA_TYPE == "VOLUME"){
                
                $label = substr($value->TRX_MONTHS, 4);
            
                if (substr($label, 0, 1) == "0") {
                $label = substr($label, 1);
                }

                $month = $arr_mon[$label];
                $year = substr($value->TRX_MONTHS, 2, 2);
                $text_month = $month." '".$year;

                
                if( !in_array($text_month, $data_top5cardtype_trx_volume['label']) ) {
                $data_top5cardtype_trx_volume['label'][] = $text_month;
                }
                
                if( !in_array( $value->CARDTYPE_NAME, $data_top5cardtype_trx_volume['dataset_list']['label']) ) {
                $data_top5cardtype_trx_volume['dataset_list']['label'][]	= $value->CARDTYPE_NAME;
                }
                
                $data_top5cardtype_trx_volume['data'][ $value->CARDTYPE_NAME ][] = $value->TOTAL;


            }//TOP 5 CARDTYPE TRX COUNT
            else if($value->DATA_TYPE == "COUNT"){
                
                $label = substr($value->TRX_MONTHS, 4);
            
                if (substr($label, 0, 1) == "0") {
                $label = substr($label, 1);
                }

                $month = $arr_mon[$label];
                $year = substr($value->TRX_MONTHS, 2, 2);
                $text_month = $month." '".$year;

                
                if( !in_array($text_month, $data_top5cardtype_trx_count['label']) ) {
                $data_top5cardtype_trx_count['label'][] = $text_month;
                }
                
                if( !in_array( $value->CARDTYPE_NAME, $data_top5cardtype_trx_count['dataset_list']['label']) ) {
                $data_top5cardtype_trx_count['dataset_list']['label'][]	= $value->CARDTYPE_NAME;
                }
                
                $data_top5cardtype_trx_count['data'][ $value->CARDTYPE_NAME ][] = $value->TOTAL;
            }
        
        }
        
        $dataset_volume_cardtype = array();
        $dataset_count_cardtype = array();
        //red, orange, yellow, green, blue  
        $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
        $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];
        $i = 0;

        foreach( $data_top5cardtype_trx_volume['dataset_list']['label'] as $key => $value ) {
            $data_trx_volume = array(
                "label" 			=> $value,
                // "fill" 				=> false,
                //"backgroundColor"	=> "rgba(122,224,119,0.0)",
                //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
                "backgroundColor"	=> $bg_color[$i],
                "borderColor"		=> $color[$i],
                "data"				=> $data_top5cardtype_trx_volume['data'][ $value ]
            );
            $i++;
            $dataset_volume_cardtype[] = $data_trx_volume;
        }
        
        $data_top5cardtype_trx_volume['dataset_list'] 	= $dataset_volume_cardtype;

        $i = 0;
        foreach( $data_top5cardtype_trx_count['dataset_list']['label'] as $key => $value ) {
            $data_trx_count = array(
                "label" 			=> $value,
                // "fill" 				=> false,
                //"backgroundColor"	=> "rgba(122,224,119,0.0)",
                //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
                "backgroundColor"	=> $bg_color[$i],
                "borderColor"		=> $color[$i],
                "data"				=> $data_top5cardtype_trx_count['data'][ $value ]
            );
            $i++;
            $dataset_count_cardtype[] = $data_trx_count;
        }
        
        $data_top5cardtype_trx_count['dataset_list'] 	= $dataset_count_cardtype;
        /*========================================================================================================*/

        /*========================================================================================================
        ---------------------------------------------TOP 5 TRXTYPE---------------------------------------------
        ========================================================================================================
        */
        $q_get_top5_trxtype = DB::select("spVDWH_Top5TrxtypeHighestTrx '$get_past3_month', '$get_current_month', '$username'");
        // $q_get_top5_trxtype = json_encode($q_get_top5_trxtype);
        // $q_get_top5_trxtype = json_decode($q_get_top5_trxtype, true);
        
        
        $data_top5trxtype_trx_volume 							            = array();
        $data_top5trxtype_trx_volume['label']					        = array();
        $data_top5trxtype_trx_volume['dataset_list']['label']	= array();
        $data_top5trxtype_trx_count 							              = array();
        $data_top5trxtype_trx_count['label']					          = array();
        $data_top5trxtype_trx_count['dataset_list']['label']	  = array();
        
        foreach( $q_get_top5_trxtype as $key => $value ) {
        
            //TOP 5 TRXTYPE TRX VOLUME
            if($value->DATA_TYPE == "VOLUME"){
                
                $label = substr($value->TRX_MONTHS, 4);
            
                if (substr($label, 0, 1) == "0") {
                $label = substr($label, 1);
                }

                $month = $arr_mon[$label];
                $year = substr($value->TRX_MONTHS, 2, 2);
                $text_month = $month." '".$year;

                
                if( !in_array($text_month, $data_top5trxtype_trx_volume['label']) ) {
                $data_top5trxtype_trx_volume['label'][] = $text_month;
                }
                
                if( !in_array( $value->TRXTYPE_NAME, $data_top5trxtype_trx_volume['dataset_list']['label']) ) {
                $data_top5trxtype_trx_volume['dataset_list']['label'][]	= $value->TRXTYPE_NAME;
                }
                
                $data_top5trxtype_trx_volume['data'][ $value->TRXTYPE_NAME ][] = $value->TOTAL;


            }//TOP 5 TRXTYPE TRX COUNT
            else if($value->DATA_TYPE == "COUNT"){
                
                $label = substr($value->TRX_MONTHS, 4);
            
                if (substr($label, 0, 1) == "0") {
                $label = substr($label, 1);
                }

                $month = $arr_mon[$label];
                $year = substr($value->TRX_MONTHS, 2, 2);
                $text_month = $month." '".$year;

                
                if( !in_array($text_month, $data_top5trxtype_trx_count['label']) ) {
                $data_top5trxtype_trx_count['label'][] = $text_month;
                }
                
                if( !in_array( $value->TRXTYPE_NAME, $data_top5trxtype_trx_count['dataset_list']['label']) ) {
                $data_top5trxtype_trx_count['dataset_list']['label'][]	= $value->TRXTYPE_NAME;
                }
                
                $data_top5trxtype_trx_count['data'][ $value->TRXTYPE_NAME ][] = $value->TOTAL;
            }
        
        }
        
        $dataset_volume_trxtype = array();
        $dataset_count_trxtype = array();
        //red, orange, yellow, green, blue  
        $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
        $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];
        $i = 0;

        foreach( $data_top5trxtype_trx_volume['dataset_list']['label'] as $key => $value ) {
            $data_trx_volume = array(
                "label" 			=> $value,
                // "fill" 				=> false,
                //"backgroundColor"	=> "rgba(122,224,119,0.0)",
                //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
                "backgroundColor"	=> $bg_color[$i],
                "borderColor"		=> $color[$i],
                "data"				=> $data_top5trxtype_trx_volume['data'][ $value ]
            );
            $i++;
            $dataset_volume_trxtype[] = $data_trx_volume;
        }
        
        $data_top5trxtype_trx_volume['dataset_list'] 	= $dataset_volume_trxtype;

        $i = 0;
        foreach( $data_top5trxtype_trx_count['dataset_list']['label'] as $key => $value ) {
            $data_trx_count = array(
                "label" 			=> $value,
                // "fill" 				=> false,
                //"backgroundColor"	=> "rgba(122,224,119,0.0)",
                //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
                "backgroundColor"	=> $bg_color[$i],
                "borderColor"		=> $color[$i],
                "data"				=> $data_top5trxtype_trx_count['data'][ $value ]
            );
            $i++;
            $dataset_count_trxtype[] = $data_trx_count;
        }
        
        $data_top5trxtype_trx_count['dataset_list'] 	= $dataset_count_trxtype;
        /*========================================================================================================*/

        // $res['chart_trx_volume'] 	= $chart_trx_volume;
        // $res['chart_trx_count'] 	= $chart_trx_count;

        $res['top5mer_trx_volume'] 	= $data_top5merchant_trx_volume;
        $res['top5mer_trx_count'] 	= $data_top5merchant_trx_count;
        $res['top5ctp_trx_volume'] 	= $data_top5cardtype_trx_volume;
        $res['top5ctp_trx_count'] 	= $data_top5cardtype_trx_count;
        $res['top5ttp_trx_volume'] 	= $data_top5trxtype_trx_volume;
        $res['top5ttp_trx_count'] 	= $data_top5trxtype_trx_count;
    
        
        return response($res);
    }

    public function getdata_backup(Request $request)
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
