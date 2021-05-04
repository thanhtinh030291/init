<div id="modalMenu" class="modal fade bd-example-modal-lg" role="dialog">
    <div class="modal-dialog modal-lg">
        
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(array('url' => '/admin/menu', 'method' => 'PUT' ,'id'=>'action_form','class' =>'')) }}
                    <div class="row mb-2">
                        {{ Form::label('id',  'Id' , array('class' => 'col-md-2')) }} 
                        {{ Form::text('id', null , array('id' => 'form_id','class' => 'col-md-4 item-price form-control', 'readonly')) }}
                    </div>
                    <div class="row mb-2">
                        {{ Form::label('title',  'Title' , array('class' => 'col-md-2')) }} 
                        {{ Form::text('title', null , array('id' => 'form_title','class' => 'col-md-4 item-price form-control')) }}
                    </div>
                    <div class="row mb-2">
                        {{ Form::label('order',  'Order' , array('class' => 'col-md-2')) }} 
                        {{ Form::text('order', null , array('id' => 'form_order','class' => 'col-md-4 item-price form-control')) }}
                    </div>
                    <div class="row mb-2">
                        {{ Form::label('parent',  'parent' , array('class' => 'col-md-2')) }}
                        {{ Form::select('parent_id', collect($allMenus)->prepend('Please Select', null), null, array('id'=>'form_parent_id', 'class' => 'select2 form-control')) }}
                    </div>
                    <div class="row">
                        <div id = 'button_save' class="pull-right">
                            <button class="btn btn-danger" name="save_letter" value="save"> OK</button> 
                            <button type="button" class="btn btn-secondary btn-cancel-delete" 
                                data-dismiss="modal">Close</button>
                        </div><br>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>