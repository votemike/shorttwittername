<?php namespace App\Twitter;

use Config;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Support\ServiceProvider;

class TwitterServiceProvider extends ServiceProvider {

    public function register() {
        $this->app->bind('twitter', function() {
            $stack = HandlerStack::create();


            $middleware = new Oauth1([
                'consumer_key'    => Config::get('twitter.consumer_key'),
                'consumer_secret' => Config::get('twitter.consumer_secret'),
                'token'           => Config::get('twitter.token'),
                'token_secret'    => Config::get('twitter.token_secret')
            ]);
            $stack->push($middleware);

            $client = new Client([
                'base_uri' => 'https://api.twitter.com/1.1/',
                'handler' => $stack,
                'auth' => 'oauth'
            ]);

            return new TwitterApi($client);
        });

    }
}