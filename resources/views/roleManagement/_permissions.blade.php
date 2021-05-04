<div class="card">
    
    <div class="card-header">
            {{$title}}
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($permissions as $perm)
                @php
                    $per_found = null;

                    if( isset($role) ) {
                        $per_found = $role->hasPermissionTo($perm->name);
                    }

                    if( isset($user)) {
                        $per_found = $user->hasDirectPermission($perm->name);
                    }
                @endphp

                <div class="col-md-3">
                    <div class="checkbox">
                        <label class="{{ str_contains($perm->name, 'delete') ? 'text-danger' : '' }}">
                            {!! Form::checkbox("permissions[]", $perm->name, $per_found, []) !!} {{ $perm->name }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>