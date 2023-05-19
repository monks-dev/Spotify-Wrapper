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
    <div class="ui menu inverted">
        <span class="item">Spotify Wrapper</span>
        <div class="right menu">
            <div class="item">
                <div class="ui form">
                    <form method="POST">
                        <input type="text" placeholder="Playlist ID..." name="id">
                    </form>

                </div>
            </div>
            <div class="item">
                <div class="ui primary button">Login</div>
            </div>
        </div>

    </div>
    <?php if (isset($playlist)) { ?>
        <div class="ui two column grid">
            <div class="left attached column">
                <div class="ui inverted horizontal card">
                    <div class="image">
                        <img class="ui small circular image" src="<?= $playlist->images[0]->url ?>">
                    </div>
                    <div class="content">
                        <div class="header"><?= $playlist->name ?></div>
                        <div class="meta">
                            <span class="item"><?= $playlist->owner->display_name ?></span>
                            <span class="item"><?= $playlist->tracks->total ?> Tracks</span>
                        </div>
                        <div class="description">
                            <p><?= $playlist->description ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="right attached column">
                <div class="ui big inverted relaxed animated selection divided list">
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

            </div>
        </div>


    <?php } ?>

</div>

</body>
</html>
