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
                    <li class="breadcrumb-item"> {{ __('messages.Payment Details') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payment Details') }}</h4>
                            <a href="#" id="exportMerchantPaymentDetails" class="btn btn-success btn-sm border-none rounded-1">{{ trans('messages.Export') }}</a>
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
                                        <option value="" selected>{{ __('messages.All') }}</option>
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

                        <div class="card-body" id="success">
                            <div class="mb-3 p-2 border bg-v-light">
                                <ul class="list-inline right-bar-list mb-0">
                                    {{-- <li class="list-inline-item">{{ __('messages.Order count:') }} <span id="payment_count"></span></li> <span class="d-none d-md-inline">|</span>
                                    <li class="list-inline-item">{{ __('messages.Total order amount:') }} <span id="order_amount_sum"></span></li> <span class="d-none d-md-inline">|</span> --}}
                                    <li class="list-inline-item">{{ __('messages.Success order count:') }} <span id="order_success_count"></span></li> <span class="d-none d-md-inline">|</span>
                                    <li class="list-inline-item">{{ __('messages.Success order amount:') }} <span id="order_success_sum"></span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body" id="fail">
                            <div class="mb-3 p-2 border bg-v-light">
                                <ul class="list-inline right-bar-list mb-0">
                                    <li class="list-inline-item">{{ __('messages.Fail order count:') }} <span id="order_fail_count"></span></li> <span class="d-none d-md-inline">|</span>
                                    <li class="list-inline-item">{{ __('messages.Fail order amount:') }} <span id="order_fail_sum"></span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="paymentDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Order Id') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
                                            <th>{{ __('messages.Transaction ID') }}</th>
                                            <th>{{ __('messages.Merchant Track No') }}</th>
                                            <th>{{ __('messages.Customer Name') }} </th>
                                            <th>{{ __('messages.Amount') }} </th>
                                            <th>{{ __('messages.Currency') }}</th>
                                            <th >{{ __('messages.Status') }}</th>
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

@section('script')
    <script>
        $(document).ready(function(){
            $("#fail").hide();
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
                order: [[ 0, "desc" ]],
                ajax: {
                    url: "{{ route('details-payment/list-merchant') }}",
                    data: function (d) {
                        d.status = $('#status').val()
                        d.daterange = $('#daterange').val()
                        d.search = $('#search').val()
                        d.timezone = $('#timezone').val()

                        var exportURL = '/merchant/export-payment-details?daterange='+d.daterange+'&status='+d.status+'&timezone='+d.timezone;
                        $('#exportMerchantPaymentDetails').attr('href', exportURL)
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
                    {data: 'created_at'},
                    {data: 'fourth_party_transection'},
                    {data: 'transaction_id'},
                    {data: 'customer_name'},
                    {data: 'amount'},
                    {data: 'Currency'},
                    {data: 'payment_status'},
                ],
                columnDefs: [
                    { className: "dt-right", targets: [  4, 5 ] },
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 4 },
                    { responsivePriority: 3, targets: 6 },
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
                $('#order_fail_count').html(table.ajax.json().order_fail_count);
                $('#order_fail_sum').html(table.ajax.json().order_fail_sum);
            })

            // filter by dropdown
            $('.filterTable').change(function()
            {
                var selected_status = $("#status option:selected").val();
                    if(selected_status == 'success' || selected_status == '')
                    {
                        $("#success").show();
                        $("#fail").hide();
                    }
                    else if(selected_status == 'fail')
                    {
                        $("#success").hide();
                        $("#fail").show();
                    }
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
                //$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
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
