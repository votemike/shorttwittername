<?php

namespace App\Http\Controllers;

use App\Twitter\TwitterUser;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, $length = 1) {
        if(TwitterUser::whereUsernameLength($length)->count() == 0 && $length > 1) {
            return redirect('/');
        }
        $users = TwitterUser::free()->whereUsernameLength($length)->get();
        $lengths = TwitterUser::selectUsernameLength()->orderBy('length')->groupBy('length')->get()->pluck('length')->toArray();
        return view('home')->withUsers($users)->withLengths($lengths)->withLength((int)$length);
    }

    public function all(Request $request, $length = 1) {
        if(TwitterUser::whereUsernameLength($length)->count() == 0 && $length > 1) {
            return redirect('/all');
        }
        $users = TwitterUser::whereUsernameLength($length)->paginate(444); // 444 = number of possible username chars * bootstrap columns = 37*12
        $lengths = TwitterUser::selectUsernameLength()->orderBy('length')->groupBy('length')->get()->pluck('length')->toArray();
        return view('all')->withUsers($users)->withLengths($lengths)->withLength((int)$length);
    }
}
