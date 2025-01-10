<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
class QRCodeController extends Controller
{
    public function index(Request $request)
    {
        return view('fcqrgenerate');
    }

    public function generateQRCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:300.00'],
            'invoice_number' => ['required', 'regex:/^[a-zA-Z0-9\-]+$/'],
        ], [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be 300.00 or greater.',
            'invoice_number.required' => 'Invoice number is required.',
            'invoice_number.regex' => 'Invoice number can only contain letters, numbers, and hyphens.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $amount=$request->amount;
        $invoice_number=$request->invoice_number;

        $url = 'http://localhost/payin/FCdeposit/deposit.php?aa='.base64_encode($amount).'&in='.base64_encode($invoice_number); 
        // $url = 'https://payin.implogix.com/FCdeposit/deposit.php?aa='.base64_encode($amount).'&in='.base64_encode($invoice_number); 

        return view('showQR', compact('url','amount','invoice_number'));
    }

        
}   
