# Spotify
A very simple and not fully implemented Spotify API wrapper

## UI Interface
![Spotify Wrapper Example](SpotifyAPI_2.png)
# Usage

## Creating Client

```php
use Monks\Spotify\Spotify;

$spotify = new Spotify($clientId, $secret);
```

## Getting Playlist By Id

```php
$playlist = $spotify->getPlaylist($playlistId);
```

## Getting Playlist Tracks

```php
$tracks = $spotify->getPlaylistTracks($playlistId, $offset, $limit);
```