<?php

namespace App\Console\Commands;

use App\Twitter\TwitterAccountStatus;
use App\Twitter\TwitterConfig;
use App\Twitter\TwitterUser;
use DB;
use Illuminate\Console\Command;

class PopulateTwitterNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:populatenames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gradually populates the database with usernames to check';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::connection()->disableQueryLog();

        $complete = $this->currentUsernameLengthComplete();

        if($complete && (TwitterUser::neverQueried()->count() > 0 || TwitterUser::free()->count() > 0)) {
            return;
        }

        $prefix = TwitterConfig::getConfig('prefix')->first();
        $this->populateDatabaseWithUsernames($prefix);
        $this->saveNextPrefix($prefix);
    }

    private function populateDatabaseWithUsernames($prefix) {
        $chars = str_split('abcdefghijklmnopqrstuvwxyz1234567890_');

        $usernames = [];
        foreach($chars as $char) {
            $usernames[] = ['username' => $prefix.$char, 'status' => TwitterAccountStatus::NOT_RETRIEVED];
        }

        DB::table('twitter_users')->insert($usernames);
    }

    private function saveNextPrefix($currentPrefix) {
        $chars = str_split($currentPrefix);
        $nextPrefix = $this->reJig($chars);

        $config = TwitterConfig::whereName('prefix')->first();
        $config->value = implode('', $nextPrefix);
        $config->save();
    }

    private function reJig($charArray) {
        $reversed = array_reverse($charArray);

        $increment = true;
        foreach($reversed as $key => $char) {
            if($increment) {
                if($char != '_') {
                    $increment = false;
                }
                $reversed[$key] = $this->incrementChar($char);
            }
        }

        if($increment) {
            $reversed[] = 'a';
        }

        return array_reverse($reversed);
    }

    private function currentUsernameLengthComplete() {
        $currentLength = TwitterUser::select(DB::raw('MAX(CHAR_LENGTH(username)) as Max'))->pluck('Max');
        if(is_null($currentLength)) {
            return false;
        }
        return TwitterUser::whereUsername(str_repeat('_', $currentLength))->exists();
    }

    private function incrementChar($char) {
        if($char == '' || $char == '_') {
            return 'a';
        }
        $chars = str_split('abcdefghijklmnopqrstuvwxyz1234567890_');
        $key = array_search($char, $chars);

        return $chars[$key+1];
    }
}
