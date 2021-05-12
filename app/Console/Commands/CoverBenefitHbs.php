<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helps\PcvBenefitBuilder;
use App\Models\HbsMember;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoverBenefitHbs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CoverBenefitHbs';

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
        $HbsMember = HbsMember::where('company','pcv')->whereNull('benefit_en')->first();
        $langs = ['en', 'vi'];
        foreach ($langs as $lang)
        {
            $builder = new PcvBenefitBuilder($HbsMember, $lang);
            $benefit = $builder->get();
            dd($benefit);
        }
    }
}
