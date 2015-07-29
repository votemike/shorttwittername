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
     * 37^x are big numbers, we need to incrementally add every permutation of allowed
     * Twitter username characters.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::connection()->disableQueryLog();

        if(!$this->populateMoreUsernames()) {
            return;
        }

        $prefix = TwitterConfig::getConfig('prefix')->first();
        $this->populateDatabaseWithUsernames($prefix);
        $this->saveNextPrefix($prefix);
    }

    /**
     * Adds more usernames to the database ready to be checked (if necessary)
     *
     * @param $prefix
     */
    private function populateDatabaseWithUsernames($prefix) {
        $chars = str_split('abcdefghijklmnopqrstuvwxyz1234567890_');

        $usernames = [];
        foreach($chars as $char) {
            $usernames[] = ['username' => $prefix.$char, 'status' => TwitterAccountStatus::NOT_RETRIEVED];
        }

        DB::table('twitter_users')->insert($usernames);
    }

    /**
     * Saves the next prefix for usernames being saved to the database
     *
     * @param $currentPrefix
     */
    private function saveNextPrefix($currentPrefix) {
        $nextPrefix = $this->getNextPrefix($currentPrefix);

        $config = TwitterConfig::whereName('prefix')->first();
        $config->value = $nextPrefix;
        $config->save();
    }

    /**
     * Increments the prefix to be added to new usernames
     *
     * @param $currentPrefix
     * @return string
     */
    private function getNextPrefix($currentPrefix) {
        $chars = str_split($currentPrefix);
        $reversed = array_reverse($chars);

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

        return implode('', array_reverse($reversed));
    }

    /**
     * Increments a characted e.g. c->d, d-e, _->a
     *
     * @param $char
     * @return string
     */
    private function incrementChar($char) {
        if($char == '' || $char == '_') {
            return 'a';
        }
        $chars = str_split('abcdefghijklmnopqrstuvwxyz1234567890_');
        $key = array_search($char, $chars);

        return $chars[$key+1];
    }

    /**
     * Decides of any more usernames should be added to the database
     * Will add more if any more of the same length need to be added
     * Won't add more if above condition passes and there are some usernames still to check or some available usernames
     *
     * @return bool
     */
    private function populateMoreUsernames() {
        $currentLength = TwitterUser::select(DB::raw('MAX(CHAR_LENGTH(username)) as Max'))->pluck('Max');

        if(is_null($currentLength) || !TwitterUser::whereUsername(str_repeat('_', $currentLength))->exists()) {
            return true;
        }

        if(TwitterUser::neverQueried()->count() > 0 || TwitterUser::free()->count() > 0) {
            return false;
        }

        return true;
    }
}
