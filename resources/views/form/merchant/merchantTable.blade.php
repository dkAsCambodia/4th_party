@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('messages.Merchant Management') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Merchant Management') }}</h4>
                            @if (auth()->user()->can('Merchant: Add Merchant'))
                                <button type="submit" id="add-merchant" class="btn btn-danger shadow btn-xs me-1 "
                                    style="float: right;" data-toggle="modal" data-target="#add_merchant">
                                    {{ __('messages.Add Merchant') }}
                                </button>
                            @endif
                        </div>
                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Agent Name') }}</label>
                                <select id="agent" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($agents as $ag)
                                        <option value="{{ $ag->id }}">{{ $ag->agent_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="status" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
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
                                <table id="merchantDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Merchant Code') }}</th>
                                            <th>{{ __('messages.Merchant Name') }}</th>
                                            <th>{{ __('messages.Agent Name') }} </th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Create Date') }}</th>
                                            <th>{{ __('messages.Action') }}</th>
                                            <th>{{ __('messages.Payment Map') }}</th>
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
    $validMsgDropDwn = __('messages.Please fill out this field');
    ?>

    {{-- Add new Merchant Modal --}}
    <div id="add_merchant" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Merchant') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="add_merchant_form" action="{{ route('Merchant: Add Merchant') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Name') }}</label>
                                <input type="text" class="form-control" name="merchant_name" id="merchant_name">
                                <span class="merchant_name_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Code') }}</label>
                                <input type="text" class="form-control" name="merchant_code" id="merchant_code">
                                <span class="merchant_code_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Agent') }}</label>
                                <select name="agent" id="agent" class="form-control" aria-label="Select Agent">
                                    <option value="">{{ __('messages.Select Agent') }}</option>
                                    @foreach ($agents as $ag)
                                        <option value="{{ $ag->id }}">{{ $ag->agent_name }}</option>
                                    @endforeach
                                </select>
                                <span class="agent_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Username') }}</strong></label>
                                <input type="text" class="form-control" id="username" name="user_name"
                                    autocomplete="false" placeholder="{{ __('messages.Enter Username') }}">
                                <span class="user_name_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Email') }}</strong></label>
                                <input type="text" class="form-control" name="email" id="email"
                                    placeholder="{{ __('messages.Enter email') }}">
                                <span class="email_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Mobile Number') }}</strong></label>
                                <input type="text" class="form-control" name="mobile_number" id="mobile_number"
                                    placeholder="{{ __('messages.Enter Mobile Number') }}">
                                <span class="mobile_number_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Password') }}</strong></label>
                                <input type="password" class="form-control" id="password" name="password"
                                    autocomplete="false" placeholder="{{ __('messages.Enter password') }} ">
                                <span class="password_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Repeat Password') }}</strong></label>
                                <input type="password" class="form-control" name="password_confirmation"
                                    id="password_confirmation" placeholder="{{ __('messages.Choose Repeat Password') }}">
                                <span class="password_confirmation_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
								<label class="form-label">{{ __('messages.Timezone') }}</label>
								<select name="timezone" id="timezone" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
									@foreach ($timezones as $tz)
                                        <option value="{{ $tz->id }}">{{ __('messages.'.$tz->timezone) }}</option>
									@endforeach
								</select>
                                <span class="timezone_err text-danger" role="alert"></span>
							</div>

                             <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Domain Name') }}</label>
                                <input type="text" class="form-control" name="url" id="url">
                                <span class="url_err text-danger" role="alert"></span>
                            </div>

                           {{--  <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select required name="status" id="status" class="form-control"
                                    aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
                            </div> --}}
                        </div>

                        <div class="submit-section">
                            <button type="submit"
                                class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Merchant Modal --}}
    <div id="edit_user" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Merchant') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('Merchant: Update Merchant') }}" method="POST" id="form_edit_merchant">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editMerchantId">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Name') }}</label>
                                <input type="text" class="form-control" name="merchant_name" id="editMerchantName">
                                <span class="merchant_name_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Code') }}</label>
                                <input type="text" class="form-control" name="merchant_code" id="editMerchantCode" readonly>
                                <span class="merchant_code_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Agent') }}</label>
                                <select name="agent" id="editAgent" class="form-control" aria-label="Select Agent">
                                    <option value="">{{ __('messages.Select Agent') }}</option>
                                    @foreach ($agents as $ag)
                                        <option value="{{ $ag->id }}">{{ $ag->agent_name }}</option>
                                    @endforeach
                                </select>
                                <span class="agent_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Username') }}</strong></label>
                                <input type="text" class="form-control" name="user_name" id="editUsername"
                                    placeholder="{{ __('messages.Enter Username') }}">
                                <span class="user_name_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Email') }}</strong></label>
                                <input type="text" class="form-control" name="email" id="editEmail"
                                    placeholder="{{ __('messages.Enter email') }}">
                                <span class="email_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Mobile Number') }}</strong></label>
                                <input type="text" class="form-control" name="mobile_number"
                                    placeholder="{{ __('messages.Enter Mobile Number') }}" id="editMobileNumber">
                                <span class="mobile_number_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Password') }}</strong></label>
                                <input type="password" class="form-control" name="password"
                                    placeholder="{{ __('messages.Enter password') }}" id="editPassword">
                                <span class="text-primary"
                                    style="size: 10px">{{ __('messages.leave blank to use same password') }}</span>
                                <span class="password_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="mb-1"><strong>{{ __('messages.Repeat Password') }}</strong></label>
                                <input type="password" class="form-control" name="password_confirmation"
                                    placeholder="{{ __('messages.Choose Repeat Password') }}">
                                <span class="password_confirmation_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
								<label class="form-label">{{ __('messages.Timezone') }}</label>
								<select name="timezone" id="editTimezone" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
									@foreach ($timezones as $tz)
                                        <option value="{{ $tz->id }}">{{ __('messages.'.$tz->timezone) }}</option>
									@endforeach
								</select>
                                <span class="timezone_err text-danger" role="alert"></span>
							</div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Domain Name') }}</label>
                                <input type="text" class="form-control" name="url" id="editUrl">
                                <span class="url_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select required name="status" class="form-control" id="editStatus">
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit"
                                class="btn btn-primary shadow btn-xs me-1">{{ __('messages.Update') }}</button>
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
                        <h3>{{ __('messages.Delete Merchant') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('Merchant: Delete Merchant') }}" method="POST">
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
        $(function() {
            var table = $('#merchantDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [
                    [4, "desc"]
                ],
                ajax: {
                    url: "{{ route('Merchant: View Merchant') }}",
                    data: function(d) {
                        d.agent = $('#agent').val()
                        d.status = $('#status').val()
                        d.search = $('#search').val()
                    },
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'merchant_code'
                    },
                    {
                        data: 'merchant_name'
                    },
                    {
                        data: 'agent_name',
                        name: 'agent.agent_name',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'action',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'payment_map',
                        searchable: false,
                        sortable: false
                    },
                ],
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 5 },
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

            // filter by dropdown
            $('.filterTable').change(function() {
                table.draw();
            });

            // search
            document.getElementById('search').addEventListener('input', (e) => {
                table.draw();
            })

            // reset filter
            $('#reset').on('click', function() {
                $('#status').val('')
                $('#agent').val('')
                $('#search').val('')
                table.draw();
            })
        });

        // add new merchant
        $("#add_merchant_form").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Merchant: Add Merchant') }}",
                    type: 'POST',
                    data: $('#add_merchant_form').serialize(),
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

        // edit merchant
        $("#form_edit_merchant").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Merchant: Update Merchant') }}",
                    type: 'POST',
                    data: $('#form_edit_merchant').serialize(),
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

        // print error message
        function printErrorMsg(msg) {
            $('.text-danger').text('');
            $.each(msg.error, function(key, value) {
                $('.' + key + '_err').text(value[0]);
            });
        }

        // remove error message
        function removeErrorMessage() {
            $('.merchant_name_err').text('')
            $('.merchant_code_err').text('')
            $('.agent_err').text('')
            $('.user_name_err').text('')
            $('.email_err').text('')
            $('.mobile_number_err').text('')
            $('.timezone_err').text('')
            $('.url_err').text('')
            $('.password_err').text('')
            $('.password_confirmation_err').text('')
        }

        // show add merchant model
        $(document).on('click', '#add-merchant', function() {
            $("label.error").hide();
            $('#merchant_name').val('');
            $('#merchant_code').val('');
            $('#agent').val('');
            $('#email').val('');
            $('#mobile_number').val('');
            $('#password_confirmation').val('');
            //$('#status').val('');
            removeErrorMessage();
            $('#username').val('');
            $('#password').val('');
            $('#timezone').val('');
            $('#url').val('');
            $('#url').attr('maxlength', '100');

        });

        // show edit merchant modal
        $(document).on('click', '.edit_merchant', function() {
            $("label.error").hide();
            removeErrorMessage();
            $('#editMerchantId').val($(this).data('id'));
            $('#editMerchantName').val($(this).data('merchant_name'));
            $('#editMerchantCode').val($(this).data('merchant_code'));
            $('#editAgent').val($(this).data('agent'));
            $('#editUsername').val($(this).data('username'));
            $('#editEmail').val($(this).data('email'));
            $('#editMobileNumber').val($(this).data('mobile_number'));
            $('#editStatus').val($(this).data('status'));
            $('#editTimezone').val($(this).data('timezone'));
            $('#editUrl').val($(this).data('url'));
            $('#editUrl').attr('maxlength', '255');
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
        $.validator.addMethod("url", function(value, element) {
            return this.optional(element) || /^(http|https)?:\/\/[a-zA-Z0-9-\.]+\.[a-z]{2,4}/.test(value);
        });

        //Add Form Validation
        $("#add_merchant_form").validate({
            rules: {
                merchant_name: {
                    required: true
                },
                merchant_code: {
                    required: true,
                    alphanumericnospace: true

                },
                /* agent: {
                    required: true
                }, */
                user_name: {
                    required: true,
                    nospace: true
                },
                email: {
                    required: true,
                    email: true
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
                },
                url: {
                    required: true,
                    url: true
                }
                /* ,
                status: {
                    required: true
                } */
            }
        })

        //Edit Form Validation
        $("#form_edit_merchant").validate({
            rules: {
                merchant_name: {
                    required: true
                },
                merchant_code: {
                    required: true,
                    alphanumericnospace: true

                },
                /* agent: {
                    required: true
                }, */
                user_name: {
                    required: true,
                    nospace: true
                },
                email: {
                    required: true,
                    email: true
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
                    required: false
                },
                url: {
                    required: true,
                    url: true
                }
                /* ,
                status: {
                    required: true
                } */
            }
        })
    </script>
@endsection
@endsection
