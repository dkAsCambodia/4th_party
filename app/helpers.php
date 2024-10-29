<?php

use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;


use App\Models\PaymentDetail;
use App\Models\SettleRequest;
use App\Models\Merchant;
use App\Models\Agent;
use Carbon\Carbon;


function getAuthPreferenceTimezone($date)
{
    $timezone = \App\Models\Timezone::where('id', auth()->user()->timezone_id)->value('timezone');

    return \Carbon\Carbon::parse($date)->setTimezone($timezone)->format('Y-m-d H:i:s');
}

// export data to Excel
function exportExcel($data, $date, $type)
{
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '4000M');
    try {
        $spreadSheet = new Spreadsheet;
        $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
        $spreadSheet->getActiveSheet()->fromArray($data);
        $Excel_writer = new Xls($spreadSheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$type.'-report-within-'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $Excel_writer->save('php://output');
        exit();
    } catch (Exception $e) {
        return;
    }
}


function getTodayTransactionCount()
{
    $todayDepositCount = 0;
    $todayWithdrawCount = 0;
    // return Auth::User()->merchant_id;
    if(Auth::User()->role_name == 'Merchant'){
        $merchant=Merchant::where('id', Auth::User()->merchant_id)->first();
        $todayDepositCount = PaymentDetail::where('merchant_code', $merchant->merchant_code)->whereDate('created_at', Carbon::today())->count();
        $todayWithdrawCount = SettleRequest::where('merchant_code', $merchant->merchant_code)->whereDate('created_at', Carbon::today())->count();
    
    }elseif(Auth::User()->role_name == 'Admin'){
        $todayDepositCount = PaymentDetail::whereDate('created_at', Carbon::today())->count();
        $todayWithdrawCount = SettleRequest::whereDate('created_at', Carbon::today())->count();
    }elseif(Auth::User()->role_name == 'Agent'){
        $todayDepositCount = PaymentDetail::where('agent_id', Auth::User()->agent_id)->whereDate('created_at', Carbon::today())->count();
        $todayWithdrawCount = SettleRequest::where('agent_id', Auth::User()->agent_id)->whereDate('created_at', Carbon::today())->count();
    }else{
        
    }

    $data = [
        "todayDepositCount" => $todayDepositCount,
        "todayWithdrawCount" => $todayWithdrawCount,
    ];
    return $data;
}
