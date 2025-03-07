
@extends('layouts.app')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<div class="row justify-content-center h-100 align-items-center">
    <div class="col-md-6">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <h4 class="text-center mb-4"><b>Generate QR Code</b></h4>
                        <form action="{{ route('saveQRdata') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="mb-1"><strong>Enter Customer Name</strong></label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name') }}" placeholder="Enter customer name">
                                @error('customer_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="mb-1"><strong>Enter Amount</strong></label>
                                <input type="text" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}" placeholder="Enter amount should be in decimal such as 100.00">
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="mb-1"><strong>Enter Invoice Number</strong></label>
                                <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="Enter invoice here">
                                @error('invoice_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-block">Generate</button>
                            </div>
                        </form>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
