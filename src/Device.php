<?php

namespace Kitzberger\BoseSoundtouch;

use Sabinus\SoundTouch\SoundTouchApi;
use Sabinus\SoundTouch\Component\ContentItem;
use Sabinus\SoundTouch\Constants\Source;
use Sabinus\SoundTouch\Constants\Key;

class Device
{
	protected $ip = null;
	protected $name = null;
	protected $api = null;
	protected $info = null;

	public function __construct($deviceInfo)
	{
		$this->ip = $deviceInfo['ip'];
		$this->name = $deviceInfo['name'];

		$this->api = new SoundTouchApi($this->ip);
		if (!$this->api) {
		    throw new \Exception('SoundTouchApi couldn\'t be initialized!');
		}

		$this->info = $this->api->getInfo();
		if (!$this->info) {
			throw new \Exception('Can\'t retrieve info from SoundTouchAPI!' . PHP_EOL . $this->api->getMessageError());
		}
	}

	public function getInfo()
	{
		return $this->info;
	}

	public function getName()
	{
		return $this->info->getName();
	}

	public function getCurrentSong()
	{
		$nowPlaying = $this->api->getNowPlaying();
		return $nowPlaying;
	}

	public function getSources()
	{
		return $this->api->getSources();
	}

	public function getPresets()
	{
		$presets = $this->api->getPresets();
		return $presets;
	}

	public function getMessageError()
	{
		return $this->api->getMessageError();
	}

	/**
	 * Play a resource
	 *
	 * @param  string $type
	 * @param  string $subtype
	 * @param  string $id
	 * @param  string $name
	 * @param  string $image
	 * @return bool
	 */
	public function play($type, $subtype, $id, $name = null, $image = null)
	{
		echo '<pre>'; var_dump($type, $subtype, $id, $name); echo '</pre>';
		switch (strtoupper($type)) {
			case 'TUNEIN':
				switch (strtoupper($subtype)) {
					case 'STATION':
						$source = new ContentItem();
						$source->setSource(Source::TUNEIN);
						$source->setType('stationurl');
						$source->setLocation('/v1/playback/station/' . $id);
						$source->setName($name);
						$source->setImage($image);
						break;
					case 'TOPIC':
						$source = new ContentItem();
						$source->setSource(Source::TUNEIN);
						$source->setType('tracklisturl');
						$source->setLocation('/v1/playback/episodes/' . $id);
						$source->setName($name);
						$source->setImage($image);
						break;
					// case 'SHOW':
					// 	$source = new ContentItem();
					// 	$source->setSource(Source::TUNEIN);
					// 	$source->setType('podcasturl');
					// 	$source->setLocation('/v1/playback/episodes/' . $id);
					// 	$source->setName($name);
					// 	$source->setImage($image);
					// 	break;
					default:
						throw new \Exception('Unsupported subtype: ' . $subtype);
				}
				#debug($source); die();
				return $this->api->selectSource($source);
				break;
			case 'SOUNDTOUCH':
				if (strtoupper($subtype) === 'PRESET') {
					return $this->api->setKey(strtoupper($subtype) . '_' . $id);
				}
				throw new \Exception('Unsupported subtype: ' . $subtype);
			default:
				throw new \Exception('Unsupported type: ' . $type);
		}
	}

	public function getVolume()
	{
		$volume = $this->api->getVolume();
		if ($volume) {
			return $volume->isMuted() ? null : $volume->getActual();
		} else {
			return null;
		}
	}

	public function setVolume($volume = 100)
	{
		if ($volume > 0) {
			$this->api->setVolume($volume);
		} else {
			$this->api->mute();
		}
	}

	public function setPreset($preset)
	{
		if ($preset > 0 && $preset <= 6) {
			return $this->api->setPreset($preset);
		}
	}

	public function playPause()
	{
		$this->api->playPause();
	}
}
