<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use App\Models\Qrgenerater;
use Illuminate\Support\Facades\File;
use App\Models\SettleRequest;
use App\Models\Merchant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
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
            'customer_name' => 'required',
            'amount' => ['required', 'numeric', 'min:300.00'],
            'invoice_number' => ['required', 'regex:/^[a-zA-Z0-9\-\/#]+$/', Rule::unique('qrgeneraters', 'invoice_number'),],
        ], [
            'customer_name.required' => 'Customer name is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be 300.00 or greater.',
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
        // $url = 'http://localhost/payin/FCdeposit/deposit.php?aa='.base64_encode($amount).'&in='.base64_encode($invoice_number).'&cu='.base64_encode($customer_name); 
        $url = 'https://payin.implogix.com/FCdeposit/deposit.php?aa='.base64_encode($amount).'&in='.base64_encode($invoice_number).'&cu='.base64_encode($customer_name);
        
        $path = 'assets/images/qrcode/'.$invoice_number.'-'.$amount.'.png';

        $addRecord = [
            'customer_name' => $customer_name,
            'amount' => $amount,
            'invoice_number' => $invoice_number,
            'qr_img_url' => $path,
            'status' => '1',
        ];
        Qrgenerater::create($addRecord);

        return view('showQR', compact('url','amount','invoice_number'));
    }

    public function saveQrCode(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'image' => 'required|string',
            'fileName' => 'required|string'
        ]);

        // Decode the image data
        $imageData = $request->input('image');
        $fileName = $request->input('fileName');

        // Remove the data URL prefix to get raw data
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);

        // Decode the base64 data
        $decodedImage = base64_decode($imageData);

        // Save the image to the public/assets/images/qrcode/ directory
        $filePath = public_path("assets/images/qrcode/{$fileName}");
        File::put($filePath, $decodedImage);

        return response()->json(['message' => 'QR Code saved successfully!']);
    }

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
                    $fileName = $data->qr_img_url; // Assuming `file_name` is the column holding the file name
                    $filePath = asset($fileName);
                    return '<img src="' . $filePath . '" alt="QR Code" style="width: 50px; height: 50px;" />';
                })
                ->addColumn('action', function ($data) use ($request) {
                    $fileName = $data->qr_img_url; // Assuming `file_name` is the column holding the file name
                    $filePath = asset($fileName);
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.detail") . '</h5>
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
                                                   
                                                    <td colspan="2" rowspan="4"><img src="' . $filePath . '" alt="QR Code" style="width: 200px; height: 200px;" /></td>
                                                
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

        return view('listQRCode');
    }

        
}   
