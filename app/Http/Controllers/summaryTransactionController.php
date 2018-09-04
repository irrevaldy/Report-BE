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

class summaryTransactionController extends Controller
{
   	public function __construct(Request $request){

    }

    public function summaryTransaction(Request $request) {

		try{

			$bankCode = $request->bank_code;
			$cardType = $request->card_type;
			$trxType = $request->transaction_type;
			$corpId = $request->corporate;
			$status = $request->statusa;
			$month = $request->month;
			$respCode = $request->specifiedrc;

			$data = DB::select("[spVDWH_GetSummaryTransactionData] '$bankCode', '$cardType', '$trxType', '$corpId',
															'$status', '$month', '$respCode' ");

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

	public function summaryResponseCode(Request $request) {

		try{

			$bankCode = $request->bank_code_rc;
			$trxType = $request->trx_type_rc;
			$corpId = $request->corp_id_rc;
			$month = $request->month_rc;

			$data = DB::select("[spVDWH_getSummaryResponseCodeData] '$bankCode', '$trxType', '$corpId', '$month' ");

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

}
