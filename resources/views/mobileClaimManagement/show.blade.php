@extends('layouts.admin.master')
@section('stylesheets')
<link href="{{asset('css/fileinput.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
@endsection
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                {{ Form::open(array('url' => '/admin/user/'.$data->id, 'method'=>'post', 'id' => 'frmUpdateStaff', 'files' => true))}} @method('PUT')
                    <!-- Staff info -->
                <div class="row">
                    <div class="col-md-6 p-2">
                        {{ Form::label('name',__('message.mantis_id'), array('class' => 'labelas')) }}
                        {{ Form::text('mantis_id', $data->mantis_id, ['class' => 'form-control','placeholder'=>__('message.enter_please'),  'required', 'readonly']) }}<br>

                        

                        {{ Form::label('mr_no',__('message.mbr_no'), array('class' => 'labelas')) }}
                        {{ Form::text('mbr_no', $data->mobile_user->mbr_no, ['class' => 'form-control','placeholder'=>__('message.enter_please'), 'required', 'readonly']) }}<br>

                        {{ Form::label('mr_no',__('message.pocy_no'), array('class' => 'labelas')) }}
                        {{ Form::text('pocy_no', $data->mobile_user->pocy_no, ['class' => 'form-control','placeholder'=>__('message.enter_please'), 'required', 'readonly']) }}<br>
                    </div>
                    <div class="col-md-6 p-2">
                        {{ Form::label('company',__('message.company'), array('class' => 'labelas')) }}
                        {{ Form::text('company', $data->mobile_user->company, ['class' => 'form-control','placeholder'=>__('message.enter_please'),   'readonly']) }}<br>

                        {{ Form::label('tel',__('message.tel'), array('class' => 'labelas')) }}
                        {{ Form::text('tel', $data->mobile_user->tel, ['class' => 'form-control','placeholder'=>__('message.enter_please'), 'readonly']) }}<br>

                        {{ Form::label('id_card',__('message.id_card'), array('class' => 'labelas')) }}
                        {{ Form::text('id_card', $data->mobile_user->email, ['class' => 'form-control','placeholder'=>__('message.enter_please'), 'readonly']) }}<br>
                    </div>
                    <div class="col-md-12 p-2">
                        <div class="file-loading">
                            <input id="url_file_sorted" type="file" name="_url_file_sorted[]" >
                        </div>
                    </div>
                </div>

                    <!-- Add update Button -->
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <a class="btn btn-secondary" href="{{url('admin/mobileclaim')}}"> {{ __('message.back')}} </a>
                            {{-- {{ Form::submit( __('message.save'),['class' => 'btn btn-primary center-block']) }}<br> --}}
                        </div>
                    </div>
                {{ Form::close() }}
                {{ Form::open(array('url' => '/admin/mobileclaim/notification/'.$data->id, 'method'=>'post', 'id' => 'frmUpdateStaff', 'files' => true))}}
                    <h5 class="card-header">Notication</h5>
                    <div class="card-body">
                        <h5 class="card-title">Title</h5>
                        <input type="text" name="title" class="card-text form-control" required />
                        <h5 class="card-title">Contents</h5>
                        <textarea name="contents" class="card-text form-control"></textarea>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{asset('js/fileinput.js?vision=') .$vision }}"></script>
<script>
    var initialPreview_j = @json($initialPreview);
    var initialPreviewConfig_j = @json($initialPreviewConfig);
    $("#url_file_sorted").fileinput({
    uploadUrl: "/file-upload-batch/1",
    uploadAsync: false,
    minFileCount: 2,
    maxFileCount: 5,
    overwriteInitial: false,
    initialPreview: initialPreview_j,
    initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup
    initialPreviewFileType: 'image', // image is the default and can be overridden in config below
    initialPreviewDownloadUrl: 'https://kartik-v.github.io/bootstrap-fileinput-samples/samples/{filename}', // includes the dynamic `filename` tag to be replaced for each config
    initialPreviewConfig: initialPreviewConfig_j,
    uploadExtraData: {
        img_key: "1000",
        img_keywords: "happy, places"
    }
}).on('filesorted', function(e, params) {
    console.log('File sorted params', params);
}).on('fileuploaded', function(e, params) {
    console.log('File uploaded params', params);
});
</script>
@endsection
