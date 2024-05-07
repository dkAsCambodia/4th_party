<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>


    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #f9ebdc;

        }

        .input_style {
            margin: 15px 0;
            padding: 15px 10px;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            outline: none;
            border: 1px solid #bbb;
            border-radius: 20px;
            display: inline-block;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            -webkit-transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -ms-transition: 0.2s ease all;
            -o-transition: 0.2s ease all;
            transition: 0.2s ease all;
        }


        .container {
            height: 96vh;
            display: flex;
            justify-content: center;
            align-items: center
        }

        .box {
            width: 50%;
            height: 60vh;
            box-shadow: -1px 0px 5px 6px rgba(184, 184, 184, 0.28);
            -webkit-box-shadow: -1px 0px 5px 6px rgba(184, 184, 184, 0.28);
            -moz-box-shadow: -1px 0px 5px 6px rgba(184, 184, 184, 0.28);
            padding: 26px;
            border-radius: 16px;
        }

        form {
            /* margin: 10% auto 0 auto; */
            /* padding: 30px; */
            width: 400px;
            height: auto;
            overflow: hidden;
            /* background: white; */
            border-radius: 10px;
        }

        form label {
            font-size: 14px;
            color: darkgray;
            cursor: pointer;
        }

        form label,
        form input {
            float: left;
            clear: both;
        }

        form input {
            margin: 15px 0;
            padding: 15px 10px;
            width: 100%;
            outline: none;
            border: 1px solid #bbb;
            border-radius: 20px;
            display: inline-block;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            -webkit-transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -ms-transition: 0.2s ease all;
            -o-transition: 0.2s ease all;
            transition: 0.2s ease all;
        }



        input[type=submit] {
            padding: 15px 50px;
            width: auto;
            background: #1abc9c;
            border: none;
            color: white;
            cursor: pointer;
            display: inline-block;
            float: right;
            clear: right;
            -webkit-transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -ms-transition: 0.2s ease all;
            -o-transition: 0.2s ease all;
            transition: 0.2s ease all;
        }

        .btn_custom{
            padding: 6px 10px;
            font-size: 20px;
            width: auto;
            background: #d7c0a9;
            box-shadow: -1px 0px 3px 3px rgba(216, 214, 214, 0.28);
            border: none;
            color: rgb(0, 0, 0);
            cursor: pointer;
            font-weight: bold;
            width: 240px;
            border-radius: 12px;
            margin-right: 16px;
            display: inline-block;
            -webkit-transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -ms-transition: 0.2s ease all;
            -o-transition: 0.2s ease all;
            transition: 0.2s ease all;
            margin-top: 10px;
        }
        .btn_active{
            box-shadow: -1px 2px 11px 5px rgb(0 0 0 / 34%) !important;
            -webkit-box-shadow: -1px 2px 11px 5px rgb(0 0 0 / 34%) !important;
            -moz-box-shadow:-1px 2px 11px 5px rgb(0 0 0 / 34%) !important;
            background: #d98011 !important;
        }

        .btn_active2{
            background: #d98011 !important;
            box-shadow: -1px 2px 11px 5px rgb(0 0 0 / 34%) !important;
            -webkit-box-shadow: -1px 2px 11px 5px rgb(0 0 0 / 34%) !important;
            -moz-box-shadow:-1px 2px 11px 5px rgb(0 0 0 / 34%) !important;
        }


        input[type=submit]:hover {
            opacity: 0.8;
        }

        input[type="submit"]:active {
            opacity: 0.4;
        }

        .forgot,
        .register {
            margin: 10px;
            float: left;
            clear: left;
            display: inline-block;
            color: cornflowerblue;
            text-decoration: none;
        }

        .forgot:hover,
        .register:hover {
            color: darkgray;
        }

        #logo {
            margin: 0 auto;
            width: 200px;
            font-family: 'Lily Script One', cursive;
            font-size: 60px;
            font-weight: bold;
            text-align: center;
            color: lightgray;
            -webkit-transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -ms-transition: 0.2s ease all;
            -o-transition: 0.2s ease all;
            transition: 0.2s ease all;
        }

        #logo:hover {
            color: cornflowerblue;
        }
        .btn_section{
            width: 100%;
            display: block;
        }
        .title_card{
            width: 400px; display: flex;
        }
        .btn_custom_card{
            display: flex;
            font-size: 24px;
            font-weight: bold;
            padding: 14px 30px;
            width: auto;
            background: #463c36;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 12px;
            margin-right: 16px;
            display: inline-block;
            -webkit-transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -ms-transition: 0.2s ease all;
            -o-transition: 0.2s ease all;
            transition: 0.2s ease all;
        }
         @media (max-width: 789px) {
            body{
                overflow: hidden;
            }
            .box {
                width: 90%;
                margin-top: 0px;
                padding-top: 0px;
                box-shadow: none;
            }
            h4{
                margin: 0px;
            }
            h3{
                margin-top: 0px;
            }
            p{
                margin-top: 0px !important;
            }
            .container {
                height: 70vh;
                display: flex;
            }
            .btn_section{
                margin-top: 0px !important;
            }
            .btn_custom_card{
                margin: 2px;
                padding: 8px;
                width: 100px;
                height: 60px;
                font-size: 12px;
            }
            .btn_custom{
                margin: 8px;
                width: 250px;
            }
            .title_card{
                width: 400px; display: flex;
            }
            .input_style{
                width: 200px;
                margin-left: 20px;
            }
            .clickBtn{
                float: left !important;
                padding: 18px;
                width: 300px !important;
                margin-top: 20px !important;
                font-size: 18px;
                margin-left: 2px;
            }
            .w_amount{
                width: 100px !important;

            }
        }

    </style>
