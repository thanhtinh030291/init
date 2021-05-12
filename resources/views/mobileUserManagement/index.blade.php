@extends('layouts.admin.master')
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="breadcrumb-holder">
            <h1 class="main-title float-left">{{ __('message.user_mobile')}}</h1>
            <ol class="breadcrumb float-right">
                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">{{ __('message.user_mobile')}}</li>
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<br>
<!-- -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <label  class="font-weight-bold" for="searchmail"> {{ __('message.search')}}</label>
            </div>
            <div class="card-body">
                <form action="{{ url('admin/admins')}}" method="GET" class="form-horizontal">
                <div class="row">
                    <div class="col-md-6">
                        {{ Form::label('email', __('message.email'), array('class' => 'labelas')) }}
                        {{ Form::text('email',$search['email'], ['class' => 'form-control']) }} <br/>
                        {{ Form::label('name', __('message.name'), array('class' => 'labelas')) }}
                        {{ Form::text('name', $search['name'], ['class' => 'form-control']) }} <br/>
                    </div>
                </div>
                <button type="submit" class="btn btn-info"> {{ __('message.search')}} </button>
                <button type="button" id="clearForm" class="btn btn-default"> {{ __('message.reset')}} </button>
                </form>
            </div>
        </div>
    </div>
</div>
<br>

<!-- staff list-->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
               <label class="font-weight-bold">{{ __('message.staff_list')}} | {{ __('message.total')}}: {{$datas->total()}} </label>
            </div>
            <div class="card-body">
                @if (count($datas) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <!-- Table Headings -->
                        <thead>
                            <tr>
                                
                                <th>{{ __('message.email')}}</th>
                                <th>{{ __('message.fullname')}}</th>
                                <th>{{ __('message.mbr_no')}}</th>
                                <th>{{ __('message.pocy_no')}}</th>
                                <th>{{ __('message.company')}}</th>
                                <th class='text-center'>{{ __('message.control')}}</th>
                            </tr>
                        </thead>
                        <!-- Table Body -->
                        @foreach ($datas as $data)
                        <tbody>
                            <tr>
                                <!-- staff info -->
                                
                                <td>{{$data->email}}</td>
                                <td>{{$data->fullname}}</td>
                                <td>{{$data->mbr_no}}</td>
                                <td>{{$data->pocy_no}}</td>
                                <td>{{$data->company}}</td>
                                <td>{{$data->created_at}}</td>
                                <td class='text-center'>
                                    <!-- control -->
                                    <a class="btn btn-success" href='{{url("admin/mobileuser/$data->id/edit")}}'>{{__('message.edit')}}</a>
                                    {{ Form::open(array('url' => '/admin/mobileuser/'.$data->id, 'method' => 'DELETE' ,'class'=>'action_form','class' =>'needs-validation')) }}
                                        {!! Form::button('Delete',['class' => 'btn btn-danger btn-needs-validation btn-delete']) !!}
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        </tbody>
                        @endforeach
                    </table>
                </div>
                {{ $datas->appends($search)->links() }}
                Showing {{ $datas->firstItem() }} to {{ $datas->lastItem() }} of total {{$datas->total()}}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- @include('layouts.admin.partials.delete_model', [
    'title'           => __('message.delete_staff_warning'),
    'confirm_message' => __('message.delete_staff_confirm'),
]) --}}

@endsection
@section('scripts')
<script src="{{asset('js/lengthchange.js?vision=') .$vision }}"></script>
@endsection