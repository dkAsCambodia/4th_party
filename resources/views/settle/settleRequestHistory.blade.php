@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Settlement Records') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="mb-0">
                                <label class="radio-inline me-1" style="cursor:pointer;">
                                    <input type="radio" name="checkValue" class="changeVisibility" value="" id="merchantValue">
                                    {{ trans('messages.Merchant') }} ({{ $merchantCount }})
                                </label>
                                <label class="radio-inline me-1" style="cursor:pointer;">
                                    <input type="radio" name="checkValue" class="changeVisibility" value="agent" id="agentValue">
                                    {{ trans('messages.Agent') }} ({{ $agentCount }})
                                </label>
                            </div>
                        </div>

                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>

                            <div class="col-md-2" id="merchantSelect">
                                <label class="form-label">{{ __('messages.Merchant Code') }}</label>
                                <select id="merchant_code" class="form-control filterTable">
                                    <option value="">{{ __('messages.All') }}</option>
                                    @foreach ($merchant as $merch)
                                        <option value="{{ $merch->id }}">{{ $merch->merchant_code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2" id="agentSelect" style="display: none">
                                <label class="form-label">{{ __('messages.Agent Code') }}</label>
                                <select id="agent_code" class="form-control filterTable">
                                    <option value="">{{ __('messages.All') }}</option>
                                    @foreach ($agents as $agen)
                                        <option value="{{ $agen->id }}">{{ $agen->agent_code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="status" class="form-control filterTable">
                                    <option value="" selected>{{ __('messages.All') }}</option>
                                    <option value="approved">{{ __('messages.Approved') }}</option>
                                    <option value="success">{{ __('messages.success') }}</option>
                                    <option value="pending">{{ __('messages.pending') }}</option>
                                    <option value="failed">{{ __('messages.Failed') }}</option>
                                    <option value="paid">{{ __('messages.Paid') }}</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Created Time') }}</label>
                                <input type="text" name="daterange" id="daterange" class="form-control daterange" value="" />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label mb-4"></label>
                                <div class="">
                                    <button type='button' id="reset" class="btn btn-danger text-white btn-xs">{{ __('messages.Reset') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="historyDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                        <th>{{ __('messages.Order Id') }}</th>
                                            <th>{{ __('messages.Settlement Trans ID') }}</th>

                                            <th>{{ __('messages.Merchant Code') }}</th>
                                            <th>{{ __('messages.Agent Code') }}</th>
                                            
                                            <th>{{ __('messages.Merchant Track No') }}</th>
                                            <th>{{ __('messages.Currency') }}</th>
                                            <th>{{ __('messages.Net Amount') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
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

@section('script')
    <script>
        $(function () {
            var table = $('#historyDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [[ 0, "desc" ]],
                ajax: {
                    url: "{{ route('Settlement: Settled History Settlement') }}",
                    data: function (d) {
                        d.status = $('#status').val()
                        d.daterange = $('#daterange').val()
                        d.search = $('#search').val()
                        d.merchant_code = $('#merchant_code').val()
                        d.agent_code = $('#agent_code').val()
                        d.checkValue = $("input[name='checkValue']:checked").val()
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
                        {data: 'fourth_party_transection'},
                        {data: 'merchant_code', searchable: false, sortable: false},
                        {data: 'agent_code', searchable: false, sortable: false},
                        {data: 'merchant_track_id'},
                        {data: 'Currency'},
                        {data: 'total'},
                        {data: 'status'},
                        {data: 'created_at'},
                        {data: 'action', searchable: false, sortable: false},
                ],
                columnDefs: [
                        { className: "dt-right", targets: [ 4, 5, 6 ] },
                        { responsivePriority: 1, targets: 0 },
                        { responsivePriority: 2, targets: 7 },
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
                if (table.ajax.json().checkValue == 'agent') {
                    $('#agentValue').prop("checked", true)
                    $('#merchantSelect').hide();
                    $('#agentSelect').show();
                    table.column([2]).visible(false)
                    table.column([3]).visible(true)
                } else {
                    $('#merchantValue').prop("checked", true);
                    $('#merchantSelect').show();
                    $('#agentSelect').hide();
                    table.column([3]).visible(false)
                    table.column([2]).visible(true)
                }
            })

            $('.changeVisibility').click(function(){
                table.draw();
            })

            // filter by dropdown
            $('.filterTable').change(function(){
                table.draw();
            });

            // search
            document.getElementById('search').addEventListener('input', (e) => {
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

            $('#reset').on('click', function() {
                $('#status').val('')
                $('input[name="daterange"]').val('')
                $('#search').val('')
                $('#merchant_code').val('')
                $('#agent_code').val('')
                table.draw();
            })
        });
    </script>
@endsection
@endsection
