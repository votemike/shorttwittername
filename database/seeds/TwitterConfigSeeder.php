<?php

use \App\Twitter\TwitterConfig;
use Illuminate\Database\Seeder;

class TwitterConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $config = new TwitterConfig();
        $config->name = 'prefix';
        $config->value = '';
        $config->save();
    }
}
