@extends('layouts.admin.master')
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="breadcrumb-holder">
            <h1 class="main-title float-left">Menu Management</h1>
            <ol class="breadcrumb float-right">
                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Menu</li>
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5>Multi Level Dynamic Menu</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                <h5 class="mb-4 text-center bg-success text-white ">Add New Menu</h5>
                {{ Form::open(array('files' => true,'url' => '/admin/menu', 'method' => 'post' ,'class'=>'form-horizontal')) }}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control">   
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" name="url" class="form-control">   
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Icon</label>
                                <input type="text" name="icon" class="form-control">   
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Order</label>
                                <input type="number" name="order" class="form-control">   
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            <label>Parent</label>
                            <select class="form-control" name="parent_id">
                                <option selected disabled>Select Parent Menu</option>
                                @foreach($allMenus as $key => $value)
                                    <option value="{{ $key }}">{{ $value}}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-success">Save</button>
                        </div>
                    </div>
                {!! Form::close() !!}
                </div>
                <div class="col-md-7">
                <h5 class="text-center mb-4 bg-info text-white">Menu List</h5>
                    <ul id="tree1">
                    @foreach($menus as $menu)
                        <li class="mt-1">
                            {{ Form::open(array('url' => '/admin/menu/'.$menu->id, 'method' => 'DELETE' ,'class'=>'action_form','class' =>'needs-validation')) }}
                            {!! Form::button($menu->title, [
                                'data-toggle' => "modal" ,  
                                'data-target' => "#modalMenu",
                                'onclick' => 'modalMenu(this);',
                                'type' => 'button', 
                                'class' => 'btn btn-primary' , 
                                'data-id' => $menu->id,
                                'data-title' => $menu->title,
                                'data-url' => $menu->url,
                                'data-order' => $menu->order,
                                'data-status' => $menu->status,
                                'data-parent_id' => $menu->parent_id,
                            ]) !!} - ({{$menu->order}})
                            {!! Form::button('X',['class' => 'btn btn-danger btn-needs-validation']) !!}
                            {{ Form::close() }}
                            @if(count($menu->childs))
                                @include('menuManagement.manageChild',['childs' => $menu->childs])
                            @endif
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
{{-- Modal preview--}}
@include('menuManagement.modalMenu')

@endsection
@section('scripts')
    <script>
        function modalMenu(e){
            var id =  e.dataset.id;
            var order = e.dataset.order;
            var title = e.dataset.title;
            var status = e.dataset.status;
            var parent_id = e.dataset.parent_id;
            var url = e.dataset.url;
            $('#action_form').attr('action', "{{url('admin/menu')}}" + "/" + id );
            $('#form_id').val(id);
            $('#form_order').val(order);
            $('#form_title').val(title);
            $('#form_parent_id').val(parent_id);
        }

    </script>
@endsection