</head>

<body class="">
    <div class="">
        <div class="container">
            <div class="box">

                <div class="btn_section">
                {{-- <button class="btn_custom_card " data-name="alipay">
                        <img style="height: 19px;" src="{{ asset('alipay.png') }}"/>
                        <span style="margin-top: -20px;">支付宝</span>
                    </button>
                     <button class="btn_custom_card" data-name="unipay">
                        <img style="height: 19px;" src="{{ asset('unipay.png') }}"/>
                        <span style="margin-top: -20px;">银联</span>
                    </button>
                    <button class="btn_custom_card" data-name="card">
                        <img style="height: 19px;" src="{{ asset('card.png') }}"/>
                        <span style="margin-top: -20px;">信用卡</span>
                    </button> --}}

                </div>
                <input maxlength="50" minlength="1"  type="hidden" class="form-control"
                            name="payment_method"  id="payment_method" value="alipay" />
                <div class="title_card" style="">
                    <p class="w_amount" style="width: 190px;">金额 CNY</p>
                    {{-- <input type="number" id="amount" class="input_style" value="100" /> --}}
                </div>
                <span class="error" style="color: red;"></span>
                <div class="btn_section">

                      @foreach ($currency as $key => $value)

                        @if(empty($value->updated_at))
                      <button class="btn_custom btn_price"  data-method={{$value->methods}} data-id={{'P00'.$value->product_id}}>{{$value->range_low}} - {{$value->range_high}}(${{$value->dollar}})</button>
                      @endif
                      @endforeach
                </div>

                <button class="btn_custom clickBtn" style="float: right; margin-top: 80px;background: green;color: #fff;">提交支付</button>

                <form style="visibility: hidden"  class="" method="get" action="{{URL::to('')}}/api/payment"
                    id="form_id" enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class=" control-label">顾客姓名<span class="mandatory">*</span></label>
                                <input   type="text" class="form-control" name="customer_name" value="testing"  id="customer_name"  />
                            </div>
                            <div class="col-sm-12 form-group">
                                <label class=" control-label">客户ID<span class="mandatory">*</span></label>
                                <input maxlength="50" minlength="1"  type="text" class="form-control"
                                    name="customer_id" required id="customer_id" value="1" />
                            </div>
                            <div class="col-sm-12 form-group d-none">
                                <label class=" control-label">Merchant Code<span class="mandatory">*</span></label>
                                <input maxlength="50" minlength="1" class="form-control"
                                    name="merchant_code" required id="merchant_code" value="kk-lotto"  />

                            </div>
                            <div class="col-sm-12 form-group">
                                <label class=" control-label"> Product id </label>
                                <input  type="text" class="form-control" name="product_id" id="product_id"  value="0" />
                            </div>
                            <div class="col-sm-12 form-group">
                                <label class=" control-label"> Trnsaction id </label>
                                <input  type="text" class="form-control" name="transaction_id" id="transaction_id" value="<?php echo  "A".rand(100000,999999); ?>"  />
                            </div>
                            <div class="col-sm-12 form-group">
                            <label class=" control-label"> Callback URL </label>
                            <input maxlength="50" minlength="1"  class="form-control"
                                name="callback_url" value="{{ url('/payment_status') }}" required />
                            </div>

                            <div class="row mt-2 text-right">
                                <div class="col-sm-12 form-group text-right">
                                    <input type="submit" class="btn btn-primary d-none" name="submit"
                                        id="submitBtn" />

                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
