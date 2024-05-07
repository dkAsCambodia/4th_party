@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Timezone Setting') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Timezone Setting') }}</h4>
                            <button type="submit" class="btn btn-danger shadow btn-xs me-1" id="add-timezone"
                                style="float: right;" data-toggle="modal" data-target="#add_timezone">
                                {{ __('messages.Add') }}
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="timezoneDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Timezone') }}</th>
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

    <!-- Add Timezone Modal -->
    <div id="add_timezone" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add New Timezone') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add_timezone_form" action="{{ route('setting.timezone.add') }}" method="POST">
                        @csrf
                        <?php $validMsg = __('messages.Please fill out this field'); ?>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Timezone') }}</label>
                                <input type="text" class="form-control" name="timezone" value="" id="timezone">
                                <span class="timezone_err text-danger" role="alert"></span>
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
    <!-- Add Timezone Modal -->

    <!-- Edit Timezone Modal -->
    <div id="update_timezone" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Timezone') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form_update_timezone" action="{{ route('setting.timezone.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Timezone') }}</label>
                                <input type="text" class="form-control" name="timezone" id="editTimezone">
                                <span class="timezone_err text-danger" role="alert"></span>
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
    <!-- Edit Timezone Modal -->

    <!-- Delete Timezone Modal -->
    <div class="modal custom-modal fade" id="delete_timezone" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Timezone') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('setting.timezone.delete') }}" method="POST">
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
    <!-- /Delete Timezone Modal -->

@section('script')
    <script>
        // datatables
        $(function() {
            $('#timezoneDataTable').DataTable({
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
                    url: "{{ route('setting.timezone.list') }}",
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'timezone'
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

        // add new timezone
        $("#add_timezone_form").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('setting.timezone.add') }}",
                    type: 'POST',
                    data: $('#add_timezone_form').serialize(),
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

        // edit timezone
        $("#form_update_timezone").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('setting.timezone.update') }}",
                    type: 'POST',
                    data: $('#form_update_timezone').serialize(),
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
        $(document).on('click', '#add-timezone', function() {
            $("label.error").hide();
            $('#timezone').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        //remove error message
        function removeErrorMessage() {
            $('.timezone_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.update_timezone', function() {
            $("label.error").hide();
            removeErrorMessage();
            $('#editId').val($(this).data('id'));
            $('#editTimezone').val($(this).data('timezone'));
            $('#editStatus').val($(this).data('status'));
        });

        $(document).on('click', '.delete_timezone', function() {
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
        //Add Form Validation
        $("#add_timezone_form").validate({
            rules: {
                timezone: {
                    required: true
                },
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#form_update_timezone").validate({
            rules: {
                timezone: {
                    required: false
                },
                status: {
                    required: false
                }

            }
        })
    </script>
@endsection
@endsection
