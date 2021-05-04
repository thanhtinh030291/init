<ul>
    @foreach($childs as $child)
    <li class="mt-1">
        
        {{ Form::open(array('url' => '/admin/menu/'.$child->id, 'method' => 'DELETE' ,'class'=>'action_form','class' =>'needs-validation')) }}
        {!! Form::button($child->title, [
            'data-toggle' => "modal" ,  
            'data-target' => "#modalMenu",
            'onclick' => 'modalMenu(this);',
            'type' => 'button', 
            'class' => ' btn btn-secondary' , 
            'data-id' => $child->id,
            'data-title' => $child->title,
            'data-url' => $child->url,
            'data-order' => $child->order,
            'data-status' => $child->status,
            'data-parent_id' => $child->parent_id,
        ]) !!} - ({{$child->order}})
        {!! Form::button('X',['class' => 'btn btn-danger btn-needs-validation']) !!}
        {{ Form::close() }}
    @if(count($child->childs))
                @include('manageChild',['childs' => $child->childs])
            @endif
    </li>
    @endforeach
</ul>