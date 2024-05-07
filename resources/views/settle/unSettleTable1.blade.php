@extends('layouts.master')
@section('content')

<style>
    .flex.justify-between.flex-1.sm\:hidden {
        display: none;
    }

    img,
    svg {
        vertical-align: middle;
    }

    table.dataTable tbody td {
        /* padding: 0px 15px !important; */
    }
</style>
{{-- message --}}
{!! Toastr::message() !!}

<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="col-md-8 breadcrumb">
                <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                <li class="breadcrumb-item"> {{ __('messages.Unsettled') }} </li>
            </ol>
        </div>

        @if ($billing)
        @if ($billing->withdraw_switch == 'closure' || $billing->week_allow_withdrawals == null)
        <div class="row page-titles">
            <ol class="col-md-12 breadcrumb">
                <li class="breadcrumb-item"><b class="text-danger">System settlement is closed and temporarily not
                        allowed</b>
                </li>
            </ol>
        </div>
        @elseif (!in_array(date('l'), $billing->week_allow_withdrawals))
        <div class="row page-titles">
            <ol class="col-md-12 breadcrumb">
                <li class="breadcrumb-item"><b class="text-danger">{{ __('messages.Today is not allowed to settle. System allows day(s) are') }}
                         :
                        @foreach ($billing->week_allow_withdrawals as $week_allow_withdrawals)
                        {{ __('messages.'.$week_allow_withdrawals) }} @if(!$loop->last), @endif
                        @endforeach
                    </b>
                </li>
            </ol>
        </div>

        @else

        <div class="row page-titles">
            <div class="col-12 mb-3">
                <h4 class="card-title my-3">{{ __('messages.Settlement Payment Account') }}</h4>
                <div> &nbsp; </div>
                <div class="mb-2 col-md-2">
					<label class="form-label">{{ __('messages.Select Account') }}</label>
						<select name="merchant_bank" id="merchant_bank"  class="form-select form" aria-label="Select Account">
                                   @foreach ($merchant_bank_details as $bank_details)
                                        <option value="{{ $bank_details->id }}">{{ $bank_details->bank_name}} - {{ $bank_details->account_number}}</option>
                                   @endforeach
  						</select>
 				</div>


                <div class="row">
                         @csrf
                        <div class="col-md-12 row">
                            <div class="col-md-8">
                                <table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
                                 <tr>
                                    <td>
                                        <table>
                                        <tr>
                                             <th style="border: 1px solid #dddddd; text-align: left; padding: 8px; background-color:#707070;color:#fff;width:280px;">{{ __('messages.Account Type') }}
                                              </th>

                                             <th style="border: 1px solid #dddddd; text-align: left; padding: 8px;  width:250px;"  >
                                             <input type="text" class="form-control" id = "account_type" name="account_type" readonly value="<?=$merchant_bank_first_details['account_type'] ;?>">
                                             </th>
                                        </tr>
                                        </table>

                                        <table>
                                                <td style="border: 1px solid #dddddd; text-align: left; padding: 8px;background-color:#707070;color:#fff;width:280px;" >
                                                    {{ __('messages.Account Name') }}
                                                </td>
                                                <td style="border: 1px solid #dddddd; text-align: left; padding: 8px; width:250px;"   >
                                                <input type="text" class="form-control" id = "account_name" name="account_name" readonly  value="<?=$merchant_bank_first_details['account_name'] ;?>">
                                            </td>
                                        </table>
                                        </td>

                                        <td>
                                        <table>
                                          <tr>
                                             <th style="border: 1px solid #dddddd; text-align: left; padding: 8px;background-color:#707070;color:#fff;width:280px;">
                                                {{ __('messages.Bank Name') }}
                                             </th>
                                             <th style="border: 1px solid #dddddd; text-align: left; padding: 8px; width:250px;"  >
                                             <input type="text" class="form-control" id = "bank_name" name="bank_name" readonly  value="<?=$merchant_bank_first_details['bank_name'] ;?>">

                                             </th>
                                         </tr>
                                        </table>

                                        <table>
                                    <tr>

                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px;background-color:#707070;color:#fff;width:280px;">
                                                {{ __('messages.Account Number') }}
                                        </td>
                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px; width:250px;" >
                                          <input type="text" class="form-control" id = "account_number" name="account_number" readonly   value="<?=$merchant_bank_first_details['account_number'] ;?>">

                                        </td>
                                    </tr>
                                        </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                        </div>

                    </form>
                </div>
                    <div id="errorMessage" class="text-danger mt-2"></div>
            </div>
        </div>

         <div style="color:black;" class="row page-titles" >
                <ul class="list-inline right-bar-list mb-0-center">
                          <li class="list-inline-item"> &nbsp;&nbsp;{{ __('messages.Handling fee type') }} :<span id="payment_count"   >
                        <?php
                             if($billing->settlement_fee_type ==='percentage_fee'){
                                echo "Percentage fee" ;
                           }
                           if($billing->settlement_fee_type ==='fixed_fee'){
                            echo "Fixed fee" ;
                             }

                        ?>
                        </span></li> <span class="d-none d-md-inline">|</span>
                        <li class="list-inline-item">{{ __('messages.Handling fee transaction') }} :<span id="order_amount_sum"  >
                        <?php
                           if($billing->settlement_fee_type ==='percentage_fee'){
                                 echo $billing->settlement_fee_ratio. "%";
                           }
                           if($billing->settlement_fee_type ==='fixed_fee'){
                                echo $billing->settlement_fee_ratio;
                             }
                        ?>
                    </span></li>
                </ul>
        </div>

        <div style="color:black; " class="row page-titles">
                         @csrf

                        <div class="col-md-12 row">
                            <div class="col-md-8">
                                     <table >
                                        <tr>
                                            <td>

                                    <li class="list-inline-item">{{ __('messages.Order count') }} :
                                            <span style="text-align: left; padding: 8px;" id="order_count"  style="color:black;">
                                                0
                                            </span>

                                        </span></li>

                                        <span class="d-none d-md-inline">|</span>

                                        <li class="list-inline-item">{{ __('messages.Total settlement amount') }} :
                                            <span style="text-align: left; padding: 8px;" id="subTotal"  style="color:black;">
                                                0.00
                                            </span>

                                        </span></li>

                                        <span class="d-none d-md-inline">|</span>

                                        <li class="list-inline-item">{{ __('messages.Handling fee') }} : <span id="commission">
                                            0.00
                                        </span></li> <span class="d-none d-md-inline">|</span>

                                        <li class="list-inline-item">{{ __('messages.Net settlement amount') }} :
                                            <span style="text-align: left; padding: 8px;"  id="total">
                                            0.00
                                        </span>
                                        </li>
                                    </li>
                                        <button type="button" id="add-request"  data-toggle="modal" data-target="#add_request" class="btn shadow " style="background-color:#3cb371 ;color:#fff;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size:14px;" disabled>{{ __('messages.Submit Request') }}</button> </li>
                                        </ul>
                                </li>

                                    </td>

                                        </tr>
                                    </table>

                            </div>

                        </div>

                </div>


        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example2" class="table table-striped" style="width:100%" >
                                <thead>
                                    <tr style="background-color:#707070;">
                                    <th style="color:#fff;">{{ __('messages.Created Time') }}</th>
                                    <th style="color:#fff;">{{ __('messages.Transaction ID') }}</th>
                                        <th style="color:#fff;">{{ __('messages.Merchant Track No') }}</th>
                                        <th style="color:#fff;">{{ __('messages.Currency') }}</th>
                                        <th style="color:#fff;">{{ __('messages.Amount') }}</th>
                                        <th style="color:#fff;">{{ __('messages.Rate') }}</th>
                                        <th style="color:#fff;">{{ __('messages.Net Amount') }}</th>
                                        <th style="color:#fff;"><input id="select_all" type="checkbox"> </th>
                                    </tr>
                                </thead>
                                <tbody id="dataTbody">
                                    @foreach ($payment_table as $items)
                                    <tr>
                                        <td class="created_at">{{ $items->created_at }}</td>
                                        <input type="hidden" value="{{ $items->id }}" name="id">
                                        <td class="transaction_id">&nbsp;&nbsp;{{ $items->fourth_party_transection }}</td>
                                        <td class="transaction_id">&nbsp;&nbsp;{{ $items->transaction_id }}</td>
                                        <td class="Currency">&nbsp;&nbsp;&nbsp;{{ $items->Currency }}</td>
                                        <td class="amount" data-value="{{$items->amount}}">&nbsp;&nbsp;{{ number_format($items->amount,2) }}</td>
                                        <td class="Currency">
                                        &nbsp;&nbsp;{{ number_format($items->merchant_commission,2) }}</td>
                                        <td class="Currency">&nbsp;&nbsp;
                                            @php
                                            $realTotal = ($items->amount * $items->merchant_commission) / 100;
                                            @endphp
                                            {{ number_format($items->amount - $realTotal,2) }}
                                        </td>
                                        <td class="">&nbsp;  <input id="withdrawalId{{ $items->id }}" data-itemId="{{ $items->id}}" data-netamount="{{ number_format($items->amount - $realTotal,2) }}" data-transaction="{{ number_format($items->amount) }}" class="checkbox temChk"
                                                onclick="getWithdrawalData({{ $items->id }}, '{{ $items->transaction_id }}', {{ $items->amount - $realTotal }}, {{ $items->amount }})"
                                                type="checkbox">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-5">
                            {!! $payment_table->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @else
        <div class="row page-titles">
            <ol class="col-md-12 breadcrumb">
                <li class="breadcrumb-item"><b class="text-danger">{{ __('messages.System settlement unavailable') }}</b>
                </li>
            </ol>
        </div>
        @endif

    </div>
</div>


<div id="add_request" class="modal custom-modal fade" role="dialog">
<form id="add_request_form" action="{{ route('unsettledRequest/unsettled-merchant') }}" method="POST" id="autoSubmit">
@csrf
    <input type="hidden"  name="subTotal" id="subTotalInput">
    <input type="hidden"  name="commissionInput" id="commissionInput">
    <input type="hidden"  name="totalInput" id="totalInput">
    <input type="hidden"  name="transactionAmount" id="transactionAmount">
    <input type="hidden"  name="khantest" id="khantest" />
    <input type="hidden"  name="paymentId[]" id="paymentIds" />

		<div class="modal-dialog modal-dialog-centered " role="document">
			<div class="modal-content">
				<div class="modal-header">
					<span  style="margin-left:45px;font-size:17px;">{{ __('messages.Request Settlement') }}</span>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

                <div>
                 <span class="modal-title" style="margin-left:150px;font-size:17px;">{{ __('messages.Confirm to request settlement') }} ?</h1>
                 </div>

				<div class="modal-body" style="margin-left:50px;">
 						<div class="row">
                         <table>

							<div >
                            <tr>
								 <td> <span class="form-label">{{ __('messages.Settlement Amount') }} :</span> </td>
                                 <td>
                                <span align="right" style="margin-left:0px;"  id="totalPopUp">
                                                0.00
                                </span>
                            </td>
                            </tr>
                            </div>

                            <div style="display:none" id="transaction_amount">
                                            0.00
                            </div>


							<div >
                            <tr>
								<td>
                                    <span class="form-label">{{ __('messages.Handling Fee') }} : </span>
                                </td>
                                <td>
                                    <span  style="margin-left:0px;" id="comissionPopUp"> 0.00 </span>
                               </td>
                            </tr>
                             </div>


                            <div>
                                <span class="form-label">{{ __('messages.Bank Name') }}</span>
                                <span align="right" style="margin-left:152px;">
                                <input type="text" class="form-control" id = "bank_name" name="bank_name" readonly  value="{{ $merchant_bank_first_details?->bank_name }}">
                                </span>
                                    </td>

                                </div>
                            </tr>

                            <tr>

                            <div>
                                <span class="form-label">{{ __('messages.Account Name') }}</span>
                                <span align="right" style="margin-left:130px;" >
                                <input type="text" class="form-control" id = "account_name" name="account_name" readonly  value="{{ $merchant_bank_first_details?->account_name }}">
                                </span>
                            </td>
                                </div>
                            </tr>

                            <tr>
                            <div>
                                <span class="form-label">{{ __('messages.Account Number') }}</span>
                                <span align="right" style="margin-left:115px;">
                                <input type="text" class="form-control" id = "account_name" name="account_number" readonly  value="{{ $merchant_bank_first_details?->account_number }}">
                                </span>

                                </div>
                            </tr>
                            </table>
         				</div>
                             <div> &nbsp; </div>
                                     <div class="row">
                                          <div class="col-2"> &nbsp;
 											</div>
											<div class="col-3">
			            <button type="submit" name="myButton" class="btn" style="background-color:#3cb371 ;color:#fff;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size:10px;">{{ __('messages.CONFIRM') }}</button>
											</div>
											<div class="col-6">
													<a href="javascript:void(0);" data-dismiss="modal"
																			class="btn" style="background-color:#808080 ;color:#fff;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size:10px;">{{ __('messages.CLOSE') }}</a>
											</div>
                                    </div>
					</form>
				</div>
			</div>
		</div>
 	</div>

@section('script')
    <!-- Bootstrap Core JS -->
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>

    <script>
        $("#autoSubmit").on('submit', function(e){
            e.preventDefault();
            $.ajax({
                url: "{{ route('unsettledRequest/unsettled-merchant') }}",
                type: 'POST',
                data: $('#autoSubmit').serialize(),
                success: function(data) {
                    if ($.isEmptyObject(data.error)) {
                        location.reload();
                    } else {
                        $('#errorMessage').html(data.error).show().fadeOut(6000);
                    }
                }
            });
        });
        let storePaymentIds = [];

        function getWithdrawalData(id, transaction_id, amount, transaction_amount) {
            var subTotal = count= 0;
            var transactionAmount = 0;
            var paymentId = $('#withdrawalId' + id).attr('data-itemId');
            var paymentIds = $('#paymentIds').val();

            if(paymentIds)
                storePaymentIds = paymentIds.split(',')

            if (document.getElementById('withdrawalId' + id).checked) {
               var oCount =  parseInt($('#order_count').text());
               oCount +=1;

                subTotal = $("#subTotal").text();
                subTotal = parseFloat(subTotal) + parseFloat(amount);

                transactionAmount = $("#transaction_amount").text();
                transactionAmount = parseFloat(transactionAmount) + parseFloat(transaction_amount);

                $("#appenWithdrawal").append('<tr id="forRemove' + id + '"><td>' + transaction_id + '</td><td>' + amount +
                    '</td></tr>');

                $("#autoSubmit").append('<input id="forRemoveInput' + id + '" type="hidden" value="' + id +
                    '" name="paymentId[]">');

                //update payment id
                if(! storePaymentIds.includes(paymentId))
                    storePaymentIds.push(paymentId);
                $('#paymentIds').val(storePaymentIds);

            } else {
                $("#add-request").attr("disabled", true);

                var oCount =  parseInt($('#order_count').text());
                oCount -=1;

                subTotal = $("#subTotal").text();
                subTotal = parseFloat(subTotal) - parseFloat(amount);
                transactionAmount = $("#transaction_amount").text();
                transactionAmount = parseFloat(transactionAmount) - parseFloat(transaction_amount);

                $("#forRemove" + id).remove();
                $("#forRemoveInput" + id).remove();
                var index = storePaymentIds.indexOf(paymentId);
                if (index !== -1)
                    storePaymentIds.splice(index, 1);
                $('#paymentIds').val(storePaymentIds);
             }
            var total = 0;
            var percent = parseInt($("#order_amount_sum").text());

            @if ($billing && $billing->settlement_fee_type == 'percentage_fee')
                var realTotal = (subTotal *percent) / 100;
                total = parseFloat(subTotal) - parseFloat(realTotal);
             @elseif ($billing && $billing->settlement_fee_type == 'fixed_fee')
                total = parseFloat(subTotal) - parseFloat(percent);
            @endif

            if (total < 0) {
                total = 0;
            }

            $("#subTotal").text(subTotal.toFixed(2));
            $("#total").text(total.toFixed(2));
            $("#commission").text(realTotal);
            $("#commissionInput").val(realTotal);
            $("#comissionPopUp").text(realTotal);
            $("#subTotalInput").val(subTotal.toFixed(2));
            $("#totalInput").val(total.toFixed(2));
            $("#transaction_amount").text(transactionAmount.toFixed(2));
            $("#transactionAmount").val(transactionAmount.toFixed(2));
            $("#totalPopUp").text(total.toFixed(2));
            $("#subtotalPopup").text(subTotal.toFixed(2));
            $('#order_count').text(oCount);

            if(subTotal > 0){
            $("#add-request").attr("disabled", false);
            }
         }

    $("#select_all").click(function(){
        var status = this.checked;
        //"select all" change
        var arr = [], i = count= 0;
        if($('#select_all').is(":checked")){
            $('.temChk').each(function(){ //iterate all listed checkbox items
                this.checked = status; //change ".checkbox" checked status
                arr.push($(this).attr('data-itemId'));
                $('#paymentIds').val(arr);
                count++;
                $(this).addClass('act');
            });
            $('#paymentIds').val(arr);
            $('#order_count').text(count);

        }
        else{
            $('.act').each(function(){
                this.checked = false;
                arr.push($(this).attr('data-itemId'));
                $(this).removeClass('act');
                count=0;

            });

            }

            if(count > 0){
                $("#add-request").attr("disabled", false);
            }else{
                $("#add-request").attr("disabled", true);

            }


            $('#paymentIds').val(arr);
            $('#order_count').text(count);


            var totalPrice = transactionAmount = data_id = i =  0 ;
            $('.act').each(function(){

            let total1 = parseFloat($(this).attr('data-netamount'));
            totalPrice += parseFloat(total1);
            let data_trans = parseFloat($(this).attr('data-transaction'));
            transactionAmount += parseFloat(data_trans);
            });

            $("#subTotal").text(totalPrice.toFixed(2));

            var total = 0;
            var percent = parseInt($("#order_amount_sum").text());

            @if ($billing && $billing->settlement_fee_type == 'percentage_fee')
                var realTotal = (totalPrice * percent) / 100;
                total = parseFloat(totalPrice) - parseFloat(realTotal);
            @elseif ($billing && $billing->settlement_fee_type == 'fixed_fee')
                total = parseFloat(totalPrice) - parseFloat(percent);
            @endif

            if (total < 0) {
                total = 0;
            }

            $("#commission").text(realTotal);
            $("#comissionPopUp").text(realTotal);
            $("#total").text(total.toFixed(2));
            $("#totalPopUp").text(total.toFixed(2));
            $("#subtotalPopup").text(totalPrice.toFixed(2));
            $("#subTotalInput").val(totalPrice.toFixed(2));
            $("#totalInput").val(total.toFixed(2));
            $("#comissionPopUp").text(realTotal.toFixed(2));
            $("#commissionInput").val(realTotal);
            $("#transactionAmount").val(transactionAmount.toFixed(2));


    });

    $('#merchant_bank').on('change',function(){
    $.ajax({
        url: "list-bank-merchant",
        data: { "value": $("#merchant_bank").val(),'_token': '{{csrf_token()}}'},
        dataType:"json",
        type: "post",
        success: function(data){
           $("input[name='bank_name']").val(data.bank_name);
 		   $("input[name='account_name']").val(data.account_name);
		   $("input[name='account_number1']").val(data.account_number);
           $("input[name='account_type']").val(data.account_type);
          }
    });
});
    </script>
@endsection
@endsection
