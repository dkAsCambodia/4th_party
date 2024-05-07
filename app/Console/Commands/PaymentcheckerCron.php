<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentcheckerCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:cron';

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
        $paymentDetails = DB::table('payment_details')->where('payment_status', '=', 'pending')->get();
        // URL
       
      
        foreach ($paymentDetails as $paymentDetail) {
        
                    $apiURL = 'https://admin.lovrenstar.com/admin/payment/checkpayment?reference_id='.$paymentDetail->transaction_id;

                    // POST Data
                    $postInput = [
                        'reference_id' => $paymentDetail->transaction_id
                        
                    ];
            
                    // Headers
                    $headers = [
                        //...
                    ];
            
                    //$response = Http::withHeaders($headers)->post($apiURL, $postInput);
                    $response = Http::withHeaders($headers)->get($apiURL);
            
                    $statusCode = $response->status();
                    $responseBody = json_decode($response->getBody(), true);
                
                            if($statusCode == 200){
                                
                            if(!empty($responseBody['data'])){
                            
                                if($responseBody['data'][0]['order_status'] != 'PENDING'){
                                
                                    
                                $data = [];
                                $data['order_id'] = $responseBody['data'][0]['order_id'];
                                $data['order_date'] =  $responseBody['data'][0]['order_date'];
                                $data['order_status'] =  $responseBody['data'][0]['order_status'];
                                
                                $respData = json_decode($responseBody['data'][0]['response_data'], true);
                                if(!empty( $respData)){
                                    $data['Currency'] =  $respData['Currency'];
                                    $data['TransId'] =   $respData['TransId'];
                                    $data['ErrDesc'] =  $respData['ErrDesc'];
                                   // $data['payment_status'] =  $respData['Status'];
                                    if($respData['Status']=='1'){
                                        $data['merchant_settle_status'] =  'unsettled';
                                        $data['agent_settle_status'] =  'unsettled';
                                        $data['payment_status'] =  'success';
                                    }else{
                                        $data['merchant_settle_status'] =  'cancel';
                                        $data['agent_settle_status'] =  'cancel';
                                        $data['payment_status'] = 'fail';
                                    }
                                    
                                }
                                DB::table('payment_details')
                                        ->where('transaction_id', $paymentDetail->transaction_id)
                                        ->update($data);
                                //dd($data);
                                }
                            }
                            }

       
        }
        
        return Command::SUCCESS;
    }
}
