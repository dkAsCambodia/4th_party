@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ trans('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Payment Url') }}</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payment Url') }}</h4>
                            @if (auth()->user()->can('PaymentUrl: Add PaymentUrl'))
                                <button id="add-url" type="submit" class="btn btn-danger shadow btn-xs me-1" style="float: right;"
                                    data-toggle="modal" data-target="#add_url">
                                    {{ __('messages.Add Url') }}
                                </button>
                            @endif
                        </div>
                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Channel Name') }}</label>
                                <select id="channel" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($channel as $chan)
                                        <option value="{{ $chan->id }}">{{ $chan->channel_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Method Name') }}</label>
                                <select id="method" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($method as $meth)
                                        <option value="{{ $meth->id }}">{{ $meth->method_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                           {{--  <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Source Name') }}</label>
                                <select id="source" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($source as $sourc)
                                        <option value="{{ $sourc->id }}">{{ $sourc->source_name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}

                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="urlDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Name') }}</th>
                                            <th>{{ __('messages.Channel Name') }}</th>
                                            <th>{{ __('messages.Method Name') }}</th>
                                            {{-- <th>{{ __('messages.Source Name') }}</th> --}}
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Created At') }}</th>
                                            @if (auth()->user()->can('PaymentUrl: Update PaymentUrl') ||
                                                    auth()->user()->can('PaymentUrl: Delete PaymentUrl'))
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
    <?php
    $validMsg = __('messages.Please fill out this field');
    $validMsgDropDwn = __('messages.Please fill out this field');
    ?>
    <!-- add new url Modal -->
    <div id="add_url" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Add Url') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('PaymentUrl: Add PaymentUrl') }}" method="POST" id="add_new_url">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Url Name') }}</label>
                                <input type="text" class="form-control" name="url_name" placeholder="Pre Defined Value"
                                    readonly>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Url') }}</label>
                                <input type="text" class="form-control" name="url" id="url">
                                <span class="url_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Key') }}</label>
                                <input type="text" class="form-control" name="merchant_key" id="merchant_key">
                                <span class="merchant_key_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Code') }}</label>
                                <input type="text" class="form-control" name="merchant_code" id="merchant_code">
                                <span class="merchant_code_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Pre Sign') }}</label>
                                <input type="text" class="form-control" name="pre_sign" id="pre_sign">
                                <span class="pre_sign_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Channel') }}</label>
                                <select name="channel_id" id="channel_id" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    @foreach ($channel as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->channel_name }}</option>
                                    @endforeach
                                </select>
                                <span class="channel_id_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Method') }}</label>
                                <select name="method_id" id="method_id" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    @foreach ($method as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->method_name }}</option>
                                    @endforeach
                                </select>
                                <span class="method_id_err text-danger" role="alert"></span>
                            </div>
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Source') }}</label>
                                <select name="source_id" id="source_id" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    @foreach ($source as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->source_name }}</option>
                                    @endforeach
                                </select>
                                <span class="source_id_err text-danger" role="alert"></span>
                            </div> --}}


                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" class="form-control" aria-label="Default select example">
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
    <!-- /add new url Modal -->

    <!-- Edit Expense Modal -->
    <div id="edit_user" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Url') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('PaymentUrl: Update PaymentUrl') }}" method="POST" id="form_edit_url">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="id" id="editUrlId">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Url Name') }}</label>
                                <input type="text" class="form-control" name="url_name" id="url_name" readonly
                                    placeholder="{{ __('messages.Pre Defined Value') }}">
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Url') }}</label>
                                <input type="text" class="form-control" name="url" id="editUrl">
                                <span class="url_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Key') }}</label>
                                <input type="text" class="form-control" name="merchant_key"
                                    id="editMerchantKey">
                                <span class="merchant_key_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Code') }}</label>
                                <input type="text" class="form-control" name="merchant_code"
                                    id="editMerchantCode">
                                <span class="merchant_code_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Pre Sign') }}</label>
                                <input type="text" class="form-control" name="pre_sign"
                                    id="editPreSign">
                                <span class="pre_sign_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Channel') }}</label>
                                <select name="channel_id" id="editChannel" class="form-control"
                                    aria-label="Default select example">
                                    @foreach ($channel as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->channel_name }}</option>
                                    @endforeach
                                </select>
                                <span class="channel_id_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Method') }}</label>
                                <select name="method_id" id="editMethod" class="form-control"
                                    aria-label="Default select example">
                                    @foreach ($method as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->method_name }}</option>
                                    @endforeach
                                </select>
                                <span class="method_id_err text-danger" role="alert"></span>
                            </div>
                            {{-- <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Source') }}</label>
                                <select name="source_id" id="editSource" class="form-control"
                                    aria-label="Default select example">
                                    @foreach ($source as $key => $items)
                                        <option value="{{ $items->id }}">{{ $items->source_name }}</option>
                                    @endforeach
                                </select>
                                <span class="source_id_err text-danger" role="alert"></span>
                            </div> --}}

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
                        <h3>{{ __('messages.Delete Url') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('PaymentUrl: Delete PaymentUrl') }}" method="POST">
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
    {{-- show data on model or edit --}}
    <script>
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
                    [4, "desc"]
                ],
                ajax: {
                    url: "{{ route('PaymentUrl: View PaymentUrl') }}",
                    data: function(d) {
                        d.channel = $('#channel').val()
                        d.method = $('#method').val()
                        //d.source = $('#source').val()
                        d.search = $('#search').val()
                    },
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'url_name'
                    },
                    {
                        data: 'channel_name',
                        name: 'channel.channel_name',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'method_name',
                        name: 'method.method_name',
                        searchable: false,
                        sortable: false
                    },
                    /* {
                        data: 'source_name',
                        name: 'source.source_name',
                        searchable: false,
                        sortable: false
                    }, */
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
        });

        // add new url
        $("#add_new_url").on('submit', function(e) {
            $('#url').attr('maxlength', '100');
            $('#pre_sign').attr('maxlength', '100');
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('PaymentUrl: Add PaymentUrl') }}",
                    type: 'POST',
                    data: $('#add_new_url').serialize(),
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

        // edit url
        $("#form_edit_url").on('submit', function(e) {
            $('#editUrl').attr('maxlength', '100');
            $('#editPreSign').attr('maxlength', '100');
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('PaymentUrl: Update PaymentUrl') }}",
                    type: 'POST',
                    data: $('#form_edit_url').serialize(),
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
        $(document).on('click', '#add-url', function() {
            $("label.error").hide();
            $('#url').val('');
            $('#merchant_key').val('');
            $('#merchant_code').val('');
            $('#pre_sign').val('');
            $('#channel_id').val('');
            $('#method_id').val('');
            //$('#source_id').val('');
            $('#status').val('');
            removeErrorMessage();
            $('#url').attr('maxlength', '100');
            $('#pre_sign').attr('maxlength', '100');
        });

        // remove error message
        function removeErrorMessage() {
            $('.url_err').text('')
            $('.url_err').text('');
            $('.merchant_key_err').text('');
            $('.merchant_code_err').text('');
            $('.pre_sign_err').text('');
            $('.channel_id_err').text('');
            $('.method_id_err').text('');
            //$('.source_id_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.edit_url', function() {
            removeErrorMessage();
            $('#editUrlId').val($(this).data('id'));
            $('#editUrl').val($(this).data('url'));
            $('#editMerchantKey').val($(this).data('merchantkey'));
            $('#editMerchantCode').val($(this).data('merchantcode'));
            $('#editPreSign').val($(this).data('pre_sign'));
            $('#editChannel').val($(this).data('channel'));
            $('#editMethod').val($(this).data('method'));
            //$('#editSource').val($(this).data('source'));
            $('#editStatus').val($(this).data('status'));
            $('#editUrl').attr('maxlength', '255');
            $('#editPreSign').attr('maxlength', '255');
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

        //Add Form Validation
        $("#add_new_url").validate({
            rules: {
                url: {
                    required: true,
                    url: true
                },
                merchant_key: {
                    required: true
                },
                merchant_code: {
                    required: true,
                    alphanumericnospace: true
                },
                pre_sign: {
                    required: true
                },
                channel_id: {
                    required: true
                },
                method_id: {
                    required: true
                },
                /* source_id: {
                    required: true
                }, */
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#form_edit_url").validate({
            rules: {
                url: {
                    required: true,
                    url: true
                },
                merchant_key: {
                    required: true
                },
                merchant_code: {
                    required: true,
                    alphanumericnospace: true
                },
                pre_sign: {
                    required: true
                },
                channel: {
                    required: true
                },
                method: {
                    required: true
                },
               /*  source: {
                    required: true
                }, */
                status: {
                    required: true
                }
            }
        })
    </script>
@endsection
@endsection
