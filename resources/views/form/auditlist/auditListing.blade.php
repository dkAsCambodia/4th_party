@extends('layouts.master')
@section('content')
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }} </a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Audit List') }}  </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Audit List') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="auditDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Date time') }}</th>
                                            <th>{{ __('messages.User') }}</th>
                                            <th>{{ __('messages.Action') }}</th>
                                            <th>{{ __('messages.Model') }}</th>
                                            <th>{{ __('messages.IP Address') }}</th>
                                            <th>{{ __('messages.Device') }}</th>
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
        <script type="text/javascript">
            $(function () {
                $('#auditDataTable').DataTable({
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    order: [[ 0, "desc" ]],
                    ajax: {
                        url: "{{ route('audit/list') }}",
                        error: function (jqXHR) {
                            if (jqXHR && jqXHR.status == 401) {location.reload()}
                        },
                    },
                    columns: [
                        {data: 'created_at'},
                        {data: 'user_id', name: 'user.name'},
                        {data: 'event'},
                        {data: 'auditable_type'},
                        {data: 'ip_address'},
                        {data: 'user_agent'},
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
