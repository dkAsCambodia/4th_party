<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use App\Models\Qrgenerater;
use Illuminate\Support\Facades\File;
use App\Models\Merchant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


use App\Models\PaymentDetail;

class QRCodeController extends Controller
{
    public function index(Request $request)
    {
        return view('fc.fcqrgenerate');
    }

    public function generateQRCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required',
            'amount' => 'required',
            'amount' => ['required', 'numeric'],
            // 'amount' => ['required', 'numeric', 'min:300.00'],
            // 'invoice_number' => ['required', 'regex:/^[a-zA-Z0-9\-\/#]+$/', Rule::unique('qrgeneraters', 'invoice_number'),],
            'invoice_number' => ['required', 'regex:/^[a-zA-Z0-9\-\/#]+$/'],
        ], [
            'customer_name.required' => 'Customer name is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            // 'amount.min' => 'Amount must be 300.00 or greater.',
            'invoice_number.required' => 'Invoice number is required.',
            'invoice_number.regex' => 'Invoice number can only contain letters, numbers, and hyphens.',
            'invoice_number.unique' => 'Invoice number must be unique.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $customer_name=$request->customer_name;
        $amount=$request->amount;
        $invoice_number=$request->invoice_number;
        $Currency=$request->Currency;
        if($request->Currency=='USDT'){
            // http://127.0.0.1:8000/m2p/payintest?amount=1000&Currency=THB&merchant_code=testmerchant005
            $url = url('/m2p/payintest?amount='.$amount.'&Currency=USD&merchant_code=FCmerchant001');
        }else{
                // $url = url('/fc/s2pdeposit/'.base64_encode($amount).'/'.base64_encode($invoice_number).'/'.base64_encode($customer_name));
                $url = url('/fc/r2pdeposit/'.base64_encode($amount).'/'.base64_encode($invoice_number).'/'.base64_encode($customer_name));
        }
      
        // $path = 'assets/images/qrcode/'.$invoice_number.'-'.$amount.'.png';
        $addRecord = [
            'customer_name' => $customer_name,
            'amount' => $amount,
            'invoice_number' => $invoice_number,
            'qr_img_url' => $url,
            'status' => '1',
        ];
        Qrgenerater::create($addRecord);
        return view('fc.showQR', compact('url','amount','invoice_number','Currency'));
    }

    
    public function fcs2pDeposit($amount, $invoice_number, $customer_name)
    {
        $amount=base64_decode($amount);
        $invoice_number=base64_decode($invoice_number);
        $customer_name=base64_decode($customer_name);
        // return view('fc.deposit-form', compact('amount','invoice_number','customer_name'));   for spaadPay
        return view('fc.deposit-form-richpay', compact('amount','invoice_number','customer_name'));
    }

    // public function saveQrCode(Request $request)
    // {
    //     // Validate the incoming data
    //     $request->validate([
    //         'image' => 'required|string',
    //         'fileName' => 'required|string'
    //     ]);

    //     // Decode the image data
    //     $imageData = $request->input('image');
    //     $fileName = $request->input('fileName');

    //     // Remove the data URL prefix to get raw data
    //     $imageData = str_replace('data:image/png;base64,', '', $imageData);
    //     $imageData = str_replace(' ', '+', $imageData);

    //     // Decode the base64 data
    //     $decodedImage = base64_decode($imageData);

    //     // Save the image to the public/assets/images/qrcode/ directory
    //     $filePath = public_path("assets/images/qrcode/{$fileName}");
    //     File::put($filePath, $decodedImage);

    //     return response()->json(['message' => 'QR Code saved successfully!']);
    // }

    public function listQrCode(Request $request)
    {
        if ($request->ajax()) {

            $data = Qrgenerater::query()
                ->where('status', '1')
                ->orderByDesc('created_at')
                ->select('*');

                return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->editColumn('status', function ($data) {
                    // $fileName = $data->qr_img_url; // Assuming `file_name` is the column holding the file name
                    // $filePath = asset($fileName);
                    $url = $data->qr_img_url;
                    $qrCode = QrCode::size(50)->generate($url);
                    return ' <div>' . $qrCode . '</div>';
                })
                ->addColumn('action', function ($data) use ($request) {
                    $url = $data->qr_img_url;
                    $qrCode = QrCode::size(200)->generate($url);
                    // $fileName = $data->qr_img_url; // Assuming `file_name` is the column holding the file name
                    // $filePath = asset($fileName);
                   
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.View") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                            <tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Customer Name") . '</td>
                                                    <td>' . $data->customer_name . '</td>
                                                   
                                                    <td colspan="2" rowspan="4">' . $qrCode . '</td>
                                                
                                            </tr>';
                   
                    $action .= '<tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Invoice Number") . '</td>
                                                    <td>' . $data->invoice_number . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Amount") . '</td>
                                                    <td>' . $data?->amount . '</td>
                                                </tr>
                                                 <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                    <td>' . $data?->created_at . '</td>
                                                </tr>
                                              
                                               
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                       
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('invoice_number', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('customer_name', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('amount', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('fc.listQRCode');
    }

    public function exportMerchantInvoice($date=null)
    {
        $data = Qrgenerater::where('status', '1')
            ->select('amount', 'invoice_number', 'customer_name', 'qr_img_url', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $data_array[] = [
            trans('messages.Customer Name'),
            trans('messages.Invoice Number'),
            trans('messages.View'),
            trans('messages.Amount'),
            trans('messages.Created Time'),
            
        ];

        foreach ($data as $item) {
            $url='';
            $url = 'https://payin.implogix.com/FCdeposit/deposit.php?aa='.base64_encode($item->amount).'&in='.base64_encode($item->invoice_number).'&cu='.base64_encode($item->customer_name);
            // $qrCode = QrCode::size(200)->generate($url);
            $data_array[] = [
                trans('messages.Customer Name') => $item->customer_name,
                trans('messages.Invoice Number') => $item->invoice_number,
                trans('messages.View') =>  $item->qr_img_url,
                trans('messages.Amount') => number_format($item->amount, 2),
                trans('messages.Created Time') => $item->created_at->format('Y-m-d H:i:s'),
            ];
        }

        exportExcel($data_array, $date, 'invoice');
    }

    // public function fcs2pWithdrawalForm(Request $request)
    // {
    //     return view('fc.withdraw-form');
    // }

    public function fcr2pWithdrawalForm(Request $request)
    {
        return view('fc.withdraw-form-richpay');
    }
        
}   
