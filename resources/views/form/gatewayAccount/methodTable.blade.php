@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <style>
        div#sourceDataTable_filter {
            display: none !important;
        }
    </style>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item active"><a href="{{ route('GatewayAccount: View Gateway Account') }}">{{ __('messages.Gateway Account') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Payment Method') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payment Method') }}</h4>
                            {{-- @if (auth()->user()->can('Source: Add Source')) --}}
                            <button id="add_account" type="submit" class="btn btn-danger shadow btn-xs me-1 add_record"
                                style="float: right;" data-toggle="modal" data-target="#add_account_gateway">
                                {{ __('messages.Add Payment Method') }}
                            </button>
                            {{-- @endif --}}
                        </div>

                        {{-- Search --}}

                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Account ID') }}</label>
                                <input type="search" class="form-control" readonly
                                    value="{{ $gatewayAccountFirst->account_id }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Description') }}</label>
                                <input type="search" class="form-control" readonly
                                    value="{{ $gatewayAccountFirst->description }}">
                            </div>
                        </div>
                        {{-- Search End --}}

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="sourceDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Payment Method') }}</th>
                                            {{-- <th>{{ __('messages.Merchant Key') }}</th>
                                            <th>{{ __('messages.Payment Link') }}</th>
                                            <th>{{ __('messages.Pre Sign') }}</th>
                                            <th>{{ __('messages.Merchant Code') }}</th> --}}
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Create Date') }}</th>
                                            @if (auth()->user()->can('GatewayAccountMethod: Update Method Account') ||
                                                    auth()->user()->can('GatewayAccount: Delete Payment Method'))
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

    <!-- Add new source Modal -->
    <div id="add_account_gateway" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Payment Method') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('GatewayAccountMethod: Add Method Account') }}" method="POST"
                        id="add_gateway_account">
                        @csrf
                        <div class="row">
                            @foreach ($parameterSetting as $parameterSettingVal)
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">{{ $parameterSettingVal->parameter_name }} <span
                                            class="text-danger">*</span></label>
                                    <input type="hidden" class="form-control" name="parameter_id[]"
                                        value="{{ $parameterSettingVal->id }}">
                                    <input type="text" class="form-control params"
                                        name="parameter_val{{ $parameterSettingVal->id }}" required>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <input type="hidden" value="{{ $gatewayChannetId }}" name="gateway_channet_id">
                            <input type="hidden" value="{{ $gatewayAccountId }}" name="gateway_account_id">

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select class="js-example-basic-multiple-add" name="method_id[]" multiple="multiple" required>
                                    @foreach ($paymentMethod as $method)
                                        <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="status" value="Enable">
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }} <span
                                        class="text-danger">*</span></label>
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
    <!-- /Add new source Modal -->

    <!-- Edit Expense Modal -->
    <div id="edit_account_gateway" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Payment Method') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('GatewayAccountMethod: Update Method Account') }}" method="POST"
                        id="edit_gateway_account">
                        @csrf
                        <div id="appendParameterVal" class="row"></div>
                        <div class="row">
                            <input type="hidden" value="{{ $gatewayChannetId }}" name="gateway_channet_id">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select id="editMethod_id" class="js-example-basic-multiple" name="method_id[]"
                                    multiple="multiple" required>
                                </select>
                                <input type="hidden" value="{{ $gatewayAccountId }}" name="gateway_account_id">
                                <input type="hidden" class="form-control" name="id" id="editid">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" class="form-control" aria-label="Default select example"
                                    id="editStatus">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
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
    <!-- /Edit Expense Modal -->

    <!-- Delete User Modal -->
    <div class="modal custom-modal fade" id="delete_account_gateway" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Payment Method') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('GatewayAccountMethod: Delete Method Account') }}" method="POST">
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-multiple-add').select2({
                dropdownParent: $("#add_gateway_account")
            });
        });
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2({
                dropdownParent: $("#edit_gateway_account")
            });
        });

        function addParameterVal(id) {
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                document.getElementById("appendParameterVal").innerHTML =
                    this.responseText;
            }
            xhttp.open("GET", "{{ url('admin/open-form-add-parameter-val') }}?id=" + id + "&channel_id=" +
                {{ $gatewayChannetId }});
            xhttp.send();
        }

        $(function() {
            var table = $('#sourceDataTable').DataTable({
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
                    url: "{{ route('GatewayAccountMethod: View Method Account', "$gatewayAccountId") }}",
                    // data: function(d) {
                    //     d.method = $('#method').val()
                    //     d.status = $('#status').val()
                    //     d.search = $('#search').val()
                    // }
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'gateway_method_name'
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
                dom: 'rt<"bottom"ipl><"clear">',
            });

            // filter by dropdown
            $('.filterTable').change(function() {
                table.draw();
            });

            // search
            // document.getElementById('search').addEventListener('input', (e) => {
            //     table.draw();
            // });

            // reset filter
            // $('#reset').on('click', function() {
            //     $('#status').val('')
            //     $('#method').val('')
            //     $('#search').val('')
            //     table.draw();
            // })
        });

        // add new source
        $("#add_gateway_account").on('submit', function(e) {
            $('.params').attr('maxlength', '200');
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('GatewayAccountMethod: Add Method Account') }}",
                    type: 'POST',
                    data: $('#add_gateway_account').serialize(),
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

        // edit source
        $("#edit_gateway_account").on('submit', function(e) {
            $('.params').attr('maxlength', '200');
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('GatewayAccountMethod: Update Method Account') }}",
                    type: 'POST',
                    data: $('#edit_gateway_account').serialize(),
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

        // show add source model
        $(document).on('click', '#add_account', function() {
            $('.params').attr('maxlength', '200');
            $("label.error").hide();
            $('#source_name').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        // remove error message
        function removeErrorMessage() {
            $('.source_name_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.edit_account_gateway', function() {
            $('.params').attr('maxlength', '200');
            removeErrorMessage();
            $('#editid').val($(this).data('id'));

            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                document.getElementById("editMethod_id").innerHTML = this.responseText;
            }
            xhttp.open("GET", "{{ url('admin/get-selected-payment-method') }}?id=" + $(this).data('method_id'));
            xhttp.send();

            // $('#editMethod_id').val();

            $('#editPayment_link').val($(this).data('payment_link'));
            $('#editMerchant_key').val($(this).data('merchant_key'));
            $('#editMerchant_code').val($(this).data('merchant_code'));
            $('#editPre_sign').val($(this).data('sign_pre'));
            $('#editUsername').val($(this).data('username'));
            $('#editPassword').val($(this).data('password'));
            $('#editClint_id').val($(this).data('clint_id'));

            $('#editStatus').val($(this).data('status'));
        });

        // delete source
        $(document).on('click', '.delete_account_gateway', function() {
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
        $("#add_gateway_account").validate({
            rules: {
                method_id: {
                    required: true,
                    alphnumericnspace: true
                },
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#edit_gateway_account").validate({
            rules: {
                method_id: {
                    required: true,
                    alphnumericnspace: true
                }
            }
        })
    </script>
@endsection
@endsection
