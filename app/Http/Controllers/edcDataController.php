<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class edcDataController extends Controller
{
  public function __construct(Request $request){

  }

  public function checkSN(Request $request)
  {
    try
    {
      $username = $request->username;

      $data = DB::select("[spVDWH_selectSN_EDC] '$username' ");

      $data = json_encode($data);
      $data = json_decode($data, true);

      if( count($data) > 0)
      {
        $status = 'exist';
      }
      else
      {
        $status = 'not';
      }

      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;
      $res['status'] = $status;


      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getSN(Request $request)
  {
    try
    {
      $username = $request->username;

      $data = DB::select("[spVDWH_selectSN_Merchant] '$username' ");

      $data = json_encode($data);
      $data = json_decode($data, true);


      $res['success'] = true;
      $res['total'] = count($data);
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function deleteSN(Request $request)
  {
    try
    {
      $username = $request->username;

      $data = DB::statement("[spVDWH_deleteSN_EDC] '$username' ");

      if($data)
      {
        $result = 'sukses';
      }
      else
      {
        $result = 'gagal';
      }

      $res['success'] = true;
      $res['status'] = $result;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function uploadEdc(Request $request)
  {
    try
    {
      $corporate = $request->corporate;
      $merchant = $request->merchant;
      $storage_path = $request->storage_path;

      /*
      $data = DB::statement("[spVIDM_deleteSN_EDC] '$username' ");

      if($data)
      {
        $result = 'sukses';
      }
      else

      */

      $objPHPSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($storage_path);
      $worksheet = $objPHPSpreadsheet->setActiveSheetIndex(0);
      $worksheetTitle     = $worksheet->getTitle();
      $highestRow         = $worksheet->getHighestRow(); // e.g. 10
      $highestRow_count = $highestRow -1;
      $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
      $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
      $nrColumns = ord($highestColumn) - 64;

      $midList = array("Serial Number", "TID MDR REGULER", "MID MDR REGULER", "TID MDR POWERBUY 3", "MID MDR POWERBUY 3", "TID MDR POWERBUY 6", "MID MDR POWERBUY 6", "TID MDR POWERBUY 12", "MID MDR POWERBUY 12", "TID MDR PREPAID", "MID MDR PREPAID", "TID BNI", "MID BNI", "TID BRI", "MID BRI", "TID TCASH", "MID TCASH", "TID CIMB", "MID CIMB"); //label for view

      $getcorpname = DB::select("[spVDWH_getCorporatebyID] '$corporate' ");

      $getcorpname = json_encode($getcorpname);
      $getcorpname = json_decode($getcorpname, true);

      $corpName = $getcorpname[0]['CORP_NAME'];

      $getmerchname = DB::select("[spVDWH_getMerchantbyFID] '$merchant' ");

      $getmerchname = json_encode($getmerchname);
      $getmerchname = json_decode($getmerchname, true);

      $merchName = $getmerchname[0]['FMERCHNAME'];

      $gettotalfsn = DB::select("[spVDWH_selectCountSN_MerchID] '$merchant' ");

      $gettotalfsn = json_encode($gettotalfsn);
      $gettotalfsn = json_decode($gettotalfsn, true);

      $totalfsn = $gettotalfsn[0]['total'];

      $headerExcel = array();
      $headerExcel2 = array();
      $indexExcel = array();
      $indexExcel2 = array();
      $bodyExcel = array();
      $bodyExcel2 = array();


      //header row
      $row = 1;
      $col = 0;

      for ($col = 0; $col <= $highestColumnIndex; ++ $col) {
        $cell = $worksheet->getCellByColumnAndRow($col, $row);
        $val = $cell->getValue();
        $headerExcel[] = $val;
      }

      foreach ($midList as $key => $value) {
        if(in_array($value, $headerExcel)) {
          $x = array_search($value, $headerExcel);
          if($x == 0)
          {
            $x = 26;
          }
          $indexExcel[] = $x;
        }
      }

      $row = 1;
      $col = 0;

      $cell = $worksheet->getCellByColumnAndRow($col, $row);
      $sn = $cell->getValue();

      //$cell = $worksheet->getCellByColumnAndRow($col, $row);
      //$sn = $cell->getValue();
      //$dataType = \PhpOffice\PhpSpreadsheet\Cell_DataType::dataTypeForValue($sn);

      foreach ($indexExcel as $key => $value) {
        $cell = $worksheet->getCellByColumnAndRow($value, $row);
        $header = $cell->getValue();
        //$dataType = \PhpOffice\PhpSpreadsheet\Cell_DataType::dataTypeForValue($header);
        $headerExcel2[] = $header;
      }

      $maxCol = count($indexExcel) + 1;

      $error = "";
      $no = 1;
      $errorCounter = 0;

      //body row
      for ($row = 2; $row <= $highestRow; ++ $row) {
        $col = 0;
        $array = array();

        $cell = $worksheet->getCellByColumnAndRow($indexExcel[0], $row);
        $sn = $cell->getValue();

        $datas = DB::select("[spVDWH_selectSN_EDC] '$sn' ");

        $datas = json_encode($datas);
        $datas = json_decode($datas, true);

        if($sn == NULL || $sn == '')
        {
          $sn_status = 'SN is empty. Broken Data.';
          $error = "ya";
          $errorCounter = $errorCounter + 1;
        }
        elseif(count($datas) > 0)
        {
          $sn_status = 'SN has been registered';
          $error = "ya";
          $errorCounter = $errorCounter + 1;
        }
        else
        {
          $sn_status = 'OK';
        }

        foreach ($indexExcel as $key => $value) {
          $cell = $worksheet->getCellByColumnAndRow($value, $row);
          $mid = $cell->getValue();
          if($mid == null)
          {
            $mid = '';
          }
          //$dataType = PHPExcel_Cell_DataType::dataTypeForValue($mid);
          $array[$value] = $mid;
          $array['status'] = $sn_status;
          $array['error'] = $error;
          //array_push($array , "status" => $sn);
        }

      $bodyExcel[] = $array;
    }

    $data = array("corporate" => $corpName,
                  "merchant"=> $merchName,
                  "merchId" => $merchant,
                  "highestRow_count" => $highestRow_count,
                  "total_fsn" => $totalfsn,
                "storage_path" => $storage_path,
              "error" => $error,
            "errorCounter" => $errorCounter);

      $res['success'] = true;
      $res['result'] = $data;
      $res['header'] = $headerExcel;
      $res['header2'] = $headerExcel2;
      //$res['index'] = $indexExcel;
      $res['body'] = $bodyExcel;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function activateEdc(Request $request)
  {
    date_default_timezone_set('Asia/Jakarta');
    $now = date("Ymdhis");
    $merchant = $request->merchant;
    $storage_path = $request->storage_path;
    $user_id = $request->user_id;
    $username = $request->username;
    $name = $request->name;

    $midList = array("Serial Number", "TID MDR REGULER", "MID MDR REGULER", "TID MDR POWERBUY 3", "MID MDR POWERBUY 3", "TID MDR POWERBUY 6", "MID MDR POWERBUY 6", "TID MDR POWERBUY 12", "MID MDR POWERBUY 12", "TID MDR PREPAID", "MID MDR PREPAID", "TID BNI", "MID BNI", "TID BRI", "MID BRI", "TID TCASH", "MID TCASH", "TID CIMB", "MID CIMB"); //label for view

    $objPHPSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($storage_path);
    $worksheet = $objPHPSpreadsheet->setActiveSheetIndex(0);
    $worksheetTitle     = $worksheet->getTitle();
    $highestRow         = $worksheet->getHighestRow(); // e.g. 10
    $highestRow_count = $highestRow -1;
    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
    $nrColumns = ord($highestColumn) - 64;

    $headerExcel = array();
    $indexExcel = array();

    $row = 1;
    $col = 0;

    for ($col = 0; $col <= $highestColumnIndex; ++ $col) {
      $cell = $worksheet->getCellByColumnAndRow($col, $row);
      $val = $cell->getValue();
      $headerExcel[] = $val;
    }

    foreach ($midList as $key => $value) {
      if(in_array($value, $headerExcel)) {
        $x = array_search($value, $headerExcel);
        if($x == 0)
        {
          $x = 26;
        }
        $indexExcel[] = $x;
      }
    }

    $maxKey = sizeof($indexExcel) - 1;

    $no = 1;
    for ($row = 2; $row <= $highestRow; ++ $row)
    {
      $cell = $worksheet->getCellByColumnAndRow($col, $row);
      $sn = $cell->getValue();

      $midFull = "";
      $midCount = 0;
      foreach ($indexExcel as $key => $value)
      {
        $cell = $worksheet->getCellByColumnAndRow($value, $row);
        $val = $cell->getValue();

        if($key == 0)
        {
          $sn = $val;
        }
        else
        {
          $midCount += 1;
          if($midCount == 1)
          {
            //tid
            $tid = $val;
            while(strlen($tid) < 8)
            {
              $tid = "0".$tid;
            }
          }
          else if($midCount == 2)
          {
            //mid
            $mid = $val;
            while(strlen($mid) < 15)
            {
              $mid = "0".$mid;
            }
            $mid = $tid.$mid;
          }
          //another if
          if($midCount == 2)
          {
            if($key == $maxKey)
            {
              $midFull = $midFull.$mid;
              $midCount = 0;
            }
            else
            {
              $midFull = $midFull.$mid.";";
              $midCount = 0;
            }
          }
        }
      }

      try
      {
        $datas = DB::select("[spVDWH_selectSN_EDC] '$sn' ");

        $datas = json_encode($datas);
        $datas = json_decode($datas, true);

        if($sn == NULL || $sn == '')
        {
          $sn_status = 'SN is empty. Broken Data.';
        }
        elseif(count($datas) > 0)
        {
          $queryInsert = DB::statement("[spVDWH_regisEdc] 'update', '$merchant', '$sn', '$midFull'");
        }
        else
        {
          $queryInsert = DB::statement("[spVDWH_regisEdc] 'insert', '$merchant', '$sn', '$midFull'");
        }
        $res['success'] = true;
        $res['result'] = 'Add EDC Success';

        $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '22', '$user_id', '$username', '$name', $now, 'Import SN, filename: $storage_path, merchant ID: $merchant'");

        return response($res);
      }
      catch(QueryException $ex)
      {
        $res['success'] = false;
        $res['result'] = 'Add EDC Failed';

        return response($res);
      }
    }
  }
}
