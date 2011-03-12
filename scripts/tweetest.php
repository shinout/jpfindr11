<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/../lib/twitteroauth/twitteroauth/twitteroauth.php");
require("${script_dir}/../lib/libfnc-bitly.php");

	$to=new TwitterOAuth(TWITTER_CKEY, TWITTER_CSEC, "264727639-zu9VinBy85zbJ4oEgxjWz2NvtaNe8sHcmiZIbHpT", "6LfTTdcOgqkir57T0Y8c1eZDZzGIV8uiEwGbJPSo6Ag");
	$result = $to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>"TESTING"));
