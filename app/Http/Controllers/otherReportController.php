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
use ZIPARCHIVE;
use Storage;
use Response;

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

      $merchant = array();
      $merchant[0] = 'AllMerchant';
      for($a = 0; $a < count($data); $a++)
      {
        $merchant[$a+1] = $data[$a]['value'];
      }

      //$merchant = $data[0]['value'];

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

          if (file_exists($fullPath) && $reportType == 'DetailReportByHost' && in_array($partValue[3], $merchant))
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
      $res['merchant_list'] = $merchant;
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

      $merchant = array();
      $merchant[0] = 'AllMerchant';
      for($a = 0; $a < count($data); $a++)
      {
        $merchant[$a+1] = $data[$a]['value'];
      }

      $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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
        				foreach($merchant as $key => $value)
        				{
        					$filename = 'DetailReportByHost_'.$dateFile."_".$branch."_".$value;
        					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        					$fullFileName = $filename.$extFile;
        					$fullPath = $dir.$filename.$extFile;

        					if (file_exists($fullPath))
        					{
        					  array_push($files, $fullFileName);
        					}
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
                for($i=$first_date; $i<=$last_date; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$filename = 'DetailReportByHost_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
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

                for($i=$eDate; $i<=$sDate; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$filename = 'DetailReportByHost_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

            $exists = Storage::disk('tms_ftp')->exists('//generate//'.$fullFileName);

						if (file_exists($exists))
						{
						  array_push($files, $fullFileName);
						}
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

  public function listDetailReportFilteredSettlement(Request $request)
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

      $merchant = array();
      $merchant[0] = 'AllMerchant';
      for($a = 0; $a < count($data); $a++)
      {
        $merchant[$a+1] = $data[$a]['value'];
      }

      $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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
                $filename = 'Settled_DetailReportByHost_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_DetailReportByHost_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'Settled_DetailReportByHost_'.$eDate.'_'.$sDate."_".$username;
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
				foreach($merchant as $key => $value)
				{
					$filename = 'Settled_DetailReportByHost_'.$dateFile."_".$branch."_".$value;
					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

					$fullFileName = $filename.$extFile;
					$fullPath = $dir.$filename.$extFile;

					if (file_exists($fullPath))
					{
					  array_push($files, $fullFileName);
					}
				}

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_DetailReportByHost_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$filename = 'Settled_DetailReportByHost_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
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

                for($i=$eDate; $i<=$sDate; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$filename = 'Settled_DetailReportByHost_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
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
        $reportType = $partValue[0].'_'.$partValue[1];

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
        if($reportType == 'Settled_DetailReportByHost')
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

  public function zipListReport(Request $request)
  {
    try
    {
      $checkedA = $request->checkedA;
       //$checkedA = ["DetailReportByHost_20180629_0000_1.csv","DetailReportByHost_20180629_6789_1.csv","DetailReportByHost_20180629_A003_1.csv","DetailReportByHost_20180629_AllBranch_1.csv","DetailReportByHost_20180629_BGD_1.csv","DetailReportByHost_20180629_G001_1.csv","DetailReportByHost_20180629_TEST_1.csv","DetailReportByHost_20180630_0000_1.csv","DetailReportByHost_20180630_6789_1.csv","DetailReportByHost_20180630_A003_1.csv"]  ;

      $dir = "C://generate/";

      $zipname = time().'AllReport.zip';
      $public_dir = storage_path("app");

      $zip = new ZipArchive;

      if (file_exists($public_dir.$zipname)) {
          $zip->open($public_dir . '/' . $zipname, ZIPARCHIVE::OVERWRITE );
      } else {
          $zip->open($public_dir . '/' . $zipname, ZIPARCHIVE::CREATE );
      }

      foreach($checkedA as $file)
      {
        //echo $file;
        //echo "<br>";
        $zip->addFile($dir.$file, $file);
      }

      $zip->close();



    //  Storage::disk('local')->put('AllReport.zip', $zipname);
      //return response()->download($zipname);
    //header('Content-Type: application/zip');
      //header('Content-disposition: attachment; filename='.$zipname);
      // header('Content-Length: ' . filesize($zipname));
      // header("Pragma: no-cache");
      // header("Expires: 0");

      $header = array(
                   'Content-Type' => 'application/octet-stream',
               );
      return response()->download(storage_path("app/".$zipname), $zipname, $header)->deleteFileAfterSend(true);
      //return Storage::download($zipname);

      // $res['success'] = true;
      // //$res['total'] = count($a);
      // $res['result'] = $zipname;
      // //
      // return response($res);

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

      //$merchant = $data[0]['value'];

      $merchant = array();
      $merchant[0] = 'AllMerchant';
      for($a = 0; $a < count($data); $a++)
      {
        $merchant[$a+1] = $data[$a]['value'];
      }

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

          if (file_exists($fullPath) && $reportType == 'ReconsiliationReport' && in_array($partValue[3], $merchant))
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
      $res['merchant_list'] = $merchant;
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

      $merchant = array();
      $merchant[0] = 'AllMerchant';
      for($a = 0; $a < count($data); $a++)
      {
        $merchant[$a+1] = $data[$a]['value'];
      }

      $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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

				foreach($merchant as $key => $value)
				{
					$filename = 'ReconsiliationReport_'.$dateFile."_".$branch."_".$value;
					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

					$fullFileName = $filename.$extFile;
					$fullPath = $dir.$filename.$extFile;

					if (file_exists($fullPath))
					{
					  array_push($files, $fullFileName);
					}
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
					foreach($merchant as $key => $value)
					{
						$filename = 'ReconsiliationReport_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}

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
					foreach($merchant as $key => $value)
					{
						$filename = 'ReconsiliationReport_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
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

  public function listReconReportFilteredSettlement(Request $request)
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

      $merchant = array();
      $merchant[0] = 'AllMerchant';
      for($a = 0; $a < count($data); $a++)
      {
        $merchant[$a+1] = $data[$a]['value'];
      }

      $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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
                $filename = 'Settled_ReconsiliationReport_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_ReconsiliationReport_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'Settled_ReconsiliationReport_'.$eDate.'_'.$sDate."_".$username;
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

				foreach($merchant as $key => $value)
				{
					$filename = 'Settled_ReconsiliationReport_'.$dateFile."_".$branch."_".$value;
					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

					$fullFileName = $filename.$extFile;
					$fullPath = $dir.$filename.$extFile;

					if (file_exists($fullPath))
					{
					  array_push($files, $fullFileName);
					}
				}
                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_ReconsiliationReport_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++) {
					foreach($merchant as $key => $value)
					{
						$filename = 'Settled_ReconsiliationReport_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}

					}
                }

                break;

            case 'w':
                $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'Settled_ReconsiliationReport_'.$eDate.'_'.$sDate."_".$branch;

                for($i=$eDate; $i<=$sDate; $i++) {
					foreach($merchant as $key => $value)
					{
						$filename = 'Settled_ReconsiliationReport_'.$i.'_'.$branch."_".$value;
						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
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
        $reportType = $partValue[0].'_'.$partValue[1];

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
        if($reportType == 'Settled_ReconsiliationReport')
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

	  $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
	  $merchant = array();
	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }

      $branch = array();
      for($a = 0; $a < count($data); $a++)
      {
        $branch[$a] = $data[$a]['branch_code'];
      }

      //$branch = $data[0]['branch_code'];

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

          if (file_exists($fullPath) && $reportType == 'DetailReportByHost' && in_array($partValue[2], $branch) && in_array($partValue[3], $merchant))
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
      $res['merchant'] = $merchant;
      $res['branch'] = $branch;
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

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }

      $branch = array();
      for($a = 0; $a < count($data); $a++)
      {
        $branch[$a] = $data[$a]['branch_code'];
      }

      //$branch = $data[0]['branch_code'];

	  $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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
				foreach($merchant as $key => $value)
				{
					$merchantm = $value;
					foreach($branch as $key => $value)
					{
						$branchb = $value;

						$filename = 'DetailReportByHost_'.$dateFile."_".$branchb."_".$merchantm;
						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
					}
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
                for($i=$first_date; $i<=$last_date; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'DetailReportByHost_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}

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

                for($i=$eDate; $i<=$sDate; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'DetailReportByHost_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}
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

  public function listDetailReportFilteredBranchSettlement(Request $request)
  {
    try
    {
	    $range = $request->range;
      $date = $request->date;
      $username = $request->username;

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }

      $branch = array();
      for($a = 0; $a < count($data); $a++)
      {
        $branch[$a] = $data[$a]['branch_code'];
      }

      //$branch = $data[0]['branch_code'];

	  $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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
                $filename = 'Settled_DetailReportByHost_'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_DetailReportByHost_'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'Settled_DetailReportByHost_'.$eDate.'_'.$sDate."_".$username;
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
				foreach($merchant as $key => $value)
				{
					$merchantm = $value;
					foreach($branch as $key => $value)
					{
						$branchb = $value;

						$filename = 'Settled_DetailReportByHost_'.$dateFile."_".$branchb."_".$merchantm;
						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
					}
				}

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_DetailReportByHost_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'Settled_DetailReportByHost_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}

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

                for($i=$eDate; $i<=$sDate; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'Settled_DetailReportByHost_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}
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
        $reportType = $partValue[0].'_'.$partValue[1];

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
        if($reportType == 'Settled_DetailReportByHost')
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

	  $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

	  $merchant = array();
	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }
      //$merchant = $datas[0]['value'];

      $branch = array();
      for($a = 0; $a < count($data); $a++)
      {
        $branch[$a] = $data[$a]['branch_code'];
      }

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

          if (file_exists($fullPath) && $reportType == 'ReconsiliationReport' && in_array($partValue[2], $branch) && in_array($partValue[3], $merchant))
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
      $res['branch'] = $branch;
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

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }

      $branch = array();
      for($a = 0; $a < count($data); $a++)
      {
        $branch[$a] = $data[$a]['branch_code'];
      }

	  $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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
				foreach($merchant as $key => $value)
				{
					$merchantm = $value;
					foreach($branch as $key => $value)
					{
						$branchb = $value;

						$filename = 'ReconsiliationReport_'.$dateFile."_".$branchb."_".$merchantm;
						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
					}
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
                for($i=$first_date; $i<=$last_date; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'ReconsiliationReport_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}

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

                for($i=$eDate; $i<=$sDate; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'ReconsiliationReport_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}
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

  public function listReconReportFilteredBranchSettlement(Request $request)
  {
    try
    {
		 $range = $request->range;
      $date = $request->date;
      $username = $request->username;

      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }

      $branch = array();
      for($a = 0; $a < count($data); $a++)
      {
        $branch[$a] = $data[$a]['branch_code'];
      }

	  $arrselected = array();
      $countas = 0;

      if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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
                $filename = 'Settled_ReconsiliationReport'.$dateFile."_".$username;
                break;
             case 'm':

                $expDate = explode('/', $date);
                //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_ReconsiliationReport'.$dateFile."_".$username;
                break;
            case 'w':
                $dateN = date('d/m/Y', strtotime('-7 days '.$dateFormat));

                $sDate = explode('/', $date);
                $sDate = $sDate[2].$sDate[1].$sDate[0];

                $eDate = explode('/', $dateN);
                $eDate = $eDate[2].$eDate[1].$eDate[0];
                $filename = 'Settled_ReconsiliationReport'.$eDate.'_'.$sDate."_".$username;
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
				foreach($merchant as $key => $value)
				{
					$merchantm = $value;
					foreach($branch as $key => $value)
					{
						$branchb = $value;

						$filename = 'Settled_ReconsiliationReport_'.$dateFile."_".$branchb."_".$merchantm;
						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

						$fullFileName = $filename.$extFile;
						$fullPath = $dir.$filename.$extFile;

						if (file_exists($fullPath))
						{
						  array_push($files, $fullFileName);
						}
					}
				}

                break;

             case 'm':

                $info = "(1 month report, ".substr($date, 3).")";

                $start = date('Ym', strtotime($dateFormat));
                $end = $start;

                $expDate = explode('/', $date);
                    //$dateFormat = date('Ymd', strtotime($date));
                $dateFile = $expDate[2].$expDate[1];
                $filename = 'Settled_ReconsiliationReport_'.$dateFile."_".$username;

                $first_date = '01-'.$expDate[1].'-'.$expDate[2];
                $first_date = date('Ym01', strtotime($first_date));
                $last_date  = date('Ymt', strtotime($first_date));

                //for($i=$first_date; $i<=20170621; $i++) {
                for($i=$first_date; $i<=$last_date; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'Settled_ReconsiliationReport_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}

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

                for($i=$eDate; $i<=$sDate; $i++)
				{
					foreach($merchant as $key => $value)
					{
						$merchantm = $value;
						foreach($branch as $key => $value)
						{
							$branchb = $value;

							$filename = 'Settled_ReconsiliationReport_'.$i."_".$branchb."_".$merchantm;
							//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

							$fullFileName = $filename.$extFile;
							$fullPath = $dir.$filename.$extFile;

							if (file_exists($fullPath))
							{
							  array_push($files, $fullFileName);
							}
						}
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
        $reportType = $partValue[0].'_'.$partValue[1];

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
        if($reportType == 'Settled_ReconsiliationReport')
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

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }

      //$acquirer = $data[0]['FNAME'];

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

          if (file_exists($fullPath) && $reportType == 'AcquirerDetailReport' && in_array($partValue[3], $acquirer))
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
      $res['acquirer_list'] = $acquirer;
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
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }
      $merchant[count($datas)] = 'AllMerchant';

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }

      //$branch = $data[0]['branch_code'];

	    $arrselected = array();
      $countas = 0;

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

      $files = array();

      switch ($range)
      {
          case 'd':

              $info = "(1 day report, ".$date.")";

              $start = $dateFormat;
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1].$expDate[0];
        			foreach($acquirer as $key => $value)
        			{
        				$acquirera = $value;
        				foreach($merchant as $key => $value)
        				{
        					$merchantm = $value;

        					$filename = 'AcquirerDetailReport_'.$dateFile."_".$merchantm."_".$acquirera;
        					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        					$fullFileName = $filename.$extFile;
        					$fullPath = $dir.$filename.$extFile;

        					if (file_exists($fullPath))
        					{
        					  array_push($files, $fullFileName);
        					}
        				}
        			}

              break;

           case 'm':

              $info = "(1 month report, ".substr($date, 3).")";

              $start = date('Ym', strtotime($dateFormat));
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1];
              //$filename = 'DetailReportByHost_'.$dateFile."_".$username;

              $first_date = '01-'.$expDate[1].'-'.$expDate[2];
              $first_date = date('Ym01', strtotime($first_date));
              $last_date  = date('Ymt', strtotime($first_date));

              //for($i=$first_date; $i<=20170621; $i++) {
              for($i=$first_date; $i<=$last_date; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'AcquirerDetailReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}

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

              for($i=$eDate; $i<=$sDate; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'AcquirerDetailReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}
        				}
              }

              break;

          default:
              # code...
              break;
      }
      $arrselected = $files;

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

  public function listDetailReportFilteredAcquirerSettlement(Request $request)
  {
    try
    {
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }
      $merchant[count($datas)] = 'AllMerchant';

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }

      //$branch = $data[0]['branch_code'];

	    $arrselected = array();
      $countas = 0;

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

      $files = array();

      switch ($range)
      {
          case 'd':

              $info = "(1 day report, ".$date.")";

              $start = $dateFormat;
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1].$expDate[0];
        			foreach($acquirer as $key => $value)
        			{
        				$acquirera = $value;
        				foreach($merchant as $key => $value)
        				{
        					$merchantm = $value;

        					$filename = 'Settled_AcquirerDetailReport_'.$dateFile."_".$merchantm."_".$acquirera;
        					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        					$fullFileName = $filename.$extFile;
        					$fullPath = $dir.$filename.$extFile;

        					if (file_exists($fullPath))
        					{
        					  array_push($files, $fullFileName);
        					}
        				}
        			}

              break;

           case 'm':

              $info = "(1 month report, ".substr($date, 3).")";

              $start = date('Ym', strtotime($dateFormat));
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1];
              //$filename = 'DetailReportByHost_'.$dateFile."_".$username;

              $first_date = '01-'.$expDate[1].'-'.$expDate[2];
              $first_date = date('Ym01', strtotime($first_date));
              $last_date  = date('Ymt', strtotime($first_date));

              //for($i=$first_date; $i<=20170621; $i++) {
              for($i=$first_date; $i<=$last_date; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'Settled_AcquirerDetailReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}

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

              for($i=$eDate; $i<=$sDate; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'Settled_AcquirerDetailReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}
        				}
              }

              break;

          default:
              # code...
              break;
      }
      $arrselected = $files;

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0].'_'.$partValue[1];

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
        if($reportType == 'Settled_AcquirerDetailReport')
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

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }

      //$acquirer = $data[0]['FNAME'];

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

          if (file_exists($fullPath) && $reportType == 'AcquirerReconsiliationReport' && in_array($partValue[3], $acquirer))
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
      $res['acquirer_list'] = $acquirer;
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
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }
      $merchant[count($datas)] = 'AllMerchant';

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }

      //$branch = $data[0]['branch_code'];

	    $arrselected = array();
      $countas = 0;

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

      $files = array();

      switch ($range)
      {
          case 'd':

              $info = "(1 day report, ".$date.")";

              $start = $dateFormat;
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1].$expDate[0];
        			foreach($acquirer as $key => $value)
        			{
        				$acquirera = $value;
        				foreach($merchant as $key => $value)
        				{
        					$merchantm = $value;

        					$filename = 'AcquirerReconsiliationReport_'.$dateFile."_".$merchantm."_".$acquirera;
        					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        					$fullFileName = $filename.$extFile;
        					$fullPath = $dir.$filename.$extFile;

        					if (file_exists($fullPath))
        					{
        					  array_push($files, $fullFileName);
        					}
        				}
        			}

              break;

           case 'm':

              $info = "(1 month report, ".substr($date, 3).")";

              $start = date('Ym', strtotime($dateFormat));
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1];
              //$filename = 'DetailReportByHost_'.$dateFile."_".$username;

              $first_date = '01-'.$expDate[1].'-'.$expDate[2];
              $first_date = date('Ym01', strtotime($first_date));
              $last_date  = date('Ymt', strtotime($first_date));

              //for($i=$first_date; $i<=20170621; $i++) {
              for($i=$first_date; $i<=$last_date; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'AcquirerReconsiliationReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}

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

              for($i=$eDate; $i<=$sDate; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'AcquirerReconsiliationReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}
        				}
              }

              break;

          default:
              # code...
              break;
      }
      $arrselected = $files;

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

  public function listReconReportFilteredAcquirerSettlement(Request $request)
  {
    try
    {
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;

      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

	    $datas = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

      $datas = json_encode($datas);
      $datas = json_decode($datas, true);

      //$merchant = $datas[0]['value'];
  	  $merchant = array();
  	  for($a = 0; $a < count($datas); $a++)
      {
        $merchant[$a] = $datas[$a]['value'];
      }
      $merchant[count($datas)] = 'AllMerchant';

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }

      //$branch = $data[0]['branch_code'];

	    $arrselected = array();
      $countas = 0;

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

      $files = array();

      switch ($range)
      {
          case 'd':

              $info = "(1 day report, ".$date.")";

              $start = $dateFormat;
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1].$expDate[0];
        			foreach($acquirer as $key => $value)
        			{
        				$acquirera = $value;
        				foreach($merchant as $key => $value)
        				{
        					$merchantm = $value;

        					$filename = 'Settled_AcquirerReconsiliationReport_'.$dateFile."_".$merchantm."_".$acquirera;
        					//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        					$fullFileName = $filename.$extFile;
        					$fullPath = $dir.$filename.$extFile;

        					if (file_exists($fullPath))
        					{
        					  array_push($files, $fullFileName);
        					}
        				}
        			}

              break;

           case 'm':

              $info = "(1 month report, ".substr($date, 3).")";

              $start = date('Ym', strtotime($dateFormat));
              $end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1];
              //$filename = 'DetailReportByHost_'.$dateFile."_".$username;

              $first_date = '01-'.$expDate[1].'-'.$expDate[2];
              $first_date = date('Ym01', strtotime($first_date));
              $last_date  = date('Ymt', strtotime($first_date));

              //for($i=$first_date; $i<=20170621; $i++) {
              for($i=$first_date; $i<=$last_date; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'Settled_AcquirerReconsiliationReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}

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

              for($i=$eDate; $i<=$sDate; $i++)
        			{
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;
          				foreach($merchant as $key => $value)
          				{
          					$merchantm = $value;

        						$filename = 'Settled_AcquirerReconsiliationReport_'.$i.'_'.$merchantm."_".$acquirera;
        						//$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

        						$fullFileName = $filename.$extFile;
        						$fullPath = $dir.$filename.$extFile;

        						if (file_exists($fullPath))
        						{
        						  array_push($files, $fullFileName);
        						}
        					}
        				}
              }

              break;

          default:
              # code...
              break;
      }
      $arrselected = $files;

      // Sort in ascending order - this is default
      //$a = scandir($dir);

      $a = array_diff(scandir($dir), array('.', '..'));
      $b = array_diff(scandir($dir), $arrselected);
      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $partValue = explode('_', $value);
        $reportType = $partValue[0].'_'.$partValue[1];

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
        if($reportType == 'Settled_AcquirerReconsiliationReport')
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
