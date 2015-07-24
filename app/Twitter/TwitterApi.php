<?php namespace App\Twitter;

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class TwitterApi {
    protected $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function lookup(Collection $usernames) {
        $response = $this->client->get('users/lookup.json', ['query' => 'screen_name='.$usernames->implode(','), 'http_errors' => false]);
        if($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            return new Collection();
        }
        return new Collection(json_decode($response->getBody()));
    }

    public function getUsernameStatus($username) {
        $response = $this->client->get('users/show.json', ['query' => 'screen_name='.$username, 'http_errors' => false]);
        $code = $response->getStatusCode();
        if($code == Response::HTTP_FORBIDDEN) {
            return TwitterAccountStatus::SUSPENDED;
        } elseif($code == Response::HTTP_NOT_FOUND) {
            return $this->getFreeOrDeactivated($username);
        } elseif($code == Response::HTTP_TOO_MANY_REQUESTS) {
            dd('Too many requests. Give it a rest for a minute');
        }
        dd('Unrecognized status code for username: '.$username.'. Michael look! '.$code);
    }

    private function getFreeOrDeactivated($username) {
        $response = $this->client->get('https://twitter.com/users/username_available', ['query' => 'username='.$username]);
        $unStatus = json_decode($response->getBody());
        if($unStatus->valid && $unStatus->reason == 'available') {
            return TwitterAccountStatus::FREE;
        }
        return TwitterAccountStatus::DEACTIVATED;
    }
}