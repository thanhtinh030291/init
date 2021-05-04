<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = [
            'name' => $request->get('name'),
            'email' => $request->get('email')
        ];
        $users = User::latest()->paginate();
        
        return view('userManagement.index', compact('users','search'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'id');
        return view('userManagement.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'bail|required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            
        ]);

        $dataNew = $request->except([]);
		
		$dataNew['password'] = bcrypt($dataNew['password']);
		try {
            DB::beginTransaction();
			$user = User::Create($dataNew);
			$user->assignRole($request->_role);
			$data['password'] =$request->password;
			$data['user'] = $user;
			sendEmail($user, $data, 'templateEmail.registerAcountTemplate' , 'Thông Tin Đăng Nhập Hệ Thống Claim Assistant');
			DB::commit();
			$request->session()->flash('status', __('message.add_account'));
			return redirect('/admin/admins/');
		} catch ( \Exception $e) {
			Log::error(generateLogMsg($e));
            DB::rollback(); $request->session()->flash(
                'errorStatus', 
                __('message.update_fail')
            );
            return redirect('/admin/admins/')->withInput();
        }

        // Create the user
        if ( $user = User::create($request->except('roles', 'permissions')) ) {
            $this->syncPermissions($request, $user);
            flash('User has been created.');
        } else {
            flash()->error('Unable to create user.');
        }

        return redirect()->route('userManagerment.index');
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name', 'id');
        $permissions = Permission::all('name', 'id');

        return view('userManagement.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'bail|required|min:2',
            'email' => 'required|email|unique:users,email,' . $id,
            'roles' => 'required|min:1'
        ]);

        // Get the user
        $user = User::findOrFail($id);

        // Update user
        $user->fill($request->except('roles', 'permissions', 'password'));

        // check for password change
        if($request->get('password')) {
            $user->password = bcrypt($request->get('password'));
        }

        // Handle the user roles
        $this->syncPermissions($request, $user);

        $user->save();
        flash()->success('User has been updated.');
        return redirect()->route('userManagement.index');
    }

    public function destroy($id)
    {
        if ( Auth::user()->id == $id ) {
            flash()->warning('Deletion of currently logged in user is not allowed :(')->important();
            return redirect()->back();
        }

        if( User::findOrFail($id)->delete() ) {
            flash()->success('User has been deleted');
        } else {
            flash()->success('User not deleted');
        }

        return redirect()->back();
    }

    private function syncPermissions(Request $request, $user)
    {
        // Get the submitted roles
        $roles = $request->get('roles', []);
        $permissions = $request->get('permissions', []);

        // Get the roles
        $roles = Role::find($roles);

        // check for current role changes
        if( ! $user->hasAllRoles( $roles ) ) {
            // reset all direct permissions for user
            $user->permissions()->sync([]);
        } else {
            // handle permissions
            $user->syncPermissions($permissions);
        }

        $user->syncRoles($roles);
        return $user;
    }

}
