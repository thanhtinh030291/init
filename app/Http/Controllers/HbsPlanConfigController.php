<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\PlanHbsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;


class HbsPlanConfigController extends Controller
{
    
    //use Authorizable;
    public function __construct()
    {
        //$this->authorizeResource(PlanHbsConfig::class);
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['search_params'] = [
            'plan_desc' => $request->get('plan_desc'),
            'plan_id' => $request->get('plan_id'),
            'rev_no' => $request->get('rev_no'),
            'ready' => $request->get('ready'),
        ];
        $PlanHbsConfig = PlanHbsConfig::findByParams($data['search_params'])->orderBy('id', 'desc');
        $data['admin_list'] = User::getListIncharge();
        //pagination result
        $data['limit_list'] = config('constants.limit_list');
        $data['limit'] = $request->get('limit');
        $per_page = !empty($data['limit']) ? $data['limit'] : Arr::first($data['limit_list']);
        $data['data']  = $PlanHbsConfig->paginate($per_page);
        
        return view('PlanHbsManagement.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('PlanHbsManagement.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userId = Auth::User()->id;
        $data = $request->except([]);
        $data['created_user'] = $userId;
        $data['updated_user'] = $userId;

        PlanHbsConfig::create($data);
        $request->session()->flash('status', __('message.update_success')); 
        
        return redirect('/admin/PlanHbsConfig');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(PlanHbsConfig $PlanHbsConfig)
    {
        $data = $PlanHbsConfig;
        $userCreated = $data->userCreated->name;
        $userUpdated = $data->userUpdated->name;
        return view('PlanHbsManagement.detail', compact('data', 'userCreated', 'userUpdated'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = PlanHbsConfig::findOrFail($id);
        return view('PlanHbsManagement.edit', compact('data'));
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
        $data = $request->except([]);
        $dataOld = PlanHbsConfig::findOrFail($id);
        
        if ($request->filename_vi) {
            $data['filename_vi'] = saveFile($request->filename_vi[0], config('constants.srcUpload'),$dataOld->filename_vi);
        }
        
        if ($request->filename_en) {
            $data['filename_en'] = saveFile($request->filename_en[0], config('constants.srcUpload'),$dataOld->filename_en);
        }
        
        PlanHbsConfig::updateOrCreate(['id' => $id], $data);
        
        $request->session()->flash('status', __('message.update_success')); 
        return redirect('/admin/hbsplan');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanHbsConfig $PlanHbsConfig)
    {
        $data = $PlanHbsConfig;
        $data->delete();
        return redirect('/admin/PlanHbsConfig')->with('status', __('message.update_fail'));
    }
}
