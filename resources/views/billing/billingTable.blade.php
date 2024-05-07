@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <?php
    $validMsg = __('messages.Please fill out this field');
    ?>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('messages.Settlement settings') }}</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-xl-8">
                                    <form action="{{ route('Settlement: Billing Add Settlement') }}" method="POST"
                                        style="margin-left: 31px;" id="form_billing">
                                        @csrf
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">{{ __('messages.Withdraw switch') }}</label>
                                                <div class="form-group">
                                                    <input type="radio" value="turn_on" name="withdraw_switch"
                                                        {{ @$billing_table->withdraw_switch == 'turn_on' ? 'checked=checked' : '' }}>
                                                    <label for="turn_on">{{ __('messages.Turn on') }}</label>
                                                    <input type="radio" value="closure" name="withdraw_switch"
                                                        {{ @$billing_table->withdraw_switch == 'closure' ? 'checked=checked' : '' }}>
                                                    <label for="closure">{{ __('messages.Closure') }}</label>
                                                </div><span class="withdraw_switch_err text-danger" role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label
                                                    class="form-label">{{ __('messages.Day allow to withdraw') }}</label>
                                                <div class="form-group">
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
                                                        <label for="remember_me">{{ $day }}</label>
                                                        <input type="checkbox" value="{{ $day }}"
                                                            @if (!empty($billing_table->week_allow_withdrawals)) {{ in_array($day, $billing_table->week_allow_withdrawals) ? 'checked' : '' }}
                                                        @else
                                                        !checked @endif
                                                            id="week_allow_withdrawals"
                                                            name="week_allow_withdrawals[]">&nbsp;&nbsp;
                                                    @endforeach
                                                </div><span class="week_allow_withdrawals_err text-danger"
                                                    role="alert"></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label
                                                    class="form-label">{{ __('messages.Daily withdrawal start time') }}</label>
                                                <div class="form-group">
                                                    <input class="form-control" type="time" name="withdrawal_start_time"
                                                        value="{{ isset($billing_table->withdrawal_start_time) ? $billing_table->withdrawal_start_time : '00:00' }}">
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label
                                                    class="form-label">{{ __('messages.Daily withdrawal end time') }}</label>
                                                <div class="form-group">
                                                    <input class="form-control" type="time" name="withdrawal_end_time"
                                                        value="{{ isset($billing_table->withdrawal_end_time) ? $billing_table->withdrawal_end_time : '23:59' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">{{ __('messages.Daily withdraw count') }}</label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="daily_withdrawals"
                                                        value="{{ isset($billing_table->daily_withdrawals) ? $billing_table->daily_withdrawals : null }}">
                                                    <span class="daily_withdrawals_err text-danger" role="alert"></span>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label
                                                    class="form-label">{{ __('messages.Max daily withdraw amount') }}</label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="max_daily_withdrawals"
                                                        value="{{ isset($billing_table->max_daily_withdrawals) ? $billing_table->max_daily_withdrawals : null }}">
                                                    <span class="max_daily_withdrawals_err text-danger"
                                                        role="alert"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label
                                                    class="form-label">{{ __('messages.Max per transaction withdraw amount') }}</label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="single_max_withdrawal"
                                                        value="{{ isset($billing_table->single_max_withdrawal) ? $billing_table->single_max_withdrawal : null }}">
                                                    <span class="single_max_withdrawal_err text-danger"
                                                        role="alert"></span>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label
                                                    class="form-label">{{ __('messages.Min per transaction withdraw amount') }}</label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="single_min_withdrawal"
                                                        value="{{ isset($billing_table->single_min_withdrawal) ? $billing_table->single_min_withdrawal : null }}">
                                                    <span class="single_min_withdrawal_err text-danger"
                                                        role="alert"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">{{ __('messages.Charges type') }}</label>
                                                <div class="form-group">
                                                    <input type="radio" value="percentage_fee" id="percentage_fee"
                                                        name="settlement_fee_type" onchange="valueChanged()"
                                                        {{ @$billing_table->settlement_fee_type == 'percentage_fee' ? 'checked=checked' : '' }}>
                                                    <label
                                                        for="percentage_fee">{{ __('messages.Percentage Fee') }}</label>
                                                    <input type="radio" value="fixed_fee" id="fixed_fee"
                                                        name="settlement_fee_type" onchange="valueChanged()"
                                                        {{ @$billing_table->settlement_fee_type == 'fixed_fee' ? 'checked=checked' : '' }}>
                                                    <label for="fixed_fee">{{ __('messages.Fixed Fee') }}</label>
                                                </div>
                                                <span class="settlement_fee_type_err text-danger" role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label "
                                                    id="percentage">{{ __('messages.Charges percentage') }}</label>
                                                <label class="form-label"
                                                    id="fixed">{{ __('messages.Each transaction charge') }}</label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control"
                                                        name="settlement_fee_ratio"
                                                        value="{{ isset($billing_table->settlement_fee_ratio) ? $billing_table->settlement_fee_ratio : null }}">
                                                    <span class="settlement_fee_ratio_err text-danger"
                                                        role="alert"></span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label
                                                    class="form-label">{{ __('messages.Single transaction fee limit (yuan)') }}</label>
                                                <div class="form-group">
                                                    <input type="text" class="form-control"
                                                        name="single_transaction_fee_limit"
                                                        value="{{ isset($billing_table->single_transaction_fee_limit) ? $billing_table->single_transaction_fee_limit : null }}">
                                                    <span class="single_transaction_fee_limit_err text-danger"
                                                        role="alert"></span>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">{{ __('messages.Payment method') }}</label>
                                                <div class="form-group mt-3">
                                                    <input type="radio" value="d0_arrives" id="payment_method"
                                                        name="payment_method"
                                                        {{ @$billing_table->payment_method == 'd0_arrives' ? 'checked=checked' : '' }}
                                                        >
                                                    <label for="d0_arrives">{{ __('messages.D0 arrives') }}</label>
                                                    <input type="radio" value="d1_account" id="payment_method"
                                                        name="payment_method"
                                                        {{ @$billing_table->payment_method == 'd1_account' ? 'checked=checked' : '' }}>
                                                    <label for="d1_account">{{ __('messages.D1 to account') }}</label>
                                                </div>
                                                <span class="payment_method_err text-danger" role="alert"></span>
                                            </div>
                                        </div> --}}

                                        <button class="btn btn-primary" type="submit">{{ __('messages.Save') }}</button>
                                    </form>
                                </div>
                            </div>

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

        // Billing Submit
        $("#form_billing").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Settlement: Billing Add Settlement') }}",
                    type: 'POST',
                    data: $('#form_billing').serialize(),
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
        $("#form_billing").validate({
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
