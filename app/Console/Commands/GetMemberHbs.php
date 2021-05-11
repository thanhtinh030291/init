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
        dump("start : " . Carbon::now());
        $url_file = resource_path('sql/import_pcv_member.sql');
        $sql =  file_get_contents($url_file);
        $benefits = [];
        $HbsMember = DB::connection('hbs_pcv')->select($sql);
        $i = 0;
        $collection = json_decode(json_encode($HbsMember), true);
        $chunks = array_chunk($collection,500);
        try {
            DB::beginTransaction();
            DB:: table('hbs_member')->truncate();
            foreach ($chunks as $key => $value) {
                DB::table('hbs_member')->insert($value);
                dump("End Time: " . Carbon::now());
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
        dump("End : " . $i ."---" .Carbon::now() ."--insert-" );
        $this->info('Cron Get Member Hbs Run successfully!');
    }
}
