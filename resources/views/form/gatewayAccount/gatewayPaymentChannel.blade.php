@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <style>
        div#gatewayPCDataTable_filter {
            display: none !important;
        }

        input[readonly] {
            background-color: #c4c3c3 !important;
        }
    </style>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Payment Channel') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payment Channel') }}</h4>
                            {{--  @if (auth()->user()->can('GatewayPaymentChannel: Add GatewayPaymentChannel')) --}}
                            <button id="add_account" type="submit" class="btn btn-danger shadow btn-xs me-1 add_record"
                                style="float: right;" data-toggle="modal" data-target="#add_gateway_payment_channel">
                                {{ __('messages.Add Payment Channel') }}
                            </button>
                            {{--  @endif --}}
                        </div>

                        {{-- Search --}}

                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select id="method" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($paymentMethod as $method)
                                        <option value="{{ $method->id }}">{{ $method->method_name }}</option>
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
                            <div class="col-md-4" bis_skin_checked="1">
                                <label class="form-label mb-4"></label>
                                <div class="" bis_skin_checked="1">
                                    <button type="button" id="reset"
                                        class="btn btn-danger text-white btn-xs">Reset</button>
                                </div>
                            </div>
                        </div>
                        {{-- Search End --}}

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="gatewayPCDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Channel ID') }}</th>
                                            <th>{{ __('messages.Description') }}</th>
                                            <th>{{ __('messages.Gateway Account') }}</th>
                                            <th>{{ __('messages.Payment Method') }}</th>
                                            <th>{{ __('messages.Created Time') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Risk Control') }}</th>
                                            {{--  @if (auth()->user()->can('GatewayPaymentChannel: Update GatewayPaymentChannel') ||
    auth()->user()->can('GatewayPaymentChannel: Delete GatewayPaymentChannel')) --}}
                                            <th>{{ __('messages.Action') }}</th>
                                            {{--  @endif --}}
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
    <div id="add_gateway_payment_channel" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Payment Channel') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('GatewayPaymentChannel: Add GatewayPaymentChannel') }}" method="POST"
                        id="add_gateway_payment_channel_form">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Channel ID') }}</label>
                                <input type="text" class="form-control" name="channel_id" id="channel_id">
                                <span class="channel_id_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Description') }}</label>
                                <input type="text" class="form-control" name="description" id="description">
                                <span class="description_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Gateway Account') }}</label>
                                <select onchange="getMethodUsingGateway(this.value, 'add')" id="gateway_account"
                                    name="gateway_account" class="form-control">
                                    <option value="">{{ trans('messages.Select') }}</option>
                                    @foreach ($gatewayAccount as $account)
                                        <option value="{{ $account->id }}">{{ $account->account_id }}</option>
                                    @endforeach
                                </select>
                                <span class="gateway_account_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select id="payment_method" name="payment_method" class="form-control">
                                    <option value="">{{ trans('messages.Select') }}</option>
                                    {{-- @foreach ($paymentMethod as $method)
                                        <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                    @endforeach --}}
                                </select>
                                <span class="payment_method_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Daily max limit') }}</label>
                                <input type="text" class="form-control" name="daily_max_limit" id="daily_max_limit">
                                <span class="daily_max_limit_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Max limit per transaction') }}</label>
                                <input type="text" class="form-control" name="max_limit_per_transaction"
                                    id="max_limit_per_transaction">
                                <span class="max_limit_per_transaction_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Daily max transaction') }}</label>
                                <input type="text" class="form-control" name="daily_max_transaction"
                                    id="daily_max_transaction">
                                <span class="daily_max_transaction_err text-danger" role="alert"></span>
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
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="risk_control">{{ __('messages.Risk Control') }}</label>
                                <div class="form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="risk_control"
                                        id="risk_control" style=" height: 30px; width: 55px; ">
                                </div>
                                <span class="risk_control_err text-danger" role="alert"></span>
                                {{-- <input type="checkbox" name="risk_control" id="risk_control">
                                <span class="risk_control_err text-danger" role="alert"></span> --}}
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
    <!-- /Add new source Modal -->

    <!-- Edit Expense Modal -->
    <div id="edit_gateway_payment_channel" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Payment Channel') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('GatewayPaymentChannel: Update GatewayPaymentChannel') }}" method="POST"
                        id="edit_gateway_payment_channel_form">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Channel ID') }}</label>
                                <input type="text" class="form-control" name="channel_id" id="Editchannel_id"
                                    readonly>
                                <input type="hidden" class="form-control" name="id" id="editid">
                                <span class="channel_id_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Description') }}</label>
                                <input type="text" class="form-control" name="description" id="Editdescription">
                                <span class="description_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Gateway Account') }}</label>
                                <select onchange="getMethodUsingGateway(this.value, 'edit')" id="Editgateway_account"
                                    name="gateway_account" class="form-control">
                                    <option value="">{{ trans('messages.Select') }}</option>
                                    @foreach ($gatewayAccount as $account)
                                        <option value="{{ $account->id }}">{{ $account->account_id }}</option>
                                    @endforeach
                                </select>
                                <span class="gateway_account_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select id="Editpayment_method" name="payment_method" class="form-control">
                                    <option value="">{{ trans('messages.Select') }}</option>
                                    {{-- @foreach ($paymentMethod as $method)
                                        <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                    @endforeach --}}
                                </select>
                                <span class="payment_method_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Daily max limit') }}</label>
                                <input type="text" class="form-control" name="daily_max_limit"
                                    id="Editdaily_max_limit">
                                <span class="daily_max_limit_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Max limit per transaction') }}</label>
                                <input type="text" class="form-control" name="max_limit_per_transaction"
                                    id="Editmax_limit_per_transaction">
                                <span class="max_limit_per_transaction_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Daily max transaction') }}</label>
                                <input type="text" class="form-control" name="daily_max_transaction"
                                    id="Editdaily_max_transaction">
                                <span class="daily_max_transaction_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="Editstatus" name="status" class="form-control"
                                    aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
                                <span class="status_err text-danger" role="alert"></span>
                            </div>
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Risk Control') }}</label>
                                <input type="checkbox" name="risk_control" id="Editrisk_control">
                                <span class="risk_control_err text-danger" role="alert"></span>
                            </div> --}}
                            <div class="mb-3 col-md-6">
                                <label class="form-label"
                                    for="Editrisk_control">{{ __('messages.Risk Control') }}</label>
                                <div class="form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="risk_control"
                                        id="Editrisk_control" style=" height: 30px; width: 55px; ">
                                </div>
                                <span class="risk_control_err text-danger" role="alert"></span>
                                {{-- <input type="checkbox" name="risk_control" id="risk_control">
                                <span class="risk_control_err text-danger" role="alert"></span> --}}
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
    <div class="modal custom-modal fade" id="delete_gateway_payment_channel" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Payment Channel') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('GatewayPaymentChannel: Delete GatewayPaymentChannel') }}" method="POST">
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
        function getMethodUsingGateway(id, work, m_id = 0) {
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (work == 'add') {
                    document.getElementById("payment_method").innerHTML = this.responseText;
                } else {
                    document.getElementById("Editpayment_method").innerHTML = this.responseText;
                }
            }
            xhttp.open("GET", "{{ url('admin/get-MethodData') }}?id=" + id + "&m_id=" + m_id);
            xhttp.send();
        }

        $(function() {
            var table = $('#gatewayPCDataTable').DataTable({
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
                    url: "{{ route('GatewayPaymentChannel: View GatewayPaymentChannel') }}",
                    data: function(d) {
                        d.method = $('#method').val()
                        d.status = $('#status').val()
                        d.search = $('#search').val()
                    },
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'channel_id'
                    },
                    {
                        data: 'channel_description'
                    },
                    {
                        data: 'account_id'
                    },
                    {
                        data: 'method_name'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status'
                    },

                    {
                        data: 'risk_control'
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
            document.getElementById('search').addEventListener('input', (e) => {
                table.draw();
            });

            // reset filter
            $('#reset').on('click', function() {
                $('#status').val('')
                $('#search').val('')
                $('#method').val('')
                table.draw();
            })
        });

        // add new source
        $("#add_gateway_payment_channel_form").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('GatewayPaymentChannel: Add GatewayPaymentChannel') }}",
                    type: 'POST',
                    data: $('#add_gateway_payment_channel_form').serialize(),
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
        $("#edit_gateway_payment_channel_form").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('GatewayPaymentChannel: Update GatewayPaymentChannel') }}",
                    type: 'POST',
                    data: $('#edit_gateway_payment_channel_form').serialize(),
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

        $(document).on('click', '.edit_gateway_payment_channel', function() {
            removeErrorMessage();
            $('#editid').val($(this).data('id'));

            getMethodUsingGateway($(this).data('gateway_account'), 'edit', $(this).data('payment_method'));

            $('#Editchannel_id').val($(this).data('channel_id'));
            $('#Editdescription').val($(this).data('channel_description'));
            $('#Editgateway_account').val($(this).data('gateway_account'));
            $('#Editpayment_method').val($(this).data('payment_method'));
            $('#Editdaily_max_limit').val($(this).data('daily_max_limit'));
            $('#Editmax_limit_per_transaction').val($(this).data('max_limit_per_trans'));
            $('#Editdaily_max_transaction').val($(this).data('daily_max_trans'));
            $('#Editstatus').val($(this).data('status'));

            if ($(this).data('risk_control') == 1) {
                $('#Editrisk_control').prop("checked", true)
            } else {
                $('#Editrisk_control').prop("checked", false)
            }
        });

        // delete source
        $(document).on('click', '.delete_gateway_payment_channel', function() {
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
        $("#add_gateway_payment_channel_form").validate({
            rules: {
                channel_id: {
                    required: true
                },
                description: {
                    required: true
                },
                gateway_account: {
                    required: true
                },
                payment_method: {
                    required: true
                },
                daily_max_limit: {
                    required: true,
                    number: true,
                    maxlength: 8
                },
                max_limit_per_transaction: {
                    required: true,
                    number: true,
                    maxlength: 8
                },
                daily_max_transaction: {
                    required: true,
                    number: true,
                    maxlength: 8
                },
                status: {
                    required: true
                },
                risk_control: {
                    required: false
                }
            }
        })

        //Edit Form Validation
        $("#edit_gateway_payment_channel_form").validate({
            rules: {
                channel_id: {
                    required: false
                },
                description: {
                    required: true
                },
                gateway_account: {
                    required: true
                },
                payment_method: {
                    required: true
                },
                daily_max_limit: {
                    required: true,
                    number: true,
                    maxlength: 8
                },
                max_limit_per_transaction: {
                    required: true,
                    number: true,
                    maxlength: 8
                },
                daily_max_transaction: {
                    required: true,
                    number: true,
                    maxlength: 8
                },
                status: {
                    required: true
                },
                risk_control: {
                    required: false
                }
            }
        })
    </script>
@endsection
@endsection
