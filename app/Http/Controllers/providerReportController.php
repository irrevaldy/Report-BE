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

class providerReportController extends Controller
{
  public function listDetailReportFilteredFtp(Request $request)
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

      $files = array();

      switch ($range)
      {
        case 'd':

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

                  $ftpdir = '//generate//';

                  $exists = Storage::disk('tms_ftp')->exists($ftpdir.$fullFileName);

      						if($exists)
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

      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $dirpath = Storage::disk('tms_ftp')->getDriver()->getAdapter()->applyPathPrefix('//generate//'.$value);
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;

        $datemodified = Storage::disk('tms_ftp')->lastmodified('//generate//'.$value);
        $goodi['datemodified'] = date ("d F Y H:i:s", $datemodified);

        $size = Storage::disk('tms_ftp')->size('//generate//'.$value);

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

  public function listReconReportFilteredFtp(Request $request)
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

      $files = array();

      switch ($range)
      {
        case 'd':

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
          for($i=$first_date; $i<=$last_date; $i++)
      		{
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
              //$filename = 'DetailReportByHost_'.$eDate.'_'.$sDate."_".$branch;

              for($i=$eDate; $i<=$sDate; $i++)
			        {
      					foreach($merchant as $key => $value)
      					{
      						$filename = 'ReconsiliationReport_'.$i.'_'.$branch."_".$value;
      						//$filename = 'ReconsiliationReport_'.$i.'_'.$username;
      						$fullFileName = $filename.$extFile;
      						$fullPath = $dir.$filename.$extFile;

                  $ftpdir = '//generate//';

                  $exists = Storage::disk('tms_ftp')->exists($ftpdir.$fullFileName);

      						if($exists)
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

      $num = 0;
      $arrgoodi = array();

      foreach($arrselected as $key => $value)
      {
        $dirpath = Storage::disk('tms_ftp')->getDriver()->getAdapter()->applyPathPrefix('//generate//'.$value);
        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;

        $datemodified = Storage::disk('tms_ftp')->lastmodified('//generate//'.$value);
        $goodi['datemodified'] = date ("d F Y H:i:s", $datemodified);

        $size = Storage::disk('tms_ftp')->size('//generate//'.$value);

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

  public function zipListReportFtp(Request $request)
  {
    try
    {
      $checkedA = $request->checkedA;
       //$checkedA = ["DetailReportByHost_20180629_0000_1.csv","DetailReportByHost_20180629_6789_1.csv","DetailReportByHost_20180629_A003_1.csv","DetailReportByHost_20180629_AllBranch_1.csv","DetailReportByHost_20180629_BGD_1.csv","DetailReportByHost_20180629_G001_1.csv","DetailReportByHost_20180629_TEST_1.csv","DetailReportByHost_20180630_0000_1.csv","DetailReportByHost_20180630_6789_1.csv","DetailReportByHost_20180630_A003_1.csv"]  ;

       $path2 = '/generate';

       $zipname = time().'AllReport.zip';
       $public_dir = storage_path("app");

       $zip = new ZipArchive;

       if (file_exists($public_dir.$zipname)) {
           $zip->open($public_dir . '/' . $zipname, ZIPARCHIVE::OVERWRITE );
       } else {
           $zip->open($public_dir . '/' . $zipname, ZIPARCHIVE::CREATE );
       }

       $filecontent = Storage::disk('tms_ftp')->files($path2);

       foreach($checkedA as $key => $fileName)
       {
         $filecontent = Storage::disk('tms_ftp')->get('//generate//'.$fileName);

         Storage::disk('local')->put($fileName, $filecontent);

         $zip->addFile($public_dir.'/'.$fileName, $fileName);
       }

       $zip->close();

       foreach($checkedA as $key => $fileName)
       {
         Storage::disk('local')->delete($fileName);
       }

       $header = array(
                    'Content-Type' => 'application/octet-stream',
                );
       return response()->download(storage_path("app/".$zipname), $zipname, $header)->deleteFileAfterSend(true);

    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function listByCorporateFiltered(Request $request)
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

      /*$data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }*/

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
      //$dateFormat = $expDate[2].$expDate[1].$expDate[0];

      /*if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }*/

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

          case 'm':

            $expDate = explode('/', $date);
            //$dateFormat = date('Ymd', strtotime($date));
            $yearMonth = $expDate[2].$expDate[1];

            $filename = 'MonthlyCorporateRevenueReport_'.$yearMonth;

            $fullFileName = $filename.$extFile;
            $fullPath = $dir.$filename.$extFile;

            if (file_exists($fullPath))
            {
              array_push($files, $fullFileName);
            }

             break;

         case 'y':

           $expDate = explode('/', $date);
           //$dateFormat = date('Ymd', strtotime($date));
           //$yearMonth = $expDate[2].$expDate[1];

           $filename = 'YearlyCorporateRevenueReport_'.$date;

           $fullFileName = $filename.$extFile;
           $fullPath = $dir.$filename.$extFile;

           if (file_exists($fullPath))
           {
             array_push($files, $fullFileName);
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
        if($reportType == 'MonthlyCorporateRevenueReport' || $reportType == 'YearlyCorporateRevenueReport')
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

  public function listByAcquirerFiltered(Request $request)
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

      /*$data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $acquirer = array();
      for($a = 0; $a < count($data); $a++)
      {
        $acquirer[$a] = $data[$a]['FNAME'];
      }*/

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
      //$dateFormat = $expDate[2].$expDate[1].$expDate[0];

      /*if($range == 'w' )
      {
          $endPoint = date('Ymd', strtotime('-7 days '.$dateFormat));
      }
      else
      {
          $endPoint = $dateFormat;
      }*/

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

          case 'm':

            $expDate = explode('/', $date);
            //$dateFormat = date('Ymd', strtotime($date));
            $yearMonth = $expDate[2].$expDate[1];

            $filename = 'MonthlyAcquirerRevenueReport_'.$yearMonth;

            $fullFileName = $filename.$extFile;
            $fullPath = $dir.$filename.$extFile;

            if (file_exists($fullPath))
            {
              array_push($files, $fullFileName);
            }

             break;

         case 'y':

           $expDate = explode('/', $date);
           //$dateFormat = date('Ymd', strtotime($date));
           //$yearMonth = $expDate[2].$expDate[1];

           $filename = 'YearlyAcquirerRevenueReport_'.$date;

           $fullFileName = $filename.$extFile;
           $fullPath = $dir.$filename.$extFile;

           if (file_exists($fullPath))
           {
             array_push($files, $fullFileName);
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
        if($reportType == 'MonthlyAcquirerRevenueReport' || $reportType == 'YearlyAcquirerRevenueReport')
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
