<?php

use Monks\Spotify\Client;
use CommandString\Utils\ArrayUtils;
use GuzzleHttp\Exception\GuzzleException;

const __PRIVATE__ = __DIR__ . '/..';

require_once __PRIVATE__ . '/vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $increment = 50;

    $keyFile = file_get_contents(__PRIVATE__ . '/apikey');
    $lines = explode(".", $keyFile);
    $spotify = new Client($lines[0], $lines[1]);
    $playlistId = $_POST['id'];

    try {
        $playlist = $spotify->getPlaylist($playlistId);
        $loops = ceil($playlist->tracks->total / $increment);

        $tracks = [];
        foreach (range(0, $loops) as $multiplier) {
            $offset = $multiplier * $increment;

            $trackList = $spotify->getPlaylistTracks($playlistId, $offset);

            $tracks = [...$tracks, ...array_map(static fn($item) => $item->track, (array)$trackList->items)];
        }

        $tracks = ArrayUtils::toStdClass($tracks);

        $error = false;
    } catch (GuzzleException) {
        $error = true;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spotify API</title>
    
    <!-- Fomantic -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js"></script>
</head>
<body>
<div style="min-height: 100vh" class="ui attached inverted segment">
    <form method="POST" class="ui inverted <?= $error ? 'error' : '' ?> form">
        <div class="field">
            <label>Playlist ID</label>
            <input type="text" name="id" placeholder="Playlist ID">
        </div>
        <button class="ui button" type="submit">Submit</button>
        <div class="ui error message">
            <div class="header">Playlist <?= $playlistId ?> does not exist!</div>
        </div>
    </form>

    <?php if (isset($playlist)) { ?>
        <div class="ui divider"></div>
        <img class="ui tiny rounded image" src="<?= $playlist->images[0]->url ?>">
        <span class="ui large text"><?= $playlist->tracks->total ?> Tracks</span>
        <div class="ui inverted very relaxed list">
            <?php foreach ($tracks as $track) { ?>
                <div class="item">
                    <img class="ui avatar image" src="<?= $track->album->images[0]->url ?>">
                    <div class="content">
                        <div class="header"><?= $track->name ?></div>
                        <div class="description"><?= implode(", ", array_map(static fn($artist) => $artist->name, (array)$track->artists)) ?></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

</body>
</html>
