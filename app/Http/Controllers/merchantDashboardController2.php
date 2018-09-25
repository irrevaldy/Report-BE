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
    $res['total_branch'] 		    = $total_branch;
    $res['total_store'] 		    = $total_store;
    $res['total_terminal'] 		  = $total_terminal;
    $res['terminal_active'] 	  = $terminal_active;
    $res['terminal_inactive']   = $terminal_inactive;
    $res['total_active_trx'] 	  = $total_active_trx;
    $res['total_trx_volume'] 	  = $total_trx_volume;
    $res['total_trx_success']   = $total_trx_success;
    $res['total_trx_failed'] 	  = $total_trx_failed;
    $res['total_trx_count'] 	  = $total_trx_count;


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
      ---------------------------------------------TOP 5 ACQUIRER---------------------------------------------
      ========================================================================================================
    */
    $q_get_top5_acquirer = DB::select("spVDWH_Top5AcquirerHighestTrx '$get_past3_month', '$get_current_month', '$username'");
    // $q_get_top5_acquirer = json_encode($q_get_top5_acquirer);
    // $q_get_top5_acquirer = json_decode($q_get_top5_acquirer, true);
    
    
    $data_top5acquirer_trx_volume 							            = array();
    $data_top5acquirer_trx_volume['label']					        = array();
    $data_top5acquirer_trx_volume['dataset_list']['label']	= array();
    $data_top5acquirer_trx_count 							            = array();
    $data_top5acquirer_trx_count['label']					        = array();
    $data_top5acquirer_trx_count['dataset_list']['label']	= array();
    
    foreach( $q_get_top5_acquirer as $key => $value ) {
      
      //TOP 5 ACQUIRER TRX VOLUME
      if($value->DATA_TYPE == "VOLUME"){
        
        $label = substr($value->TRX_MONTHS, 4);
      
        if (substr($label, 0, 1) == "0") {
          $label = substr($label, 1);
        }

        $month = $arr_mon[$label];
        $year = substr($value->TRX_MONTHS, 2, 2);
        $text_month = $month." '".$year;

        
        if( !in_array($text_month, $data_top5acquirer_trx_volume['label']) ) {
          $data_top5acquirer_trx_volume['label'][] = $text_month;
        }
        
        if( !in_array( $value->BANK_NAME, $data_top5acquirer_trx_volume['dataset_list']['label']) ) {
          $data_top5acquirer_trx_volume['dataset_list']['label'][]	= $value->BANK_NAME;
        }
        
        $data_top5acquirer_trx_volume['data'][ $value->BANK_NAME ][] = $value->TOTAL;


      }//TOP 5 ACQUIRER TRX COUNT
      else if($value->DATA_TYPE == "COUNT"){
       
        $label = substr($value->TRX_MONTHS, 4);
      
        if (substr($label, 0, 1) == "0") {
          $label = substr($label, 1);
        }

        $month = $arr_mon[$label];
        $year = substr($value->TRX_MONTHS, 2, 2);
        $text_month = $month." '".$year;

        
        if( !in_array($text_month, $data_top5acquirer_trx_count['label']) ) {
          $data_top5acquirer_trx_count['label'][] = $text_month;
        }
        
        if( !in_array( $value->BANK_NAME, $data_top5acquirer_trx_count['dataset_list']['label']) ) {
          $data_top5acquirer_trx_count['dataset_list']['label'][]	= $value->BANK_NAME;
        }
        
        $data_top5acquirer_trx_count['data'][ $value->BANK_NAME ][] = $value->TOTAL;
      }
      
    }
    
    $dataset_volume_acquirer = array();
    $dataset_count_acquirer = array();
    //red, orange, yellow, green, blue  
    $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
    $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];
    $i = 0;

    foreach( $data_top5acquirer_trx_volume['dataset_list']['label'] as $key => $value ) {
      $data_trx_volume = array(
        "label" 			=> $value,
        // "fill" 				=> false,
        //"backgroundColor"	=> "rgba(122,224,119,0.0)",
        //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
        "backgroundColor"	=> $bg_color[$i],
        "borderColor"		=> $color[$i],
        "data"				=> $data_top5acquirer_trx_volume['data'][ $value ]
      );
      $i++;
      $dataset_volume_acquirer[] = $data_trx_volume;
    }
    
    $data_top5acquirer_trx_volume['dataset_list'] 	= $dataset_volume_acquirer;

    $i = 0;
    foreach( $data_top5acquirer_trx_count['dataset_list']['label'] as $key => $value ) {
      $data_trx_count = array(
        "label" 			=> $value,
        // "fill" 				=> false,
        //"backgroundColor"	=> "rgba(122,224,119,0.0)",
        //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
        "backgroundColor"	=> $bg_color[$i],
        "borderColor"		=> $color[$i],
        "data"				=> $data_top5acquirer_trx_count['data'][ $value ]
      );
      $i++;
      $dataset_count_acquirer[] = $data_trx_count;
    }
    
    $data_top5acquirer_trx_count['dataset_list'] 	= $dataset_count_acquirer;
    /*========================================================================================================*/

    /*========================================================================================================
      ---------------------------------------------TOP 5 BRANCH---------------------------------------------
      ========================================================================================================
    */
    $q_get_top5_branch = DB::select("spVDWH_Top5BranchHighestTrx '$get_past3_month', '$get_current_month', '$username'");
    // $q_get_top5_branch = json_encode($q_get_top5_branch);
    // $q_get_top5_branch = json_decode($q_get_top5_branch, true);
    
    
    $data_top5branch_trx_volume 							            = array();
    $data_top5branch_trx_volume['label']					        = array();
    $data_top5branch_trx_volume['dataset_list']['label']	= array();
    $data_top5branch_trx_count 							            = array();
    $data_top5branch_trx_count['label']					        = array();
    $data_top5branch_trx_count['dataset_list']['label']	= array();
    
    foreach( $q_get_top5_branch as $key => $value ) {
      
      //TOP 5 BRANCH TRX VOLUME
      if($value->DATA_TYPE == "VOLUME"){
        
        $label = substr($value->TRX_MONTHS, 4);
      
        if (substr($label, 0, 1) == "0") {
          $label = substr($label, 1);
        }

        $month = $arr_mon[$label];
        $year = substr($value->TRX_MONTHS, 2, 2);
        $text_month = $month." '".$year;

        
        if( !in_array($text_month, $data_top5branch_trx_volume['label']) ) {
          $data_top5branch_trx_volume['label'][] = $text_month;
        }
        
        if( !in_array( $value->BRANCH_NAME, $data_top5branch_trx_volume['dataset_list']['label']) ) {
          $data_top5branch_trx_volume['dataset_list']['label'][]	= $value->BRANCH_NAME;
        }
        
        $data_top5branch_trx_volume['data'][ $value->BRANCH_NAME ][] = $value->TOTAL;


      }//TOP 5 ACQUIRER TRX COUNT
      else if($value->DATA_TYPE == "COUNT"){
        
        $label = substr($value->TRX_MONTHS, 4);
      
        if (substr($label, 0, 1) == "0") {
          $label = substr($label, 1);
        }

        $month = $arr_mon[$label];
        $year = substr($value->TRX_MONTHS, 2, 2);
        $text_month = $month." '".$year;

        
        if( !in_array($text_month, $data_top5branch_trx_count['label']) ) {
          $data_top5branch_trx_count['label'][] = $text_month;
        }
        
        if( !in_array( $value->BRANCH_NAME, $data_top5branch_trx_count['dataset_list']['label']) ) {
          $data_top5branch_trx_count['dataset_list']['label'][]	= $value->BRANCH_NAME;
        }
        
        $data_top5branch_trx_count['data'][ $value->BRANCH_NAME ][] = $value->TOTAL;
      }
      
    }
    
    $dataset_volume_branch = array();
    $dataset_count_branch = array();
    //red, orange, yellow, green, blue  
    $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
    $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];
    $i = 0;

    foreach( $data_top5branch_trx_volume['dataset_list']['label'] as $key => $value ) {
      $data_trx_volume = array(
        "label" 			=> $value,
        // "fill" 				=> false,
        //"backgroundColor"	=> "rgba(122,224,119,0.0)",
        //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
        "backgroundColor"	=> $bg_color[$i],
        "borderColor"		=> $color[$i],
        "data"				=> $data_top5branch_trx_volume['data'][ $value ]
      );
      $i++;
      $dataset_volume_branch[] = $data_trx_volume;
    }
    
    $data_top5branch_trx_volume['dataset_list'] 	= $dataset_volume_branch;

    $i = 0;
    foreach( $data_top5branch_trx_count['dataset_list']['label'] as $key => $value ) {
      $data_trx_count = array(
        "label" 			=> $value,
        // "fill" 				=> false,
        //"backgroundColor"	=> "rgba(122,224,119,0.0)",
        //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
        "backgroundColor"	=> $bg_color[$i],
        "borderColor"		=> $color[$i],
        "data"				=> $data_top5branch_trx_count['data'][ $value ]
      );
      $i++;
      $dataset_count_branch[] = $data_trx_count;
    }
    
    $data_top5branch_trx_count['dataset_list'] 	= $dataset_count_branch;
    /*========================================================================================================*/

    /*========================================================================================================
      ---------------------------------------------TOP 5 STORE---------------------------------------------
      ========================================================================================================
    */
    $q_get_top5_store = DB::select("spVDWH_Top5StoreHighestTrx '$get_past3_month', '$get_current_month', '$username'");
    // $q_get_top5_store = json_encode($q_get_top5_store);
    // $q_get_top5_store = json_decode($q_get_top5_store, true);
    
    
    $data_top5store_trx_volume 							            = array();
    $data_top5store_trx_volume['label']					        = array();
    $data_top5store_trx_volume['dataset_list']['label']	= array();
    $data_top5store_trx_count 							            = array();
    $data_top5store_trx_count['label']					        = array();
    $data_top5store_trx_count['dataset_list']['label']	= array();
    
    foreach( $q_get_top5_store as $key => $value ) {
      
      //TOP 5 BRANCH TRX VOLUME
      if($value->DATA_TYPE == "VOLUME"){
        
        $label = substr($value->TRX_MONTHS, 4);
      
        if (substr($label, 0, 1) == "0") {
          $label = substr($label, 1);
        }

        $month = $arr_mon[$label];
        $year = substr($value->TRX_MONTHS, 2, 2);
        $text_month = $month." '".$year;

        
        if( !in_array($text_month, $data_top5store_trx_volume['label']) ) {
          $data_top5store_trx_volume['label'][] = $text_month;
        }
        
        if( !in_array( $value->STORE_NAME, $data_top5store_trx_volume['dataset_list']['label']) ) {
          $data_top5store_trx_volume['dataset_list']['label'][]	= $value->STORE_NAME;
        }
        
        $data_top5store_trx_volume['data'][ $value->STORE_NAME ][] = $value->TOTAL;


      }//TOP 5 ACQUIRER TRX COUNT
      else if($value->DATA_TYPE == "COUNT"){
        
        $label = substr($value->TRX_MONTHS, 4);
      
        if (substr($label, 0, 1) == "0") {
          $label = substr($label, 1);
        }

        $month = $arr_mon[$label];
        $year = substr($value->TRX_MONTHS, 2, 2);
        $text_month = $month." '".$year;

        
        if( !in_array($text_month, $data_top5store_trx_count['label']) ) {
          $data_top5store_trx_count['label'][] = $text_month;
        }
        
        if( !in_array( $value->STORE_NAME, $data_top5store_trx_count['dataset_list']['label']) ) {
          $data_top5store_trx_count['dataset_list']['label'][]	= $value->STORE_NAME;
        }
        
        $data_top5store_trx_count['data'][ $value->STORE_NAME ][] = $value->TOTAL;
      }
      
    }
    
    $dataset_volume_store = array();
    $dataset_count_store = array();
    //red, orange, yellow, green, blue  
    $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
    $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];
    $i = 0;

    foreach( $data_top5store_trx_volume['dataset_list']['label'] as $key => $value ) {
      $data_trx_volume = array(
        "label" 			=> $value,
        // "fill" 				=> false,
        //"backgroundColor"	=> "rgba(122,224,119,0.0)",
        //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
        "backgroundColor"	=> $bg_color[$i],
        "borderColor"		=> $color[$i],
        "data"				=> $data_top5store_trx_volume['data'][ $value ]
      );
      $i++;
      $dataset_volume_store[] = $data_trx_volume;
    }
    
    $data_top5store_trx_volume['dataset_list'] 	= $dataset_volume_store;

    $i = 0;
    foreach( $data_top5store_trx_count['dataset_list']['label'] as $key => $value ) {
      $data_trx_count = array(
        "label" 			=> $value,
        // "fill" 				=> false,
        //"backgroundColor"	=> "rgba(122,224,119,0.0)",
        //"borderColor"		=> $data_total_trx['dataset_list']['color'][$key],
        "backgroundColor"	=> $bg_color[$i],
        "borderColor"		=> $color[$i],
        "data"				=> $data_top5store_trx_count['data'][ $value ]
      );
      $i++;
      $dataset_count_store[] = $data_trx_count;
    }
    
    $data_top5store_trx_count['dataset_list'] 	= $dataset_count_store;
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
      
      //TOP 5 ACQUIRER TRX VOLUME
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


      }//TOP 5 ACQUIRER TRX COUNT
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
      
      //TOP 5 ACQUIRER TRX VOLUME
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


      }//TOP 5 ACQUIRER TRX COUNT
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

    $res['top5acq_trx_volume'] 	= $data_top5acquirer_trx_volume;
    $res['top5acq_trx_count'] 	= $data_top5acquirer_trx_count;
    $res['top5bra_trx_volume'] 	= $data_top5branch_trx_volume;
    $res['top5bra_trx_count'] 	= $data_top5branch_trx_count;
    $res['top5sto_trx_volume'] 	= $data_top5store_trx_volume;
    $res['top5sto_trx_count'] 	= $data_top5store_trx_count;
    $res['top5ctp_trx_volume'] 	= $data_top5cardtype_trx_volume;
    $res['top5ctp_trx_count'] 	= $data_top5cardtype_trx_count;
    $res['top5ttp_trx_volume'] 	= $data_top5trxtype_trx_volume;
    $res['top5ttp_trx_count'] 	= $data_top5trxtype_trx_count;
    $res['top5ttp_trx_count'] 	= $data_top5trxtype_trx_count;
	
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

  public function backup_coding(Request $request){
    // $query_get_data     = DB::select("spVMonitoringReport_GetTotalBranch");
    // $total_branch       = $query_get_data[0]->total_branch;

    // $query_get_data     = DB::select("spVMonitoringReport_GetTotalStore");
    // $total_store          = $query_get_data[0]->total_store;

    // $query_get_data     = DB::select("spVMonitoringReport_GetTotalTerminal");
    // $total_terminal      = $query_get_data[0]->total_terminal;

    $user_merchant_list = [];
    $user_branch_list = [];
    $user_store_list = [];
    //get merchant id
    $q_get_user_data_filter_merchant = DB::select("SELECT value FROM TUSER_DATA_FILTER WHERE user_id = '$user_id' AND data_filter_type_id = '3'");
    foreach ($q_get_user_data_filter_merchant as $key => $value) {
        $user_merchant_list[] = $value->value;
    }
    
    //get branch id
    $q_get_user_data_filter_branch = DB::select("SELECT value FROM TUSER_DATA_FILTER WHERE user_id = '$user_id' AND data_filter_type_id = '4'");
    foreach ($q_get_user_data_filter_branch as $key => $value) {
        $user_branch_list[] = $value->value;
    }
    //get store id
    $q_get_user_data_filter_store = DB::select("SELECT value FROM TUSER_DATA_FILTER WHERE user_id = '$user_id' AND data_filter_type_id = '5'");
    foreach ($q_get_user_data_filter_store as $key => $value) {
        $user_store_list[] = $value->value;
    }

    //get user data
    $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
    $username = $q_get_user_data[0]->user_name;

    $get_current_month = date("Ym")."00000000";
    $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";


    $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    //get data by data filter merchant
    if(!empty($user_merchant_list)){
		
      $total_merchant = 0;
      $total_trx_volume = 0;
      $total_trx_success = 0;
      $total_trx_failed = 0;
      $total_trx_count = 0;

      for($i = 0; $i < count($user_merchant_list); $i++){
        
        //total merchant
        $q_get_total_merchant = DB::select("spVDWH_GetTotalMerchantByMerchantID '', '$user_merchant_list[$i]'");
        $get_total_merchant = $q_get_total_merchant[0]->total_merchant;
    
        $total_merchant += $get_total_merchant;

        //total trx volume
        $q_get_total_trx_volume = DB::select("spVDWH_GetTotalTrxVolume '', '$user_merchant_list[$i]'");
        $get_total_trx_volume = $q_get_total_trx_volume[0]->total_volume;
    
        $total_trx_volume += $get_total_trx_volume;

        //total trx, trx success, trx failed
        $q_get_total_trx_count 	= DB::select("spVDWH_GetTotalTrxCount '', '$user_merchant_list[$i]'");
        $get_total_trx_success 	= $q_get_total_trx_count[0]->trx_success;
        $get_total_trx_failed 	= $q_get_total_trx_count[0]->trx_failed;
        $get_total_trx_count	= $get_total_trx_success+$get_total_trx_failed;
    
        $total_trx_success 	+= $get_total_trx_success;
        $total_trx_failed 	+= $get_total_trx_failed;
        $total_trx_count 	+= $get_total_trx_count;
        
        // //chart trx volume
        // $q_get_chart_trx_volume = DB::select("spVDWH_GetPast12MonthTrxVolumeByMerchantID '$user_merchant_list[$i]'");
        // $q_get_chart_trx_volume = json_encode($q_get_chart_trx_volume);
        // $q_get_chart_trx_volume = json_decode($q_get_chart_trx_volume, true);
        
        // $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        
        // $month_char = [];
        // $month_find = [];
        // for ($j=0; $j < 12; $j++) { 
        
        //   $month_num = substr($q_get_chart_trx_volume[$j]['trx_month'],4);

        //   if (substr($month_num, 0, 1) == "0") {
        //     $month_num = substr($month_num, 1);
        //   }

        //   $month = $arr_mon[$month_num];
        //   $year = substr($q_get_chart_trx_volume[$j]['trx_month'], 2, 2);
        //   $text_month = $month." '".$year;

        //   $q_get_chart_trx_volume[$j]['trx_month'] = $text_month;
          
        // }

        // foreach( $q_get_chart_trx_volume as $key => $value ) {
        //   $chart_trx_volume['trx_month'][] = $value['trx_month'];
        //   $chart_trx_volume['trx_volume'][] = $value['total_amount'];
        // }


        // //chart trx count
        // $q_get_chart_trx_count = DB::select("spVDWH_GetPast12MonthTrxCountByMerchantID '$user_merchant_list[$i]'");
        // $q_get_chart_trx_count = json_encode($q_get_chart_trx_count);
        // $q_get_chart_trx_count = json_decode($q_get_chart_trx_count, true);
        
      
        // $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        
        // $month_char = [];
        // $month_find = [];
        // for ($k=0; $k < 12; $k++) { 
        
        //   $month_num = substr($q_get_chart_trx_count[$k]['trx_month'],4);

        //   if (substr($month_num, 0, 1) == "0") {
        //     $month_num = substr($month_num, 1);
        //   }

        //   $month = $arr_mon[$month_num];
        //   $year = substr($q_get_chart_trx_count[$k]['trx_month'], 2, 2);
        //   $text_month = $month." '".$year;

        //   $q_get_chart_trx_count[$k]['trx_month'] = $text_month;
          
        // }

        // foreach( $q_get_chart_trx_count as $key => $value ) {
        //   $chart_trx_count['trx_month'][] = $value['trx_month'];
        //   $chart_trx_count['trx_count'][] = $value['total_trx'];
        // }
      }	  
    }
    
    // else{

    // 	// $q_total_branch = DB::select("spVDWH_GetTotalBranchByMerchant 'all', ''");
    // 	// $total_branch = $q_total_branch[0]->total_branch;
    // 	$total_branch = 0;

    // }

    //get data by data filter branch
    if(!empty($user_branch_list)){
		
		$total_branch = 0;

		for($i = 0; $i < count($user_branch_list); $i++){
			
			//total branch
			$q_get_total_branch = DB::select("spVDWH_GetTotalBranchByBranchID '', '$user_branch_list[$i]'");
			$get_total_branch = $q_get_total_branch[0]->total_branch;
	
			$total_branch += $get_total_branch;

		}	  
	}
	// else{

	// 	// $q_total_branch = DB::select("spVDWH_GetTotalBranchByMerchant 'all', ''");
	// 	// $total_branch = $q_total_branch[0]->total_branch;
	// 	$total_branch = 0;

	// }

	//get data by data filter store
	if(!empty($user_store_list)){
		 
		$total_store 		= 0;
		$total_terminal 	= 0;
		$terminal_active 	= 0;
		$terminal_inactive 	= 0;
		$total_active_trx 	= 0;

		for($i = 0; $i < count($user_store_list); $i++){
			
			//total store
			$q_get_total_store = DB::select("spVDWH_GetTotalStoreByStoreID '', '$user_store_list[$i]'");
    		$get_total_store = $q_get_total_store[0]->total_store;

			$total_store += $get_total_store;


			//total terminal
			$q_get_total_terminal = DB::select("spVDWH_GetTotalTerminalByStoreID '', '$user_store_list[$i]'");
			$get_total_terminal 	= $q_get_total_terminal[0]->total_terminal;
			$get_terminal_active 	= $q_get_total_terminal[0]->total_active;
			$get_terminal_inactive 	= $q_get_total_terminal[0]->total_inactive;
			$get_total_active_trx 	= $q_get_total_terminal[0]->total_active_trx;
			
			$total_terminal 	+= $get_total_terminal;
			$terminal_active 	+= $get_terminal_active;
			$terminal_inactive 	+= $get_terminal_inactive;
			$total_active_trx 	+= $get_total_active_trx;

		}	  
	}
	// else{

	// 	// $q_total_store = DB::select("spVDWH_GetTotalStoreByMerchant 'all', ''");
	// 	// $total_store = $q_total_store[0]->total_store;
	// 	$total_store = 0;
	// 	$total_terminal = 0;

	// }
  }
}
