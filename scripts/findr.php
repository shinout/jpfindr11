<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/../lib/twitteroauth/twitteroauth/twitteroauth.php");
require("${script_dir}/../lib/libfnc-bitly.php");

$xml=simplexml_load_file("https://japan.person-finder.appspot.com/feeds/person");
foreach($xml->entry as $v){
	$name=(string)$v->title;
	$time=(string)$v->updated;
	$post=(string)$v->author->name;
	$uri =(string)$v->id;
	if(preg_match("/ /",$name)){
		$name=explode(" ",$name);
		$name=$name[1]." ".$name[0];
	}
	if(preg_match("/ /",$post)){
		$post=explode(" ",$post);
		$post=$post[1]." ".$post[0];
	}
	$uri=explode("/",$uri);
	$uri="http://japan.person-finder.appspot.com/view?id=japan.person-finder.appspot.com/".$uri[1];
	$url=bitly($uri);
	$time=date("Y.m.d H:i:s",strtotime($time));
	$str="[".$time."] 「".$name."」を探しています、探している人は「".$post."」です。 ".$url." #personfinder_anpi";
  var_dump($str);
  /*
	$to=new TwitterOAuth($ckey,$csec,$akey,$asec);
	$to=new TwitterOAuth(TWITTER_CKEY, TWITTER_CSEC, TWITTER_AKEY, TWITTER_ASEC);
	$to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>$str));
  */
}
/*
define("TWITTER_AKEY", "264666101-HlCRAq8H5KwxxgRwxo2USJDOdB1RAvrA5pVkh9C0");
define("TWITTER_ASEC", "264666101-HlCRAq8H5KwxxgRwxo2USJDOdB1RAvrA5pVkh9C0");
*/
