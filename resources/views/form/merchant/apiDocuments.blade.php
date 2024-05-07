@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <style>
        .pricing-plan li {
            border-bottom: 1px dotted #ededed;
            font-size: 16px;
        }

        .pricing-plan {
            font-family: "Montserrat", sans-serif;
            margin-bottom: 25px;
        }

        .pricing-plan .card-header {
            font-family: "Montserrat", sans-serif;
            font-weight: 900;
            text-transform: uppercase;
        }

        .card li {
            padding: 8px 0;
        }

        .set-price {
            background: #225b8b !important;
            color: #ffffff;
        }

        .starter {
            background: #2da2bc !important;
            color: #ffffff;
        }

        .advanced {
            background: #f79125 !important;
            color: #ffffff;
        }

        .business {
            background: #cc2836 !important;
            color: #ffffff;
        }
    </style>
    <style>
        .form-control-sushil {
            display: block;
            width: 100%;
            height: 24px;
            /* padding: 6px 12px; */
            /* font-size: 14px; */
            /* line-height: 1.42857143; */
            color: #555;
            background-color: #ffffff00;
            /* background-image: none; */
            /* border: 1px solid #ccc; */
            /* border-radius: 4px; */
            color: #ffffff;
            /* outline: 0; */
            /* border-width: 0 0 2px; */
            border-color: #ffffff00;
            text-align: center;
        }

        .form-control-sushil[readonly] {
            background-color: #ffffff00 !important;
        }

        label#api_doc-error {
            color: red;
        }
    </style>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') ?? '--' }}">{{ __('messages.Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">{{ __('messages.Api Documentation') }}</li>
                </ol>
                @if (Auth()->user()->role_name === 'Merchant')
                    <div class="" style=" text-align: end; margin-top: -26px;">
                        <button class="btn btn-danger shadow btn-xs me-1" download>
                            <a href="{{ url($apiData->api_doc_file ?? '/apiDocs/Api_Documentation.pdf') }}"
                                class="text-light" download>
                                {{ __('messages.API Download') }}
                            </a>
                        </button>
                    </div>
                @endif
                @if (auth()->user()->can('ApiDocument: Add ApiDocument'))
                    @if (Auth()->user()->role_name === 'Admin')
                        <div class="" style=" text-align: end; margin-top: -26px;">
                            <button id="showAddButton" class="btn btn-primary shadow btn-xs me-1" download>
                                {{ __('messages.Add Document') }}
                            </button>
                        </div>
                    @endif
                @endif
            </div>
            <div id="hideAndShowAddForm" style="display: none;">
                <div class="page-titles">
                    <form action="{{ route('ApiDocument: Add ApiDocument') }}" class="row" id="add_api_form"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mt-3 col-md-3">
                            <input type="file" class="form-control" name="api_doc" required id="api_doc">
                        </div>
                        <div class="mt-3 col-md-3">
                            <div class="">
                                <button type="submit" class="btn btn-danger shadow btn-xs">{{ __('messages.Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <iframe
                                src="{{ url($apiData->api_doc_file ?? '/apiDocs/Api_Documentation.pdf') }}#toolbar=0&navpanes=0&scrollbar=0"
                                width="100%" height="720px">
                            </iframe>
                            {{-- <form action="{{ url('admin/insert-api-document') ?? '--' }}" method="post">
                                @csrf
                                <div id="htmlContent" class="pricing-plan card-group d-flex">
                                    <div class="card set-price p-1  d-lg-block">
                                        <div class="card-header text-center pb-4 item" style="height: 60px">
                                            <span class="h3 text-white">Parameter Name</span>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <ul class="list-unstyled text-right">
                                                <li>Product ID</li>
                                                <li>Merchant Code</li>
                                                <li>Customer Name</li>
                                                <li>Customer ID</li>
                                                <li>Transaction ID</li>
                                                <li>Call Back URL</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="card p-1 starter">
                                        <div class="card-header text-center pb-4 item" style="height: 60px">
                                            <span class="h3 text-white">Mandatory</span>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <ul class="list-unstyled text-center">
                                                <li>
                                                    <i class="fa fa-check" data-unicode="f00c"></i>
                                                </li>
                                                <li><i class="fa fa-check" data-unicode="f00c"></i></li>
                                                <li><i class="fa fa-times" data-unicode="f00c"></i></li>
                                                <li><i class="fa fa-times" data-unicode="f00c"></i></li>
                                                <li><i class="fa fa-check" data-unicode="f00c"></i></li>
                                                <li><i class="fa fa-check" data-unicode="f00c"></i></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card advanced p-1">
                                        <div class="card-header text-center item" style="height: 60px">
                                            <span class="h3 text-white">Description</span>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <ul class="list-unstyled text-center">
                                                <li>
                                                    <span class=""><input type="text" name="product_id_desc"
                                                            value="{{ $apiData->product_id_desc ?? '--' }}"
                                                            class="form-control-sushil params" readonly />
                                                    </span>
                                                </li>
                                                <li><span class=""><input type="text" name="merchant_code_desc"
                                                            value="{{ $apiData->merchant_code_desc ?? '--' }}"
                                                            class="form-control-sushil params" readonly />
                                                    </span></li>
                                                <li><span class=""><input type="text" name="customer_name_desc"
                                                            value="{{ $apiData->customer_name_desc ?? '--' }}"
                                                            class="form-control-sushil params" readonly />
                                                    </span></li>
                                                <li><span class=""><input type="text" name="customer_id_desc"
                                                            value="{{ $apiData->customer_id_desc ?? '--' }}"
                                                            class="form-control-sushil params" readonly />
                                                    </span></li>
                                                <li><span class=""><input type="text" name="transaction_id_desc"
                                                            value="{{ $apiData->transaction_id_desc ?? '--' }}"
                                                            class="form-control-sushil params" readonly />
                                                    </span></li>
                                                <li><span class=""><input type="text" name="call_back_url_desc"
                                                            class="form-control-sushil params"
                                                            value="{{ $apiData->call_back_url_desc ?? '--' }}" readonly />
                                                    </span></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card business p-1">
                                        <div class="card-header text-center pb-4 item" style="height: 60px">
                                            <span class="h3 text-white">Examples</span>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <ul class="list-unstyled text-center">
                                                <li>
                                                    <span class="">
                                                        <input type="text" name="product_id_examp"
                                                            class="form-control-sushil params" value="{{ $apiData->product_id_examp ?? '--' }}" readonly />
                                                    </span>
                                                </li>
                                                <li><span class="">
                                                        <input type="text" name="merchant_code_examp"
                                                            class="form-control-sushil params"
                                                            value="{{ $apiData->merchant_code_examp ?? '--' }}" readonly />
                                                    </span>
                                                </li>
                                                <li><span class="">
                                                        <input type="text" name="customer_name_examp"
                                                            class="form-control-sushil params"
                                                            value="{{ $apiData->customer_name_examp ?? '--' }}" readonly />
                                                    </span>
                                                </li>
                                                <li><span class="">
                                                        <input type="text" name="customer_id_examp"
                                                            class="form-control-sushil params"
                                                            value="{{ $apiData->customer_id_examp ?? '--' }}" readonly />
                                                    </span>
                                                </li>
                                                <li><span class="">
                                                        <input type="text" name="transaction_id_examp"
                                                            class="form-control-sushil params"
                                                            value="{{ $apiData->transaction_id_examp ?? '--' }}" readonly />
                                                    </span>
                                                </li>
                                                <li><span class="">
                                                        <input type="text" name="call_back_url_examp"
                                                            class="form-control-sushil params"
                                                            value="{{ $apiData->call_back_url_examp ?? '--' }}" readonly />
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @if (Auth()->user()->role_name === 'Admin')
                                    <button type="submit" id="showEditButton" class="btn btn-danger shadow btn-xs"
                                        style="float: right; display: none;">
                                        Save
                                    </button>
                                    <button type="button" onclick="removeReadOnly();" id="hideEditButton"
                                        class="btn btn-success shadow btn-xs" style="float: right;">
                                        Edit
                                    </button>
                                @endif
                            </form> --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@section('script')
    <script>
        $(document).ready(function() {
            $("#showAddButton").click(function() {
                $("#hideAndShowAddForm").toggle(700);
                $('#api_doc').removeAttr('maxlength');
            });
        });


        @if (Auth()->user()->role_name === 'Admin')
            function removeReadOnly() {
                $('.form-control-sushil').removeAttr("readonly");
                $('#hideEditButton').hide();
                $('#showEditButton').show();
            }
        @endif
        // Add Form Validation
        $("#add_api_form").validate({
            rules: {
                api_doc: {
                    required: true
                },
            }
        })
    </script>

    {{-- @endsection --}}
@endsection
@endsection
