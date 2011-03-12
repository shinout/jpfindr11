<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/../lib/twitteroauth/twitteroauth/twitteroauth.php");
require("${script_dir}/../lib/libfnc-bitly.php");

class PersonFinderBot {
  private $token_filename = "tokens.tsv";
  private $bitly_filename = "bitlys.tsv";

  private function l($str) {
    echo $str."\n";
  }

  public function execute() {
    list($home_state, $str) = $this->parseXML();
    $this->l("XML was parsed.");
    $this->l("str: ".$str);
    $this->l("home_state: ".$home_state);
    $this->tweet($home_state, $str);
  }


  private function parseXML() {
    $tmp=file_get_contents("https://japan.person-finder.appspot.com/feeds/person");
    $rep=str_replace("<pfif:","<", $tmp);
    $rep=str_replace("</pfif:","</",$rep);
    $xml=simplexml_load_string($rep);
    foreach ($xml->entry as $v) {
      $name=(string)$v->title;
      $time=(string)$v->updated;
      $post=(string)$v->author->name;
      $uri =(string)$v->id;
      $home_state=(string)$v->person->home_state;
      $home_city=(string)$v->person->home_city;
      $home_street=(string)$v->person->home_street;
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
      list($bitly_user, $bitly_key) = $this->getBitLy();
      $url=bitly($uri, $bitly_user, $bitly_key );
      $time=date("Y.m.d H:i:s",strtotime($time));
      $address = (strcmp($home_state,'')==0 && strcmp($home_city,'')==0 && strcmp($home_street,'')==0)
                  ? "住所未記入"
                  : $home_state." ".$home_city." ".$home_street;

      $str = sprintf("「%s」さん（%s）を「%s」さんが探しています。 [ %s ] %s #personfinder_anpi", $name, $address, $post, $time, $url);
      return array($home_state, $str);
    }
  }

  private function getBitLy() {
    $bitlys = array();
    $script_dir = dirname(__FILE__);
    $lines = file($script_dir."/../".$this->bitly_filename);
    foreach ($lines as $line) {
      if ( substr($line,0,1) == "#")
        continue;

      $linearr = preg_split("/\t/", $line);
      if ( count($linearr) > 1) {
        $bitlys[] = $linearr;
      }
    }
    return $bitlys[array_rand( $bitlys )];
    //return array(BITLY_USERNAME, BITLY_APIKEY);
  }
 
  private function loadTokens() {
    $tokens = array();
    $script_dir = dirname(__FILE__);
    $lines = file($script_dir."/../".$this->token_filename);
    foreach ($lines as $line) {
      if ( substr($line,0,1) == "#")
        continue;

      $linearr = preg_split("/\t/", $line);
      $pref = $linearr[0];
      $akey = $linearr[1];
      $asec = $linearr[2];
      $tokens[$pref] = array("akey"=>$akey, "asec"=>$asec);
    }
    return $tokens;
  }

/*
  private function getHomeState($name) {
    $states = array(
      "青森" => array("青森", "青森県"),
      "茨城" => array("茨城", "茨城県"),
      "長野" => array("長野", "長野県"),
      "福島" => array("福島", "福島県"),
      "岩手" => array("岩手", "岩手県"),
      "宮城" => array("宮城", "宮城県"),
    );

    foreach ($states as $key => $value){
      if (in_array($name, $value)){
        return $key;
      }
    }
    return "その他";
  }
*/

  private function getStates() {
    $states = array();
    $states["青森"] = array("青森", "青森県");
    $states["岩手"] = array("岩手", "岩手県");
    $states["茨城"] = array("茨城", "茨城県");
    $states["長野"] = array("長野", "長野県");
    $states["福島"] = array("福島", "福島県");
    $states["宮城"] = array("宮城", "宮城県");
    return $states;

    return "その他";
  }


  private function tweet($home_state, $str) {
    $states = $this->getStates();
    $valid_home_state = "その他";
    foreach ($states as $k => $v) {
      foreach($v as $a) {
        if ($a == $home_state) {
          $valid_home_state = $k;
        }
      }
    }

    $tokens = $this->loadTokens();
    $token  = $tokens[$valid_home_state];

    $this->l("token[akey]:".$token["akey"]);
    $this->l("token[asec]:".$token["asec"]);

    $to=new TwitterOAuth(TWITTER_CKEY, TWITTER_CSEC, trim($token["akey"]), trim($token["asec"]));
    $result = $to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>$str));
    $this->l("tweet request. result in detail is ->". $result);


    /*
    $result = $to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>$str));
    $this->l("tweeted. result in detail :", $result);
    */
  }
}

$pf = new PersonFinderBot();
$pf->execute();
