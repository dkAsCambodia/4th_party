@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <style>
        table#bankDataTable {
            width: 100% !important;
        }
    </style>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Bank Account') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="mb-0">
                                <label class="radio-inline me-1" style="cursor:pointer;">
                                    <input type="radio" name="checkValue" class="changeVisibility" value=""
                                        id="merchantValue">
                                    {{ trans('messages.Merchant') }}
                                </label>
                                <label class="radio-inline me-1" style="cursor:pointer;">
                                    <input type="radio" name="checkValue" class="changeVisibility" value="agent"
                                        id="agentValue">
                                    {{ trans('messages.Agent') }}
                                </label>
                            </div>
                        </div>

                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Account Type') }}</label>
                                <select id="bank" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($allBank as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->account_type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2" id="merchantSelect">
                                <label class="form-label">{{ __('messages.Merchant Code') }}</label>
                                <select id="merchant" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($allMerchant as $mer)
                                        <option value="{{ $mer->id }}">{{ $mer->merchant_code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2" id="agentSelect" style="display: none">
                                <label class="form-label">{{ __('messages.Agent Code') }}</label>
                                <select id="agent" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($allAgent as $agen)
                                        <option value="{{ $agen->id }}">{{ $agen->agent_code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="status" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    <option value="enable">{{ __('messages.Enable') }}</option>
                                    <option value="disable">{{ __('messages.Disable') }}</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="bankDataTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Merchant Code') }}</th>
                                            <th>{{ __('messages.Agent Code') }}</th>
                                            <th>{{ __('messages.Account Type') }}</th>
                                            <th>{{ __('messages.Bank Name') }}</th>
                                            <th>{{ __('messages.Account Name') }}</th>
                                            <th>{{ __('messages.Account Number') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Default') }}</th>
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

    <script>
        // datatables
        $(function() {
            var table = $('#bankDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [
                    [6, "desc"]
                ],
                ajax: {
                    url: "{{ route('Account: View Account') }}",
                    data: function(d) {
                        d.bank = $('#bank').val()
                        d.merchant = $('#merchant').val()
                        d.agent = $('#agent').val()
                        d.status = $('#status').val()
                        d.search = $('#search').val()
                        d.checkValue = $("input[name='checkValue']:checked").val()
                    },
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'merchant_code',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'agent_code',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'account_type',
                        sortable: false,
                        searchable: false
                    },
                    {
                        data: 'bank_name'
                    },
                    {
                        data: 'account_name'
                    },
                    {
                        data: 'account_number'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'default'
                    },
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
                    table.column([0]).visible(false)
                    table.column([1]).visible(true)
                } else {
                    $('#merchantValue').prop("checked", true);
                    $('#merchantSelect').show();
                    $('#agentSelect').hide();
                    table.column([1]).visible(false)
                    table.column([0]).visible(true)
                }
            })

            // filter by dropdown
            $('.filterTable').change(function() {
                table.draw();
            });

            // search
            document.getElementById('search').addEventListener('input', (e) => {
                table.draw();
            });

            $('.changeVisibility').click(function() {
                table.draw();
            })
        });
    </script>
@endsection
