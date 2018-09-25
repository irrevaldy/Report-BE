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

class branchDashboardController extends Controller
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

    return response($res);
  }

  public function getTransactionVolume(Request $request, $user_id)
  {
    try
    {
      $res['success'] = true;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTransactionCount(Request $request, $user_id)
  {
    try
    {
      $res['success'] = true;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5AcquirerTransactionVolume(Request $request, $user_id)
  {
    try
    {
      //get user data
      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_acquirer = DB::select("spVDWH_Top5AcquirerHighestTrx '$get_past3_month', '$get_current_month', '$username'");
      // $q_get_top5_acquirer = json_encode($q_get_top5_acquirer);
      // $q_get_top5_acquirer = json_decode($q_get_top5_acquirer, true);

      $data_top5acquirer_trx_volume 							            = array();
      $data_top5acquirer_trx_volume['label']					        = array();
      $data_top5acquirer_trx_volume['dataset_list']['label']	= array();

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


        }

      $dataset_volume_acquirer = array();
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

      $data_top5acquirer_trx_volume = json_encode($data_top5acquirer_trx_volume);
      $data_top5acquirer_trx_volume = json_decode($data_top5acquirer_trx_volume, true);

      $res['success'] = true;
      $res['top5acq_trx_volume'] 	= $data_top5acquirer_trx_volume;

      return response($res);
    }
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5AcquirerTransactionCount(Request $request, $user_id)
  {
    try
    {

      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_acquirer = DB::select("spVDWH_Top5AcquirerHighestTrx '$get_past3_month', '$get_current_month', '$username'");

      $data_top5acquirer_trx_count 							            = array();
      $data_top5acquirer_trx_count['label']					        = array();
      $data_top5acquirer_trx_count['dataset_list']['label']	= array();

      foreach( $q_get_top5_acquirer as $key => $value ) {

      if($value->DATA_TYPE == "COUNT"){

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

        $dataset_count_acquirer = array();
      //red, orange, yellow, green, blue
      $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
      $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];

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

      $data_top5acquirer_trx_count = json_encode($data_top5acquirer_trx_count);
      $data_top5acquirer_trx_count = json_decode($data_top5acquirer_trx_count, true);

      $res['success'] = true;
      $res['top5acq_trx_count'] 	= $data_top5acquirer_trx_count;

      //$res['result'] = $data_top5acquirer_trx_volume;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5StoreTransactionVolume(Request $request, $user_id)
  {
    try
    {
      //get user data
      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_store = DB::select("spVDWH_Top5StoreHighestTrx '$get_past3_month', '$get_current_month', '$username'");
      // $q_get_top5_store = json_encode($q_get_top5_store);
      // $q_get_top5_store = json_decode($q_get_top5_store, true);


      $data_top5store_trx_volume 							            = array();
      $data_top5store_trx_volume['label']					        = array();
      $data_top5store_trx_volume['dataset_list']['label']	= array();

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


        }

      }

      $dataset_volume_store = array();

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

      $data_top5store_trx_volume = json_encode($data_top5store_trx_volume);
      $data_top5store_trx_volume = json_decode($data_top5store_trx_volume, true);

      $res['success'] = true;
      $res['top5sto_trx_volume'] 	= $data_top5store_trx_volume;

      return response($res);

    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5StoreTransactionCount(Request $request, $user_id)
  {
    try
    {
      //get user data
      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_store = DB::select("spVDWH_Top5StoreHighestTrx '$get_past3_month', '$get_current_month', '$username'");
      $data_top5store_trx_count 							            = array();
      $data_top5store_trx_count['label']					        = array();
      $data_top5store_trx_count['dataset_list']['label']	= array();

      foreach( $q_get_top5_store as $key => $value ) {

        if($value->DATA_TYPE == "COUNT"){

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

      $dataset_count_store = array();
      //red, orange, yellow, green, blue
      $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
      $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];

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

      $data_top5store_trx_count = json_encode($data_top5store_trx_count);
      $data_top5store_trx_count = json_decode($data_top5store_trx_count, true);

      $res['success'] = true;
      $res['top5sto_trx_count'] 	= $data_top5store_trx_count;

      return response($res);

    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5CardTypeTransactionVolume(Request $request, $user_id)
  {
    try
    {
      //get user data
      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_cardtype = DB::select("spVDWH_Top5CardtypeHighestTrx '$get_past3_month', '$get_current_month', '$username'");

      $data_top5cardtype_trx_volume 							            = array();
      $data_top5cardtype_trx_volume['label']					        = array();
      $data_top5cardtype_trx_volume['dataset_list']['label']	= array();

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


        }

      }

      $dataset_volume_cardtype = array();
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

      $data_top5cardtype_trx_volume = json_encode($data_top5cardtype_trx_volume);
        $data_top5cardtype_trx_volume= json_decode($data_top5cardtype_trx_volume, true);

      $res['success'] = true;
      $res['top5ctp_trx_volume'] 	=  $data_top5cardtype_trx_volume;

      return response($res);

    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5CardTypeTransactionCount(Request $request, $user_id)
  {
    try
    {
      //get user data
      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_cardtype = DB::select("spVDWH_Top5CardtypeHighestTrx '$get_past3_month', '$get_current_month', '$username'");

      $data_top5cardtype_trx_count 							              = array();
      $data_top5cardtype_trx_count['label']					          = array();
      $data_top5cardtype_trx_count['dataset_list']['label']	  = array();

      foreach( $q_get_top5_cardtype as $key => $value ) {
        if($value->DATA_TYPE == "COUNT"){

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

      $dataset_count_cardtype = array();
      //red, orange, yellow, green, blue
      $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
      $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];

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

      $data_top5cardtype_trx_count = json_encode($data_top5cardtype_trx_count);
      $data_top5cardtype_trx_count = json_decode($data_top5cardtype_trx_count, true);

      $res['success'] = true;
      $res['top5ctp_trx_count'] 	= $data_top5cardtype_trx_count;

      return response($res);

    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5TransactionTypeTransactionVolume(Request $request, $user_id)
  {
    try
    {
      //get user data
      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_trxtype = DB::select("spVDWH_Top5TrxtypeHighestTrx '$get_past3_month', '$get_current_month', '$username'");

      $data_top5trxtype_trx_volume 							            = array();
      $data_top5trxtype_trx_volume['label']					        = array();
      $data_top5trxtype_trx_volume['dataset_list']['label']	= array();

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


        }

      }

      $dataset_volume_trxtype = array();
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

      $data_top5trxtype_trx_volume = json_encode($data_top5trxtype_trx_volume);
      $data_top5trxtype_trx_volume = json_decode($data_top5trxtype_trx_volume, true);

      $res['success'] = true;
      $res['top5ttp_trx_volume'] 	= $data_top5trxtype_trx_volume;

      //$res['result'] = $data_top5acquirer_trx_volume;

      return response($res);

    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTop5TransactionTypeTransactionCount(Request $request, $user_id)
  {
    try
    {
      //get user data
      $q_get_user_data = DB::select("spVDWH_GetUserData '$user_id'");
      $username = $q_get_user_data[0]->user_name;

      $get_current_month = date("Ym")."00000000";
      $get_past1_month = date("Ym", strtotime("-1 Months"))."00000000";
      $get_past3_month = date("Ym", strtotime("-3 Months"))."00000000";

      $arr_mon = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

      $q_get_top5_trxtype = DB::select("spVDWH_Top5TrxtypeHighestTrx '$get_past3_month', '$get_current_month', '$username'");

        $data_top5trxtype_trx_count 							              = array();
      $data_top5trxtype_trx_count['label']					          = array();
      $data_top5trxtype_trx_count['dataset_list']['label']	  = array();

      foreach( $q_get_top5_trxtype as $key => $value ) {

      if($value->DATA_TYPE == "COUNT"){

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

      $dataset_count_trxtype = array();
      //red, orange, yellow, green, blue
      $bg_color = ["rgba(255, 99, 132, 0.9)", "rgba(255, 159, 64, 0.9)", "rgba(255, 205, 86, 0.9)", "rgba(75, 192, 192, 0.9)", "rgba(54, 162, 235, 0.9)"];
      $color = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)", "rgb(54, 162, 235)"];

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

      $data_top5trxtype_trx_count = json_encode($data_top5trxtype_trx_count);
      $data_top5trxtype_trx_count = json_decode($data_top5trxtype_trx_count, true);

      $res['success'] = true;
      $res['top5ttp_trx_count'] 	= $data_top5trxtype_trx_count;

      //$res['result'] = $data_top5acquirer_trx_volume;

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
