<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;


class GetMemberHbs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GetMemberHbs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Member from Hbs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dump("JoBName : Get Member Hbs");
        dump("-------------Start : " . Carbon::now());
        $url_file = resource_path('sql/import_pcv_member.sql');
        $sql =  file_get_contents($url_file);
        $arr = [
            'hbs_pcv' => 'pcv',
            'hbs_bsh' => 'bsh'
        ];
        DB:: table('hbs_member')->truncate();
        foreach ($arr as $key => $value) {
            $HbsMember = DB::connection($key)->select($sql,[$value]);
            $i = 0;
            $collection = json_decode(json_encode($HbsMember), true);
            $chunks = array_chunk($collection,500);
            try {
                DB::beginTransaction();
                foreach ($chunks as $key2 => $value2) {
                    $num_row = count($value2);
                    DB::table('hbs_member')->insert($value2);
                    dump("Success Time: " .$i. "..".$value."..: ".$num_row);
                    $i++;
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                dump("Failed Time: " .$i. "..".$value."..:");
            }
        }
        
        dump("-----------End : " .Carbon::now() );
        $this->info('Cron Get Member Hbs Run successfully!');
    }
}
