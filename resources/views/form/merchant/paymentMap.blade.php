@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a>
                    </li>
                    <li class="breadcrumb-item"> {{ __('messages.Product Management') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Product Management') }}</h4>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="channelDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Product Id') }}</th>
                                            <th>{{ __('messages.Cny Min') }}</th>
                                            <th>{{ __('messages.Cny Max') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Created Date') }}</th>
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
                $('#channelDataTable').DataTable({
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    order: [[ 4, "desc" ]],
                    ajax: {
                        url: "{{ route('sow/payment-product') }}",
                        error: function (jqXHR) {
                            if (jqXHR && jqXHR.status == 401) {location.reload()}
                        },
                    },
                    columns: [
                        {data: 'product_id'},
                        {data: 'cny_min'},
                        {data: 'cny_max'},
                        {data: 'status'},
                        {data: 'created_at'},
                    ],
                    columnDefs: [
                        { className: "dt-right", targets: [ 1, 2 ] },
                        { responsivePriority: 1, targets: 0 },
                        { responsivePriority: 2, targets: 4 },
                        { responsivePriority: 3, targets: 3 },
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
                    dom: 'frt<"bottom"ipl><"clear">',
                });
            });
        </script>
    @endsection
@endsection
