
@extends('layouts.app')
@section('content')
<style>
    .qr-container {
        text-align: center;
        margin-top: 50px;
    }
    .qr-btn {
        display: block;
        margin-top: 20px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
    }
    .qr-btn:hover {
        background-color: #45a049;
    }
</style>
{{-- message --}}
{!! Toastr::message() !!}
<div class="row justify-content-center h-100 align-items-center">
    <div class="col-md-6">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    
                    <div class="qr-container">
                        <!-- Display QR Code as SVG -->
                        {!! QrCode::size(300)->generate($url) !!}
                        <center><br/>
                            <a href="{{$url}}" target="_blank"><p><b>{{ $invoice_number }}-{{$amount}}-{{$Currency}}.png</b></p></a>
                            
                            <button class="qr-btn" id="downloadQR" 
                                    data-invoice-number="{{ $invoice_number }}" 
                                    data-amount="{{ $amount }}"
                                    data-Currency="{{ $Currency }}">
                                <i class="fa fa-download"></i> Download QR Code
                            </button>
                        </center><br/>
                    </div>
                    
                    {{-- <script>
                        // Function to convert SVG to PNG and send it to the server
                        function saveQrCode() {
                            var qrCodeSvg = document.querySelector('svg');
                            var svgData = new XMLSerializer().serializeToString(qrCodeSvg);
                            var svgBlob = new Blob([svgData], { type: 'image/svg+xml' });
                            var svgUrl = URL.createObjectURL(svgBlob);
                            var img = new Image();
                            img.src = svgUrl;
                    
                            // Get dynamic name values
                            var invoiceNumber = "{{ $invoice_number }}";
                            var amount = "{{ $amount }}";
                    
                            var canvas = document.createElement('canvas');
                            var ctx = canvas.getContext('2d');
                            img.onload = function () {
                                canvas.width = img.width;
                                canvas.height = img.height;
                                ctx.drawImage(img, 0, 0);
                    
                                // Convert the canvas to PNG
                                var imgData = canvas.toDataURL('image/png');
                    
                                // Send the image to the server via an AJAX POST request
                                fetch('/save-qr-code', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include the CSRF token for Laravel
                                    },
                                    body: JSON.stringify({
                                        image: imgData,
                                        fileName: `${invoiceNumber}-${amount}.png`
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('QR Code saved successfully:', data);
                                })
                                .catch(error => {
                                    console.error('Error saving QR Code:', error);
                                });
                            };
                        }
                    
                        // Automatically save QR code on page load
                        window.onload = saveQrCode;
                    </script> --}}
                    <script>
                        document.getElementById('downloadQR').addEventListener('click', function () {
                            var qrCodeSvg = document.querySelector('svg');
                            var svgData = new XMLSerializer().serializeToString(qrCodeSvg);
                            var svgBlob = new Blob([svgData], { type: 'image/svg+xml' });
                            var svgUrl = URL.createObjectURL(svgBlob);
                            var img = new Image();
                            img.src = svgUrl;
                    
                            // Get dynamic name values
                            var invoiceNumber = this.getAttribute('data-invoice-number');
                            var amount = this.getAttribute('data-amount');
                            var Currency = this.getAttribute('data-Currency');
                    
                            // Create a temporary canvas to draw the SVG
                            var canvas = document.createElement('canvas');
                            var ctx = canvas.getContext('2d');
                            img.onload = function () {
                                // Set the canvas size equal to the image size
                                canvas.width = img.width;
                                canvas.height = img.height;
                                ctx.drawImage(img, 0, 0);
                    
                                // Convert the canvas to PNG or JPEG format
                                var imgData = canvas.toDataURL('image/png'); // Change 'image/png' to 'image/jpeg' for JPEG format
                    
                                // Create a download link
                                var a = document.createElement('a');
                                a.href = imgData;
                                a.download = `${invoiceNumber}-${amount}-${Currency}.png`; // Dynamic file name
                                a.click();
                            };
                        });
                    </script>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
