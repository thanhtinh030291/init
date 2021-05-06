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
			//sendEmail($user, $data, 'templateEmail.registerAcountTemplate' , 'Thông Tin Đăng Nhập Hệ Thống');
			DB::commit();
			$request->session()->flash('status', __('message.add_account'));
			return redirect('/admin/user/');
		} catch ( \Exception $e) {
			Log::error(generateLogMsg($e));
            DB::rollback(); $request->session()->flash(
                'errorStatus', 
                __('message.update_fail')
            );
            return redirect('/admin/user/')->withInput();
        }

        // Create the user
        if ( $user = User::create($request->except('roles', 'permissions')) ) {
            $this->syncPermissions($request, $user);
            flash('User has been created.');
        } else {
            flash()->error('Unable to create user.');
        }

        return redirect()->route('user.index');
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
            'email' => 'required|email|unique:users,email,' . $id
        ]);
        $dataNew = $request->except('profile_image', '_method', '_token');
        // Get the user
        $user = User::findOrFail($id);

        // Update user
        $user = User::updateOrCreate(['id' => $id], $dataNew);
        // Handle the user roles
        $user->syncRoles($request->_role);

        $user->save();
        flash()->success('User has been updated.');
        return redirect()->route('user.index');
    }

    public function destroy($id)
    {
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
