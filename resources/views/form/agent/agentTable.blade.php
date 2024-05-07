@extends('layouts.master')

@section('content')
	{!! Toastr::message() !!}

	<div class="content-body">
		<div class="container-fluid">
			<div class="row page-titles">
				<ol class="breadcrumb">
					<li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
					<li class="breadcrumb-item"> {{ __('messages.Agent Management') }} </li>
				</ol>
			</div>
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">{{ __('messages.Agent Management') }}</h4>
							@if(auth()->user()->can('Agent: Add Agent'))
								<button type="submit" id="add-agent" class="btn btn-danger shadow btn-xs me-1 add_record" style="float: right;" data-toggle="modal" data-target="#add_agent">
									{{ __('messages.Add agent') }}
								</button>
							@endif
						</div>
						<div class="card-body row">
							<div class="col-md-2">
								<label class="form-label">{{ __('messages.Search') }}</label>
								<input id="search" type="search" class="form-control">
							</div>

							<div class="col-md-2">
								<label class="form-label">{{ __('messages.Status') }}</label>
								<select id="status" class="form-control filterTable">
									<option value="">{{ trans('messages.All') }}</option>
									<option value="Enable">{{ __('messages.Enable') }}</option>
									<option value="Disable">{{ __('messages.Disable') }}</option>
								</select>
							</div>
						</div>

						<div class="card-body">
							<div class="table-responsive">
								<table id="merchantDataTable" style="width: 100% !important">
									<thead>
										<tr>
											<th>{{ __('messages.agent Code') }}</th>
											<th>{{ __('messages.agent Name') }}</th>
											<th>{{ __('messages.Status') }}</th>
											<th>{{ __('messages.Create Date') }}</th>
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

    <?php
        $validMsg = __('messages.Please fill out this field');
    ?>

	{{-- Add agent modal --}}
	<div id="add_agent" class="modal custom-modal fade" role="dialog">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{{ __('messages.Add agent') }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					<form action="{{ route('Agent: Add Agent') }}" method="POST" id="add_agent_form">
						@csrf
						<div class="row">
							<div class="mb-3 col-md-6">
								<label class="form-label">{{ __('messages.agent Name') }}</label>
								<input type="text" class="form-control" name="agent_name" id="agent_name">
								<span class="agent_name_err text-danger" role="alert"></span>
							</div>

							<div class="mb-3 col-md-6">
								<label class="form-label">{{ __('messages.agent Code') }}</label>
								<input type="text" class="form-control" name="agent_code" id="agent_code">
								<span class="agent_code_err text-danger" role="alert"></span>
							</div>

							<div class="mb-3 col-md-6">
								<label class="mb-1"><strong>{{ __('messages.Email') }}</strong></label>
								<input type="text" class="form-control" name="email" id="email" placeholder="{{ __('messages.Enter email') }}">
								<span class="email_err text-danger" role="alert"></span>
							</div>

							<div class="mb-3 col-md-6">
								<label class="mb-1"><strong>{{ __('messages.Username') }}</strong></label>
								<input type="text" class="form-control" name="user_name" id="username" placeholder="{{ __('messages.Enter Username') }}">
								<span class="user_name_err text-danger" role="alert"></span>
							</div>

							<div class="mb-3 col-md-6">
								<label class="mb-1"><strong>{{ __('messages.Mobile Number') }}</strong></label>
								<input type="text" class="form-control" name="mobile_number" id="mobile_number" placeholder="{{ __('messages.Enter Mobile Number') }}">
								<span class="mobile_number_err text-danger" role="alert"></span>
							</div>

							<div class="mb-3 col-md-6">
								<label class="form-label">{{ __('messages.Timezone') }}</label>
								<select name="timezone" id="timezone" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
									@foreach ($timezones as $tz)
										<option value="{{ $tz->id }}">{{ __('messages.'.$tz->timezone) }}</option>
									@endforeach
								</select>
							</div>

							<div class="mb-3 col-md-6">
								<label class="mb-1"><strong>{{ __('messages.Password') }}</strong></label>
								<input type="password" class="form-control" id="password" name="password" placeholder="{{ __('messages.Enter password') }}">
								<span class="password_err text-danger" role="alert"></span>
							</div>

							<div class="mb-3 col-md-6">
								<label class="mb-1"><strong>{{ __('messages.Repeat Password') }}</strong></label>
								<input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="{{ __('messages.Choose Repeat Password') }}">
								<span class="password_confirmation_err text-danger" role="alert"></span>
							</div>

							{{-- <div class="mb-3 col-md-6">
								<label class="form-label">{{ __('messages.Status') }}</label>
								<select name="status" id="status" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
									<option value="Enable">{{ __('messages.Enable') }}</option>
									<option value="Disable">{{ __('messages.Disable') }}</option>
								</select>
							</div> --}}
						</div>

						<div class="submit-section">
							<button type="submit" class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	{{-- Edit agent modal --}}
	<div id="edit_agent" class="modal custom-modal fade" role="dialog">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
					<div class="modal-content">
							<div class="modal-header">
									<h5 class="modal-title">{{ __('messages.Edit agent') }}</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
									</button>
							</div>
							<div class="modal-body">
									<form action="{{ route('Agent: Update Agent') }}" method="POST" id="form_edit_agent">
											@csrf
											<div class="row">
												<input type="hidden" name="id" id="editAgentId">
												<div class="mb-3 col-md-6">
													<label class="form-label">{{ __('messages.agent Name') }}</label>
													<input type="text" class="form-control" name="agent_name" id="editAgentName">
													<span class="agent_name_err text-danger" role="alert"></span>
												</div>

												<div class="mb-3 col-md-6">
													<label class="form-label">{{ __('messages.agent Code') }}</label>
													<input type="text" class="form-control" name="agent_code" id="editAgentCode">
													<span class="agent_code_err text-danger" role="alert"></span>
												</div>

												<div class="mb-3 col-md-6">
													<label class="mb-1"><strong>{{ __('messages.Email') }}</strong></label>
													<input type="text" class="form-control" name="email" placeholder="{{ __('messages.Enter email') }}" id="editEmail">
													<span class="email_err text-danger" role="alert"></span>
												</div>

												<div class="mb-3 col-md-6">
													<label class="mb-1"><strong>{{ __('messages.Username') }}</strong></label>
													<input type="text" class="form-control" name="user_name" placeholder="{{ __('messages.Enter Username') }}" id="editUsername">
													<span class="user_name_err text-danger" role="alert"></span>
												</div>

												<div class="mb-3 col-md-6">
													<label class="mb-1"><strong>{{ __('messages.Mobile Number') }}</strong></label>
													<input type="text" class="form-control" name="mobile_number" placeholder="{{ __('messages.Enter Mobile Number') }}" id="editMobileNumber">
													<span class="mobile_number_err text-danger" role="alert"></span>
												</div>

												<div class="mb-3 col-md-6">
													<label class="form-label">{{ __('messages.Timezone') }}</label>
													<select name="timezone" id="editTimezone" class="form-control" aria-label="Default select example">
														<option value="">{{ __('messages.Select') }}</option>
														@foreach ($timezones as $tz)
															<option value="{{ $tz->id }}">{{ __('messages.'.$tz->timezone) }}</option>
														@endforeach
													</select>
												</div>

												<div class="mb-3 col-md-6">
													<label class="mb-1"><strong> {{ __('messages.Password') }}</strong></label>
													<input type="password" class="form-control" name="password" placeholder="{{ __('messages.Enter password') }}" id="editPassword">
													<span class="text-primary" id="leave" style="size: 10px">{{ __('messages.leave blank to use same password') }}</span>
													<span class="password_err text-danger" role="alert"></span>
												</div>

												<div class="mb-3 col-md-6">
													<label class="mb-1"><strong> {{ __('messages.Repeat Password') }}</strong></label>
													<input type="password" class="form-control" name="password_confirmation" placeholder="{{ __('messages.Choose Repeat Password') }}">
													<span class="password_confirmation_err text-danger" role="alert"></span>
												</div>

												<div class="mb-3 col-md-6">
													<label class="form-label">{{ __('messages.Status') }}</label>
													<select name="status" class="form-control" id="editStatus">
														<option value="Enable">{{ __('messages.Enable') }}</option>
														<option value="Disable">{{ __('messages.Disable') }}</option>
													</select>
												</div>
											</div>

											<div class="submit-section">
													<button type="submit" class="btn btn-primary shadow btn-xs me-1">{{ __('messages.Update') }}</button>
											</div>
									</form>
							</div>
					</div>
			</div>
	</div>

	<!-- Delete User Modal -->
	<div class="modal custom-modal fade" id="delete_user" role="dialog">
			<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
							<div class="modal-body">
									<div class="form-header">
											<h3>{{ __('messages.Delete agent') }}</h3>
											<p>{{ __('messages.Are you sure want to delete?') }}</p>
									</div>
									<div class="modal-btn delete-action">
											<form action="{{ route('Agent: Delete Agent') }}" method="POST">
													@csrf
													<input type="hidden" id="e_id" name="id">
													<div class="row">
															<div class="col-6">
																	<button type="submit"
																			class="btn btn-primary-cus continue-btn submit-btn">{{ __('messages.Delete') }}</button>
															</div>
															<div class="col-6">
																	<a href="javascript:void(0);" data-dismiss="modal"
																			class="btn btn-primary-cus cancel-btn">{{ __('messages.Cancel') }}</a>
															</div>
													</div>
											</form>
									</div>
							</div>
					</div>
			</div>
	</div>
	<!-- /Delete User Modal -->

	@section('script')
	<script>
		// datatables
		$(function () {
			var table = $('#merchantDataTable').DataTable({
				rowReorder: {
					selector: 'td:nth-child(2)'
				},
				responsive: true,
				processing: true,
				serverSide: true,
				order: [[ 3, "desc" ]],
				ajax: {
					url: "{{ route('Agent: View Agent') }}",
					data: function (d) {
						d.status = $('#status').val()
						d.search = $('#search').val()
					},
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
				},
				columns: [
					{data: 'agent_code'},
					{data: 'agent_name'},
					{data: 'status'},
					{data: 'created_at'},
					{data: 'action', searchable: false, sortable: false},
				],
				columnDefs: [
					{ responsivePriority: 1, targets: 0 },
					{ responsivePriority: 2, targets: 4 },
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
			$('.filterTable').change(function(){
				table.draw();
			});

			// search
			document.getElementById('search').addEventListener('input', (e) => {
				table.draw();
			})
		});

		// add new agent
		$("#add_agent_form").on('submit', function(e){
            if ($(this).valid()) {
			e.preventDefault();
			$.ajax({
				url: "{{ route('Agent: Add Agent') }}",
				type: 'POST',
				data: $('#add_agent_form').serialize(),
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

		// edit agent
		$("#form_edit_agent").on('submit', function(e){
            if ($(this).valid()) {
			e.preventDefault();
			$.ajax({
				url: "{{ route('Agent: Update Agent') }}",
				type: 'POST',
				data: $('#form_edit_agent').serialize(),
				success: function(data) {
					if ($.isEmptyObject(data.error)) {
						location.reload();
					} else {
						printErrorMsg(data);
                        $('#leave').hide();
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

		// remove error message
		function removeErrorMessage() {
			$('.agent_name_err').text('')
			$('.agent_code_err').text('')
			$('.user_name_err').text('')
			$('.email_err').text('')
			$('.mobile_number_err').text('')
			$('.timezone').text('')
			$('.password_err').text('')
			$('.password_confirmation_err').text('')
		}

		// show add agent model
		$(document).on('click', '#add-agent', function() {
            $("label.error").hide();
            $('#agent_name').val('');
            $('#agent_code').val('');
            $('#email').val('');
            $('#mobile_number').val('');
            $('#timezone').val('');
            $('#password_confirmation').val('');
           // $('#status').val('');
			removeErrorMessage();
            $('#username').val('');
            $('#password').val('');
		});

		$(document).on('click', '.edit_agent', function() {
            $("label.error").hide();
			removeErrorMessage();
			$('#editAgentId').val($(this).data('id'));
			$('#editAgentName').val($(this).data('agent_name'));
			$('#editAgentCode').val($(this).data('agent_code'));
			$('#editUsername').val($(this).data('user_name'));
			$('#editEmail').val($(this).data('email'));
			$('#editMobileNumber').val($(this).data('mobile_number'));
			$('#editTimezone').val($(this).data('timezone'));
			$('#editStatus').val($(this).data('status'));
            $('#editPassword').val('');
		});

        $(document).on('click', '.delete_user', function() {
            $('#e_id').val($(this).data('id'));
        });
    </script>
    {{-- Jquery Validation --}}
    <style>
        label.error {
            color: #dc3545;
            font-size: 14px;
        }
    </style>
    <script>
        $.validator.addMethod("alphanumericnospace", function(value, element) {
            return this.optional(element) || /^[A-Za-z0-9]+$/.test(value);
        });
        $.validator.addMethod("url", function(value, element) {
            return this.optional(element) || /^(http|https)?:\/\/[a-zA-Z0-9-\.]+\.[a-z]{2,4}/.test(value);
        });
        $.validator.addMethod("nospace", function(value, element) {
                return value.indexOf(" ") < 0 && value != "";
        });
        $.validator.addMethod("mobile", function(value, element) {
            return this.optional(element) || /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/.test(value);
        });

        //Add Form Validation
        $("#add_agent_form").validate({
            rules: {
                agent_name: {
                    required: true
                },
                agent_code: {
                    required: true,
                    alphanumericnospace: true

                },
                email: {
                    required: true,
                    email: true
                },
                user_name: {
                    required: true,
                    nospace: true
                },
                mobile_number: {
                    required: true,
                    mobile: true
                },
				timezone: {
                    required: true
                },
                password: {
                    required: true
                },
                password_confirmation: {
                    required: true,
                    equalTo: '#password'
                }/* ,
                status: {
                    required: true
                } */
            }
        })

        //Edit Form Validation
        $("#form_edit_agent").validate({
            rules: {
                agent_name: {
                    required: true
                },
                agent_code: {
                    required: true,
                    alphanumericnospace: true

                },
                email: {
                    required: true,
                    email: true
                },
                user_name: {
                    required: true,
                    nospace: true
                },
                mobile_number: {
                    required: true,
                    mobile: true
                },
				timezone: {
                    required: true
                },
                password: {
                    required: false
                },
                password_confirmation: {
                    required: false,
                }/* ,
                status: {
                    required: true
                } */
            }
        })
    </script>
@endsection
@endsection
