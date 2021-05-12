<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\HbsMember;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GetBenefitMemberHbs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GetBenefitMemberHbs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $url_file2 = resource_path('sql/pcv_benefit_detail.sql');
        $sql_detail =  file_get_contents($url_file2);
        $sql = "BEGIN PKG_RP.sp_benefit_schedule(:mplOid, :cur); END;";
        $conn = oci_connect(
            config('oracle.hbs_pcv.username'),
            config('oracle.hbs_pcv.password'),
            config('oracle.hbs_pcv.host') . '/' . config('oracle.hbs_pcv.database')
        );
        $cursor = oci_new_cursor($conn);
        $HbsMember = HbsMember::where('company' = 'pcv')->whereIsNull('ben_schedule')->get();
        foreach ($HbsMember as $key => $value) {
            try
            {
                $mplid = $value->mepl_oid;
                $stid = oci_parse($conn, $sql2);
                oci_bind_by_name($stid, ":mplOid", $mplid);
                oci_bind_by_name($stid, ":cur", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
                $benSchedule = [];
                while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false)
                {
                    $benSchedule["tmp"] = $row;
                }
            }
            catch (Exception $e)
            {
                $benSchedule = [];
            }
            if (!empty($benSchedule))
            {
                
                $benDetails = DB::connection('hbs_pcv')->select($sql_detail, [$benSchedule['tmp']['PLAN_OID']]);
                $benDetails = json_decode(json_encode($benDetails), true);
                if (!empty($benDetails))
                {
                    $benSchedule['detail'] = $benDetails[0];
                }
                else
                {
                    $benSchedule['detail']['copay'] = null;
                    $benSchedule['detail']['amtperday'] = null;
                    $benSchedule['detail']['amtpervis'] = null;
                }

                $benSchedule = json_encode($benSchedule);
                HbsMember::where('id', $value->id)->update(['ben_schedule' => $benSchedule]);
            }
        }
    }
}
