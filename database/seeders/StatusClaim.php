<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class StatusClaim extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();
        if (Schema::hasTable('mobile_claim_status')) {
            
            $data = [
                array('id' => 'c0b850b9-4ff7-11eb-ba33-000d3a821253', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  17, 'name' => 'Info Submitted', 'name_vi' => 'Đã Nhận Thông Tin'),
                array('id' => 'cbb1245a-95e6-11eb-ac76-000d3a821253', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  70, 'name' => 'Info Request Sent', 'name_vi' => 'Đã gửi yêu cầu'),
                array('id' => 'fb01ff6b-4a6b-11eb-a7cf-98fa9b10d0b1', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  10, 'name' => 'New', 'name_vi' => 'Mới'),
                array('id' => 'fb0377c2-4a6b-11eb-a7cf-98fa9b10d0b1', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  11, 'name' => 'Accepted', 'name_vi' => 'Chấp Nhận'),
                array('id' => 'fb037b49-4a6b-11eb-a7cf-98fa9b10d0b1', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  12, 'name' => 'Partially Accepted', 'name_vi' => 'Chấp Nhận Một Phần'),
                array('id' => 'fb03ea27-4a6b-11eb-a7cf-98fa9b10d0b1', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  13, 'name' => 'Declined', 'name_vi' => 'Từ Chối'),
                array('id' => 'fb03ee1b-4a6b-11eb-a7cf-98fa9b10d0b1', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  16, 'name' => 'Info Request', 'name_vi' => 'Yêu Cầu Thông Tin'),
                array('id' => 'fb043d22-4a6b-11eb-a7cf-98fa9b10d0b1', 'crt_by' => 'admin', 'created_at' => $now, 'updated_at' => $now, 'code' =>  18, 'name' => 'Ready For Process', 'name_vi' => 'Sẵng Sàng Xủ Lý')
            ];
            DB::table('mobile_claim_status')->insert($data);
        }
    }
}
