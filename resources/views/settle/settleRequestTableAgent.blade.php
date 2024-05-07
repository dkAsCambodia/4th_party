@extends('layouts.master')
@section('content')
	{!! Toastr::message() !!}

	<div class="content-body">
		<div class="container-fluid">
			<div class="row page-titles">
				<ol class="col-md-6 breadcrumb">
					<li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
					<li class="breadcrumb-item"> {{ __('messages.Settled Request') }}
					</li>
				</ol>
			</div>
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">{{ __('messages.Settled Request') }}</h4>
						</div>

						<div class="card-body row">
							<div class="col-md-2">
								<label class="form-label">{{ __('messages.Search') }}</label>
								<input id="search" type="search" class="form-control">
							</div>

							<div class="col-md-2">
								<label class="form-label">{{ __('messages.Created Time') }}</label>
								<input type="text" name="daterange" id="daterange" class="form-control daterange" value="" />
							</div>
						</div>

						<div class="card-body">
							<div class="table-responsive">
								<table id="requestDataTable" style="width: 100% !important">
									<thead>
										<tr>
                                            <th>{{ __('messages.Order Id') }}</th>
                                            <th>{{ __('messages.Settlement Trans ID') }}</th>
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
                var table = $('#requestDataTable').DataTable({
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    order: [[ 0, "desc" ]],
                    ajax: {
                        url: "{{ route('view/settleRequest-agent') }}",
                        data: function (d) {
                            d.daterange = $('#daterange').val()
                            d.search = $('#search').val()
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
            });
        </script>
    @endsection
@endsection
