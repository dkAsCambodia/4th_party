@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <style>
        div#sourceDataTable_filter {
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
                    <li class="breadcrumb-item"> {{ __('messages.Gateway Account') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Gateway Account') }}</h4>
                            <button id="add_account" type="submit" class="btn btn-danger shadow btn-xs me-1 add_record"
                                style="float: right;" data-toggle="modal" data-target="#add_account_gateway">
                                {{ __('messages.Add Gateway Account') }}
                            </button>
                        </div>

                        {{-- Search --}}
                        <div class="card-body row">
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Search') }}</label>
                                <input id="search" type="search" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.Gateway') }}</label>
                                <select id="channel" class="form-control filterTable">
                                    <option value="">{{ trans('messages.All') }}</option>
                                    @foreach ($paymentGateway as $chan)
                                        <option value="{{ $chan->id }}">{{ $chan->channel_name }}</option>
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
                            <div class="col-md-4">
                                <label class="form-label mb-4"></label>
                                <div class="">
                                    <button type='button' id="reset" class="btn btn-danger text-white btn-xs">{{ __('messages.Reset') }}</button>
                                </div>
                            </div>
                        </div>
                        {{-- Search End --}}

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="sourceDataTable" style="width: 100% !important">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Account ID') }}</th>
                                            <th>{{ __('messages.Description') }}</th>
                                            <th>{{ __('messages.Gateway') }}</th>
                                            <th>{{ __('messages.Create Date') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            @if (auth()->user()->can('GatewayAccount: Update Gateway Account') ||
                                                    auth()->user()->can('GatewayAccount: Delete Gateway Account'))
                                                <th>{{ __('messages.Action') }}</th>
                                            @endif
                                            @if (auth()->user()->can('GatewayAccountMethod: View Method Account'))
                                                <th>{{ __('messages.Payment Method') }}</th>
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
                    <h5 class="modal-title">{{ __('messages.Add Gateway Account') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('GatewayAccount: Add Gateway Account') }}" method="POST"
                        id="add_gateway_account">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account ID') }}</label>
                                <input type="text" class="form-control" name="account_id" id="account_id">
                                <span class="account_id_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Description') }}</label>
                                <input type="text" class="form-control" name="description" id="description">
                                <span class="description_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Gateway') }}</label>
                                <select name="gateway" class="form-control" aria-label="Default select example">
                                    <option value="">{{ __('messages.Select') }}</option>
                                    @foreach ($paymentGateway as $paymentGatewayVal)
                                        <option value="{{ $paymentGatewayVal->id }}">
                                            {{ $paymentGatewayVal->channel_name }}</option>
                                    @endforeach
                                </select>
                                <span class="gateway_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.E-comm Website') }}</label>
                                <input type="text" class="form-control" name="e_comm_website" id="e_comm_website">
                                <span class="e_comm_website_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Website Description') }}</label>
                                <input type="text" class="form-control" name="website_description"
                                    id="website_description">
                                <span class="website_description_err text-danger" role="alert"></span>
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
    <!-- /Add new source Modal -->

    <!-- Edit Expense Modal -->
    <div id="edit_account_gateway" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.Edit Gateway Account') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('GatewayAccount: Update Gateway Account') }}" method="POST"
                        id="edit_gateway_account">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Account ID') }}</label>
                                <input type="text" class="form-control" name="account_id" id="editAccount_id" readonly>
                                <span class="account_id_err text-danger" role="alert"></span>
                            </div>

                            <input type="hidden" class="form-control" name="id" id="editid">

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Description') }}</label>
                                <input type="text" class="form-control" name="description" id="editDescription">
                                <span class="description_err text-danger" role="alert"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Gateway') }}</label>
                                <input type="text" class="form-control" name="gateway" id="editGateway" readonly>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.E-comm Website') }}</label>
                                <input type="text" class="form-control" name="e_comm_website" id="editE_comm_website">
                                <span class="e_comm_website_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Website Description') }}</label>
                                <input type="text" class="form-control" name="website_description"
                                    id="editWebsite_description">
                                <span class="website_description_err text-danger" role="alert"></span>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select name="status" class="form-control" aria-label="Default select example" id="editStatus">
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
                        <h3>{{ __('messages.Delete Gateway Account') }}</h3>
                        <p>{{ __('messages.Are you sure want to delete?') }}</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('GatewayAccount: Delete Gateway Account') }}" method="POST">
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
        $(function () {
            var table = $('#sourceDataTable').DataTable({
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: true,
                processing: true,
                serverSide: true,
                order: [[ 4, "desc" ]],
                ajax: {
                    url: "{{ route('GatewayAccount: View Gateway Account') }}",
                    data: function (d) {
                        d.channel = $('#channel').val()
                        d.status = $('#status').val()
                        d.search = $('#search').val()
                    },
                    error: function (jqXHR) {
                        if (jqXHR && jqXHR.status == 401) {location.reload()}
                    },
                },
                columns: [{
                        data: 'account_id'
                    },
                    {
                        data: 'description'
                    },
                    {
                        data: 'gateway_name'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        searchable: false,
                        sortable: false
                    },
                    @if (auth()->user()->can('GatewayAccountMethod: View Method Account'))
                    {
                        data: 'payment_method',
                        searchable: false,
                        sortable: false
                    },
                    @endif

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
            $('.filterTable').change(function(){
                table.draw();
            });

            // search
            document.getElementById('search').addEventListener('input', (e) => {
                table.draw();
            });

            // reset filter
            $('#reset').on('click', function() {
                $('#status').val('')
                $('#channel').val('')
                $('#search').val('')
                table.draw();
            })
        });

        // add new account
        $("#add_gateway_account").on('submit', function(e) {
            $('#e_comm_website').attr('maxlength', '200');
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('GatewayAccount: Add Gateway Account') }}",
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

        // edit account
        $("#edit_gateway_account").on('submit', function(e) {
            $('#editE_comm_website').attr('maxlength', '200');
            if ($(this).valid()) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('GatewayAccount: Update Gateway Account') }}",
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
            $("label.error").hide();
            $('#account_id').val('');
            $('#description').val('');
            $('#gateway').val('');
            $('#e_comm_website').val('');
            $('#website_description').val('');
            $('#status').val('');
            removeErrorMessage();
        });

        // remove error message
        function removeErrorMessage() {
            $('.source_name_err').text('');
            $('.status_err').text('');
        }

        $(document).on('click', '.edit_account_gateway', function() {
            removeErrorMessage();
            $("label.error").hide();
            $('#editid').val($(this).data('id'));
            $('#editAccount_id').val($(this).data('account_id'));
            $('#editDescription').val($(this).data('description'));
            $('#editE_comm_website').val($(this).data('e_comm_website'));
            $('#editGateway').val($(this).data('gateway'));
            $('#editWebsite_description').val($(this).data('website_description'));
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
        $.validator.addMethod("alphnumericnspacendash", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9\-\s]+$/i.test(value);
        });
        $.validator.addMethod("alphnumericndash", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9\-]+$/i.test(value);
        });

        $.validator.addMethod("verifyUnique", function(value, element) {
                var result = false;
                $.ajax({
                    type:"POST",
                    async: false,
                    url: "{{ route('GatewayAccount: Check Account') }}",
                    data: $('#add_gateway_account').serialize(),
                    success: function(data) {
                        if(data.value == true){
                            result = true;
                        } else {
                            result = false;
                        }
                    }
                });
                return result;
            });

        //Add Form Validation
        $("#add_gateway_account").validate({
            rules: {
                account_id: {
                    required: true,
                    alphnumericndash: true,
                    verifyUnique: true,
                    maxlength: 15
                },
                description: {
                    required: true,
                    alphnumericnspacendash: true
                },
                gateway: {
                    required: true
                },
                e_comm_website: {
                    required: true,
                    url: false
                },
                website_description: {
                    required: true,
                    alphnumericnspacendash: true
                },
                status: {
                    required: true
                }
            }
        })

        //Edit Form Validation
        $("#edit_gateway_account").validate({
            rules: {
                description: {
                    required: true,
                    alphnumericnspacendash: true
                },
                e_comm_website: {
                    required: true,
                    url: false
                },
                website_description: {
                    required: true,
                    alphnumericnspacendash: true
                },
                status: {
                    required: true
                }
            }
        })
    </script>
@endsection
@endsection
