<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/twitteroauth/twitteroauth/twitteroauth.php");
require("${script_dir}/libfnc-bitly.php");
require("${script_dir}/PersonFinderPlace.php");

class PersonFinderBot {
  private $token_filename = "tokens.tsv";
  private $bitly_filename = "bitlys.tsv";
  private $tweeted_filename = "tweeted";

  /* ログを出力 */
  public function l($str) { echo $str."\n"; }

  /* エントリ. ここをたたけ。 */
  public function execute() {
    $parsed_arr = $this->parseXML();
    $this->l("XML was parsed.");
    $this->l("got ".count($parsed_arr)." data from the XML.");
    foreach ($parsed_arr as $i=>$parsed) {
      $n = $i+1;
      $this->l("------data $n  -------");
      list($place, $str) = $parsed;
      $this->l("str: ".$str);
      $this->l("place: ".$place->__toString());
      $this->tweet($place, $str);
    }
  }

  /* XMLをパースして、位置情報(PersonFinderPlaceオブジェクト)とツイート文字列を返す */
  protected function parseXML() {
    $parsed_arr = array();
    $tmp=file_get_contents("https://japan.person-finder.appspot.com/feeds/person?key=".PERSONFINDER_KEY);
    //$tmp=file_get_contents("https://japan.person-finder.appspot.com/feeds/person");
    $rep=str_replace("<pfif:","<", $tmp);
    $rep=str_replace("</pfif:","</",$rep);
    $xml=simplexml_load_string($rep);
    foreach ($xml->entry as $v) {
      $uri =(string)$v->id;

      // あるかどうかチェック
      if ( $this->hasTweeted($uri) ) {
        $this->l("URL: $uri was already tweeted. Skipping this node.");
        continue;
      }

      $name=(string)$v->title;
      $time=(string)$v->updated;
      $post=(string)$v->author->name;
      $home_state=(string)$v->person->home_state;
      $home_city=(string)$v->person->home_city;
      $home_street=(string)$v->person->home_street;
      $description=(string)$v->person->other;
      $description=str_replace("description:","",$description);
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
      $url = bitly($uri, $bitly_user, $bitly_key );
      $counter = 1;
      while (substr($url,0,5) == "ERROR" && $counter < 5) {
        $this->l("getting from bit.ly: failed. $counter time.");
        //$url=bitly($uri, $bitly_user, $bitly_key );
        $url = $uri;
        $counter++;
      }
      $this->l("Final URL from bit.ly: ".$url);
      if (substr($url,0,5) == "ERROR") {
        $this->l("Could't get shorten URL from bit.ly, then use original one.");
        $url = $uri;
      }
      $place = new PersonFinderPlace($home_state, $home_city, $home_street);
      $time=date("m/d H:i",strtotime($time));
      $address = $place->__toString();
      $str = sprintf("「%s」さん（%s）を探しています。%s by %s [ %s ] %s #pf_anpi", $name, $address, $description, $post, $time, $url);
      $str_len = mb_strlen($str,"UTF-8");
      if($str_len > 140){
        $cut_len = $str_len - 140;
        $description_len = mb_strlen($description,"UTF-8");
        $cut_description_len = $description_len - $cut_len - 1;
        $description = mb_substr($description, 0, $cut_description_len, "UTF-8");
        $str = sprintf("「%s」さん（%s）を探しています。%s… by %s [ %s ] %s #pf_anpi", $name, $address, $description, $post, $time, $url);
      }
      $parsed_arr[] = array($place, $str);
    }
    return $parsed_arr;
  }

  protected function hasTweeted($uri) {
    $uri = trim($uri);
    $script_dir = dirname(__FILE__);
    $path = $script_dir."/../tmp/".$this->tweeted_filename;
    if (!is_file($path)) {
      //touch($path, 0666);
      touch($path);
    }
    $uris = file($path);
    if (in_array($uri."\n", $uris)) {
      return true;
    }
    $uris[] = $uri;
    if ( count($uris) > 100 ) {
      array_shift($uris);
    }

    //file_put_contents($path, implode("\n",$uris));

    $put = "";
    foreach ($uris as $uri) {
      if (trim($uri) == "")
        continue;
      $put .= trim($uri)."\n";
    }
    file_put_contents($path, $put);

    return false;
  }

  /* BitLyのアカウントのいずれかを取得 */
  protected function getBitLy() {
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
 
  /* Twitterアカウントのトークンを読み込む */
  protected function loadTokens() {
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

  /* ツイートする */
  protected function tweet($place, $str) {
    $twitter_region_name = $place->getTwitterKey();

    $tokens = $this->loadTokens();
    $token  = $tokens[$twitter_region_name];

    $this->l("token[akey]:".$token["akey"]);
    $this->l("token[asec]:".$token["asec"]);
    if (mb_strlen($str, "UTF-8") > 140 ) {
      $str = mb_substr($str,0,140, "UTF-8");
    }

    $to=new TwitterOAuth(TWITTER_CKEY, TWITTER_CSEC, trim($token["akey"]), trim($token["asec"]));
    $result = $to->OAuthRequest("http://twitter.com/statuses/update.xml","POST",array("status"=>$str));
    $this->l("tweet request. result in detail is ->". $result);

  }
}

