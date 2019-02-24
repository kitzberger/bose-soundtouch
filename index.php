<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require '/projects/soundtouch-client/vendor/autoload.php';
require '/projects/soundtouch-client/src/SoundtouchClient.php';
require '/projects/soundtouch-client/src/TuneIn.php';

$soundtouch = new SoundtouchClient();
$soundtouch->init();

$tunein_query = $_GET['tunein_query'] ?? '';
if ($tunein_query) {
	$tuneinResults = TuneIn::search($tunein_query);
}

$tunein_play = $_GET['tunein_play'] ?? null;
if ($tunein_play) {
	$success = $soundtouch->play(
		'TuneIn',
		$tunein_play['subtype'],
		$tunein_play['id'],
		$tunein_play['name'],
		$tunein_play['image']
	);
	if ($success) {
		header('Location: index.php');
	}
}

$preset = $_GET['soundtouch_preset'] ?? null;
if ($preset) {
	$success = $soundtouch->play('SoundTouch', 'Preset', $preset);
	if ($success) {
		header('Location: index.php');
	}
}

$preset = $_GET['soundtouch_setpreset'] ?? null;
if ($preset) {
	$success = $soundtouch->setPreset($preset);
	if ($success) {
		header('Location: index.php');
	}
}

function render_media($tag, $id, $title, $text, $image, $urlTitle, $urlExtra = null)
{
	echo '<'.$tag.' class="media mb-2" data-id="'.$id.'">';
	if ($image) {
		echo '<img width="64" height="64" src="'.$image.'" class="img-thumbnail mr-3" alt="'.$title.'">';
	} else {
		echo '<svg class="img-thumbnail mr-3" width="64" height="64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="'.$id.'" style="text-anchor: middle;"><title>Placeholder</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">'.$id.'</text></svg>';
	}
	echo '	<div class="media-body">';
	if ($urlExtra) {
		echo '<a href="' . $urlExtra[0] . '" class="float-right" onclick="return confirm(\'' . $urlExtra[1] . '\')">' . $urlExtra[2] . '</a>';
	}
	if ($urlTitle) {
		$title = '<a href="'.$urlTitle.'">'.$title.'</a>';
	}
	echo '  	<h5 class="mt-0 mb-1">' . $title . '</h5>';
	echo '		' . $text;
	echo '	</div>';
	echo '</'.$tag.'>';
}

function debug($obj)
{
	if (is_null($obj)) {
		echo '<code>NULL</code>';
	} else {
		echo '<pre><code>' . print_r($obj, true) . '</code></pre>';
	}
}

?>
<!doctype html>
<html lang="en">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<style></style>
	</head>
	<body>
		<div class="container jumbotron">
			<h1 class="display-4">
				Bose control for <span class="text-primary"><?= $soundtouch->getName() ?></span>
			</h1>

		</div>
		<div class="container">

			<div class="row">
				<div class="col-7">
					<h3>Currently playing</h3>
					<?php
						$currentSong = $soundtouch->getCurrentSong();
						if ($currentSong) {
							//debug($currentSong);
							switch ($currentSong->getSource()) {
								case 'INVALID_SOURCE': $class = 'danger'; break;
								case 'STANDBY': $class = 'warning'; break;
								default: $class = 'info'; break;
							}
							echo '<div class="row">';
							echo '	<div class="col-6">';
							echo '		<div class="alert alert-'.$class.'">';
							echo 'Source: ' . $currentSong->getSource();
							if ($item = $currentSong->getContentItem()) {
								echo '<br><a href="' . $item->getLocation(). '">' . $item->getName() . '</a>';
							}
							echo '		</div>';
							if ($currentSong->getTrack()) {
								echo 'Track : ' . $currentSong->getTrack();
								echo ' (' . intval($currentSong->getDuration() / 60) . ':' . ($currentSong->getDuration() % 60) . ')';
								echo '<br>';
							}
							if ($currentSong->getArtist()) {
								echo 'Artist : ' . $currentSong->getArtist() . "<br>";
							}
							if ($currentSong->getAlbum()) {
								echo 'Album : ' . $currentSong->getAlbum() . "<br>";
							}
							echo '	</div>';
							echo '	<div class="col-6">';
							if ($currentSong->getImage()) {
								echo '<img class="img-fluid" src="' . $currentSong->getImage() . '"/>';
							}
							echo '	</div>';
							echo '</div>';


						} else {
							echo '<div class="alert alert-warning">Nothing at the moment. Search for station?</div>';
						}
					?>
					<h3>Spotify search</h3>
					<form>
						<div class="form-group row">
							<label for="spotify_query" class="col-sm-2 col-form-label">Query</label>
							<div class="col-sm-10">
								<input id="spotify_query" name="spotify_query" class="form-control" value="<?= isset($spotify_query) ? htmlspecialchars($spotify_query) : '' ?>" />
							</div>
						</div>
					</form>
					<h3>TuneIn search</h3>
					<form>
						<div class="form-group row">
							<label for="tunein_query" class="col-sm-2 col-form-label">Query</label>
							<div class="col-sm-10">
								<input id="tunein_query" name="tunein_query" class="form-control" value="<?= isset($tunein_query) ? htmlspecialchars($tunein_query) : '' ?>" placeholder="swr1, ..." />
							</div>
						</div>
					</form>
					<?php
						if (isset($tuneinResults)) {
							echo '<ul class="list-unstyled">';
							foreach ($tuneinResults->body as $result) {
								#echo debug($result);
								render_media(
									'li',
									$result->guide_id,
									$result->text . ' (' . $result->item . ')',
									$result->subtext ?? '',
									$result->image,
									'index.php' .
										'?tunein_play[subtype]=' . $result->item .
										'&tunein_play[id]=' . $result->guide_id .
										'&tunein_play[name]=' . rawurlencode($result->text) .
										'&tunein_play[image]=' . rawurlencode($result->image)
								);
							}
							echo '</ul>';
						}
					?>
				</div>
				<div class="col-5">
					<h2>Volume</h2>
					<?php
						if ($vol = $soundtouch->getVolume()) {
							echo '<div class="alert alert-info">'.$vol.' of 100</div>';
						} else {
							echo '<div class="alert alert-warning">Muted</div>';
						}
					?>
					<h2>Presets</h2>
					<?php
						$presets = $soundtouch->getPresets();
						if (empty($presets)) {
							echo 'None';
						} else {
							echo '<ul class="list-unstyled">';
							$presetSlots = [];
							foreach ($presets as $preset) {
								$presetSlots[$preset->getId()] = $preset;
							}
							#debug($presets);
							foreach ([1,2,3,4,5,6] as $presetIndex) {
								$preset = $presetSlots[$presetIndex] ?? null;
								if ($preset) {
									//debug($preset);
									$item = $preset->getContentItem();
									//debug($item);
									render_media(
										'li',
										$presetIndex,
										$item->getName() ?: 'Unknown',
										$item->getSource() . ($item->getAccount() ? ' (' . $item->getAccount() . ')' : ''),
										$item->getImage(),
										'index.php?soundtouch_preset=' . $preset->getId(),
										['index.php?soundtouch_setpreset=' . $preset->getId(), 'Sure?', 'Set']
									);
								} else {
									render_media(
										'li',
										$presetIndex,
										'Empty preset slot #' . $presetIndex,
										'',
										null,
										null,
										['index.php?soundtouch_setpreset=' . $presetIndex, 'Sure?', 'Set']
									);
								}
							}
							echo '</ul>';
						}
					?>
				</div>
			</div>
		</div>
	</body>
</html>
