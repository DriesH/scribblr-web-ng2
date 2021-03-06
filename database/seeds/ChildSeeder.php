<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('children')->insert([
           'short_id' => 'gkhjtubh',
           'user_id' => 1,
           'gender' => 'Female',
           'full_name' => 'Amy',
           'date_of_birth' => '2015-02-03',
           'created_at' => Carbon::now()->format('Y-m-d H:i:s')
       ]);

        DB::table('children')->insert([
           'short_id' => 'gmhledkh',
           'user_id' => 1,
           'gender' => 'Male',
           'full_name' => 'Tommy',
           'date_of_birth' => '2013-07-15',
           'created_at' => Carbon::now()->format('Y-m-d H:i:s')
       ]);

    //    for ($a=0; $a < 10; $a++) {
    //        DB::table('children')->insert([
    //           'short_id' => substr(md5(uniqid(mt_rand(), true)), 0, 8),
    //           'user_id' => 1,
    //           'gender' => 'Female',
    //           'full_name' => 'Miranda',
    //           'date_of_birth' => '2014-07-15',
    //       ]);
    //    }
    }
}
