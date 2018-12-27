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

class ftpFileController extends Controller
{
  public function ftpFileFiltered(Request $request)
  {
    try
    {
      //$dir = "C://generate/";

      //$a = array_diff(scandir($dir), array('.', '..'));
    //  $b = array_diff(scandir($dir), $arrselected);
      $path2 = '/A920_Aslan_Citibank/1.0.0.0';

      //return $zombie;
      $filecontent = Storage::disk('tms_ftp')->files($path2);

      $num = 0;
      $arrgoodi = array();

      foreach($filecontent as $key => $fullvalue)
      {

        $fullvalueExplode = explode('/', $fullvalue);
        $value = $fullvalueExplode[2];
        $dirpath = Storage::disk('tms_ftp')->getDriver()->getAdapter()->applyPathPrefix($fullvalue);

        $partValue = explode('_', $value);
        $reportType = $partValue[0];

        $goodi['number'] = $num;
        $goodi['val'] = $value;
        /*$filename = env('FTP_HOST').'/'.$dirpath;
        //$goodi['datecreated'] = date ("d F Y H:i:s", filectime($filename));
        $filemtime = filemtime($filename);
        $filemtimez = strtotime("+420 minutes", $filemtime);

        $goodi['datemodified'] = date ("d F Y H:i:s", $filemtimez);
*/
        $datemodified = Storage::disk('tms_ftp')->lastmodified($fullvalue);
        $goodi['datemodified'] = date ("d F Y H:i:s", $datemodified);
        $size = Storage::disk('tms_ftp')->size($fullvalue);


        $decimals = 2;
        $sz = 'BKMGTP';
        $factor = floor((strlen($size) - 1) / 3);
        $human_filesize = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];

        $goodi['size'] = $human_filesize."B";
        $arrgoodi[$num] = $goodi;
        $num++;
        $filename = "";
      }

      $res['success'] = true;
      $res['total'] = count($fullvalue);
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

  public function downloadFile(Request $request)
  {
    $zombie = 0;
    $exists = Storage::disk('tms_ftp')->exists('/A920_Aslan_Citibank/1.0.0.0/citibank_ico.png');
    //if(Storage::disk('tms_ftp')->exists('index-.html'))
    if($exists)
    {
      $zombie = 1;
    }

    $path = '/A920_Aslan_Citibank/1.0.0.0/citibank_ico.png';
    $path2 = '/A920_Aslan_Citibank/1.0.0.0';

    $path3 = '/project/'.$nama.'/'.$nama_file;

    //return $zombie;
    $filecontent = Storage::disk('tms_ftp')->files($path2);

    $file[] = $filecontent[0];

    return $file;



    $filecontent = Storage::disk('tms_ftp')->get($path);

    Storage::disk('local')->put('citibank_ico.png', $filecontent);

    $fileName = 'citibank_ico.png';

    //$filecontent = $ftp->get($file); // read file content
         // download file.
    /*     return Response::make($filecontent, '200', array(
              'Content-Type' => 'application/octet-stream',
              'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
          ));*/

    //$checkedA = $request->checkedA;
     //$checkedA = ["DetailReportByHost_20180629_0000_1.csv","DetailReportByHost_20180629_6789_1.csv","DetailReportByHost_20180629_A003_1.csv","DetailReportByHost_20180629_AllBranch_1.csv","DetailReportByHost_20180629_BGD_1.csv","DetailReportByHost_20180629_G001_1.csv","DetailReportByHost_20180629_TEST_1.csv","DetailReportByHost_20180630_0000_1.csv","DetailReportByHost_20180630_6789_1.csv","DetailReportByHost_20180630_A003_1.csv"]  ;

    //$dir = "C://generate/";

    $zipname = time().'AllReport.zip';
    $public_dir = storage_path("app");
    //$path = '/A920_Aslan_Citibank/1.0.0.0/citibank_ico.png';
    //return $zombie;
    //$filecontent = Storage::disk('tms_ftp')->url($path);

    $zip = new ZipArchive;

    if (file_exists($public_dir.$zipname)) {
        $zip->open($public_dir . '/' . $zipname, ZIPARCHIVE::OVERWRITE );
    } else {
        $zip->open($public_dir . '/' . $zipname, ZIPARCHIVE::CREATE );
    }

    $zip->addFile($public_dir.'/'.$fileName, $fileName);

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
    return response()->download(storage_path("app/".$zipname), $zipname, $header);

  }
}



?>
