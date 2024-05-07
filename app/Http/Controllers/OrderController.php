<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller{

    public function __construct()
    {

    }

    public function create(Request $request){
            $url = 'http://one-dollar-ecommerce-api.kk-lotto.com:8080/api/forders/create';

            $result =  Http::post($url, $request->all());

            $response = json_decode($result);
            if(!empty($response['payment_link'])){
                return redirect(($response['payment_link']));
            }
    }
    public function paginate(Request $request){
            $url = 'http://one-dollar-ecommerce-api.kk-lotto.com:8080/api/forders/paginate';
            return Http::get($url, $request->all());
    }


}
