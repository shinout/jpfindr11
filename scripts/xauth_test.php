<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/../lib/twitteroauth/twitteroauth/twitteroauth.php");
require("${script_dir}/../lib/libfnc-bitly.php");

$users = array(
/*
  array(
    "username" => "PersonFinder_Iw",
    "password" => "pfiwate2011",
  ),
*/
  array(
    "username" => "PersonFinder_MI",
    "password" => "697no1",
  ),
);

/* USE XAUTH */
/*
foreach ($users as $user) {
  $to=new TwitterOAuth(TWITTER_CKEY, TWITTER_CSEC);
  $token = $to->getXAuthToken($user["username"], $user["password"]);
  var_dump($token);
}
*/
