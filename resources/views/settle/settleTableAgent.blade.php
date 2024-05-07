@extends('layouts.master')
@section('content')
    <style>
        .flex.justify-between.flex-1.sm\:hidden {
            display: none;
        }
        img, svg {
            vertical-align: middle;
        }
        table.dataTable tbody td {
          padding: 0px 15px !important;
        }
    </style>
    {{-- message --}}
    {!! Toastr::message() !!}

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="col-md-6 breadcrumb">
                    <li class="breadcrumb-item active"><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li class="breadcrumb-item"> {{ __('messages.All Payments') }} </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.Payments List') }}</h4>
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table id="example2" class="display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.Merchant Code') }}</th>
                                            {{-- <th>{{ __('messages.Merchant Code') }}</th> --}}
                                            <th>{{ __('messages.Transaction ID') }}</th>
                                            <th>{{ __('messages.Amount') }}</th>
                                            {{-- <th>{{ __('messages.Customer Name') }}</th> --}}


                                            <th>{{ __('messages.Order Id') }}</th>
                                            <th>{{ __('messages.Order Date') }}</th>
                                            {{-- <th>{{ __('messages.Order Status') }}</th> --}}
                                            <th>{{ __('messages.Currency') }}</th>
                                            {{-- <th>{{ __('messages.TransId') }}</th> --}}
                                            {{-- <th>{{ __('messages.ErrDesc') }}</th> --}}


                                            {{-- <th>{{ __('messages.Callback URL') }}</th> --}}
                                            {{-- <th>{{ __('messages.Payment Channel') }}</th>
                                            <th>{{ __('messages.Payment Method') }}</th>
                                            <th>{{ __('messages.Payment Source') }}</th> --}}
                                            {{-- <th>{{ __('messages.Payment Status') }}</th> --}}
                                            {{-- <th>{{ __('messages.Created Date') }}</th> --}}
                                            {{-- <th>Action</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payment_table as $key => $items)
                                            <tr>
                                                {{-- <td>{{ $items->merchant_code }}</td> --}}
                                                <td class="merchant_code">{{ $items->merchant_code }}</td>
                                                <td class="transaction_id">{{ $items->transaction_id }}</td>
                                                <td class="amount">{{ $items->amount }}</td>
                                                {{-- <td class="customer_name">{{ $items->customer_name }}</td> --}}


                                                <td class="order_id">{{ $items->order_id }}</td>
                                                <td class="order_date">{{ $items->order_date }}</td>
                                                {{-- <td class="order_status">{{ $items->order_status }}</td> --}}
                                                <td class="Currency">{{ $items->Currency }}</td>
                                                {{-- <td class="TransId">{{ $items->TransId }}</td> --}}
                                                {{-- <td class="ErrDesc">{{ $items->ErrDesc }}</td> --}}



                                                {{-- <td class="callback_url">{{ $items->callback_url }}</td>
                                                <td class="payment_channel">{{ $items->payment_channel }}</td>
                                                <td class="payment_method">{{ $items->payment_method }}</td>
                                                <td class="payment_source">{{ $items->payment_source }}</td> --}}

                                                {{-- <td>
                                                    @if ($items->payment_status == 'pending')
                                                        <span class="badge light badge-warning">{{ $items->payment_status }}</span>
                                                    @endif
                                                    @if ($items->payment_status == 0)
                                                        <span class="badge light badge-danger">{{ __('messages.Failed') }}</span>
                                                    @endif
                                                    @if ($items->payment_status == 1)
                                                        <span class="badge light badge-success">{{ __('messages.Success') }}</span>
                                                    @endif
                                                </td> --}}

                                                {{-- <td class="join_date">{{ $items->created_at }}</td> --}}

                                                {{-- <td class="">
                                                    <input id="withdrawalId{{ $items->id }}"
                                                        onclick="getWithdrawalData({{ $items->id }}, '{{ $items->transaction_id }}', {{ $items->amount }}, '{{ $items->customer_name }}')"
                                                        type="checkbox">
                                                </td> --}}

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-5">
                                {!! $payment_table->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
