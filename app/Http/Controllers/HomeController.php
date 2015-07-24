<?php

namespace App\Http\Controllers;

use App\Twitter\TwitterAccountStatus;
use App\Twitter\TwitterUser;
use DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, $length = 1) {
        $users = TwitterUser::whereStatus(TwitterAccountStatus::FREE)->whereRaw('LENGTH(username) = ?', [$length])->get();
        if($users->isEmpty()) {
            return redirect('/');
        }
        $lengths = TwitterUser::select(DB::raw('CHAR_LENGTH(username) as length'))->orderBy('length')->groupBy('length')->get()->pluck('length')->toArray();
        return view('home')->withChunks($users->chunk(12))->withLengths($lengths)->withLength((int)$length);
    }
}
