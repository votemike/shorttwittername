<?php

namespace App\Console\Commands;

use App\Twitter\Twitter;
use App\Twitter\TwitterAccountStatus;
use App\Twitter\TwitterUser;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class CheckTwitterNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:checknames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chooses some Twitter names to check up on and saves the information to the DB.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $usernames = $this->selectUsernames()->pluck('username');
        $users = Twitter::lookup($usernames);

        if($users->isEmpty()) {
            $this->saveNonActiveUsers($usernames);
            return;
        }

        $this->saveActiveUsers($users);

        $active = array_map('strtolower', $users->pluck('screen_name')->toArray());
        $notActive = array_diff($usernames->toArray(), $active);

        $this->saveNonActiveUsers($notActive);
    }

    private function saveActiveUsers($active) {
        foreach($active as $user) {
            $twitterUser = TwitterUser::whereUsername(strtolower($user->screen_name))->first();
            $twitterUser->name = $user->name;
            $twitterUser->profile_pic = str_replace('normal', 'bigger', $user->profile_image_url);
            $twitterUser->status = TwitterAccountStatus::ACTIVE;
            $twitterUser->date_registered = Carbon::parse($user->created_at);
            $twitterUser->last_checked = Carbon::now();
            $twitterUser->save();
        }
    }

    private function saveNonActiveUsers($notActive) {
        foreach($notActive as $user) {
            $twitterUser = TwitterUser::whereUsername($user)->first();
            $twitterUser->status = Twitter::getUsernameStatus($user);
            $twitterUser->name = null;
            $twitterUser->profile_pic = null;
            $twitterUser->date_registered = null;
            $twitterUser->last_checked = Carbon::now();
            $twitterUser->save();
        }
    }

    private function selectUsernames() {
        $toCheck = TwitterUser::neverQueried();

        if($toCheck->count() == 0) {
            if(TwitterUser::free()->count() > 0) {
                return TwitterUser::orderBy('last_checked', 'asc')->take(100)->get();
            } else {
                $length = $this->getLongestUsernameLength();
                $this->populateDatabaseWithUsernamesOfLength($length+1);
            }
        }
        return TwitterUser::neverQueried()->take(100)->get();
    }

    private function populateDatabaseWithUsernamesOfLength($length) {
        $strings = $this->makeStrings($length);

        $chunks = array_chunk($strings, 100);
        foreach($chunks as $chunk) {
            $toInsert = $this->convertChunkToInsert($chunk);
            DB::table('twitter_users')->insert($toInsert);
        }
    }

    private function convertChunkToInsert($chunk) {
        $toInsert = [];
        foreach($chunk as $item) {
            $toInsert[] = ['username' => $item, 'status' => TwitterAccountStatus::NOT_RETRIEVED];
        }
        return $toInsert;
    }

    private function makeStrings($length) {
        $chars = str_split('abcdefghijklmnopqrstuvwxyz1234567890_');

        if($length <= 1) {
            return $chars;
        }

        $strings = $this->makeStrings($length-1);

        $finalStrings = [];
        foreach($chars as $char) {
            foreach($strings as $string) {
                $finalStrings[] = $char.$string;
            }
        }

        return $finalStrings;
    }

    private function getLongestUsernameLength() {
        if(TwitterUser::count() == 0) {
            return 0;
        }
        return TwitterUser::select(DB::raw('MAX(CHAR_LENGTH(username)) as Max'))->pluck('Max');
    }
}
