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
        DB::connection()->disableQueryLog();

        $usernames = $this->selectUsernames();
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

    /**
     * Save a non active user
     *
     * @param $active
     */
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

    /**
     * Save a non active user (available, suspended, deactivated etc...)
     *
     * @param $notActive
     */
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

    /**
     * Choose which usernames to query next
     * If there are no free usernames at the moment, increae the length of the possible usernames by 1
     *
     * @return mixed
     */
    private function selectUsernames() {
        $toCheck = TwitterUser::neverQueried();

        if($toCheck->count() == 0) {
            return TwitterUser::orderBy('last_checked', 'asc')->take(100)->get()->pluck('username');
        }

        return TwitterUser::neverQueried()->take(100)->get()->pluck('username');
    }
}
