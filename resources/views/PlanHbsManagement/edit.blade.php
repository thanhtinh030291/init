@extends('layouts.admin.master')
@section('title', __('message.hbsplan'))
@section('stylesheets')
<link href="{{asset('css/fileinput.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
{{ Form::open(array('url' => "admin/hbsplan/{$data->id}", 'method' => 'post' ,'class'=>'form-horizontal','files' => true)) }}
@method('PUT')
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-1">
                {{ Form::label('company', 'Company', ['class' => 'labelas']) }}
                {{ Form::text('company', $data->company, ['class' => 'form-control', 'disabled' => 'disabled']) }}
            </div>
            <div class="col-md-1">
                {{ Form::label('plan_id', 'Plan ID', ['class' => 'labelas']) }}
                {{ Form::text('plan_id', str_pad($data->plan_id, 4,'0',STR_PAD_LEFT), ['class' => 'form-control', 'disabled' => 'disabled']) }}
            </div>
            <div class="col-md-1">
                {{ Form::label('rev_no', 'Rev No', ['class' => 'labelas']) }}
                {{ Form::text('rev_no', str_pad($data->rev_no, 2,'0',STR_PAD_LEFT), ['class' => 'form-control', 'disabled' => 'disabled']) }}
            </div>
            <div class="col-md-1">
                {{ Form::label('ready', 'Ready', ['class' => 'labelas']) }}
                {{ Form::select('ready', [0 => 'No' , 1 => 'Yes'], $data->is_benefit_ready, ['class' => 'form-control']) }}
            </div>
            <div class="col-md-8">
                {{ Form::label('plan_desc', 'Plan description', ['class' => 'labelas']) }}
                {{ Form::text('plan_desc', $data->plan_desc, ['class' => 'form-control', 'disabled' => 'disabled']) }}
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                {{ Form::label('filename_vi', 'Filename Vietnamese', array('class' => 'labelas')) }} <span class="text-danger">*(PDF)</span>
                <div class="embed-responsive embed-responsive-16by9 mb-3">
                  <iframe class="embed-responsive-item" src="{{ asset(config('constants.srcStorage')) . '/'. $data->filename_vi }}" allowfullscreen></iframe>
                </div>
                <div class="file-loading">
                    <input id="filename_vi" type="file" name="filename_vi[]" >
                </div>
            </div>
            <div class="col-md-6">
                {{ Form::label('filename_en', 'Filename English', array('class' => 'labelas')) }} <span class="text-danger">*(PDF)</span>
                <div class="embed-responsive embed-responsive-16by9 mb-3">
                  <iframe class="embed-responsive-item" src="{{ asset(config('constants.srcStorage')) . '/'. $data->filename_en }}" allowfullscreen></iframe>
                </div>
                <div class="file-loading">
                    <input id="filename_en" type="file" name="filename_en[]" >
                </div>
            </div>
        </div>
        <div class="text-center tour-button">
            <a class="btn btnt btn-secondary" href="{{url('admin/hbsplan')}}">
                {{ __('message.back')}}
            </a>
            <button type="submit" class="btn btnt btn-danger center-block"> {{__('message.save')}}</button>
        </div>
    </div>
</div>
{{ Form::close() }}
@endsection

@section('scripts')
<script src="{{asset('js/fileinput.js?vision=') .$vision }}"></script>

<script type="text/javascript">
    $('input[type=file]').fileinput({
        maxFileCount: 1,
        overwriteInitial: true,
        validateInitialCount: true,
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@endsection
