
@extends('layouts.admin.master')
@section('title', __('message.hbsplan'))
@section('stylesheets')
<link href="{{asset('css/fileinput.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
@endsection
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                {{ Form::open(array('url' => "admin/hbsplan/{$data->id}", 'method' => 'post' ,'class'=>'form-horizontal','files' => true)) }}
                @method('PUT')
                {{ Form::label('plan_desc', 'plan desc', ['class' => 'labelas']) }}
                {{ Form::text('plan_desc', $data->plan_desc, ['class' => 'form-control']) }}

                {{ Form::label('Rev_No', 'Rev No', ['class' => 'labelas']) }}
                {{ Form::text('rev_no', $data->rev_no, ['class' => 'form-control']) }}

                {{ Form::label('plan_id', 'plan desc', ['class' => 'labelas']) }}
                {{ Form::text('plan_id', $data->plan_id, ['class' => 'form-control']) }}

                {{ Form::label('ready', 'Ready', ['class' => 'labelas']) }}
                {{ Form::select('ready', [0 => 'No' , 1 => 'Yes'], $data->ready, ['class' => 'form-control']) }}

                <div class="col-md-12">
                    {{ Form::label('_url_file', 'url', array('class' => 'labelas')) }} <span class="text-danger">*(PDF)</span>
                    <div class="file-loading">
                        <input id="url_file" type="file" name="_url_file[]" >
                    </div>
                </div>
                <br />
                <div class="text-center tour-button">
                    <a class="btn btnt btn-secondary" href="{{url('admin/hbsplan')}}">
                        {{ __('message.back')}}
                    </a>
                    <button type="submit" class="btn btnt btn-danger center-block"> {{__('message.save')}}</button> <br>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{asset('js/fileinput.js?vision=') .$vision }}"></script>

<script type="text/javascript">
    var dataImage = @json($dataImage);
    var previewConfig = @json($previewConfig);
    var maxFile = 1;
    $('#url_file').fileinput({
        maxFileCount: maxFile,
        overwriteInitial: true,
        validateInitialCount: true,
        initialPreview: dataImage,
        initialPreviewConfig: previewConfig,
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@endsection
