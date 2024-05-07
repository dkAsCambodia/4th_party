@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Account Setting') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Account Setting') }}</h4>
                            <button type="submit" class="btn btn-danger shadow btn-xs me-1" id="add-account"
                                style="float: right;" data-toggle="modal" data-target="#add_account">
                                {{ __('messages.Add') }}
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="accountDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Account Type') }}</th>
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

    <!-- Add Account Modal -->
    <div id="add_account" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add New Account Type') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add_account_form" action="{{ route('setting.account.add') }}" method="POST">
                        @csrf
                        <?php $validMsg = __('messages.Please fill out this field'); ?>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Type') }}</label>
                                <input type="text" class="form-control" name="account_type" value="" id="account_type">
                                <span class="account_type_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="{{ $_GET['status'] ?? '' }}" selected>{{ __('messages.Select') }}
                                    </option>
                                    <option value="active">{{ __('messages.active') }}</option>
                                    <option value="inactive">{{ __('messages.inactive') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit"
                                class="btn btn-danger shadow btn-xs me-1 add_record">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Account Modal -->

    <!-- Edit Account Modal -->
    <div id="update_account" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Account Type') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form_update_account" action="{{ route('setting.account.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Type') }}</label>
                                <input type="text" class="form-control" name="account_type" id="editAccountType">
                                <span class="account_type_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="active">{{ __('messages.active') }}</option>
                                    <option value="inactive">{{ __('messages.inactive') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit"
                                class="btn btn-danger shadow btn-xs me-1 add_record">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Account Modal -->

    <!-- Delete Account Modal -->
    <div class="modal custom-modal fade" id="delete_account" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Account Type') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('setting.account.delete') }}" method="POST">
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
    <!-- /Delete Account Modal -->

@section('script')
    <script>
        // datatables
        $(function() {
            $('#accountDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [
                    [2, "desc"]
                ],
                ajax: {
                    url: "{{ route('setting.account.list') }}",
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'account_type'
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
                ],
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 3 },
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

        // add new account
        $("#add_account_form").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('setting.account.add') }}",
                    type: 'POST',
                    data: $('#add_account_form').serialize(),
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

        // edit user
        $("#form_update_account").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('setting.account.update') }}",
                    type: 'POST',
                    data: $('#form_update_account').serialize(),
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

        //print error message
        function printErrorMsg(msg) {
            $.each(msg.error, function(key, value) {
                $('.' + key + '_err').text(value[0]);
            });
        }

        //Remove Error Message
        $(document).on('click', '#add-account', function() {
            $("label.error").hide();
            $('#account_type').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        //remove error message
        function removeErrorMessage() {
            $('.account_type_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.update_account', function() {
            $("label.error").hide();
            removeErrorMessage();
            $('#editId').val($(this).data('id'));
            $('#editAccountType').val($(this).data('account_type'));
            $('#editStatus').val($(this).data('status'));
        });

        $(document).on('click', '.delete_account', function() {
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
        $.validator.addMethod("alphabetsnspace", function(value, element) {
            return this.optional(element) || /^[a-zA-Z ]*$/.test(value);
        });

        /* var en = {
                required: "Please enter account type",
                alphabetsnspace: "Please enter only letters"
            },
            ch = {
                required: "请输入账户类型",
                alphabetsnspace: "请只输入字母"
            }; */

        //Add Form Validation
        $("#add_account_form").validate({
            rules: {
                account_type: {
                    required: true,
                    alphabetsnspace: true
                },
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#form_update_account").validate({
            rules: {
                account_type: {
                    required: true,
                    alphabetsnspace: true
                }
            }
        })
    </script>
@endsection
@endsection
