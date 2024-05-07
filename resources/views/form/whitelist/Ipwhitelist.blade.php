@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.IP Whitelisting') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.IP Whitelisting') }}</h4>
                            @if (auth()->user()->can('add/whitelist'))
                                <button type="submit" id="add-whitelist" class="btn btn-danger shadow btn-xs me-1 "
                                    style="float: right;" data-toggle="modal" data-target="#add_merchant">
                                    {{ __('messages.Add IP Whitelist') }}
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="whitelistDataTable" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Sr. No.') }}</th>
                                            <th>{{ __('messages.IP Address') }}</th>
                                            <th>{{ __('messages.Remark') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
                                            @if (auth()->user()->can('edit/whitelist') ||
                                                    auth()->user()->can('whitelist/delete'))
                                                <th>{{ __('messages.Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-5">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $validMsg = __('messages.Please fill out this field'); ?>

    <!-- add new whitelist Modal -->
    <div id="add_merchant" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add IP Whitelist') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('add/whitelist') }}" method="POST" id="add_new_whitelist">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.IP Address') }}</label>
                                <input type="text" class="form-control" name="address" id="address">
                                <span class="address_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Remarks') }}</label>
                                <input type="text" class="form-control" name="remarks" id="remarks">
                                <span class="remarks_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" id="status" class="form-control"
                                    aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="1">{{ __('messages.Enable') }}</option>
                                    <option value="0">{{ __('messages.Disable') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
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
    <!-- /add new whitelist Modal -->

    <!-- edit whitelist Modal -->
    <div id="edit_wip" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Whitelist IP') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('edit/whitelist') }}" method="POST" id="edit_whitelist">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Address') }}</label>
                                <input type="text" class="form-control" name="address" id="editAddress">
                                <span class="address_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Remarks') }}</label>
                                <input type="text" class="form-control" name="remarks" id="editRemarks">
                                <span class="remarks_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="editStatus" name="status" class="form-control" class="status">
                                    <option value="1">{{ __('messages.Enable') }}</option>
                                    <option value="0">{{ __('messages.Disable') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
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
    <!-- /edit whitelist Modal -->

    <!-- Delete User Modal -->
    <div class="modal custom-modal fade" id="delete_whitelist" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Whitelisted IP') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('whitelist/delete') }}" method="POST">
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
        // DataTables
        $(function() {
            var table = $('#whitelistDataTable').DataTable({
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
                    url: "{{ route('whitelist/list') }}",
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'address'
                    },
                    {
                        data: 'remarks'
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
                    { responsivePriority: 1, targets: 1 },
                    { responsivePriority: 2, targets: 5 },
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

        // add new whitelist
        $("#add_new_whitelist").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('add/whitelist') }}",
                    type: 'POST',
                    data: $('#add_new_whitelist').serialize(),
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

        // edit whitelist
        $("#edit_whitelist").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('edit/whitelist') }}",
                    type: 'POST',
                    data: $('#edit_whitelist').serialize(),
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

        // show add whitelist model
        $(document).on('click', '#add-whitelist', function() {
            $("label.error").hide();
            $('#address').val('');
            $('#remarks').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        // remove error message
        function removeErrorMessage() {
            $('.address_err').text('')
            $('.status_err').text('')
            $('.remarks_err').text('')
        }

        $(document).on('click', '.edit_wip', function() {
            $("label.error").hide();
            removeErrorMessage();
            $('#editId').val($(this).data('id'));
            $('#editAddress').val($(this).data('address'));
            $('#editRemarks').val($(this).data('remarks'));
            $('#editStatus').val($(this).data('status'));
        });

        // delete whitelist
        $(document).on('click', '.delete_whitelist', function() {
            var _this = $(this).parents('tr');
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
        $.validator.addMethod('IPChecker', function(value) {
            return value.match(/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/);
        });

        //Add Form Validation
        $("#add_new_whitelist").validate({
            rules: {
                address: {
                    required: true,
                    IPChecker: true
                },
                remarks: {
                    required: false
                },
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#edit_whitelist").validate({
            rules: {
                address: {
                    required: true,
                    IPChecker: true
                },
                remarks: {
                    required: false
                },
                status: {
                    required: true
                }
            }
        })
    </script>
@endsection
@endsection
