<?php
function bitly($url){
	$username=BITLY_USERNAME;
	$apikey=BITLY_APIKEY;
	$request="http://api.bit.ly/shorten?login=".$username."&apiKey=".$apikey."&version=2.0.1&format=xml&longUrl=".$url;
	$xml=simplexml_load_file($request);
	if($xml->errorCode==0){
		return (string)$xml->results->nodeKeyVal->shortUrl;
	}else{
		return "ERROR: ".(string)$xml->errorMessage;
	}
}

#test
#echo bitly("http://example.com");
