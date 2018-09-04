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

class otherReportController extends Controller
{
  public function __construct(Request $request){

  }

  public function monthlyRevenue(Request $request)
  {
    try
    {
      $bankCode = $request->bank_code;
      $cardType = $request->card_type;
      $trxType = $request->transaction_type;
      $corpId = $request->corporate;
      $status = $request->statusa;
      $month = $request->month;

      $data = DB::select("[spVDWH_GetMonthlyRevenueData] '$bankCode', '$cardType', '$trxType', '$corpId',
                              '$status', '$month' ");

      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;

      return response($res);
    } catch(QueryException $ex){
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function monthlyRevenueT10H(Request $request)
  {
    try
    {
      $bankCode = $request->bank_code;
      $corpId = $request->corporate;
      $month = $request->month;

      $data = DB::select("[spVDWH_GetMonthlyRevenueDataT10HM] '$bankCode', '$corpId', '$month' ");

      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;

      return response($res);
    } catch(QueryException $ex){
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function monthlyRevenueT10L(Request $request)
  {
    try
    {
      $bankCode = $request->bank_code;
      $corpId = $request->corporate;
      $month = $request->month;

      $data = DB::select("[spVDWH_GetMonthlyRevenueDataT10LM] '$bankCode', '$corpId', '$month' ");

      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;

      return response($res);
    } catch(QueryException $ex){
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function monthlyOnOff(Request $request)
  {
    try
    {
      $corpId = $request->corporate;
      $transaction_onoff = $request->transaction_onoff;
      $month = $request->month;

      $data = DB::select("[spVDWH_GetMonthlyOnOff] '$corpId', '$transaction_onoff','$month' ");

      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;

      return response($res);
    } catch(QueryException $ex){
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function monthlyErrorCode(Request $request)
  {
    try
    {
      $corpId = $request->corporate;
      $bank_code = $request->bank_code;
      $transaction_type = $request->transaction_type;
      $month = $request->month;

      $data = DB::select("[spVDWH_GetMonthlyErrorCode] '$corpId', '$bank_code','$transaction_type','$month' ");

      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;

      return response($res);
    } catch(QueryException $ex){
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listDetailReport(Request $request)
  {
    try
    {
      // $username = 'merchant1';
      // $branch = '6789';
      // $merchant = '1';

      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $merchant = $data[0]['value'];

      $dir = "C://generate/";
      $extFile = ".csv";

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $num = 0;
      $arrgoodi = array();

      foreach($a as $key => $value)
      {
        $partingExt = explode('.', $value);
        $partValue = explode('_', $partingExt[0]);
        $reportType = $partValue[0];
        $totalPart = count($partValue);

        if($totalPart == 3)
        {
          //$reportType = $partValue[0];
          //$date = $partValue[1];
          //$username = $partValue[2];

          $filename = $partValue[0]."_".$partValue[1]."_".$username.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'DetailReportByHost' && $partValue[2] == $username)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));

            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
        else if($totalPart == 4)
        {
          //$filename = $partValue[0]."_".$partValue[1]."_".$branch."_".$merchant.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'DetailReportByHost' && $partValue[3] == $merchant)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listDetailReportFiltered(Request $request)
  {
    try
    {
      $branch = $request->branch;
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $merchant = $data[0]['value'];

      $arrselected = array();
      $countas = 0;

      /*if($branch == 'All Branch')
      {
        $branch = '';
      }*/
      $now = date("YmdHis");

      $dir = "C://generate/";
      $extFile = ".csv";

      if(strlen($date) == 7)
      {
          $date = '01/'.$date;
      }

      $expDate = explode('/', $date);
      //$dateFormat = date('Ymd', strtotime($date));
      $dateFormat = $expDate[2].$expDate[1].$expDate[0];

      if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }
      //belum selesai

      if($branch == '')
      {
        switch ($range)
        {
            case 'd':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'DetailReportByHost_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'DetailReportByHost_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'DetailReportByHost_'.$eDate.'_'.$sDate."_".$username;
                break;

            default:
                # code...
                break;
        }

        //$sp = "[spPortal_GenerateReportByBank_CMD] '$code', '$branch', '$dateFormat', '$range', '$endPoint', '$merchId', '$filename'";

        $fullFileName = $filename.$extFile;
        $fullPath = $dir.$filename.$extFile;

        if (file_exists($fullPath))
        {
            $arrselected[$countas] = $fullFileName;
            $countas++;
        }
      }
      else if ($branch != '')
      {
        $files = array();

        switch ($range) {
            case 'd':

                $info = "(1 day report, ".$date.")";

                $start = $dateFormat;
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'DetailReportByHost_'.$dateFile."_".$branch."_".$merchant;
                //$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $fullFileName = $filename.$extFile;
                $fullPath = $dir.$filename.$extFile;

                if (file_exists($fullPath))
                {
                  array_push($files, $fullFileName);
                }

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'DetailReportByHost_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++) {

                    $filename = 'DetailReportByHost_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            case 'w':
                $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                //$filename = 'DetailReportByHost_'.$eDate.'_'.$sDate."_".$branch;

                for($i=$eDate; $i<=$sDate; $i++) {

                    $filename = 'DetailReportByHost_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            default:
                # code...
                break;
        }
        $arrselected = $files;
      }

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;
        $filename = $dir.$value;
        //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
        $filemtime = filemtime($fullPath);
        $filemtimez = strtotime("+420 minutes", $filemtime);

        $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);

        $size = filesize($filename);

        $decimals = 2;
        $sz = 'BKMGTP';
        $factor = floor((strlen($size) - 1) / 3);
        $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

        $goodi['size'] = $human_filesize."B";
        if($reportType == 'DetailReportByHost')
        {
          $arrgoodi[$num] = $goodi;
        }
        $num++;
        $filename = "";

      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listReconReport(Request $request)
  {
    try
    {
      // $username = 'merchant1';
      // $branch = '6789';
      // $merchant = '1';

      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $merchant = $data[0]['value'];

      $dir = "C://generate/";
      $extFile = ".csv";

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $num = 0;
      $arrgoodi = array();

      foreach($a as $key => $value)
      {
        $partingExt = explode('.', $value);
        $partValue = explode('_', $partingExt[0]);
        $reportType = $partValue[0];
        $totalPart = count($partValue);

        if($totalPart == 3)
        {
          //$reportType = $partValue[0];
          //$date = $partValue[1];
          //$username = $partValue[2];

          $filename = $partValue[0]."_".$partValue[1]."_".$username.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'ReconsiliationReport' && $partValue[2] == $username)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
        else if($totalPart == 4)
        {
          //$filename = $partValue[0]."_".$partValue[1]."_".$branch."_".$merchant.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'ReconsiliationReport' && $partValue[3] == $merchant)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listReconReportFiltered(Request $request)
  {
    try
    {
      $branch = $request->branch;
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $merchant = $data[0]['value'];

      $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = '';
      }
      $now = date("YmdHis");

      $dir = "C://generate/";
      $extFile = ".csv";

      if(strlen($date) == 7)
      {
          $date = '01/'.$date;
      }

      $expDate = explode('/', $date);
      //$dateFormat = date('Ymd', strtotime($date));
      $dateFormat = $expDate[2].$expDate[1].$expDate[0];

      if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }
      //belum selesai

      if($branch == '')
      {
        switch ($range)
        {
            case 'd':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'ReconsiliationReport_'.$eDate.'_'.$sDate."_".$username;
                break;

            default:
                # code...
                break;
        }

        //$sp = "[spPortal_GenerateReportByBank_CMD] '$code', '$branch', '$dateFormat', '$range', '$endPoint', '$merchId', '$filename'";

        $fullFileName = $filename.$extFile;
        $fullPath = $dir.$filename.$extFile;

        if (file_exists($fullPath))
        {
            $arrselected[$countas] = $fullFileName;
            $countas++;
        }
      }
      else if ($branch != '')
      {
        $files = array();

        switch ($range) {
            case 'd':

                $info = "(1 day report, ".$date.")";

                $start = $dateFormat;
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$branch."_".$merchant;
                //$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $fullFileName = $filename.$extFile;
                $fullPath = $dir.$filename.$extFile;

                if (file_exists($fullPath))
                {
                  array_push($files, $fullFileName);
                }

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++) {

                    $filename = 'ReconsiliationReport_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            case 'w':
                $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'ReconsiliationReport_'.$eDate.'_'.$sDate."_".$branch;

                for($i=$eDate; $i<=$sDate; $i++) {

                    $filename = 'ReconsiliationReport_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            default:
                # code...
                break;
        }
        $arrselected = $files;
      }

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;
        $filename = $dir.$value;
        //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
        $filemtime = filemtime($filename);
        $filemtimez = strtotime("+420 minutes", $filemtime);

        $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
        $size = filesize($filename);

        $decimals = 2;
        $sz = 'BKMGTP';
        $factor = floor((strlen($size) - 1) / 3);
        $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

        $goodi['size'] = $human_filesize."B";
        if($reportType == 'ReconsiliationReport')
        {
          $arrgoodi[$num] = $goodi;
        }
        $num++;
        $filename = "";

      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listDetailReportAcquirer(Request $request)
  {
    try
    {
      // $username = 'merchant1';
      // $branch = '6789';
      // $merchant = '1';

      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $acquirer = $data[0]['FNAME'];

      $dir = "C://generate/";
      $extFile = ".csv";

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $num = 0;
      $arrgoodi = array();

      foreach($a as $key => $value)
      {
        $partingExt = explode('.', $value);
        $partValue = explode('_', $partingExt[0]);
        $reportType = $partValue[0];
        $totalPart = count($partValue);

        if($totalPart == 3)
        {
          //$reportType = $partValue[0];
          //$date = $partValue[1];
          //$username = $partValue[2];

          $filename = $partValue[0]."_".$partValue[1]."_".$username.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'AcquirerDetailReport' && $partValue[2] == $username)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
        else if($totalPart == 4)
        {
          //AcquirerDetailReport_20180630_8_MANDIRI
          //$filename = $partValue[0]."_".$partValue[1]."_".$branch."_".$merchant.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'AcquirerDetailReport' && $partValue[3] == $acquirer)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listDetailReportFilteredAcquirer(Request $request)
  {
    try
    {
      $merchant = $request->merchant;
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $acquirer = $data[0]['FNAME'];

      $arrselected = array();
      $countas = 0;

      /*if($branch == 'All Branch')
      {
        $branch = '';
      }*/
      $now = date("YmdHis");

      $dir = "C://generate/";
      $extFile = ".csv";

      if(strlen($date) == 7)
      {
          $date = '01/'.$date;
      }

      $expDate = explode('/', $date);
      //$dateFormat = date('Ymd', strtotime($date));
      $dateFormat = $expDate[2].$expDate[1].$expDate[0];

      if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }
      //belum selesai

      if($merchant == '')
      {
        switch ($range)
        {
            case 'd':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'AcquirerDetailReport_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'AcquirerDetailReport_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'AcquirerDetailReport_'.$eDate.'_'.$sDate."_".$username;
                break;

            default:
                # code...
                break;
        }

        //$sp = "[spPortal_GenerateReportByBank_CMD] '$code', '$branch', '$dateFormat', '$range', '$endPoint', '$merchId', '$filename'";

        $fullFileName = $filename.$extFile;
        $fullPath = $dir.$filename.$extFile;

        if (file_exists($fullPath))
        {
            $arrselected[$countas] = $fullFileName;
            $countas++;
        }
      }
      else if ($merchant != '')
      {
        $files = array();

        switch ($range) {
            case 'd':

                $info = "(1 day report, ".$date.")";

                $start = $dateFormat;
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'AcquirerDetailReport_'.$dateFile."_".$merchant."_".$acquirer;
                //$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $fullFileName = $filename.$extFile;
                $fullPath = $dir.$filename.$extFile;

                if (file_exists($fullPath))
                {
                  array_push($files, $fullFileName);
                }

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'AcquirerDetailReport_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++) {

                    $filename = 'AcquirerDetailReport_'.$i.'_'.$merchant."_".$acquirer;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            case 'w':
                $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                //$filename = 'AcquirerDetailReport_'.$eDate.'_'.$sDate."_".$merchant;

                for($i=$eDate; $i<=$sDate; $i++) {

                    $filename = 'AcquirerDetailReport_'.$i.'_'.$merchant."_".$acquirer;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            default:
                # code...
                break;
        }
        $arrselected = $files;
      }

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;
        $filename = $dir.$value;
        //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
        $filemtime = filemtime($filename);
        $filemtimez = strtotime("+420 minutes", $filemtime);

        $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
        $size = filesize($filename);

        $decimals = 2;
        $sz = 'BKMGTP';
        $factor = floor((strlen($size) - 1) / 3);
        $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

        $goodi['size'] = $human_filesize."B";
        if($reportType == 'AcquirerDetailReport')
        {
          $arrgoodi[$num] = $goodi;
        }
        $num++;
        $filename = "";

      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listReconReportAcquirer(Request $request)
  {
    try
    {
      // $username = 'merchant1';
      // $branch = '6789';
      // $merchant = '1';

      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $acquirer = $data[0]['FNAME'];

      $dir = "C://generate/";
      $extFile = ".csv";

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $num = 0;
      $arrgoodi = array();

      foreach($a as $key => $value)
      {
        $partingExt = explode('.', $value);
        $partValue = explode('_', $partingExt[0]);
        $reportType = $partValue[0];
        $totalPart = count($partValue);

        if($totalPart == 3)
        {
          //$reportType = $partValue[0];
          //$date = $partValue[1];
          //$username = $partValue[2];

          $filename = $partValue[0]."_".$partValue[1]."_".$username.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'AcquirerReconsiliationReport' && $partValue[2] == $username)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
        else if($totalPart == 4)
        {
          //AcquirerDetailReport_20180630_8_MANDIRI
          //$filename = $partValue[0]."_".$partValue[1]."_".$branch."_".$merchant.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'AcquirerReconsiliationReport' && $partValue[3] == $acquirer)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listReconReportFilteredAcquirer(Request $request)
  {
    try
    {
      $merchant = $request->merchant;
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $acquirer = $data[0]['FNAME'];

      $arrselected = array();
      $countas = 0;

      /*if($branch == 'All Branch')
      {
        $branch = '';
      }*/
      $now = date("YmdHis");

      $dir = "C://generate/";
      $extFile = ".csv";

      if(strlen($date) == 7)
      {
          $date = '01/'.$date;
      }

      $expDate = explode('/', $date);
      //$dateFormat = date('Ymd', strtotime($date));
      $dateFormat = $expDate[2].$expDate[1].$expDate[0];

      if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }
      //belum selesai

      if($merchant == '')
      {
        switch ($range)
        {
            case 'd':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'AcquirerReconsiliationReport_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'AcquirerReconsiliationReport_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'AcquirerReconsiliationReport_'.$eDate.'_'.$sDate."_".$username;
                break;

            default:
                # code...
                break;
        }

        //$sp = "[spPortal_GenerateReportByBank_CMD] '$code', '$branch', '$dateFormat', '$range', '$endPoint', '$merchId', '$filename'";

        $fullFileName = $filename.$extFile;
        $fullPath = $dir.$filename.$extFile;

        if (file_exists($fullPath))
        {
            $arrselected[$countas] = $fullFileName;
            $countas++;
        }
      }
      else if ($merchant != '')
      {
        $files = array();

        switch ($range) {
            case 'd':

                $info = "(1 day report, ".$date.")";

                $start = $dateFormat;
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'AcquirerReconsiliationReport_'.$dateFile."_".$merchant."_".$acquirer;
                //$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $fullFileName = $filename.$extFile;
                $fullPath = $dir.$filename.$extFile;

                if (file_exists($fullPath))
                {
                  array_push($files, $fullFileName);
                }

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'AcquirerReconsiliationReport_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++) {

                    $filename = 'AcquirerReconsiliationReport_'.$i.'_'.$merchant."_".$acquirer;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            case 'w':
                $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'AcquirerReconsiliationReport_'.$eDate.'_'.$sDate."_".$merchant;

                for($i=$eDate; $i<=$sDate; $i++) {

                    $filename = 'AcquirerReconsiliationReport_'.$i.'_'.$merchant."_".$acquirer;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            default:
                # code...
                break;
        }
        $arrselected = $files;
      }

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;
        $filename = $dir.$value;
        //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
        $filemtime = filemtime($fullPath);
        $filemtimez = strtotime("+420 minutes", $filemtime);

        $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
        $size = filesize($filename);

        $decimals = 2;
        $sz = 'BKMGTP';
        $factor = floor((strlen($size) - 1) / 3);
        $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

        $goodi['size'] = $human_filesize."B";
        if($reportType == 'AcquirerReconsiliationReport')
        {
          $arrgoodi[$num] = $goodi;
        }
        $num++;
        $filename = "";

      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listDetailReportBranch(Request $request)
  {
    try
    {
      // $username = 'merchant1';
      // $branch = '6789';
      // $merchant = '1';

      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $branch = $data[0]['branch_code'];

      $dir = "C://generate/";
      $extFile = ".csv";

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $num = 0;
      $arrgoodi = array();

      foreach($a as $key => $value)
      {
        $partingExt = explode('.', $value);
        $partValue = explode('_', $partingExt[0]);
        $reportType = $partValue[0];
        $totalPart = count($partValue);

        if($totalPart == 3)
        {
          //$reportType = $partValue[0];
          //$date = $partValue[1];
          //$username = $partValue[2];

          $filename = $partValue[0]."_".$partValue[1]."_".$username.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'DetailReportByHost' && $partValue[2] == $username)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));

            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
        else if($totalPart == 4)
        {
          //$filename = $partValue[0]."_".$partValue[1]."_".$branch."_".$merchant.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'DetailReportByHost' && $partValue[2] == $branch)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listDetailReportFilteredBranch(Request $request)
  {
    try
    {

      $range = $request->range;
      $date = $request->date;
      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $branch = $data[0]['branch_code'];
      $merchant = $data[0]['merch_id'];

      $arrselected = array();
      $countas = 0;

      /*if($branch == 'All Branch')
      {
        $branch = '';
      }*/
      $now = date("YmdHis");

      $dir = "C://generate/";
      $extFile = ".csv";

      if(strlen($date) == 7)
      {
          $date = '01/'.$date;
      }

      $expDate = explode('/', $date);
      //$dateFormat = date('Ymd', strtotime($date));
      $dateFormat = $expDate[2].$expDate[1].$expDate[0];

      if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }
      //belum selesai

      if($branch == '')
      {
        switch ($range)
        {
            case 'd':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'DetailReportByHost_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'DetailReportByHost_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'DetailReportByHost_'.$eDate.'_'.$sDate."_".$username;
                break;

            default:
                # code...
                break;
        }

        //$sp = "[spPortal_GenerateReportByBank_CMD] '$code', '$branch', '$dateFormat', '$range', '$endPoint', '$merchId', '$filename'";

        $fullFileName = $filename.$extFile;
        $fullPath = $dir.$filename.$extFile;

        if (file_exists($fullPath))
        {
            $arrselected[$countas] = $fullFileName;
            $countas++;
        }
      }
      else if ($branch != '')
      {
        $files = array();

        switch ($range) {
            case 'd':

                $info = "(1 day report, ".$date.")";

                $start = $dateFormat;
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'DetailReportByHost_'.$dateFile."_".$branch."_".$merchant;
                //$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $fullFileName = $filename.$extFile;
                $fullPath = $dir.$filename.$extFile;

                if (file_exists($fullPath))
                {
                  array_push($files, $fullFileName);
                }

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'DetailReportByHost_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++) {

                    $filename = 'DetailReportByHost_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            case 'w':
                $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'DetailReportByHost_'.$eDate.'_'.$sDate."_".$branch;

                for($i=$eDate; $i<=$sDate; $i++) {

                    $filename = 'DetailReportByHost_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            default:
                # code...
                break;
        }
        $arrselected = $files;
      }

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;
        $filename = $dir.$value;
        //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
        $filemtime = filemtime($filename);
        $filemtimez = strtotime("+420 minutes", $filemtime);

        $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);

        $size = filesize($filename);

        $decimals = 2;
        $sz = 'BKMGTP';
        $factor = floor((strlen($size) - 1) / 3);
        $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

        $goodi['size'] = $human_filesize."B";
        if($reportType == 'DetailReportByHost')
        {
          $arrgoodi[$num] = $goodi;
        }
        $num++;
        $filename = "";

      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listReconReportBranch(Request $request)
  {
    try
    {
      // $username = 'merchant1';
      // $branch = '6789';
      // $merchant = '1';

      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $branch = $data[0]['branch_code'];

      $dir = "C://generate/";
      $extFile = ".csv";

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $num = 0;
      $arrgoodi = array();

      foreach($a as $key => $value)
      {
        $partingExt = explode('.', $value);
        $partValue = explode('_', $partingExt[0]);
        $reportType = $partValue[0];
        $totalPart = count($partValue);

        if($totalPart == 3)
        {
          //$reportType = $partValue[0];
          //$date = $partValue[1];
          //$username = $partValue[2];

          $filename = $partValue[0]."_".$partValue[1]."_".$username.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'ReconsiliationReport' && $partValue[2] == $username)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));

            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
        else if($totalPart == 4)
        {
          //$filename = $partValue[0]."_".$partValue[1]."_".$branch."_".$merchant.$extFile;
          $fullPath = $dir.$value;

          if (file_exists($fullPath) && $reportType == 'ReconsiliationReport' && $partValue[2] == $branch)
          {
            $goodi['number'] = $num;
            $goodi['val'] = $value;
            //$filename = $dir.$value;
            //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
            $filemtime = filemtime($fullPath);
            $filemtimez = strtotime("+420 minutes", $filemtime);

            $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
            $size = filesize($fullPath);

            $decimals = 2;
            $sz = 'BKMGTP';
            $factor = floor((strlen($size) - 1) / 3);
            $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

            $goodi['size'] = $human_filesize."B";
            $arrgoodi[$num] = $goodi;
            $num++;
            $filename = "";
          }
        }
      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listReconReportFilteredBranch(Request $request)
  {
    try
    {

      $range = $request->range;
      $date = $request->date;
      $username = $request->username;
      //$merchant = "1";

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $branch = $data[0]['branch_code'];
      $merchant = $data[0]['merch_id'];

      $arrselected = array();
      $countas = 0;

      /*if($branch == 'All Branch')
      {
        $branch = '';
      }*/
      $now = date("YmdHis");

      $dir = "C://generate/";
      $extFile = ".csv";

      if(strlen($date) == 7)
      {
          $date = '01/'.$date;
      }

      $expDate = explode('/', $date);
      //$dateFormat = date('Ymd', strtotime($date));
      $dateFormat = $expDate[2].$expDate[1].$expDate[0];

      if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }

      /*
      if($branch == '')
      {
        switch ($range)
        {
            case 'd':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'ReconsiliationReport_'.$eDate.'_'.$sDate."_".$username;
                break;

            default:
                # code...
                break;
        }

        //$sp = "[spPortal_GenerateReportByBank_CMD] '$code', '$branch', '$dateFormat', '$range', '$endPoint', '$merchId', '$filename'";

        $fullFileName = $filename.$extFile;
        $fullPath = $dir.$filename.$extFile;

        if (file_exists($fullPath))
        {
            $arrselected[$countas] = $fullFileName;
            $countas++;
        }
      }
      else */
      if ($branch != '')
      {
        $files = array();

        switch ($range) {
            case 'd':

                $info = "(1 day report, ".$date.")";

                $start = $dateFormat;
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1].$expDate[0];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$branch."_".$merchant;
                //$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $fullFileName = $filename.$extFile;
                $fullPath = $dir.$filename.$extFile;

                if (file_exists($fullPath))
                {
                  array_push($files, $fullFileName);
                }

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'ReconsiliationReport_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++) {

                    $filename = 'ReconsiliationReport_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            case 'w':
                $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'ReconsiliationReport_'.$eDate.'_'.$sDate."_".$branch;

                for($i=$eDate; $i<=$sDate; $i++) {

                    $filename = 'ReconsiliationReport_'.$i.'_'.$branch."_".$merchant;
                    //$filename = 'ReconsiliationReport_'.$i.'_'.$username;
                    $fullFileName = $filename.$extFile;
                    $fullPath = $dir.$filename.$extFile;

                    if (file_exists($fullPath))
                    {
                      array_push($files, $fullFileName);
                    }

                }

                break;

            default:
                # code...
                break;
        }
        $arrselected = $files;
      }

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;
        $filename = $dir.$value;
        //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
        $filemtime = filemtime($filename);
        $filemtimez = strtotime("+420 minutes", $filemtime);

        $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);

        $size = filesize($filename);

        $decimals = 2;
        $sz = 'BKMGTP';
        $factor = floor((strlen($size) - 1) / 3);
        $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

        $goodi['size'] = $human_filesize."B";
        if($reportType == 'ReconsiliationReport')
        {
          $arrgoodi[$num] = $goodi;
        }
        $num++;
        $filename = "";

      }

      $res['success'] = true;
      $res['total'] = count($a);
      $res['result'] = $arrgoodi;

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
