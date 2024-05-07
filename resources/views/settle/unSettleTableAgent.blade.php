@extends('layouts.master')
    @section('content')
    <style>
        .bg-v-light {
            background: #efefef;
        }
    </style>

    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-8 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ trans('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ trans('messages.Unsettled') }} </li>
                </ol>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ trans('messages.Unsettled') }}</h4>
                        </div>

                        <div class="card-body">
                            @if ($billing)
                                @if ($billing->withdraw_switch == 'closure' || $billing->week_allow_withdrawals == null)
                                    <b class="text-danger">{{ trans('messages.System settlement is closed and temporarily not allowed') }}</b>
                                @elseif (!in_array(date('l'), $billing->week_allow_withdrawals))
                                    <b class="text-danger">
                                        {{ trans('messages.Today is not allowed to settle. System allows day(s) are') }}:
                                        @foreach ($billing->week_allow_withdrawals as $week_allow_withdrawals)
                                            {{ trans('messages.'.$week_allow_withdrawals) }}@if(!$loop->last), @endif
                                        @endforeach
                                    </b>
                                @else
                                    @if (count($agentBanks) > 0)
                                        <div class="mb-2 col-md-2">
                                            <label class="form-label">{{ trans('messages.Select Account') }}</label>
                                            <select name="merchant_bank" id="bankAccountSelect"  class="form-select form" aria-label="Select Account">
                                                @foreach ($agentBanks as $bank)
                                                    <option value="{{ $bank->id }}" data-id="{{ $bank->id }}">{{ $bank->bank_name}} - {{ $bank->account_number}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8">
                                                <table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
                                                    <tr>
                                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px; background-color:#707070;color:#fff;width:280px;">
                                                            {{ trans('messages.Account Type') }}
                                                        </td>

                                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px;  width:250px;"  >
                                                            <span id="displayAccountType">{{ $agentBankFirst?->bank?->account_type }}</span>
                                                        </td>

                                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px;background-color:#707070;color:#fff;width:280px;" >
                                                            {{ trans('messages.Account Name') }}
                                                        </td>

                                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px; width:250px;"   >
                                                            <span id="displayAccountName">{{ $agentBankFirst?->account_name }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #dddddd; text-align: left; padding: 8px;background-color:#707070;color:#fff;width:280px;">
                                                            {{ trans('messages.Bank Name') }}
                                                        </th>

                                                        <th style="border: 1px solid #dddddd; text-align: left; padding: 8px; width:250px;"  >
                                                            <span id="displayBankName">{{ $agentBankFirst?->bank_name }}</span>
                                                        </th>

                                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px;background-color:#707070;color:#fff;width:280px;">
                                                            {{ trans('messages.Account Number') }}
                                                        </td>

                                                        <td style="border: 1px solid #dddddd; text-align: left; padding: 8px; width:250px;" >
                                                            <span id="displayAccountNumber">{{ $agentBankFirst?->account_number }}</span>
                                                        </td>
                                                    </tr>
                                                </table>

                                                <div class="mb-3 mt-3 p-2 border bg-v-light">
                                                    <ul class="list-inline right-bar-list mb-0 text-black">
                                                        <li class="list-inline-item">{{ __('messages.Handling fee type') }}: {{ $billing->settlement_fee_type == 'percentage_fee' ? trans("messages.Percentage Fee") : trans("messages.Fixed Fee") }}</li><span class="d-none d-md-inline">|</span>
                                                        <li class="list-inline-item">{{ __('messages.Handling fee transaction') }}: {{ $billing->settlement_fee_type == 'percentage_fee' ? $billing->settlement_fee_ratio.'%' : $billing->settlement_fee_ratio }}</li>
                                                    </ul>
                                                </div>

                                                <div class="mb-3 mt-3 p-2 border bg-v-light">
                                                    <ul class="list-inline right-bar-list mb-0 text-black">
                                                        <li class="list-inline-item">{{ __('messages.Order count') }}: <span id="orderCount"></span></li><span class="d-none d-md-inline">|</span>
                                                        <li class="list-inline-item">{{ __('messages.Total settlement amount') }}: <span id="totalSettlement"></span></li><span class="d-none d-md-inline">|</span>
                                                        <li class="list-inline-item">{{ __('messages.Handling fee') }}: <span id="totalHandlingFee"></span></li><span class="d-none d-md-inline">|</span>
                                                        <li class="list-inline-item">{{ __('messages.Net settlement amount') }}: <span id="totalNetSettlement"></span></li>
                                                        @if (count($agentBanks) > 0)
                                                        <li class="list-inline-item float-right">
                                                            <button type="button" id="add-request"  data-toggle="modal" data-target="#confirmSettle" class="btn shadow btn-sm btn-success border-none rounded-1 fw-bold" disabled>{{ __('messages.Submit Request') }}</button>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="col-md-4 border rounded-2">
                                                <table class="table table-borderless table-sm" style="margin-bottom: 0; margin-top: 5px;">
                                                    <tbody>
                                                        <tr>
                                                            <td><span class="form-label">{{ __('messages.Daily withdrawal start time') }}</span></td>
                                                            <td align="right">{{ date('h:i A', strtotime($billing->withdrawal_start_time)) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="form-label">{{ __('messages.Daily withdrawal end time') }}</span></td>
                                                            <td align="right">{{ date('h:i A', strtotime($billing->withdrawal_end_time)) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="form-label">{{ __('messages.Max daily withdraw amount') }}</span></td>
                                                            <td align="right">{{ number_format($billing->max_daily_withdrawals, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="form-label">{{ __('messages.Max per transaction withdraw amount') }}</span></td>
                                                            <td align="right">{{ number_format($billing->single_max_withdrawal, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="form-label">{{ __('messages.Min per transaction withdraw amount') }}</span></td>
                                                            <td align="right">{{ number_format($billing->single_min_withdrawal, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="form-label">{{ __('messages.Daily withdraw count') }}</span></td>
                                                            <td align="right">{{ number_format($billing->daily_withdrawals, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="form-label">{{ __('messages.Day allow to withdraw') }}</span></td>
                                                            <td align="right">
                                                                @foreach ($billing->week_allow_withdrawals as $week_allow_withdrawals)
                                                                    {{ trans('messages.'.$week_allow_withdrawals) }}@if(!$loop->last), @endif
                                                                @endforeach
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-black">
                                            {{ trans('messages.Bank Account not found') }}! {{ trans('messages.Please add bank account') }} <a href="{{ route('Account: View Agent Account') }}" class="text-blue text-decoration-underline">{{ trans('messages.here') }}</a> {{ trans('messages.before requesting settle') }}.
                                        </div>
                                    @endif

                                    @if (count($paymentDetails) > 0)
                                        <div class="table-responsive">
                                            <table class="table" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('messages.Created Time') }}</th>
                                                        <th>{{ __('messages.Transaction ID') }}</th>
                                                        <th>{{ __('messages.Merchant Track No') }}</th>
                                                        <th>{{ __('messages.Currency') }}</th>
                                                        <th style="text-align: right;">{{ __('messages.Amount') }}</th>
                                                        <th style="text-align: right;">{{ __('messages.Rate') }}</th>
                                                        <th style="text-align: right;">{{ __('messages.Net Amount') }}</th>
                                                        <th style="text-align: right;"><input id="selectAll" type="checkbox"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($paymentDetails as $item)
                                                        <tr>
                                                            <td>{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                                                            <td>{{ $item->fourth_party_transection }}</td>
                                                            <td>{{ $item->transaction_id }}</td>
                                                            <td>{{ $item->Currency }}</td>
                                                            <td align="right">{{ number_format($item->amount, 2) }}</td>
                                                            <td align="right">{{ number_format($item->paymentMaps->agent_commission, 2) }}</td>
                                                            <td align="right">{{ number_format((($item->amount * $item->paymentMaps->agent_commission) / 100), 2) }}</td>
                                                            <td align="right"><input name="payment" type="checkbox" id="{{ $item->id }}" value="{{ $item->amount }}" data-netAmount="{{ (($item->amount * $item->paymentMaps->agent_commission) / 100) }}"></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-danger">{{ trans('messages.Unsettled Payment not found') }}</div>
                                    @endif
                                @endif
                            @else
                                <b class="text-danger">{{ trans('messages.System settlement unavailable') }}</b>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal custom-modal fade" id="confirmSettle" role="dialog">
        <div class="modal-dialog modal-dialog-centered" style="min-width: 200px">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Request Settlement') }}</h3>
                        <p>{{ __('messages.Confirm to request settlement') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form id="autoSubmit">
                            @csrf
                            <input type="hidden"  name="amount" id="formAmount">
                            <input type="hidden"  name="commission" value="{{ $billing?->settlement_fee_ratio }}">
                            <input type="hidden"  name="netAmount" id="formNetAmount">
                            <input type="hidden"  name="transactionAmount" id="formTransactionAmount">
                            <input type="hidden"  name="handlingFee" id="formHandlingFee">
                            <input type="hidden"  name="paymentId[]" id="formPaymentId" />
                            <input type="hidden"  name="account_id" id="formAccountId" value="{{ $agentBankFirst?->id }}" />

                            <div class="row">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <td><span class="form-label">{{ __('messages.Total settlement amount') }}</span></td>
                                            <td align="right" id="modalAmount"></td>
                                        </tr>
                                        <tr>
                                            <td><span class="form-label">{{ __('messages.Handling Fee') }}</span></td>
                                            <td align="right" id="modalFee"></td>
                                        </tr>
                                        <tr>
                                            <td><span class="form-label">{{ __('messages.Bank Name') }}</span></td>
                                            <td align="right" id="modalBankName">{{ $agentBankFirst?->bank_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><span class="form-label">{{ __('messages.Account Name') }}</span></td>
                                            <td align="right" id="modalAccountName">{{ $agentBankFirst?->account_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><span class="form-label">{{ __('messages.Account Number') }}</span></td>
                                            <td align="right" id="modalAccountNumber">{{ $agentBankFirst?->account_number }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div id="errorMessage" class="text-danger mt-2"></div>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                <div class="d-inline-flex p-2">
                                    <button type="submit" class="btn btn-primary shadow btn-sm">{{ __('messages.CONFIRM') }}</button>
                                </div>
                                <div class="d-inline-flex p-2">
                                    <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-danger shadow btn-sm">{{ __('messages.CLOSE') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('script')
        <script>
            $(document).ready(function() {
                $('#orderCount').text('0');
                $('#totalSettlement').text('0.00');
                $('#totalNetSettlement').text('0.00');
                $('#totalHandlingFee').text('0.00');
                var $selectAll = $('#selectAll');
                var $table = $('.table');
                var $tdCheckbox = $table.find('tbody input:checkbox');
                var tdCheckboxChecked = 0;

                $selectAll.on('click', function () {
                    $tdCheckbox.prop('checked', this.checked);
                    checkData();
                });

                $tdCheckbox.on('change', function(e){
                    tdCheckboxChecked = $table.find('tbody input:checkbox:checked').length;
                    $selectAll.prop('checked', (tdCheckboxChecked === $tdCheckbox.length));
                    checkData();
                })

                function checkData() {
                    var allIds = $('input[name="payment"]:checked').toArray().map(x => x.id);
                    var allAmount = $('input[name="payment"]:checked').toArray().map(x => x.value);
                    var allNetAmount = $('input[name="payment"]:checked').map(function() {
                        return $(this).attr("data-netAmount")
                    }).get();

                    if (allIds.length > 0) {
                        $("#add-request").attr("disabled", false);
                    } else {
                        $("#add-request").attr("disabled", true);
                    }

                    var transactionAmount = 0;
                    allAmount.forEach(e => {
                        transactionAmount += parseFloat(e);
                    });
                    $('#formTransactionAmount').val(transactionAmount)
                    $('#orderCount').text(allIds.length)
                    $('#modalOrderCount').text(allIds.length)
                    $('#formPaymentId').val(allIds);
                    var totalAmount = 0;
                    allNetAmount.forEach(e => {
                        totalAmount += parseFloat(e);
                    });
                    $('#totalSettlement').text(totalAmount.toFixed(2));
                    $('#modalAmount').text(totalAmount.toFixed(2));
                    $('#formAmount').val(totalAmount.toFixed(2))

                    var netSettleAmount = 0;
                    @if ($billing && $billing->settlement_fee_type == 'percentage_fee')
                        var realTotal = (totalAmount * {{ $billing->settlement_fee_ratio }}) / 100;

                        netSettleAmount = parseFloat(totalAmount) - parseFloat(realTotal);
                    @elseif ($billing?->settlement_fee_type == 'fixed_fee')
                        netSettleAmount = parseFloat(totalAmount) - {{ $billing->settlement_fee_ratio }};
                    @endif

                    if (netSettleAmount < 0) {
                        netSettleAmount = 0;
                    }

                    $('#totalHandlingFee').text((totalAmount.toFixed(2) - netSettleAmount.toFixed(2)).toFixed(2))
                    $('#modalFee').text((totalAmount.toFixed(2) - netSettleAmount.toFixed(2)).toFixed(2))

                    $('#totalNetSettlement').text(netSettleAmount.toFixed(2));
                    $('#modalNetAmount').text(netSettleAmount.toFixed(2));
                    $('#formNetAmount').val(netSettleAmount.toFixed(2))
                    $('#formHandlingFee').val((totalAmount.toFixed(2) - netSettleAmount.toFixed(2)).toFixed(2))
                }
            });

            // change bank account details
            $('#bankAccountSelect').on('change',function(){
                $.ajax({
                    type: "GET",
                    url: "list-bank-agent",
                    data: {id: $(this).find(':selected').data('id')},
                    success: function(res){
                        $("#displayBankName").text(res.bank_name);
                        $("#displayAccountName").text(res.account_name);
                        $("#displayAccountNumber").text(res.account_number);
                        $("#displayAccountType").text(res.bank.account_type);
                        $("#formAccountId").val(res.id);
                        $("#modalBankName").html(res.bank_name);
                        $("#modalAccountName").html(res.account_name);
                        $("#modalAccountNumber").html(res.account_number);
                    }
                });
            });


            // submit settle request
            $("#autoSubmit").on('submit', function(e){
                e.preventDefault();
                $.ajax({
                    url: "{{ route('unsettledRequest/unsettled-agent') }}",
                    type: 'POST',
                    data: $('#autoSubmit').serialize(),
                    success: function(res) {
                        if ($.isEmptyObject(res.error)) {
                            location.reload();
                        } else {
                            $('#errorMessage').html(res.error).show().fadeOut(6000);
                        }
                    }
                });
            });
        </script>
    @endsection
@endsection
