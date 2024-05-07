@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Bank Account') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Bank Account') }}</h4>
                            @if (Auth()->user()->role_name === 'Agent' || Auth()->user()->role_name === 'Merchant')
                                <button type="submit" class="btn btn-danger shadow btn-xs me-1 "
                                    style="float: right;" data-toggle="modal" data-target="#add_account" id="add-account">
                                    {{ __('messages.Add') }}
                                </button>
                            @endif
                        </div>

                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Account Type') }}</label>
                                <select id="bank" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($account_type as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->account_type }}</option>
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
                                <table id="bankDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Account Type') }}</th>
                                            <th>{{ __('messages.Bank Name') }}</th>
                                            <th>{{ __('messages.Account Name') }}</th>
                                            <th>{{ __('messages.Account Number') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Default') }}</th>
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

    <!-- Add Modal -->
    <div id="add_account" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add New Bank Account') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add_account_form" action="{{ route('Account: Add Account') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Type') }}</label>
                                <select name="account_type" id="account_type" class="form-control">
                                    <option value="">{{ trans('messages.Select') }}</option>
                                    @foreach ($account_type as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->account_type }}</option>
                                    @endforeach
                                </select>
                                <span class="account_type_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Bank Name') }}</label>
                                <input type="text" class="form-control" name="bank_name" id="bank_name">
                                <span class="bank_name_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Name') }}</label>
                                <input type="text" class="form-control" name="account_name" id="account_name">
                                <span class="account_name_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Number') }}</label>
                                <input type="text" class="form-control" name="account_number" id="account_number">
                                <span class="account_number_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Remark') }}</label>
                                <input type="text" class="form-control" name="remark" id="remark">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="enable">{{ __('messages.enable') }}</option>
                                    <option value="disable">{{ __('messages.disable') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Default') }}</label>&nbsp;&nbsp;
                                <input type="checkbox" name="default" id="addDefault" />
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Expense Modal -->

    <!-- Edit Expense Modal -->
    <div id="update_account" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Bank Account') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form_update_account" action="{{ route('Account: Edit Account') }}" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Type') }}</label>
                                <select required name="account_type" id="editAccountType" class="form-control">
                                    <option value="">{{ trans('messages.Select') }}</option>
                                    @foreach ($account_type as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->account_type }}</option>
                                    @endforeach
                                </select>
                                <span class="account_type_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Bank Name') }}</label>
                                <input required type="text" class="form-control" name="bank_name" id="editBankName">
                                <span class="bank_name_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Name') }}</label>
                                <input required type="text" class="form-control" name="account_name" id="editAccountName">
                                <span class="account_name_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account Number') }}</label>
                                <input required type="text" class="form-control" name="account_number" id="editAccountNumber">
                                <span class="account_number_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Remark') }}</label>
                                <input type="text" class="form-control" name="remark" id="editRemark">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select required name="status" id="editStatus" class="form-control">
                                    <option value="enable">{{ __('messages.enable') }}</option>
                                    <option value="disable">{{ __('messages.disable') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Default') }}</label>&nbsp;&nbsp;
                                <input type="checkbox" name="default" id="editIsDefault" />
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Expense Modal -->

    <!-- Delete User Modal -->
    <div class="modal custom-modal fade" id="delete_account" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Account') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('Account: Delete Account') }}" method="POST">
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
                var table = $('#bankDataTable').DataTable({
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    order: [[ 4, "desc" ]],
                    ajax: {
                        url: "{{ route('Account: View Agent Account') }}",
                        data: function (d) {
                            d.bank = $('#bank').val()
                            d.status = $('#status').val()
                            d.search = $('#search').val()
                        },
                        error: function (jqXHR) {
                            if (jqXHR && jqXHR.status == 401) {location.reload()}
                        },
                    },
                    columns: [
                        {data: 'account_type', sortable: false, searchable: false},
                        {data: 'bank_name'},
                        {data: 'account_name'},
                        {data: 'account_number'},
                        {data: 'created_at'},
                        {data: 'status'},
                        {data: 'default'},
                        {data: 'action', sortable: false, searchable: false},
                    ],
                    columnDefs: [
                        { responsivePriority: 1, targets: 1 },
                        { responsivePriority: 2, targets: 7 },
                        { responsivePriority: 3, targets: 5 },
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

            // add new
            $("#add_account_form").on('submit', function(e){
                if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Account: Add Account') }}",
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

            // edit
            $("#form_update_account").on('submit', function(e){
                if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Account: Edit Account') }}",
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

            // print error message
            function printErrorMsg(msg) {
                $.each(msg.error, function(key, value) {
                    $('.' + key + '_err').text(value[0]);
                });
            }

            // show add url model
            $(document).on('click', '#add-account', function() {
                $("label.error").hide();
                $('#account_type').val('');
                $('#bank_name').val('');
                $('#account_name').val('');
                $('#account_number').val('');
                $('#remark').val('');
                $('#status').val('');
                $('#addDefault').prop("checked", false)
                removeErrorMessage();
            });

            // remove error message
            function removeErrorMessage() {
                $('.account_type_err').text('')
                $('.bank_name_err').text('');
                $('.account_name_err').text('');
                $('.account_number_err').text('');
                $('.status_err').text('');
            }

            $(document).on('click', '.update_account', function() {
                $('#editId').val($(this).data('id'));
                $('#editAccountType').val($(this).data('bank_id'));
                $('#editBankName').val($(this).data('bank_name'));
                $('#editAccountName').val($(this).data('account_name'));
                $('#editAccountNumber').val($(this).data('account_number'));
                $('#editRemark').val($(this).data('remark'));
                $('#editStatus').val($(this).data('status'));

                if ($(this).data('default') == 'yes') {
                    $('#editIsDefault').prop("checked", true)
                } else {
                    $('#editIsDefault').prop("checked", false)
                }
            });

            // delete
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
        $.validator.addMethod("alphanumericnospace", function(value, element) {
            return this.optional(element) || /^[A-Za-z0-9]+$/.test(value);
        });
        $.validator.addMethod("alphabetsnspace", function(value, element) {
            return this.optional(element) || /^[a-zA-Z ]*$/.test(value);
        });
        $.validator.addMethod("numbersndash", function(value, element) {
            return this.optional(element) || /^[0-9\s-]+$/.test(value);
        });

        //Add Form Validation
        $("#add_account_form").validate({
            rules: {
                account_type: {
                    required: true
                },
                bank_name: {
                    required: true,
                    alphabetsnspace: true

                },
                account_name: {
                    required: true,
                    alphabetsnspace: true
                },
                account_number: {
                    required: true,
                    numbersndash: true
                },
                remark: {
                    required: false
                },
                status: {
                    required: true
                },
                default: {
                    required: false
                }
            }
        })

        //Edit Form Validation
        $("#form_update_account").validate({
            rules: {
                account_type: {
                    required: true
                },
                bank_name: {
                    required: true,
                    alphabetsnspace: true

                },
                account_name: {
                    required: true,
                    alphabetsnspace: true
                },
                account_number: {
                    required: true,
                    numbersndash: true
                },
                remark: {
                    required: false
                },
                status: {
                    required: true
                },
                default: {
                    required: false
                }
            }
        })
    </script>
    @endsection
@endsection
