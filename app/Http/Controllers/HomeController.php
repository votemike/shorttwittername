<?php

namespace App\Http\Controllers;

use App\Twitter\TwitterUser;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show free usernames of a certain length
     *
     * @param Request $request
     * @param int $length
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(Request $request, $length = 1) {
        if(TwitterUser::whereUsernameLength($length)->count() == 0 && $length > 1) {
            return redirect('/');
        }
        $users = TwitterUser::free()->whereUsernameLength($length)->get();
        $last = null;
        if($users->isEmpty() && TwitterUser::notRetrieved()->whereUsernameLength($length)->count() == 0) {
            $last = TwitterUser::whereUsernameLength($length)->orderBy('date_registered', 'DESC')->first();
        }
        $lengths = TwitterUser::selectUsernameLength()->orderBy('length')->groupBy('length')->get()->pluck('length')->toArray();
        return view('home')->withUsers($users)->withLengths($lengths)->withLength((int)$length)->withLast($last);
    }

    /**
     * Show details of all usernames of a certain length
     *
     * @param Request $request
     * @param int $length
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function all(Request $request, $length = 1) {
        if(TwitterUser::whereUsernameLength($length)->count() == 0 && $length > 1) {
            return redirect('/all');
        }
        $users = TwitterUser::whereUsernameLength($length)->paginate(444); // 444 = number of possible username chars * bootstrap columns = 37*12
        $lengths = TwitterUser::selectUsernameLength()->orderBy('length')->groupBy('length')->get()->pluck('length')->toArray();
        return view('all')->withUsers($users)->withLengths($lengths)->withLength((int)$length);
    }
}
