<?php

namespace App\Http\Controllers;
use App\Models\User;
use Auth;
use App\Models\PlanHbsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


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
        $request->session()->flash('status', __('message.reason_inject_create_success')); 
        
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
        $dirStorage = config('constants.srcStorage');
        $dataImage = [];
        $previewConfig = [];
        if($data->url){
            $dataImage[] = "<img class='kv-preview-data file-preview-image' src='" . asset(config('constants.srcStorage').'/'.$data->url) . "'>";
            $previewConfig[]['caption'] = $data->url;
            $previewConfig[]['width'] = "120px";
            $previewConfig[]['url'] = "/admin/hbsplan/removeImage";
            $previewConfig[]['key'] = $data->url;
        }
        return view('PlanHbsManagement.edit', compact('data','dataImage', 'previewConfig'));
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
        
        if ($request->_url_file) {
            $data['url'] = saveFile($request->_url_file[0], config('constants.srcUpload'),$dataOld->url);
        }
        PlanHbsConfig::updateOrCreate(['id' => $id], $data);
        $request->session()->flash('status', __('message.reason_inject_update_success')); 
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
        return redirect('/admin/PlanHbsConfig')->with('status', __('message.reason_inject_delete_success'));
    }
}
