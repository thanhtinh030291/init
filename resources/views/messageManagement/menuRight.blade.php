<a href="#" class="btn btn-primary btn-block mb-3">Compose</a>

        <div class="card">
        <div class="card-header">
            <h3 class="box-title">Folders</h3>
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-pills flex-column">
            <li class="p-2"><a href="{{route('message.index')}}"><i class="fa fa-inbox"></i> Inbox
                <span class="label label-primary pull-right">12</span></a></li>
            <li class="p-2"><a href="{{route('message.sent')}}"><i class="fa fa-envelope"></i> Sent</a></li>
            <li class="p-2"><a href="{{route('message.trash')}}"><i class="fa fa-trash"></i> Trash</a></li>
            </ul>
        </div>
        <!-- /.box-body -->
        </div>
        <!-- /. box -->
        <div class="card">
        <div class="card-header">
            <h3 class="box-title">Labels</h3>
        </div>
        <div class="card-body p-0" >
            <ul class="nav nav-pills flex-column">
                <li class="p-2">
                    {{ Form::open(array('url' => route('message.index'), 'method' => 'get', 'class' => 'form-inline')) }}
                        
                        <button type="submit" name="important" value="1" class=""><i class="fa fa-star text-yellow"></i> Important</button>
                    {{ Form::close() }}
                </li>
            </ul>
        </div>
        <!-- /.box-body -->
        </div>
        <!-- /.box -->