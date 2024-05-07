<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentpendingtofailCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pendingtofail:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $paymentDetails = DB::table('payment_details')->whereIn('payment_status', ['pending', '0', '1'])->get();
        
        foreach ($paymentDetails as $paymentDetail) {
        
                $currentDateTime = Carbon::now();
                $paymentDateTime = Carbon::parse($paymentDetail->created_at);
                $mins            = $currentDateTime->diffInMinutes($paymentDateTime, true);
                if($mins >= 10){
                    $data = [];
                   
                    
                    if($paymentDetail->payment_status=='pending'){
                        $data['payment_status'] = 'fail';
                        $data['merchant_settle_status'] = 'cancel';
                        $data['agent_settle_status'] = 'cancel';
                    }elseif($paymentDetail->payment_status == '1'){
                        $data['payment_status'] = 'success';
                    }else{
                        $data['payment_status'] = 'fail';
                        $data['merchant_settle_status'] = 'cancel';
                        $data['agent_settle_status'] = 'cancel';  
                    }

                    DB::table('payment_details')
                                      ->where('id', $paymentDetail->id)
                                      ->update($data);
                }
              
            

            }
        return Command::SUCCESS;
    }
}
