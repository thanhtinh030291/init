@extends('layouts.admin.master')
@section('content')
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="breadcrumb-holder">
            <h1 class="main-title float-left">{{ __('message.staff_create')}}</h1>
            <ol class="breadcrumb float-right">
                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="{{ url('admin/user')}}">{{
                __('message.staff_management')}}</a></li>
                <li class="breadcrumb-item active">{{ __('message.staff_create')}}</li>
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                {{ Form::open(array('url' => '/admin/user/'.$user->id, 'method'=>'post', 'id' => 'frmUpdateStaff', 'files' => true))}} @method('PUT')
                    <!-- Staff info -->
                    {{ Form::label('name',__('message.name'), array('class' => 'labelas')) }}
                    {{ Form::text('name', $user->name, ['class' => 'form-control','placeholder'=>__('message.enter_staff_name'),  'required']) }}<br>
                    {{ Form::label('email',__('message.email'), array('class' => 'labelas')) }}
                    {{ Form::text('email', $user->email, ['class' => 'form-control','placeholder'=>__('message.enter_staff_email'), 'required', 'readonly']) }}<br>

                    {{ Form::label('role','Role', array('class' => 'labelas')) }}<span class="text-danger">*</span>
                    {{ Form::select('_role', $roles, $user->roles->pluck('name'), ['class' => 'select2 form-control', 'multiple' => 'multiple', 'name'=>'_role[]']) }}<br>
                    
                   

                    <!-- Add update Button -->
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <a class="btn btn-secondary" href="{{url('admin/admins')}}"> {{ __('message.back')}} </a>
                            {{ Form::submit( __('message.save'),['class' => 'btn btn-primary center-block']) }}<br>
                        </div>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

@endsection
