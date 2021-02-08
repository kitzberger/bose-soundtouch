<?php

namespace Kitzberger\BoseSoundtouch;

class TuneIn
{

	// <outline type="link" text="Lokales Radio" URL="http://opml.radiotime.com/Browse.ashx?c=local" key="local"/>
	// <outline type="link" text="Musik" URL="http://opml.radiotime.com/Browse.ashx?c=music" key="music"/>
	// <outline type="link" text="Talksendungen" URL="http://opml.radiotime.com/Browse.ashx?c=talk" key="talk"/>
	// <outline type="link" text="Sport" URL="http://opml.radiotime.com/Browse.ashx?c=sports" key="sports"/>
	// <outline type="link" text="Orte" URL="http://opml.radiotime.com/Browse.ashx?id=r0" key="location"/>
	// <outline type="link" text="Sprachen" URL="http://opml.radiotime.com/Browse.ashx?c=lang" key="language"/>
	// <outline type="link" text="Podcasts" URL="http://opml.radiotime.com/Browse.ashx?c=podcast" key="podcast"/>

	const URL_SCHEME = 'http://opml.radiotime.com/Search.ashx?render=%s&query=%s';

	public static function search($query)
	{
		$url = sprintf(self::URL_SCHEME, 'json', rawurlencode($query));
		$json = file_get_contents($url);
		return json_decode($json);
	}
}
