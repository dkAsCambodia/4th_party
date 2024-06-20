@extends('layouts.master')

@section('content')
	{!! Toastr::message() !!}

	<div class="content-body">
		<div class="container-fluid">
			<div class="row page-titles">
				<ol class="col-md-6 breadcrumb">
					<li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
					<li class="breadcrumb-item active"><a href="{{ route('admin-summary-report') }}">{{ __('messages.Summary Report') }}</a></li>
					<li class="breadcrumb-item"> {{ __('messages.Summary Report Detail') }} </li>
				</ol>
			</div>
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">{{ __('messages.Summary Report Detail') }}</h4>
						</div>

						<div class="card-body">
                            <div class="d-md-flex justify-content-between text-black">
                                <div class="mt-2">
                                    <h5>
                                        {{ trans('messages.Date') }}: {{ $date }}
                                        <span class="ms-3">{{ trans('messages.Merchant') }}: {{ $merchant_code != null ? $merchant_code .' '. $merchant_name : trans('messages.All') }}</span>
                                    </h5>
                                </div>
                                <span>
                                    <a href="{{ route('exportAdminReport', ['date' => $date, 'merchant_code' => $merchant_code]) }}" class="btn btn-success btn-sm border-none rounded-1">{{ trans('messages.Export Report') }}</a>
                                    <a href="{{ route('admin-summary-report') }}" class="btn btn-light btn-sm border-none rounded-1">{{ trans('messages.Back') }}</a>
                                </span>
                            </div>
							<div class="table-responsive">
								<table id="adminDetailDataTable" style="width: 100% !important">
									<thead>
										<tr>
											<th>{{ __('messages.Created Time') }}</th>
											<th>{{ __('messages.Merchant Code') }}</th>
											<th>{{ __('messages.Merchant Name') }}</th>
											<th>{{ __('messages.Transaction ID') }}</th>
											<th>{{ __('messages.Merchant Track No.') }}</th>
											<th>{{ __('messages.Customer Name') }}</th>
											<th>{{ __('messages.Amount') }}</th>
											<th>{{ __('messages.Currency') }}</th>
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
                $('#adminDetailDataTable').DataTable({
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    order: [[ 0, "desc" ]],
                    ajax: {
                        url: "{{ route('admin-summary-report-by-date', ['date' => $date, 'merchant_code' => $merchant_code]) }}",
                        error: function (jqXHR) {
                            if (jqXHR && jqXHR.status == 401) {location.reload()}
                        },
                    },
                    columns: [
                        {data: 'created_at'},
                        {data: 'merchant_code'},
                        {data: 'merchant_name'},
                        {data: 'fourth_party_transection'},
                        {data: 'transaction_id'},
                        {data: 'customer_name'},
                        {data: 'amount'},
                        {data: 'Currency'},
                    ],
                    columnDefs: [
                        { className: "dt-right", targets: [ 6, 7 ] },
                        { responsivePriority: 1, targets: 1 },
                        { responsivePriority: 2, targets: 6 },
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
            });
        </script>
    @endsection
@endsection
