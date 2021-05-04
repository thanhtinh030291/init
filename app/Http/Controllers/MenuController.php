<?php

namespace App\Http\Controllers;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(){
        $menus = Menu::where('parent_id', '=', 0)->orderBy('order')->get();
        $allMenus = Menu::pluck('title','id')->all();
        return view('menuManagement.index',compact('menus','allMenus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);
        
        $input = $request->all();
        $input['parent_id'] = empty($input['parent_id']) ? 0 : $input['parent_id'];
        Menu::create($input);
        return back()->with('success', 'Menu added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $data = Menu::findOrFail($id);
        $data_n = $request->except([]);
        Menu::updateOrCreate(['id' => $data->id], $data_n);
        flash()->success('Menu has been updated.');
        return back()->with('success', 'Menu update successfully.');
    }


    public function show()
    {
        $menus = Menu::where('parent_id', '=', 0)->get();
        return view('menuManagement.dynamicMenu',compact('menus'));
    }

    public function destroy($id)
    {
        $data = Menu::findOrFail($id);
        $data->delete();
        return back()->with('success', 'Menu delete successfully.');
    }
}
