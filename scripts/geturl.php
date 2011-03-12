<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/../lib/twitteroauth/twitteroauth/twitteroauth.php");
require("${script_dir}/../lib/libfnc-bitly.php");

$to=new TwitterOAuth(TWITTER_CKEY, TWITTER_CSEC);
$token = $to->getRequestToken();
$url   = $to->getAuthorizeURL($token);
$res   = $to->getAccessToken();
echo $url;
