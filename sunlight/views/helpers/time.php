<?php
namespace Views\Helpers;

class Time extends Helper {
	public function timeAgoInWords($timestamp) {
		$delta = time() - $timestamp;

		if ($delta < 60) {
			return "less than 1 minute ago";
		} elseif ($delta < 120) {
			return "1 minute ago";
		} elseif ($delta < 3600) {
			return floor($delta / 60) . " minutes ago";
		} elseif ($delta < 7200) {
			return "1 hour ago";
		} elseif ($delta < 86400) {
			return floor($delta / 3600) . " hours ago";
		} elseif ($delta < 172800) {
			return "1 day ago";
		} elseif ($delta < 604800) {
			return floor($delta / 86400) . " days ago";
		} elseif ($delta < 1209600) {
			return "1 week ago";
		} elseif ($delta < 2419200) {
			return floor($delta / 604800) . " weeks ago";
		} elseif ($delta < 4838400) {
			return "1 month ago";
		} elseif ($delta < 29030400) {
			return floor($delta / 2419200) . " months ago";
		} elseif ($delta < 63072000) {
			return "1 year ago";
		} else {
			return floor($delta / 31536000) . " years ago";
		}
	}

	public function timeAgoInWordsLikeFacebook($timestamp, $timezone) {
		$delta = time() - $timestamp;
		$adjustedTimestamp = $timestamp + $timezone * 3600;

		if ($delta < 2) {
			return "1 second ago";
		} elseif ($delta < 60) {
			return "$delta seconds ago";
		} elseif ($delta < 120) {
			return "about a minute ago";
		} elseif ($delta < 3600) {
			return floor($delta / 60) . " minutes ago";
		} elseif ($delta < 7200) {
			return "about an hour ago";
		} elseif ($delta < 86400) {
			return floor($delta / 3600) . " hours ago";
		} elseif ($delta < 172800) {
			return "Yesterday at " . gmdate("H:i", $adjustedTimestamp);
		} elseif ($delta < 345600) {
			return gmdate("l", $adjustedTimestamp) . " at " . gmdate("H:i", $adjustedTimestamp);
		} else {
			return gmdate("F d", $adjustedTimestamp) . " at " . gmdate("H:i", $adjustedTimestamp);
		}
	}
}
?>