@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item active"><a
                            href="{{ route('Merchant: View Merchant') }}">{{ __('messages.Merchant Management') }}</a></li>
                    <li class="breadcrumb-item">{{ __('messages.Settlement settings') }}</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('Settlement: Billing Add Settlement') }}" method="POST"
                                id="form_billing_merchant">
                                @csrf
                                <div class="row justify-content-center">
                                    <div class="mb-3 col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('messages.Merchant Name') }}</label>

                                            <input type="text" class="form-control" name="merchant_name"
                                                value="{{ $merchant_billing->merchant_name }}">
                                            <input type="hidden" name="merchant_id" value="{{ $merchant_billing->id }}">
                                        </div>
                                    </div>


                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">{{ __('messages.Settlement settings') }}</label>
                                        <div class="mb-3 col-md-8">
                                            <div class="form-group">
                                                <ul class="list-inline mt-2">
                                                    <li class="list-inline-item">
                                                        <input type="radio" value="inherit" id="inherit"
                                                            name="settlement_settings" onchange="hideChanged()"
                                                            {{ @$billing?->status == 'inherit' ? 'checked=checked' : '' }}>
                                                        <label
                                                            for="inherit">{{ __('messages.Inherit from system') }}</label>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <input type="radio" value="self" id="self"
                                                            name="settlement_settings" onchange="hideChanged()"
                                                            {{ @$billing?->status == 'self' ? 'checked=checked' : '' }}
                                                            {{ @$billing?->status == '' ? 'checked=checked' : '' }}>
                                                        <label for="self">{{ __('messages.Self defined') }}</label>
                                                    </li>
                                                </ul>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="inherit_hide">
                                    <div class="row justify-content-center">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Withdrawal switch') }}</label>
                                                        <input type="radio" value="turn_on" name="withdraw_switch"
                                                            id="withdraw_switch"
                                                            {{ @$billing?->withdraw_switch == 'turn_on' ? 'checked=checked' : '' }}
                                                            {{ @$billing?->withdraw_switch == '' ? 'checked=checked' : '' }}>
                                                        <label for="turn_on">{{ __('messages.Turn on') }}</label>
                                                        <input type="radio" value="closure" name="withdraw_switch"
                                                            id="withdraw_switch"
                                                            {{ @$billing?->withdraw_switch == 'closure' ? 'checked=checked' : '' }}>
                                                        <label for="closure">{{ __('messages.Closure') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Day allow to withdraw') }}</label>
                                                        @php
                                                            $Sunday = __('messages.Sunday');
                                                            $Monday = __('messages.Monday');
                                                            $Tuesday = __('messages.Tuesday');
                                                            $Wednesday = __('messages.Wednesday');
                                                            $Thursday = __('messages.Thursday');
                                                            $Friday = __('messages.Friday');
                                                            $Saturday = __('messages.Saturday');
                                                            $weekDays = [$Sunday, $Monday, $Tuesday, $Wednesday, $Thursday, $Friday, $Saturday];
                                                        @endphp
                                                        @foreach ($weekDays as $day)
                                                            <label for="remember_me d-block">{{ $day }}</label>
                                                            <input type="checkbox" value="{{ $day }}"
                                                                @if (!empty($billing->week_allow_withdrawals))
                                                                {{ in_array($day, $billing->week_allow_withdrawals) ? 'checked' : '' }}
                                                                @else
                                                                !checked
                                                                @endif
                                                                id="week_allow_withdrawals" name="week_allow_withdrawals[]">
                                                        @endforeach
                                                    </div><span class="week_allow_withdrawals_err text-danger"
                                                        role="alert"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Daily withdrawal start time') }}</label>
                                                        <input class="form-control" type="time"
                                                            name="withdrawal_start_time"
                                                            value="{{ isset($billing->withdrawal_start_time) ? $billing->withdrawal_start_time : '00:00' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Daily withdrawal end time') }}</label>
                                                        <input class="form-control" type="time"
                                                            name="withdrawal_end_time"
                                                            value="{{ isset($billing->withdrawal_end_time) ? $billing->withdrawal_end_time : '23:59' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Daily withdraw count') }}</label>
                                                        <input type="text" class="form-control" name="daily_withdrawals"
                                                            value="{{ isset($billing->daily_withdrawals) ? $billing->daily_withdrawals : null }}">
                                                        <span class="daily_withdrawals_err text-danger"
                                                            role="alert"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Max daily withdraw amount') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="max_daily_withdrawals"
                                                            value="{{ isset($billing->max_daily_withdrawals) ? $billing->max_daily_withdrawals : null }}">
                                                        <span class="max_daily_withdrawals_err text-danger"
                                                            role="alert"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Max per transaction withdraw amount') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="single_max_withdrawal"
                                                            value="{{ isset($billing->single_max_withdrawal) ? $billing->single_max_withdrawal : null }}">
                                                        <span class="single_max_withdrawal_err text-danger"
                                                            role="alert"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Min per transaction withdraw amount') }}</label>
                                                        <input type="text" class="form-control single_min_withdrawal"
                                                            name="single_min_withdrawal"
                                                            value="{{ isset($billing->single_min_withdrawal) ? $billing->single_min_withdrawal : null }}">
                                                        <span class="single_min_withdrawal_err text-danger"
                                                            role="alert"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label d-block">{{ __('messages.Charges type') }}</label>
                                                        <input type="radio" value="percentage_fee" id="percentage_fee"
                                                            name="settlement_fee_type" onchange="valueChanged()"
                                                            {{ @$billing->settlement_fee_type == 'percentage_fee' ? 'checked=checked' : '' }}
                                                            {{ @$billing->settlement_fee_type == '' ? 'checked=checked' : '' }}>
                                                        <label for="turn_on">{{ __('messages.Percentage Fee') }}</label>
                                                        <input type="radio" value="fixed_fee" id="fixed_fee"
                                                            name="settlement_fee_type" onchange="valueChanged()"
                                                            {{ @$billing->settlement_fee_type == 'fixed_fee' ? 'checked=checked' : '' }}>
                                                        <label for="closure">{{ __('messages.Fixed Fee') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label "
                                                            id="percentage">{{ __('messages.Charges percentage') }}</label>
                                                        <label class="form-label"
                                                            id="fixed">{{ __('messages.Each transaction charge') }}</label>
                                                        <input type="text" class="form-control settlement_fee_ratio"
                                                            name="settlement_fee_ratio"
                                                            value="{{ isset($billing->settlement_fee_ratio) ? $billing->settlement_fee_ratio : null }}">
                                                        <span class="settlement_fee_ratio_err text-danger"
                                                            role="alert"></span>
                                                    </div>
                                                </div>
                                                {{-- <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label
                                                            class="form-label">{{ __('messages.Max charge per transaction') }}</label>
                                                        <input type="text"
                                                            class="form-control single_transaction_fee_limit"
                                                            name="single_transaction_fee_limit"
                                                            value="{{ isset($billing->single_transaction_fee_limit) ? $billing->single_transaction_fee_limit : null }}">
                                                        <span class="single_transaction_fee_limit_err text-danger"
                                                            role="alert"></span>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            {{-- <button class="btn btn-primary" type="submit">{{ __('messages.Save') }}</button>
                                            <a href="{{ route('Merchant: View Merchant') }}">
                                                <button class="btn btn-primary" type="button">{{ __('messages.Cancel') }}</button>
                                            </a> --}}
                                        </div>
                                    </div>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary"
                                                type="submit">{{ __('messages.Save') }}</button>

                                            <a href="{{ route('Merchant: View Merchant') }}">
                                                <button class="btn btn-primary" type="button">{{ __('messages.Cancel') }}</button>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@section('script')
    <script>
        $("#fixed").hide();

        function valueChanged() {
            if ($('#fixed_fee').is(":checked")) {
                $("#fixed").show();
                $("#percentage").hide();
            } else {
                $("#percentage").show();
                $("#fixed").hide();
            }

        }

        function hideChanged() {
            if ($('#inherit').is(":checked")) {
                $("#inherit_hide").hide();
                $('.daily_withdrawals').removeAttr('required');
                $('.max_daily_withdrawals').removeAttr('required');
                $('.single_min_withdrawal').removeAttr('required');
                $('.single_max_withdrawal').removeAttr('required');
                $('.single_transaction_fee_limit').removeAttr('required');
                $('.settlement_fee_ratio').removeAttr('required');
            } else {
                $("#inherit_hide").show();
            }
        }

        $(document).ready(function() {
            if ($('#inherit').is(":checked")) {
                $("#inherit_hide").hide();
                $('.single_min_withdrawal').removeAttr('required');
            }
        });

        // edit merchant
        $("#form_billing_merchant").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Settlement: Billing Add Settlement') }}",
                    type: 'POST',
                    data: $('#form_billing_merchant').serialize(),
                    success: function(data) {
                        if ($.isEmptyObject(data.error)) {
                            location.reload();
                        } else {
                            printErrorMsg(data);
                        }
                    }
                });
            }
        });

        // print error message
        function printErrorMsg(msg) {
            $('.text-danger').text('');
            $.each(msg.error, function(key, value) {
                $('.' + key + '_err').text(value[0]);
            });
        }
    </script>
    {{-- Jquery Validation --}}
    <style>
        label.error {
            color: #dc3545;
            font-size: 14px;
        }
    </style>
    <script>
        $.validator.addMethod("numbersndecimals", function(value, element) {
            return this.optional(element) || /^(\d*\.?\d*)$/.test(value);
        });

        //Billing Form Validation
        $("#form_billing_merchant").validate({
            rules: {
                week_allow_withdrawals: {
                    required: true
                },
                daily_withdrawals: {
                    required: true,
                    digits: true,
                    maxlength: 10
                },
                max_daily_withdrawals: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                single_max_withdrawal: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                single_min_withdrawal: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                settlement_fee_ratio: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                single_transaction_fee_limit: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                }
            }
        })
    </script>
@endsection
@endsection
