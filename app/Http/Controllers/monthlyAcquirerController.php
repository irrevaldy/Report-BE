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

class monthlyAcquirerController extends Controller
{
  public function listMonthlyByMerchantFiltered(Request $request)
  {
    try
    {

      //$branch = $request->branch;
      $range = $request->range;
      $date = $request->date;
      $username = $request->username;
      //$merchant = "1";

      /*$data = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $merchant = array();
      $merchant[0] = 'AllMerchant';
      for($a = 0; $a < count($data); $a++)
      {
        $merchant[$a+1] = $data[$a]['value'];
      }*/
      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }

      $arrselected = array();
      $countas = 0;

      /*if($branch == 'All Branch')
      {
        $branch = 'AllBranch';
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

              $filename = 'InactiveEDCReport_'.$dateFile;
              //$filename = 'ReconsiliationReport_'.$dateFile."_".$username;

              $fullFileName = $filename.$extFile;
              $fullPath = $dir.$filename.$extFile;

              if (file_exists($fullPath))
              {
                array_push($files, $fullFileName);
              }

              break;

           case 'm':

              //$info = "(1 month report, ".substr($date, 3).")";

              //$start = date('Ym', strtotime($dateFormat));
              //$end = $start;

              $expDate = explode('/', $date);
                  //$dateFormat = date('Ymd', strtotime($date));
              $dateFile = $expDate[2].$expDate[1];
              //$filename = 'InactiveEDCReport_'.$dateFile;

              $first_date = '01-'.$expDate[1].'-'.$expDate[2];
              $first_date = date('Ym01', strtotime($first_date));
              $last_date  = date('Ymt', strtotime($first_date));

              //for($i=$first_date; $i<=20170621; $i++) {
              for($i=$first_date; $i<=$last_date; $i++)
              {
                foreach($acquirer as $key => $value)
          			{
          				$acquirera = $value;

                  $filename = 'AcquirerMonthlyRevenueByMerchant_'.$i.'_'.$acquirera;
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

          case 'w':
              $dateN = date('d/m/Y', strtotime('-6 days '.$dateFormat));

              $sDate = explode('/', $date);
              $sDate = $sDate[2].$sDate[1].$sDate[0];

              $eDate = explode('/', $dateN);
              $eDate = $eDate[2].$eDate[1].$eDate[0];
              //$filename = 'DetailReportByHost_'.$eDate.'_'.$sDate."_".$branch;

              for($i=$eDate; $i<=$sDate; $i++)
              {
                $filename = 'InactiveEDCReport_'.$i;
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
        if($reportType == 'AcquirerMonthlyRevenueByMerchant')
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

?>
