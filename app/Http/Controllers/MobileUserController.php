<?php

namespace App\Http\Controllers;
use App\Models\MobileUser;
use App\Models\MobileDevice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Jobs\PushNotificationJob;
class MobileUserController extends Controller
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
        $datas = MobileUser::latest()->paginate();
        
        return view('mobileUserManagement.index', compact('datas','search'));
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
        $data = MobileUser::findOrFail($id);
        return view('mobileUserManagement.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = MobileUser::findOrFail($id);
        return view('mobileUserManagement.show', compact('data'));
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
        if( MobileUser::findOrFail($id)->delete() ) {
            flash()->success('User has been deleted');
        } else {
            flash()->success('User not deleted');
        }
        return redirect()->back();
    }

    public function notification(Request $request,$id)
    {
        
        
        $rp = push_notify_fcm($request->title , $request->contents , $id);
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
