<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class MenuSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();
        $data = [
            ['title'=>'Settings Management', 'parent_id'=> 0 ,  'order' => 2 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now , 'url' => '#'],
            ['title'=>'Mobile Managemant', 'parent_id'=> 0 ,  'order' => 0 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now , 'url' => '#'],
            ['title'=>'Message Managerment', 'parent_id'=> 0 ,  'order' => 1 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => '#'],
            ['title'=>'Roles Managerment', 'parent_id'=> 1 ,  'order' => 1 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => 'role.index'],
            ['title'=>'Menus Management', 'parent_id'=> 1 ,  'order' => 1 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => 'menu.index'],
            ['title'=>'Users Managerment', 'parent_id'=> 1 ,  'order' => 1 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => 'user.index'],
            ['title'=>'Inbox', 'parent_id'=> 3 ,  'order' => 0 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => 'message.index'],
            ['title'=>'Config', 'parent_id'=> 1 ,  'order' => 0 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => 'setting.index'],            
            ['title'=>'Custom Plan HBS', 'parent_id'=> 2 ,  'order' => 0 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => 'hbsplan.index'],
            ['title'=>'Mobile User', 'parent_id'=> 2 ,  'order' => 1 , 'icon' => 'fa-cog' , 'created_at' => $now , 'updated_at' => $now, 'url' => 'mobileuser.index'],
        ];
        DB::table('menus')->insert($data);
    }
}