<script type="text/javascript" src="{{ asset('/assets/js/jquery3.min.js') }}"></script>
<script>
    var baseUrl = '{{ url('/') }}';
    $(document).ready(function() {
        $('.error').hide();

        $('.btn_price').click(function(e) {
            $('.btn_price').each(function() {
                $(this).removeClass('btn_active')
            });
            $(this).addClass('btn_active');
            $('#product_id').val($(this).data('id'))
        });

        $('.btn_price').each(function() {
            if($(this).data('method') == $('#payment_method') .val()){
                $(this).show()
            } else {
                $(this).hide()
            }

        });

        // $('#amount').change(function(){
        //     if($('#payment_method').val() == 'alipay') {
        //         if($('#amount').val() == 1){
        //                 $('.error').hide();
        //         } else {
        //             $('.error').show();
        //         }
        //     }
        //     $('.btn_price').each(function() {
        //         $(this).removeClass('btn_active')
        //     });
        //    $('#amount_form').val($('#amount').val());
        // })

        $('.btn_custom_card').click(function(e) {
            $('.btn_custom_card').each(function() {
                $(this).removeClass('btn_active2')
            });
            $('#product_id').val('')
            $(this).addClass('btn_active2')
            $('#payment_method') .val($(this).data('name'))

            $('.btn_price').each(function() {
                if($(this).data('method') == $('#payment_method') .val()){
                    $(this).show()
                } else {
                    $(this).hide()
                }

            });

            // $('#payment_method') .val($(this).data('name'));
            // if($(this).data('name') == 'alipay'){
            //     if($('#amount').val() != 1){
            //         $('.error').show();
            //         $('.error').text('Remark: Alipay testing amount not able to change, only can accept $1.00 - 提示：支付宝测试接口的金额无法更改，测试金额为 $1.00');
            //      }

            // } else {
            //     $('.error').hide();
            // }

        });
        $('.btn_custom_card').each(function() {
            if($(this).data('name') == $('#payment_method') .val()){
                $(this).addClass('btn_active2')
            }
        });

        $('.clickBtn').click(function(){

            // var amount = $('#amount_form').val();
            var productId =  $('#product_id').val();
            // if(amount > 1 && payment_method == 'alipay') {
            //     amount = 1
            //     $('#amount_form').val(1)
            // }
            // if(amount == '' || amount <= 0){
            //     alert('Please Enter your amount');
            //     return false;
            // }
            if(productId < 1){
                alert('Please Select Amount');
                return false;
            }else{
                submitForm();
            }

        });
    });

    function submitForm() {
        $.ajax({
                url: baseUrl + '/payment/store',
                method: 'POST',
                data: $('#form_id').serialize(),
                success: function(response) {
                    $('#submitBtn').click();
                }
            });

    }
</script>
