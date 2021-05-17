@extends('layouts.admin.master')
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="breadcrumb-holder">
            <h1 class="main-title float-left">{{ __('message.claim_mobile')}}</h1>
            <ol class="breadcrumb float-right">
                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">{{ __('message.claim_mobile')}}</li>
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
                        {{-- {{ Form::label('email', __('message.email'), array('class' => 'labelas')) }}
                        {{ Form::text('email',$search['email'], ['class' => 'form-control']) }} <br/>
                        {{ Form::label('name', __('message.name'), array('class' => 'labelas')) }}
                        {{ Form::text('name', $search['name'], ['class' => 'form-control']) }} <br/> --}}
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
                                <th>{{ __('message.status')}}</th>
                                <th>{{ __('message.mantis')}}</th>
                                <th>{{ __('message.fullname')}}</th>
                                <th>{{ __('message.pay_type')}}</th>
                                <th>{{ __('message.pres_amt')}}</th>
                                <th>{{ __('message.reason')}}</th>
                                <th>{{ __('message.updated_at')}}</th>
                                <th>{{ __('message.created_at')}}</th>
                                <th class='text-center'>{{ __('message.control')}}</th>
                            </tr>
                        </thead>
                        <!-- Table Body -->
                        @foreach ($datas as $data)
                        <tbody>
                            <tr>
                                <!-- staff info -->
                                <th>{{$data->mobile_claim_status->name}}</th>
                                <td>{{$data->mantis_id}}</td>
                                <td>{{$data->fullname}}</td>
                                <td>{{$data->pay_type}}</td>
                                <td>{{$data->pres_amt}}</td>
                                <td>{{$data->reason}}</td>
                                <td>{{$data->updated_at}}</td>
                                <td>{{$data->created_at}}</td>
                                <td class='text-center'>
                                    <!-- control -->
                                    <a class="btn btn-success" href='{{url("admin/mobileclaim/$data->id")}}'>{{__('message.view')}}</a>
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