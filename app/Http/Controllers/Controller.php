<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use App\Models\Menu;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct()
    {
        $menus = Menu::where('parent_id', '=', 0)->orderBy('order')->get();
        $allMenus = Menu::pluck('title','id')->all();
        $vision = 6;
        View::share('vision', $vision);
        View::share('menus', $menus);
        View::share('allMenus', $allMenus);
    }

}
