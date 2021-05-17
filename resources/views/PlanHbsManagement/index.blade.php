@extends('layouts.admin.master')
@section('title', )
@section('stylesheets')
    <link href="{{ asset('css/condition_advance.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-12">
        <form action="{{ url('admin/hbsplan') }}" method="GET" class="form-horizontal" >
            <div class="card">
                <div class="card-header">
                    <label  class="font-weight-bold" for="searchmail"> {{ __('message.search')}}</label>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            {{ Form::label('Company', 'Company', ['class' => 'labelas']) }}
                            {{ Form::select('company', config('constants.company'), data_get($search_params,'company'), ['class' => 'form-control' ,  'placeholder' => '']) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('plan_id', 'Plan ID', ['class' => 'labelas']) }}
                            {{ Form::text('plan_id', data_get($search_params,'plan_id'), ['class' => 'form-control']) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('rev_no', 'Rev No', ['class' => 'labelas']) }}
                            {{ Form::text('rev_no', $search_params['rev_no'], ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            {{ Form::label('plan_desc', 'Plan Description', ['class' => 'labelas']) }}
                            {{ Form::text('plan_desc', data_get($search_params,'plan_desc'), ['class' => 'form-control']) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('ready', 'Ready', ['class' => 'labelas']) }}
                            {{ Form::select('ready', [0 => 'No' , 1 => 'Yes'], $search_params['ready'], ['class' => 'form-control' ,  'placeholder' => '']) }}
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-info">{{ __('message.search') }}</button>
                    <a class="btn btn-default" href="/admin/hbsplan">{{ __('message.reset') }}</a>
                </div>
            </div>
            
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                @if (count($data) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <!-- Table Headings -->
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Plan ID</th>
                                <th>Rev No</th>
                                <th>{{ __('message.name') }}</th>
                                <th>Ready</th>
                                <th>Filename VI</th>
                                <th>Filename EN</th>
                                <th>{{ __('message.date_created') }}</th>
                                <th>{{ __('message.date_updated') }}</th>
                                <th class='text-center control_btn'>{{ __('message.control') }}</th>
                            </tr>
                        </thead>
                        <!-- Table Body -->
                        @foreach ($data as $value)
                        <tbody>
                            <tr>
                                <!-- ticket info -->
                                <td>{{ $value->company }}</td>
                                <td>{{ str_pad($value->plan_id, 4,'0',STR_PAD_LEFT) }}</td>
                                <td>{{ str_pad($value->rev_no, 2,'0',STR_PAD_LEFT) }}</td>
                                <td>{{ $value->plan_desc }}</td>
                                <td>{{ $value->is_benefit_ready == 1 ? 'Yes' : 'No' }}</td>
                                <td>{{ $value->filename_vi }}</td>
                                <td>{{ $value->filename_en }}</td>
                                <td>{{ $value->created_at }}</td>
                                <td>{{ $value->updated_at }}</td>
                                <td class='text-center'>
                                    <!-- control -->
                                    <a class="btn btn-success" href='{{ url("admin/hbsplan/$value->id/edit") }}'>{{ __('message.edit') }}</a>
                                </td>
                            </tr>
                        </tbody>
                        @endforeach
                    </table>
                </div>
                {{ $data->appends($search_params)->links() }}
                @endif
            </div>
        </div>
    </div>
</div>



@endsection
@section('scripts')
<script src="{{asset('js/lengthchange.js?vision=') .$vision }}"></script>
@endsection
