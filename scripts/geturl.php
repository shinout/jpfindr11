<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/../lib/PersonFinderTwitterOAuth.php");
require("${script_dir}/../lib/libfnc-bitly.php");

$to=new PersonFinderTwitterOAuth(TWITTER_CKEY, TWITTER_CSEC);
$token = $to->getRequestToken();
$url   = $to->getAuthorizeURL($token);
$res   = $to->getAccessToken();
echo $url;
