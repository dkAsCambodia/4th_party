@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}

    <style>
        .radio-toolbar input[type="radio"] {
            display: none;
        }

        .radio-toolbar label {
            display: inline-block;
            background-color: gray;
            padding: 0px 12px;
            font-family: Arial;
            font-size: 16px;
            cursor: pointer;
            color: white;
            margin-right: 14px;
            border-radius: 7px;
        }

        .radio-toolbar input[type="radio"]:checked+label {
            background-color: blue;
        }

        .checkedraam {
            color: orange;
        }
    </style>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.Add Payment ') }} </li>
                </ol>
                <div class="col-md-6">
                @if(auth()->user()->can('add/map-payment'))
                    <button type="submit" class="btn btn-danger shadow btn-xs me-1 add_record" style="float: right;" data-toggle="modal"
                        data-target="#add_product">
                        {{ __('messages.Add Configuration') }}
                    </button>
                @endif
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">

                            <div class="radio-toolbar mb-2">
                                @foreach ($paymentUrl as $paymentUrlVal)
                                    <input id="radio1{{ $paymentUrlVal->id }}" value="{{ $paymentUrlVal->id }}"
                                        type="radio" name="p_size"
                                        @if(Request::segment(4)==$paymentUrlVal->id) checked @endif
                                    >

                                    <label onclick="goToAddMaping({{ $paymentUrlVal->id }})"
                                        for="radio1{{ $paymentUrlVal->id }}">{{ $paymentUrlVal->url_name }}</label>
                                @endforeach
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example2" class="display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Sr. No.') }}</th>
                                            <th>{{ __('messages.Value') }}</th>
                                            <th>{{ __('messages.Agent Rate') }}</th>
                                            <th>{{ __('messages.Merchant Rate') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Joining Date') }}</th>
                                            @if(auth()->user()->can('edit/map-payment') || auth()->user()->can('Channel: Delete Channel') )
                                            <th>{{ __('messages.Action') }}</th>
                                            @endif
                                            {{-- <th></th>
                                            <th></th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($map_table as $key => $items)
                                            <tr>
                                                <td>P00{{ $items->id }}</td>
                                                <td class="map_value">{{ $items->map_value }}</td>
                                                <td class="agent_commission">{{ $items->agent_commission }}</td>
                                                <td class="merchant_commission">{{ $items->merchant_commission }}</td>
                                                <td>
                                                    @if($items->status == 'Enable')
                                                        <span class="badge light badge-success">{{ $items->status }}</span>
                                                    @else
                                                        <span class="badge light badge-danger">{{ $items->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="join_date">{{ $items->created_at }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                    @if(auth()->user()->can('edit/map-payment'))
                                                        <a class="btn btn-primary shadow btn-xs sharp me-1 edit_channel"
                                                            href="#" data-toggle="modal" data-target="#edit_user"><i
                                                                class="fas fa-pencil-alt"></i></a>
                                                    @endif
                                                    @if(auth()->user()->can('Channel: Delete Channel'))
                                                        <a class="btn btn-danger shadow btn-xs sharp delete_user"
                                                            href="#" data-toggle="modal" data-target="#delete_user"><i
                                                                class="fa fa-trash"></i></a>
                                                    @endif
                                                    </div>
                                                </td>
                                                <td class="channel_id" style="display: none">{{ $items->id }}</td>
                                                <td class="status" style="display: none">{{ $items->status }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>{{ __('messages.Sr. No.') }}</th>
                                            <th>{{ __('messages.Value') }}</th>
                                            <th>{{ __('messages.Status') }}</th>
                                            <th>{{ __('messages.Joining Date') }}</th>
                                            <th>{{ __('messages.Action') }}</th>
                                            {{-- <th></th>
                                            <th></th> --}}
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-5">
                                {!! $map_table->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Expense Modal -->
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
                    <form action="{{ route('add/map-payment') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Value') }}</label>
                                <input required type="text" class="form-control" name="map_value">
                                <input type="hidden" class="form-control" name="payment_url_id" value="{{ Request::segment(4) }}">
                                <input type="hidden" class="form-control" name="merchant_id" value="{{ Request::segment(3) }}">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Agent Rate') }}</label>
                                <input required type="text" class="form-control" name="agent_commission">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Rate') }}</label>
                                <input required type="text" class="form-control" name="merchant_commission">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select required name="status" class="form-control" aria-label="Default select example">
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button type="submit" class="btn btn-danger shadow btn-xs me-1 add_record">{{ __('messages.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Expense Modal -->
    <!-- Edit Expense Modal -->
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
                    <form action="{{ route('edit/map-payment', 1) }}" method="POST" id="form_edit_channel">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Map Value') }}</label>
                                <input required type="text" class="form-control" name="map_value" id="map_value">
                                <input type="hidden" class="form-control" name="payment_url_id" value="{{ Request::segment(4) }}">
                                <input type="hidden" class="form-control" name="merchant_id" value="{{ Request::segment(3) }}">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Agent Rate') }}</label>
                                <input required type="text" class="form-control" name="agent_commission" id="agent_commission">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Merchant Rate') }}</label>
                                <input required type="text" class="form-control" name="merchant_commission" id="merchant_commission">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ __('messages.Status') }}</label>
                                <select id="status" required name="status" class="form-control"
                                    aria-label="Default select example">
                                    <option value="Enable">{{ __('messages.Enable') }}</option>
                                    <option value="Disable">{{ __('messages.Disable') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button type="submit" class="btn btn-danger shadow btn-xs me-1 add_record">{{ __('messages.Save') }}</button>
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
                        <h3>{{ __('messages.Delete Map') }}</h3>
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
    {{-- show data on model or edit --}}
    <script>
        $(document).on('click', '.edit_channel', function() {
            var _this = $(this).parents('tr');
            $('#map_value').val(_this.find('.map_value').text());
            $('#agent_commission').val(_this.find('.agent_commission').text());
            $('#merchant_commission').val(_this.find('.merchant_commission').text());

            document.getElementById('status').value = _this.find('.status').text();
            document.getElementById('status').value = _this.find('.status').text();
            var action = document.getElementById('form_edit_channel').action;
            const editedText = action.slice(0, -1);
            document.getElementById('form_edit_channel').action = editedText + _this.find('.channel_id').text();

        });
    </script>

    {{-- delete user --}}
    <script>
        function goToAddMaping(urlId) {
            window.location = "{{ url('admin/add-payment-map') }}/" + {{ Request::segment(3) }} + "/" + urlId;
        }

        $(document).on('click', '.delete_user', function() {
            var _this = $(this).parents('tr');
            $('#e_id').val(_this.find('.channel_id').text());
        });
    </script>
@endsection
@endsection
