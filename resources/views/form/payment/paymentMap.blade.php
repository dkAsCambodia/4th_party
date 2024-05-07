@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item active"><a
                            href="{{ route('Merchant: View Merchant') }}">{{ __('messages.Merchant Management') }}</a></li>
                    <li class="breadcrumb-item">{{ __('messages.Configure Payment') }}</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Configure Payment') }} <b>({{ $merchantName }})</b></h4>
                            @if (auth()->user()->can('PaymentMap: Add PaymentMap'))
                                <button type="submit" id="add-product" class="btn btn-danger shadow btn-xs me-1 add_record"
                                    style="float: right;" data-toggle="modal" data-target="#add_product">
                                    {{ __('messages.Add Configuration') }}
                                </button>
                            @endif
                        </div>
                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select id="p-method" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    {{-- @foreach ($paymentUrl as $item)
                                        <option value="{{ $item->id }}">{{ $item->url_name }}</option>
                                    @endforeach --}}
                                    @foreach ($paymentMethod as $paymentMethodVal)
                                        <option value="{{ $paymentMethodVal->id }}">{{ $paymentMethodVal->method_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="status" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    <option value="Enable">{{ trans('messages.Enable') }}</option>
                                    <option value="Disable">{{ trans('messages.Disable') }}</option>
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

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="urlDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Product ID') }}</th>
                                            <th>{{ __('messages.Payment Method') }}</th>

                                            <th>{{ __('messages.Min Value') }}</th>
                                            <th>{{ __('messages.Max Value') }}</th>
                                            {{-- <th>{{ __('messages.Amount') }}</th> --}}
                                            {{-- <th>{{ __('messages.Product Name') }}</th> --}}
                                            <th>{{ __('messages.Agent Rate') }}</th>
                                            <th>{{ __('messages.Merchant Rate') }}</th>
                                            <th>{{ __('messages.cny_range') }}</th>
                                            <th>{{ __('messages.URL') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Created At') }}</th>
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

    <!-- add_product Expense Modal -->
    <div id="add_product" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Map') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('PaymentMap: Add PaymentMap') }}" method="POST" id="add_new_record">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="merchant_id" value="{{ $merchantId }}">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select onchange="getChannelsUsingMethod(this.value, 'add')" name="payment_method"
                                    id="payment_method" class="form-control">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    @foreach ($paymentMethod as $paymentMethodVal)
                                        <option value="{{ $paymentMethodVal->id }}">{{ $paymentMethodVal->method_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="payment_method_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Min Value') }}</label>
                                <input type="text" class="form-control" name="min_value" id="min_value">
                                <span class="min_value_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Max Value') }}</label>
                                <input type="text" class="form-control" name="max_value">
                                <span class="max_value_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.cny_min') }}</label>
                                <input type="text" class="form-control" name="cny_min" id="cny_min">
                                <span class="cny_min_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.cny_max') }}</label>
                                <input type="text" class="form-control" name="cny_max" id="cny_max">
                                <span class="cny_max_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Agent Rate') }}</label>
                                <input type="text" class="form-control" name="agent_rate" id="agent_rate">
                                <span class="agent_rate_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Rate') }}</label>
                                <input type="text" class="form-control" name="merchant_rate" id="merchant_rate">
                                <span class="merchant_rate_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label d-flex flex-column" style="cursor:pointer;">
                                    <div>
                                        {{ trans('messages.Channel Mode') }}
                                    </div>
                                    <div class="pt-2 d-flex" style="gap:10px">
                                        <div>
                                            <input type="radio" name="channel_mode" class="changeVisibility"
                                                value="single" id="channel_mode_single" checked>
                                            <label for="channel_mode_single">
                                                {{ trans('messages.Single') }}
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" name="channel_mode" class="changeVisibility"
                                                value="rotate" id="channel_mode_rotate">
                                            <label for="channel_mode_rotate">{{ trans('messages.Rotate') }}</label>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Channel') }}</label>
                                <span id="for-single-add">
                                    <select class="js-example-basic-multiple-add" name="channel_single[]"
                                        id="channel-single-add">
                                    </select>
                                    <span class="channel_single_err text-danger" role="alert"></span>
                                </span>

                                <span id="for-multiple-add" style="display:none;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="check_multiple" name="channel_multi[]">
                                                </th>
                                                <th>{{ __('messages.Channel ID') }}</th>
                                                <th>{{ __('messages.Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="channel-multiple-add">

                                        </tbody>

                                    </table>
                                    {{-- <select class="js-example-basic-multiple-add" name="channel_single[]"
                                        multiple="multiple" id="channel-multiple-add">
                                    </select> --}}
                                    <span class="channel_multi_err text-danger" role="alert"></span>
                                </span>
                            </div>
                            <input type="hidden" name="status" value="Enable">
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" id="status" class="form-control"
                                    aria-label="Default select example">
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
    <!-- /add_product Expense Modal -->

    <!-- edit_product Expense Modal -->
    <div id="edit_product" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Map') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('PaymentMap: Update PaymentMap') }}" method="POST" id="form_edit_channel">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editId" value="">
                            <input type="hidden" name="merchant_id" value="{{ $merchantId }}">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Payment Method') }}</label>
                                <select onchange="getChannelsUsingMethod(this.value, 'edit')" name="payment_method"
                                    id="Editpayment_method" class="form-control">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    @foreach ($paymentMethod as $paymentMethodVal)
                                        <option value="{{ $paymentMethodVal->id }}">{{ $paymentMethodVal->method_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="payment_method_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Min Value') }}</label>
                                <input type="text" class="form-control" name="min_value" id="editminValue">
                                <span class="min_value_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Max Value') }}</label>
                                <input type="text" class="form-control" name="max_value" id="editmaxValue">
                                <span class="max_value_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.cny_min') }}</label>
                                <input type="text" class="form-control" name="cny_min" id="editCnyMin">
                                <span class="cny_min_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.cny_max') }}</label>
                                <input type="text" class="form-control" name="cny_max" id="editCnyMax">
                                <span class="cny_max_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Agent Rate') }}</label>
                                <input type="text" class="form-control" name="agent_rate" id="editAgentRate">
                                <span class="agent_rate_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Rate') }}</label>
                                <input type="text" class="form-control" name="merchant_rate" id="editMerchantRate">
                                <span class="merchant_rate_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label d-flex flex-column" style="cursor:pointer;">
                                    <div>
                                        {{ trans('messages.Channel Mode') }}
                                    </div>
                                    <div class="pt-2 d-flex" style="gap:10px">
                                        <div>
                                            <input onclick="editChannetMode('single');" type="radio"
                                                name="channel_mode" value="single" id="Editchannel_mode_single">
                                            <label for="channel_mode_single">{{ trans('messages.Single') }}</label>
                                        </div>
                                        <div>
                                            <input onclick="editChannetMode('rotate');" type="radio"
                                                name="channel_mode" value="rotate" id="Editchannel_mode_rotate">
                                            <label for="channel_mode_rotate">{{ trans('messages.Rotate') }}</label>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Channel') }}</label>
                                <select id="Editchannel" name="channel[]" class="form-control">
                                </select>
                                <span class="channel_err text-danger" role="alert"></span>
                            </div> --}}


                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Channel') }}</label>
                                <span id="for-single-edit">
                                    <select class="js-example-basic-multiple-edit" name="channel_single[]"
                                        id="channel-single-edit">
                                    </select>
                                    <span class="channel_single_err text-danger" role="alert"></span>
                                </span>

                                <span id="for-multiple-edit" style="display:none;">
                                    {{-- <select class="js-example-basic-multiple-edit" name="channel_multi[]"
                                        multiple="multiple" id="channel-multiple-edit">
                                    </select> --}}

                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="check_multiple_edit"
                                                        name="channel_multi[]"></th>
                                                <th>{{ __('messages.Channel ID') }}</th>
                                                <th>{{ __('messages.Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="channel-multiple-edit">

                                        </tbody>
                                    </table>

                                    <span class="channel_multi_err text-danger" role="alert"></span>
                                </span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" id="editStatus" class="form-control"
                                    aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
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
    <!-- /edit_product Expense Modal -->

    <!-- Delete User Modal -->
    <div class="modal custom-modal fade" id="delete_product" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('messages.Delete Map') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('PaymentMap: Delete PaymentMap') }}" method="POST">
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
                dropdownParent: $("#add_new_record")
            });
        });
        $(document).ready(function() {
            $('.js-example-basic-multiple-edit').select2({
                dropdownParent: $("#form_edit_channel")
            });
        });

        $('#check_multiple').click(function(e) {
            $(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
        });
        $('#check_multiple_edit').click(function(e) {
            $(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
        });

        function getChannelsUsingMethod(id, work, m_id = 0) {
            getChannelsUsingMethodForMulti(id, work, m_id);
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (work == 'add') {
                    document.getElementById("channel-single-add").innerHTML = this.responseText;
                    getChannelsUsingMethodForMulti(id, work, m_id);
                } else {
                    document.getElementById("channel-single-edit").innerHTML = this.responseText;
                    getChannelsUsingMethodForMulti(id, work, m_id);
                    // document.getElementById("channel-multiple-edit").innerHTML = this.responseText;
                }
            }
            xhttp.open("GET", "{{ url('admin/get-ChannelData') }}?id=" + id + "&m_id=" + m_id);
            xhttp.send();
        }

        function getChannelsUsingMethodForMulti(id, work, m_id) {
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (work == 'add') {
                    document.getElementById("channel-multiple-add").innerHTML = this.responseText;
                } else {
                    document.getElementById("channel-multiple-edit").innerHTML = this.responseText;
                }
            }
            xhttp.open("GET", "{{ url('admin/get-ChannelData') }}?id=" + id + "&m_id=" + m_id + "&mode=table");
            xhttp.send();
        }

        $('.changeVisibility').change(function() {
            // alert($('.changeVisibility:checked').val());
            if ($('.changeVisibility:checked').val() == 'rotate') {
                $('#for-single-add').hide();
                $('#for-multiple-add').show();
                $('#for-single-edit').hide();
                $('#for-multiple-edit').show();
            } else {
                $('#for-single-add').show();
                $('#for-multiple-add').hide();
                $('#for-single-edit').show();
                $('#for-multiple-edit').hide();
            }
        });

        function editChannetMode(channelMode) {
            if (channelMode == 'rotate') {
                $('#for-single-add').hide();
                $('#for-multiple-add').show();
                $('#for-single-edit').hide();
                $('#for-multiple-edit').show();
            } else {
                $('#for-single-add').show();
                $('#for-multiple-add').hide();
                $('#for-single-edit').show();
                $('#for-multiple-edit').hide();
            }
        }

        // datatables
        $(function() {
            var table = $('#urlDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [
                    [8, "desc"]
                ],
                ajax: {
                    url: "{{ route('Merchant: View PaymentMap Merchant', $merchantId) }}",
                    data: function(d) {
                        d.pMethod = $('#p-method').val()
                        d.status = $('#status').val()
                        d.search = $('#search').val()
                    },
                    error: function(jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {
                            location.reload()
                        }
                    },
                },
                columns: [{
                        data: 'product_id'
                    },
                    {
                        data: 'payment_method_name'
                    },
                    {
                        data: 'min_value'
                    },
                    {
                        data: 'max_value'
                    },
                    //{data: 'product_name', searchable: false, sortable: false},
                    {
                        data: 'agent_commission'
                    },
                    {
                        data: 'merchant_commission'
                    },
                    {
                        data: 'cny_range',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'url',
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
                ],
                columnDefs: [{
                        className: "dt-right",
                        targets: [1, 2, 3, 4, 5, 6]
                    },
                    {
                        responsivePriority: 1,
                        targets: 0
                    },
                    {
                        responsivePriority: 2,
                        targets: 9
                    },
                    {
                        responsivePriority: 3,
                        targets: 7
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
            })

            // reset filter
            $('#reset').on('click', function() {
                $('#status').val('')
                $('#search').val('')
                $('#p-method').val('')
                table.draw();
            })
        });

        // add new record
        $("#add_new_record").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('PaymentMap: Add PaymentMap') }}",
                    type: 'POST',
                    data: $('#add_new_record').serialize(),
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

        // edit record
        $("#form_edit_channel").on('submit', function(e) {
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('PaymentMap: Update PaymentMap') }}",
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

        // print error message
        function printErrorMsg(msg) {
            $('.text-danger').text('');
            $.each(msg.error, function(key, value) {
                $('.' + key + '_err').text(value[0]);
            });
        }

        // show add url model
        $(document).on('click', '#add-product', function() {
            $("label.error").hide();
            $('#payment_method').val('');
            $('#min_value').val('');
            //$('#payment_url').val('');
            $('#agent_rate').val('');
            $('#max_value').val('');
            $('#merchant_rate').val('');
            $('#channel').val('');
            $('#channel_mode').val('');
            $('#cny_min').val('');
            $('#cny_max').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        // remove error message
        function removeErrorMessage() {
            $('.min_value_err').text('')
            $('.max_value_err').text('')
            $('.map_value_err').text('')
            //$('.payment_url_err').text('');
            $('.agent_rate_err').text('');
            $('.merchant_rate_err').text('');
            $('.cny_min_err').text('');
            $('.cny_max_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.edit_channel', function() {
            removeErrorMessage();
            $('#editId').val($(this).data('id'));
            getChannelsUsingMethod($(this).data('payment_method_id'), 'edit', $(this).data(
                'gateway_payment_channel_id'));
            $('#editValue').val($(this).data('value'));
            //$('#editUrl').val($(this).data('url'));
            $('#editAgentRate').val($(this).data('agent_rate'));
            $('#editMerchantRate').val($(this).data('merchant_rate'));
            $('#editCnyMin').val($(this).data('cny_min'));
            $('#editCnyMax').val($(this).data('cny_max'));
            $('#editStatus').val($(this).data('status'));
            $('#editminValue').val($(this).data('min_value'));
            $('#editmaxValue').val($(this).data('max_value'));
            $('#Editpayment_method').val($(this).data('payment_method_id'));


            if ($(this).data('channel_mode') == 'single') {
                $('#Editchannel_mode_single').prop("checked", true)
                $('#for-single-edit').show();
                $('#for-multiple-edit').hide();

            } else {
                $('#Editchannel_mode_rotate').prop("checked", true)
                $('#for-single-edit').hide();
                $('#for-multiple-edit').show();
            }
        });

        function copyToClipboard(id) {
            $.ajax({
                url: "{{ route('PaymentMap: Copy payment link PaymentMap') }}",
                type: 'GET',
                data: 'id=' + id,
                success: function(textToCopy) {
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(textToCopy).then(function() {
                            toastr.success('Copied to clipboard');
                        }, function() {
                            toastr.error('Failure to copy. Check permissions for clipboard');
                        });
                    } else {
                        let textArea = document.createElement("textarea");
                        textArea.value = textToCopy;
                        textArea.style.position = "fixed";
                        textArea.style.left = "-999999px";
                        textArea.style.top = "-999999px";
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        return new Promise((res, rej) => {
                            document.execCommand('copy') ?
                                res(
                                    toastr.success('Copied to clipboard')
                                ) : rej(
                                    toastr.error('Failure to copy. Check permissions for clipboard')
                                );
                            textArea.remove();
                        });
                    }
                }
            });
        }

        $(document).on('click', '.delete_product', function() {
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
        $.validator.addMethod("numbersndecimals", function(value, element) {
            return this.optional(element) || /^(\d*\.?\d*)$/.test(value);
        });
        $.validator.addMethod("greaterThan", function(value, element, param) {
            var $otherElement = $(param);
            return parseInt(value, 10) > parseInt($otherElement.val(), 10);
        });
        $.validator.addMethod("maxgreaterThan", function(value, element, param) {
            var $otherElement = $(param);
            return parseInt(value, 10) > parseInt($otherElement.val(), 10);
        });

        //Add Form Validation
        $("#add_new_record").validate({
            rules: {
                payment_method: {
                    required: true
                },
                min_value: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                max_value: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10,
                    maxgreaterThan: "#min_value"
                },
                agent_rate: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                merchant_rate: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10,
                    greaterThan: "#agent_rate"
                },
                cny_min: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                cny_max: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                status: {
                    required: true
                },
                'channel_single[]': {
                    required: true
                },
                'channel_multi[]': {
                    required: false
                }
            }
        })

        //Edit Form Validation
        $("#form_edit_channel").validate({
            rules: {
                min_value: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                max_value: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10,
                    maxgreaterThan: "#editminValue"
                },
                /* payment_url: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                }, */
                agent_rate_edit: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                merchant_rate: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10,
                    greaterThan: "#editAgentRate"
                },
                cny_min: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                cny_max: {
                    required: true,
                    numbersndecimals: true,
                    maxlength: 10
                },
                status: {
                    required: true
                },
                'channel_single[]': {
                    required: true
                },
                'channel_multi[]': {
                    required: false
                }
            }
        })
    </script>
@endsection
@endsection
