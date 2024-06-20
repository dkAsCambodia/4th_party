@extends('layouts.master')

@section('content')
	{!! Toastr::message() !!}

	<div class="content-body">
		<div class="container-fluid">
			<div class="row page-titles">
				<ol class="col-md-6 breadcrumb">
					<li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
					<li class="breadcrumb-item"> {{ __('messages.Summary Report') }} </li>
				</ol>
			</div>
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">{{ __('messages.Summary Report') }}</h4>
						</div>

						<div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Report Range') }}</label>
                                <input type="text" name="daterange" id="daterange" class="form-control daterange" value="" />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Merchant') }}</label>
                                <select id="merchant" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($merchants as $merc)
                                        <option value="{{ $merc->merchant_code }}">{{ $merc->merchant_name }}</option>
                                    @endforeach
                                </select>
                            </div>
						</div>

						<div class="card-body">
							<div class="table-responsive">
								<table id="adminSummaryDataTable" style="width: 100% !important">
									<thead>
										<tr>
											<th>{{ __('messages.Date') }}</th>
											<th>{{ __('messages.Transactions count') }}</th>
											<th>{{ __('messages.Total Amount') }}</th>
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
                var table = $('#adminSummaryDataTable').DataTable({
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    order: [[ 0, "desc" ]],
                    ajax: {
                        url: "{{ route('admin-summary-report') }}",
                        data: function (d) {
                            d.daterange = $('#daterange').val()
                            d.merchant = $('#merchant').val()
                        },
                        error: function (jqXHR) {
                            if (jqXHR && jqXHR.status == 401) {location.reload()}
                        },
                    },
                    columns: [
                        {data: 'date'},
                        {data: 'order_count'},
                        {data: 'total_amount'},
                        {data: 'Currency'},
                    ],
                    columnDefs: [
                        { className: "dt-right", targets: [ 2 ] },
                        { responsivePriority: 1, targets: 0 },
                        { responsivePriority: 2, targets: 2 },
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

                // filter by dropdown
                $('.filterTable').change(function() {
                    table.draw();
                });

                $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                    table.draw();
                });

                $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    table.draw();
                });
            });
        </script>
    @endsection
@endsection
