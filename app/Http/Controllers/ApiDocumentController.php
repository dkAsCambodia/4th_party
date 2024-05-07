<?php

namespace App\Http\Controllers;

use App\Models\ApiDocument;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ApiDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiDocument  $apiDocument
     * @return \Illuminate\Http\Response
     */
    public function show(ApiDocument $apiDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApiDocument  $apiDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(ApiDocument $apiDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApiDocument  $apiDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiDocument $apiDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiDocument  $apiDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiDocument $apiDocument)
    {
        //
    }



    public function apiDocuments()
    {
        $apiData = ApiDocument::latest()->where('locale', session()->get('locale') ?? 'ch')->first();
        return view('form.merchant.apiDocuments', compact('apiData'));
    }

    public function addApiDocuments(Request $request)
    {
        $mytime = Carbon::now();
        $currentDateTime = str_replace(' ', '_', $mytime->parse($mytime->toDateTimeString())->format('Y-m-d H-i-s'));

        $fileName = pathinfo($request->file('api_doc')->getClientOriginalName(), PATHINFO_FILENAME);
        $fileExtension = $request->file('api_doc')->guessExtension();

        $finamFileName = $request->file('api_doc')->move('apiDocs', $fileName . '_' . session()->get('locale') . '_' . $currentDateTime . '.' . $fileExtension);

        $apiData = ApiDocument::latest()->where('locale', session()->get('locale') ?? 'ch')->first();
        if ($apiData) {
            $apiData->update(['api_doc_file' => $finamFileName, 'locale' => session()->get('locale') ?? 'ch']);
        } else {
            ApiDocument::create(['api_doc_file' => $finamFileName, 'locale' => session()->get('locale') ?? 'ch']);
        }

        $messages2 = __('messages.Added Successfully');
        Toastr::success($messages2, 'Success');

        return redirect()->back();
    }

    public function insertApiDocument(Request $request)
    {
        $apiData = ApiDocument::latest()->first();
        if ($apiData) {
            $apiData->update($request->all());
        } else {
            ApiDocument::create($request->all());
        }

        $messages2 = __('messages.Added Successfully');
        Toastr::success($messages2, 'Success');

        return redirect()->back();
    }
}
