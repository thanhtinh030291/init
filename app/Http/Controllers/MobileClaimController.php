<?php

namespace App\Http\Controllers;
use App\Models\MobileClaim;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MobileClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $search = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
        ];
        $datas = MobileClaim::latest()->paginate();
        
        return view('mobileClaimManagement.index', compact('datas','search'));
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = MobileClaim::findOrFail($id);
        $files = $data->mobile_claim_file;
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($files as $key => $value) {
            $initialPreview[] = asset(config('constants.srcStorage').$value->disk ."/".$value->url);
            $path = storage_path('app'.config('constants.srcUpload').$value->disk ."/".$value->url);
            $info =  pathinfo($path);
            $filesize = filesize($path); // bytes
            $filesize = round($filesize / 1024 , 1);
            $arr_map_type = [
                'pdf' => 'pdf',
                'tiff' => 'gdocs',
                'doc' => 'office',
                'docx' => 'office',
                'xls' => 'office',
                'ppt' => 'office',
                'text' => 'text',
                'html' => 'html',
                'mp4' => 'video'
            ];
            $initialPreviewConfig[] = [
                'type' => data_get($arr_map_type , $info['extension'] ,'image'),
                'size' => $filesize,
                'caption' => $info['basename'],
                'url' => '/file-upload-batch/2',
                'key' => $key+1
            ];
        }

        
        return view('mobileClaimManagement.show', compact('data', 'initialPreview', 'initialPreviewConfig'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = MobileClaim::findOrFail($id);
        return view('mobileClaimManagement.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if( MobileClaim::findOrFail($id)->delete() ) {
            flash()->success('User has been deleted');
        } else {
            flash()->success('User not deleted');
        }
        return redirect()->back();
    }

    public function notification(Request $request,$id)
    {
        
        $data = MobileClaim::findOrFail($id);
        $data->is_read = 0;
        $data->save();
        $rp = push_notify_fcm($request->title , $request->contents , $data->mobile_user_id);

        if($rp == false){
            $request->session()->flash(
                'errorStatus', 
                'Không tìm thấy divice token'
            );
            return redirect()->back();
        }
        flash()->success('Send ok');
        return redirect()->back();
    }
}
