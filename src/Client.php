<?php

namespace Monks\Spotify;

use GuzzleHttp\Client as Guzzle;
use stdClass;

class Client {
    private string $token;
    private Guzzle $client;

    protected const GET = "GET";
    protected const POST = "POST";

    public function __construct(
        private readonly string $id,
        private readonly string $secret
    )
    {
        $this->client = new Guzzle([
            'verify' => false
        ]);

        $response = $this->client->post(
            'https://accounts.spotify.com/api/token',
            [
                "headers" => [
                    "content-type" => 'application/x-www-form-urlencoded'
                ],
                "body" => "grant_type=client_credentials&client_id={$id}&client_secret={$secret}"
            ]
        );

        $this->token = json_decode((string)$response->getBody())->access_token;
    }

    public function getPlaylist(string $id): stdClass
    {
        return $this->request(self::GET, "/playlists/{$id}");
    }

    public function getPlaylistTracks(string $id, int $offset = 0, int $limit = 50) {
        return $this->request(self::GET, "/playlists/{$id}/tracks?offset={$offset}&limit={$limit}");
    }

    private function request(string $method, string $uri): stdClass
    {
        $response = $this->client->request(
            $method,
            "https://api.spotify.com/v1{$uri}",
            [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}"
                ]
            ]
        );

        return json_decode((string)$response->getBody());
    }
}

