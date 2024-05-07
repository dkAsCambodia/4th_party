@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('messages.Payment Method') }}</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payment Method') }}</h4>
                            @if (auth()->user()->can('Method: Add Method'))
                                <button type="submit" id="add-method" class="btn btn-danger shadow btn-xs me-1 add_record"
                                    style="float: right;" data-toggle="modal" data-target="#add_method">
                                    {{ __('messages.Add Method') }}
                                </button>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="methodDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Method Name') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Create Date') }}</th>
                                            @if (auth()->user()->can('Method: Update Method') ||
                                                    auth()->user()->can('Method: Delete Method'))
                                                <th>{{ __('messages.Action') }}</th>
                                            @endif
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

    <?php $validMsg = __('messages.Please fill out this field'); ?>

    <!-- Add new method Modal -->
    <div id="add_method" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Method') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('Method: Add Method') }}" method="POST" id="add_new_method">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Method Name') }}</label>
                                <input type="text" class="form-control" name="method_name" id="method_name">
                                <span class="method_name_err text-danger" role="alert"></span>
                            </div>
                            <input type="hidden" name="status" value="Enable">
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
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
    <!-- /Add new method Modal -->
    <!-- Edit Expense Modal -->
    <div id="edit_user" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Method') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('Method: Update Method') }}" method="POST" id="form_edit_method">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editMethodId">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Method Name') }}</label>
                                <input type="text" class="form-control" name="method_name" id="editMethodName">
                                <span class="method_name_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="editStatus" name="status" class="form-control"
                                    aria-label="Default select example">
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
                            </div>
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
    <!-- /Edit Expense Modal -->

    <!-- Delete User Modal -->
    <div class="modal custom-modal fade" id="delete_user" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Method') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('Method: Delete Method') }}" method="POST">
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
        $(function() {
            $('#methodDataTable').DataTable({
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
                    url: "{{ route('Method: View Method') }}",
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [
                    {
                        data: 'method_name'
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

        // add new method
        $("#add_new_method").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Method: Add Method') }}",
                    type: 'POST',
                    data: $('#add_new_method').serialize(),
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

        // edit method
        $("#form_edit_method").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Method: Update Method') }}",
                    type: 'POST',
                    data: $('#form_edit_method').serialize(),
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

        // show add method model
        $(document).on('click', '#add-method', function() {
            $("label.error").hide();
            $('#method_name').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        // remove error message
        function removeErrorMessage() {
            $('.method_name_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.edit_method', function() {
            removeErrorMessage();
            $('#editMethodId').val($(this).data('id'));
            $('#editMethodName').val($(this).data('name'));
            $('#editStatus').val($(this).data('status'));
        });

        // delete method
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
         $.validator.addMethod("alphnumericnspace", function(value, element) {
              return this.optional(element) || /^[a-zA-Z0-9 ]*$/.test(value);
        });

        //Add Form Validation
        $("#add_new_method").validate({
            rules: {
                method_name: {
                    required: true,
                    alphnumericnspace: true
                },
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#form_edit_method").validate({
            rules: {
                method_name: {
                    required: true,
                    alphnumericnspace: true
                }
            }
        })
    </script>
@endsection
@endsection
