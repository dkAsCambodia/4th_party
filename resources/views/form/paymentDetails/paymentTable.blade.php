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
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li> 
                    <li class="breadcrumb-item">{{ __('messages.Payment Details') }}</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payment Details') }}</h4>
                            <a href="#" id="exportAdminPaymentDetails" class="btn btn-success btn-sm border-none rounded-1">{{ trans('messages.Export') }}</a>
                        </div>

                        <div class="card-body">
                            <form action="" method="GET" class="row g-1">
                                <div class="col-md-2">
                                    <label class="form-label">{{ __('messages.Search') }}</label>
                                    <input id="search" type="search" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('messages.Status') }}</label>
                                   <select id="status" class="form-control filterTable">
                                        <option value="">{{ __('messages.All') }}</option>
                                        <option value="pending">{{ __('messages.pending') }}</option>
                                        <option value="success">{{ __('messages.Success') }}</option>
                                        <option value="failed">{{ __('messages.Failed') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('messages.Created Time') }}</label>
                                    <input type="text" name="daterange" id="daterange" class="form-control daterange" value="{{ date('Y-m-d') }} - {{ date('Y-m-d') }}" />
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('messages.Timezone') }}</label>
                                   <select id="timezone" class="form-control filterTable">
                                        @foreach ($timezones as $zone)
                                            <option value="{{ $zone->id }}">{{ __('messages.'.$zone->timezone) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('messages.refresh_interval') }}</label>
                                    <select id="minutes" class="form-control">
                                        <option value="1">{{ __('messages.one_minute') }}</option>
                                        <option value="2" selected>{{ __('messages.two_minutes') }}</option>
                                        <option value="5">{{ __('messages.five_minutes') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-4"></label>
                                    <div class="">
                                        <button type='button' id="manual_update" class="btn btn-primary text-white btn-xs">{{ __('messages.Refresh') }}</button>
                                        <button type='button' id="auto_update" class="btn btn-primary text-white btn-xs">{{ __('messages.resume') }}</button>
                                        <button type='button' id="reset" class="btn btn-danger text-white btn-xs">{{ __('messages.Reset') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <form class="row g-1">
                                <div class="mb-3 p-2 border bg-v-light">
                                    <ul class="list-inline right-bar-list mb-0">
                                        <li class="list-inline-item">{{ __('messages.Order count:') }} <span id="payment_count"></span></li> <span class="d-none d-md-inline">|</span>
                                        <li class="list-inline-item">{{ __('messages.Total order amount:') }} <span id="order_amount_sum"></span></li> <span class="d-none d-md-inline">|</span>
                                        <li class="list-inline-item">{{ __('messages.Success order count:') }} <span id="order_success_count"></span></li> <span class="d-none d-md-inline">|</span>
                                        <li class="list-inline-item">{{ __('messages.Success order amount:') }} <span id="order_success_sum"></span></li>
                                    </ul>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="paymentDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Order Id') }}</th>
                                            <th>{{ __('messages.Merchant Code') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
                                            <th>{{ __('messages.Transaction ID') }}</th>
                                            <th>{{ __('messages.Merchant Track No') }}</th>
                                            <th>{{ __('messages.Customer Name') }} </th>
                                            <th>{{ __('messages.Amount') }} </th>
                                            <th>{{ __('messages.Currency') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- /Payment View Modal -->
     <div id="view_record" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width:1000px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.View Payment Details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Merchant Code') }}</td>
                                <td><span class="merchant_code"></span></td>
                                <td class="bg-dark text-white" style="width: 20%;">{{ __('messages.Transaction ID') }}</td>
                                <td><span class="fourth_transaction_id"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Platform Profit') }}</td>
                                <td><span class="platform_profit"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Merchant Name') }}</td>
                                <td><span class="merchant_name"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Agent Code') }}</td>
                                <td><span class="agent_code"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Agent Name') }}</td>
                                <td><span class="agent_name"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Created Time') }}</td>
                                <td><span class="created_date"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Merchant Track No.') }}</td>
                                <td><span class="transaction_id"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Gateway Order No.') }}</td>
                                <td><span class="Gateway_Order_No"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Customer Name') }}</td>
                                <td><span class="customer_name"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Gateway Ref No.') }}</td>
                                <td><span class="Gateway_Ref_ID"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Status') }}</td>
                                <td><span class="order_status"></span></td>
                            </tr>

                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Remark') }}</td>
                                <td><span class="error_desc"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Settlement Status') }}</td>
                                <td><span class="merchant_settle_status"></span></td>
                            </tr>
                            {{-- <tr>
                                <td class="bg-dark text-white">{{ __('messages.Device') }}</td>
                                <td><span class=""></span>NULL</td>
                                <td class="bg-dark text-white">{{ __('messages.IP Address') }}</td>
                                <td><span class=""></span>NULL</td>
                            </tr> --}}
                            <tr>
                                <td class="bg-white text-white"></td>
                                <td><span class=""></span></td>
                                <td class="bg-white text-white"></td>
                                <td><span class=""></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Payment Amount') }}</td>
                                <td><span class="amount"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Currency') }}</td>
                                <td><span class="currency"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Merchant Rate') }}</td>
                                <td><span class="merchant_rate"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Merchant Income') }}</td>
                                <td><span class="merchant_income"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Agent Rate') }}</td>
                                <td><span class="agent_rate"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Agent Profit') }}</td>
                                <td><span class="agent_profit"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-white text-white"></td>
                                <td><span class=""></span></td>
                                <td class="bg-white text-white"></td>
                                <td><span class=""></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Callback URL') }}</td>
                                <td style="width:290px;word-break:break-all;" class="callback_url"> </td>
                                <td class="bg-dark text-white">{{ __('messages.Payment Product ID') }}</td>
                                <td><span class="product_id"></span></td>
                            </tr>
                            <tr>
                                <td class="bg-dark text-white">{{ __('messages.Payment Channel') }}</td>
                                <td><span class="payment_channel"></span></td>
                                <td class="bg-dark text-white">{{ __('messages.Payment Method') }}</td>
                                <td><span class="payment_method"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Agent Account Modal -->

@section('script')
    <script>
        $(document).on('click', '.view_record', function() {
            var _this = $(this).parents('tr');
            let paymentId  = $(this).data('view_record');
            $.ajax({
                url: "payment-details/" + paymentId,
                type: 'GET',
                data: {_token : "{{ csrf_token() }}"},
                success: function(data) {
                    //$("input[name='sr_no']").val(data.data.id);
                    //$("div[name='sr_no']").val(data.data.id);
                    $('span.sr_no').text(data.data.id);
                    $('span.merchant_code').text(data.data.merchant_code);
                    $('span.merchant_name').text(data.data.merchant_data.merchant_name);
                    $('span.agent_code').text(data.data.merchant_data?.agent?.agent_code);
                    $('span.agent_name').text(data.data.merchant_data?.agent?.agent_name);

                    $('span.transaction_id').text(data.data.transaction_id);
                    $('span.fourth_transaction_id').text(data.data.fourth_party_transection);
                    $('span.amount').text(data.data.amount);
                    $('span.customer_name').text(data.data.customer_name);
                    $('span.order_id').text(data.data.order_id);
                    $('span.order_date').text(data.data.order_date);
                    $('span.order_status').text(data.data.payment_status);
                    $('span.currency').text(data.data.Currency);
                    $('span.merchant_rate').text(data.data.payment_maps.merchant_commission);
                    $('span.agent_rate').text(data.data.payment_maps.agent_commission);

                    $('span.trans_id').text(data.data.trans_id);
                    $('span.Gateway_Order_No').text(data.data.order_id);
                    $('span.Gateway_Ref_ID').text(data.data.TransId);
                    $('span.error_desc').text(data.data.ErrDesc);
                    $('td.callback_url').text(data.data.callback_url);
                    // $('span.product_name').text(data.data.payment_maps.payment_url.url_name);


                    var realTotal = (data.data.amount * data.data.payment_maps.merchant_commission) / 100;
                    var realTotalAgent = (data.data.amount * data.data.payment_maps.agent_commission) / 100;


                    var platformProfit = realTotal-realTotalAgent;

                    // if(data.data.billing_table.settlement_fee_type == "percentage_fee"){
                    //     // alert(data.data.billing_table.settlement_fee_ratio);
                    //     platformProfit = (data.data.amount * data.data.billing_table.settlement_fee_ratio) / 100;
                    // }else{
                    //     platformProfit = (data.data.amount - data.data.billing_table.settlement_fee_ratio);
                    // }


                    $('span.merchant_income').text((data.data.amount-realTotal).toFixed(2));
                    $('span.agent_profit').text(realTotalAgent.toFixed(2));
                    $('span.platform_profit').text(platformProfit.toFixed(2));


                    $('span.payment_channel').text(data.data.payment_channel);
                    $('span.payment_method').text(data.data.payment_method);
                    //$('span.payment_source').text(data.data.payment_source);
                    $('span.payment_status').text(data.data.payment_status);
                    $('span.created_date').text( new Date(data.data.created_at).toLocaleString('sv-SE'));

                    $('span.product_id').text(data.data.product_id);
                    $('span.merchant_settle_status').text(data.data.merchant_settle_status);
                },
            })
        });
    </script>

    <script>
        $(function () {
            var table = $('#paymentDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [[ 1, "desc" ]],
                ajax: {
                    url: "{{ route('PaymentDetails: View PaymentDetails') }}",
                    data: function (d) {
                        d.status = $('#status').val()
                        d.daterange = $('#daterange').val()
                        d.search = $('#search').val()
                        d.timezone = $('#timezone').val()

                        var exportURL = '/admin/export-payment-details?daterange='+d.daterange+'&status='+d.status+'&timezone='+d.timezone;
                        $('#exportAdminPaymentDetails').attr('href', exportURL)
                    },
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [
                    {
                    data: null,
                    render: function (data, type, row, meta) {
                        // Auto-increment the counter for each row
                        return meta.row + 1;
                    },
                    orderable: false,
                    searchable: false,
                    },
                    {data: 'merchant_code'},
                    {data: 'created_at'},
                    {data: 'fourth_party_transection'},
                    {data: 'transaction_id'},
                    {data: 'customer_name'},
                    {data: 'amount'},
                    {data: 'Currency'},
                    {data: 'payment_status'},
                    {data: 'action', searchable: false, sortable: false},
                ],
                columnDefs: [
                    { className: "dt-right", targets: [ 5, 6 ] },
                    { responsivePriority: 1, targets: 1 },
                    { responsivePriority: 2, targets: 8 },
                    { responsivePriority: 3, targets: 7 },
                ],
                language: {
                    search: "{{ trans('messages.Search') }}",
                    info: "_START_ - _END_ {{ trans('messages.of') }} _TOTAL_",
                    infoEmpty: "0 - 0 {{ trans('messages.of') }} 0",
                    lengthMenu: "{{ trans('messages.records per page') }} _MENU_",
                    infoFiltered: "({{ trans('messages.filtered from') }} _MAX_ {{ trans('messages.results') }})",
                    zeroRecords: "{{ trans('messages.No matching records found') }}",
                    loadingRecords: "{{ trans('messages.Loading') }}",
                    paginate: {
                        previous: "<",
                        next: ">",
                    },
                    emptyTable: "{{ trans('messages.No data available in the table') }}",
                    processing: "{{ trans('messages.processing') }}",
                },
                dom: 'rt<"bottom"ipl><"clear">',
            });

            table.on('draw.dt', function() {
                $('#payment_count').html(table.ajax.json().payment_count);
                $('#order_amount_sum').html(table.ajax.json().order_amount_sum);
                $('#order_success_count').html(table.ajax.json().order_success_count);
                $('#order_success_sum').html(table.ajax.json().order_success_sum);
                $('#merchant_income').html(table.ajax.json().merchant_income);
                $('#agent_income').html(table.ajax.json().agent_income);
            })

            // filter by dropdown
            $('.filterTable').change(function(){
                table.draw();
            });

            // search
            document.getElementById('search').addEventListener('input', (e) => {
                table.draw();
            })

            // reset filter
            $('#reset').on('click', function() {
                $('#status').val('')
                $('input[name="daterange"]').val(
                    new Date().toJSON().slice(0,10) +' - '+ new Date().toJSON().slice(0,10)
                )
                $('#search').val('')
                table.draw();
            })

            $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                table.draw();
            });

            $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                table.draw();
            });

            $('#manual_update').click(function(){
                table.ajax.reload();
            });

            $('#auto_update').click(function()
            {
                var Interval;
              if($("#auto_update").text() == "Resume")
              {
                $("#auto_update").text('Pause').removeClass('btn-success').addClass('btn-warning');
                var selected_interval = $("#minutes option:selected").val();
                var time = selected_interval * 60000;
                window.Interval = setInterval(function(){
                    ajax_reload()
                }, time);

              }
              else if($("#auto_update").text() == "恢复")
              {
                $("#auto_update").text('暂停').removeClass('btn-success').addClass('btn-warning');
                var selected_interval = $("#minutes option:selected").val();
                var time = selected_interval * 60000;
                window.Interval = setInterval(function(){
                    ajax_reload()
                }, time);

              }
              else if ($("#auto_update").text() == "Pause")
              {
                $("#auto_update").text('Resume').removeClass('btn-warning').addClass('btn-success');
                window.clearInterval(window.Interval);
              }
              else if ($("#auto_update").text() == "暂停")
              {
                $("#auto_update").text('恢复').removeClass('btn-warning').addClass('btn-success');
                window.clearInterval(window.Interval);
              }

              function ajax_reload()
              {
                table.ajax.reload();
              }
              return false;
            });
        });
    </script>

@endsection
@endsection
