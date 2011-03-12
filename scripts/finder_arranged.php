<?php

error_reporting(E_ALL);
require("twitteroauth.php");
require("libfnc-bitly.php");

$tmp=file_get_contents("https://japan.person-finder.appspot.com/feeds/person");
$rep=str_replace("<pfif:","<", $tmp);
$rep=str_replace("</pfif:","</",$rep);
//var_dump($rep);
$xml=simplexml_load_string($rep);
foreach($xml->entry as $v){
  $name=(string)$v->title;
  $time=(string)$v->updated;
  $post=(string)$v->author->name;
  $uri =(string)$v->id;
  $home_state=(string)$v->person->home_state;
  $home_city=(string)$v->person->home_city;
  $home_street=(string)$v->person->home_street;
  //$home="test";
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
  if(strcmp($home_state,'')==0 && strcmp($home_city,'')==0 && strcmp($home_street,'')==0)
    $str="「".$name."」さん（住所未記入）を「".$post."」さんが探しています。 [".$time."] ".$url." #personfinder_anpi";
  else
    $str="「".$name."」さん（".$home_state." ".$home_city." ".$home_street."）を「".$post."」さんが探しています。 [".$time."] ".$url." #personfinder_anpi";



  switch($home_state){
    case "宮城":
    case "宮城県":
      list($akey, $asec) = getTokenByPref("miyagi");
      tweet($akey, $asc, $str);
      consolelog( "miyagi");
    break;

    case "岩手":
    case "岩手県":
    $ckey="UhR5PWAfW0ODL7iz5Zg";
                $csec="WSRqCnxwbLgcEpxWBwCWtbPImKiZTOqtrYxDXPCV0";
                $akey="264725307-c02D3Gy1H9qVQEnVibQ7v0lVi0KQwjjgSgnmu4gW";
                $asec="w5VUu4GEYbDsbRV5sFipqUSOx60EThEXvwypxDviHJw";
                $to=new TwitterOAuth($ckey,$csec,$akey,$asec);
                $to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>$str));
    print "iwate";
    break;

    case "福島":
    case "福島県":
    $ckey="UhR5PWAfW0ODL7iz5Zg";
                $csec="WSRqCnxwbLgcEpxWBwCWtbPImKiZTOqtrYxDXPCV0";
                $akey="264727639-zu9VinBy85zbJ4oEgxjWz2NvtaNe8sHcmiZIbHpT";
                $asec="6LfTTdcOgqkir57T0Y8c1eZDZzGIV8uiEwGbJPSo6Ag";
                $to=new TwitterOAuth($ckey,$csec,$akey,$asec);
                $to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>$str));
    print "fukushima";
    break;
    
    case "長野":
    case "長野県":
    $ckey="UhR5PWAfW0ODL7iz5Zg";
                $csec="WSRqCnxwbLgcEpxWBwCWtbPImKiZTOqtrYxDXPCV0";
                $akey="264733583-ha3diQCVlYLFCinTsRA5VtMnM85Obm2FDlTqvfC8";
                $asec="Aoo6xTecuGDbtxYZ5JulbZjvjGdHUCb8jvaiAqVxA";
                $to=new TwitterOAuth($ckey,$csec,$akey,$asec);
                $to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>$str));
    print "nagano";
    break;
    
    case "茨城":
    case "茨城県":
      $akey="264744081-8Edgwvt0SuTH3veAplMCWWY8ptAMFCR8Q6yjrr9k";
      $asec="GJr3Lsey4cnxFHnQUYv00p30bgcANWLCrlCvjaCs3M";
      tweet($akey, $asec, $str);
      cosolelog( "ibaraki.");
      break;
    break;

    case "青森":
    case "青森県":
      $akey="264739659-UOVvfJQ16qx549cKgvbbPR1NKD5GZJ5YbyU2o0IO";
      $asec="TWLUKgy1jWnZUA1TrG7hLRDlEPKdpdkKBqewaPZVA";
      tweet($akey, $asec, $str);
      cosolelog( "aomori.");
      break;

    default:
      $akey="264776369-MywDRlUaKLdnOMmaqTa22KSNtPEcPX0xUDz4dxjJ";
      $asec="975p3fYwoeeNPwJEjYuc5Orc4qZYKu6WcH2d4MXSgdo";
      tweet($akey, $asec, $str);
      consolelog( "others");
      break;
  }
  cosolelog( "finish.");
}

function consolelog($str) {
  print $str."\n";
}

?>

