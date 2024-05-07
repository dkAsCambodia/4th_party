<?php

use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

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
