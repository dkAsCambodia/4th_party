@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Payment Gateway') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payment Gateway') }}</h4>
                            @if (auth()->user()->can('Channel: Add Channel'))
                                <button id="add-channel" type="submit" class="btn btn-danger shadow btn-xs me-1 add_record"
                                    style="float: right;" data-toggle="modal" data-target="#add_channel">
                                    {{ __('messages.Add Payment Gateway') }}
                                </button>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="channelDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Gateway Name') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Create Date') }}</th>
                                            @if (auth()->user()->can('Channel: Update Channel') ||
                                                    auth()->user()->can('Channel: Delete Channel'))
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
    <!-- Add new channel Modal -->
    <div id="add_channel" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Payment Gateway') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('Channel: Add Channel') }}" method="POST" id="add_new_channel">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Gateway Name') }}</label>
                                <input type="text" class="form-control" name="channel_name" id="channel_name">
                                <span class="channel_name_err text-danger" role="alert"></span>
                            </div>
                            <input type="hidden" name="status" value="Enable">
                            <label class="form-label mb-3">{{ __('messages.Add Parameter') }}</label>
                            <div id="rowAdd">
                                <div class="input-group mb-3 col-md-6">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-danger" id="DeleteRowAdd" type="button">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <input type="text" class="form-control m-input" name="parameter_name[]">
                                </div>
                            </div>
                            <div id="newinputAdd"></div>

                            <div class="input-group mb-3 col-md-6">
                                <div class="input-group-prepend">
                                    <button id="rowAdderAdd" type="button" class="btn btn-dark">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            {{-- <button id="rowAdderAdd" type="button" class="btn btn-dark col-md-1">
                                <i class="fa fa-plus"></i>
                            </button> --}}

                            <div class="submit-section">
                                <button type="submit"
                                    class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add new channel Modal -->

    <!-- Edit channel Modal -->
    <div id="edit_channel" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Payment Gateway') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('Channel: Update Channel') }}" method="POST" id="form_edit_channel">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="editChannelId" name="id">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Gateway Name') }}</label>
                                <input type="text" class="form-control" name="channel_name" id="editChannelName">
                                <span class="channel_name_err text-danger" role="alert"></span>
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
                        <label class="form-label mb-3">{{ __('messages.Add Parameter') }}</label>
                        <div class="row" id="appendParameter"></div>

                        <div class="submit-section">
                            <button type="submit"
                                class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit channel Modal -->

    <!-- Add Parameter Modal -->
    {{-- <div id="add_parameter" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Parameter') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="appendParameter">

                <div id="rowAdd">
                    <div class="input-group mb-3 col-md-6">
                        <div class="input-group-prepend">
                            <button class="btn btn-danger" id="DeleteRowAdd" type="button">
                                <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <input type="text" class="form-control m-input" name="parameter_name[]">
                </div>
            </div>
            <div id="newinputAdd"></div>
        </div>
            </div>
        </div>
    </div> --}}
    <!-- /Add Parameter Modal -->
    <!-- Delete channel Modal -->
    <div class="modal custom-modal fade" id="delete_channel" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Payment Gateway') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('Channel: Delete Channel') }}" method="POST">
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
        $("body").on("click", "#rowAdderAdd", function() {
            // $("#rowAdderAdd").click(function() {
            // alert('ppp');
            newRowAdd =
                '<div id="rowAdd"> <div class="input-group mb-3 col-md-6">' +
                '<div class="input-group-prepend">' +
                '<button class="btn btn-danger" id="DeleteRowAdd" type="button">' +
                '<i class="bi bi-trash"></i> {{-- __('messages.Delete') --}}</button> </div>' +
                '<input type="text" class="form-control m-input" name="parameter_name[]"> </div> </div>';

            $('#newinputAdd').append(newRowAdd);
        });

        $("body").on("click", "#DeleteRowAdd", function() {
            // alert('ppppddd');
            $(this).parents("#rowAdd").remove();
        })
    </script>
    <script>
        function addParameter(id) {
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                document.getElementById("appendParameter").innerHTML =
                    this.responseText;
            }
            xhttp.open("GET", "{{ url('admin/open-form-add-parameter') }}?id=" + id);
            xhttp.send();
        }
        // datatables
        $(function() {
            $('#channelDataTable').DataTable({
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
                    url: "{{ route('Channel: View Channel') }}",
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'channel_name'
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
                columnDefs: [{
                        responsivePriority: 1,
                        targets: 0
                    },
                    {
                        responsivePriority: 2,
                        targets: 3
                    },
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

        // add new channel
        $("#add_new_channel").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Channel: Add Channel') }}",
                    type: 'POST',
                    data: $('#add_new_channel').serialize(),
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

        // edit channel
        $("#form_edit_channel").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('Channel: Update Channel') }}",
                    type: 'POST',
                    data: $('#form_edit_channel').serialize(),
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

        // Add Parameter Settings
        $("body").on("submit", "#add_parameter_setting", function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('ParameterSetting: Add Parameter Setting') }}",
                    type: 'POST',
                    data: $('#add_parameter_setting').serialize(),
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

        // show add channel model
        $(document).on('click', '#add-channel', function() {
            $("label.error").hide();
            $('#channel_name').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        // remove error message
        function removeErrorMessage() {
            $('.channel_name_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.edit_channel', function() {
            addParameter($(this).data('id'));
            $("label.error").hide();
            removeErrorMessage();
            $('#editChannelId').val($(this).data('id'));
            $('#editChannelName').val($(this).data('name'));
            $('#editStatus').val($(this).data('status'));
        });

        // delete channel
        $(document).on('click', '.delete_channel', function() {
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
        $("#add_new_channel").validate({
            rules: {
                channel_name: {
                    required: true,
                    alphnumericnspace: true
                },
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#form_edit_channel").validate({
            rules: {
                channel_name: {
                    required: true,
                    alphnumericnspace: true
                }
            }
        })
    </script>
@endsection
@endsection
