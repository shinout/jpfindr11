<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/PersonFinderTwitterOAuth.php");
require("${script_dir}/libfnc-bitly.php");
require("${script_dir}/PersonFinderPlace.php");
require("${script_dir}/PersonFinderDTO.php");

class PersonFinderBot {
  private $token_filename = "/../data/tokens.tsv";
  private $bitly_filename = "/../data/bitlys.tsv";
  private $tweeted_filename = "/../tmp/tweeted";
  private $tokens;
  private $test = false;

  /* ログを出力 */
  public function l($str, $e=false) { 
    echo $str."\n";
    if ($e) {
      trigger_error($str);
    }
  }

  /**
   * $pfb->setTest(); // testモード(実際はつぶやかない）
   * $pfb->setTest(false); // 本番モード
   */ 
  public function setTest($test=true) {
    $this->test = $test;
  }

  /**
   * PersonFinderのXMLを取得してツイートします 
   */
  public function tweetXMLData($test=false) {
    $this->test = $test;
    $parsed_arr = $this->parseXML(true);
    $this->l("XML was parsed.");
    $this->l("got ".count($parsed_arr)." data from the XML.");
    foreach ($parsed_arr as $i=>$parsed) {
      $n = $i+1;
      $this->l("------data $n  -------");
      list($place, $str, $geo) = $parsed;
      $this->l("str: ".$str);
      $this->l("place: ".$place->__toString());
			$this->l("geo: ".$geo["lat"]." , ".$geo["long"]);
      $this->tweet($this->getTokenByPlace($place), $str, $geo);
    }

    // wait処理
    $cnt = max( 4 - count($parsed_arr),0);
    if ($cnt) {
      $this->l("waiting $cnt sec.", true);
      sleep($cnt);
    }
  }

  /**
   * param1: $state : "岩手", "青森", などのあれをいれましょう。
   * param2: $str   : つぶやきたい文字をいれましょう。140字以内です。もちろん。
   * param3: $test  : 実際にはつぶやかないでテストだけするときのオプション。
   */
  public function tweetString($state, $str, $test=false) {
    $this->test = $test;
    $this->tweet($this->getTokenByName($state), $str);
  }

  /**
   * XMLをパースして、位置情報(PersonFinderPlaceオブジェクト)とツイート文字列を返す 
   */
  protected function parseXML($tweet_english = true) {
    $parsed_arr = array();
    $tmp=file_get_contents("https://japan.person-finder.appspot.com/feeds/person?key=".PERSONFINDER_KEY);
    $rep=str_replace("<pfif:","<", $tmp);
    $rep=str_replace("</pfif:","</",$rep);
    $xml=simplexml_load_string($rep);

    foreach ($xml->entry as $v) {

      // すでにあるかどうかチェック
      if ( $this->hasTweeted($v->id) ) {
        $this->l("URL: ".$v->id." was already tweeted. Skipping this node.");
        continue;
      }

      // パースされたデータオブジェクトを生成
      $pfdata = PersonFinderDTO::createFromXML($v);

      // URLを短くする
      $pfdata->uri = $this->getShortenURL($pfdata->uri);

      // 場所データオブジェクトを生成
      $place = $this->getPlace($pfdata);
			
      // ジオコード生成用の緯度経度
      $geo = $this->getLatLong($place, $pfdata);

      // つぶやき文字列を生成
      $str = $this->getText($pfdata, $place);

      // 英語であれば英語用ツイート
      if ($tweet_english && $pfdata->isEnglish()) {
        $this->tweet($this->getTokenByName("英語"), $this->getEnglishText($pfdata, $place), $geo);
      }
      
      $parsed_arr[] = array($place, $str, $geo);
    }
    return $parsed_arr;
  }

  /**
   * 取得したデータオブジェクトから場所情報オブジェクトを作成
   */
  protected function getPlace($pfdata) {
    return new PersonFinderPlace($pfdata->home_state, $pfdata->home_city, $pfdata->home_street);
  }
	
	/**
	 * 取得したデータオブジェクトの場所情報からジオコード取得
	 */
	protected function getLatLong($place, $pfdata) {
		
		// まずは住所から緯度経度を求める
		$state  = ereg_replace("/\s/","",$pfdata->home_state);
		$city   = ereg_replace("/\s/","",$pfdata->home_city);
		$street = ereg_replace("/\s/","",$pfdata->home_street);
		$neighb = ereg_replace("/\s/","",$pfdata->home_neighborhood);

		$pdata  = $state." ".$city." ".$street." ".$neighb;

		$params = array (
			'address' => $pdata,
			'sensor'  => 'false',
			'client'  => 'free-personfinderbot',		
		);
		
		$privateKey = 'RNvz7Spq2M4z3UTEaHDSqKfLR-c=';
		$privateKey = str_replace("-", "+", $privateKey);
		$privateKey = str_replace("_", "/", $privateKey);
		$decodedPrivateKey = base64_decode($privateKey, true);

		$url = '/maps/api/geocode/json?'.http_build_query($params);
		
		$hmacSignature = hash_hmac('sha1', $url, $decodedPrivateKey, true);

		$signature = base64_encode($hmacSignature);
		$signature = str_replace("+", "-", $signature);
		$signature = str_replace("/", "_", $signature);

		$url = "http://maps.google.com".$url."&signature=".$signature;
		$results = json_decode(file_get_contents($url));

		$lat = $results->results[0]->geometry->location->lat;
		$long = $results->results[0]->geometry->location->lng;
		
		print "lat: ".$lat."\n";
		print "long: ".$long."\n";
		
		// 求めた緯度経度をtwitterAPIに渡してgeoIDに
		$geo = array (
			'lat'  => $lat,
			'long' => $long,
		);
		var_dump($geo);
		
		return $geo;
	}

  /**
   * bit.lyを使ってURLを短くする.
   */
  private function getShortenURL($uri) {
    $uri=explode("/",$uri);
		$small=urlencode("&small=yes");
    $uri="http://japan.person-finder.appspot.com/view?id=japan.person-finder.appspot.com/".$uri[1].$small;

    // read account data from file
    $script_dir = dirname(__FILE__);
    $lines = file($script_dir.$this->bitly_filename);
    foreach ($lines as $line) {
      if ( substr($line,0,1) == "#")
        continue;

      $linearr = preg_split("/\t/", $line);
      if ( count($linearr) > 1) {
        $bitlys[] = $linearr;
      }
    }
    list($bitly_user, $bitly_key) = $bitlys[array_rand( $bitlys )];
    $url = bitly($uri, $bitly_user, $bitly_key);
    $this->l("URL from bit.ly: ".$url);
    if (substr($url,0,5) == "ERROR") {
      $this->l("Could't get shorten URL from bit.ly, then use original one.", true);
      $url = $uri;
    }
    return $url;
  }
    
  /**
   * 取得したデータと場所情報からツイートする文章を作成
   */
  protected function getText($pfdata, $place) {
    $str = "";
    switch ($pfdata->status) {
      default:
        $template = sprintf("「%s」さん（%s）を探しています。<<DESC>> by %s [ %s ] %s #pf_anpi",
                              $pfdata->name, $place->__toString(), $pfdata->post, $pfdata->time, $pfdata->uri);
        break;
      case "is_note_author":
        $template = sprintf("「%s」さん（%s）本人より生存報告です。<<DESC>> by %s [ %s ] %s #pf_anpi", 
                              $pfdata->name, $place->__toString(), $pfdata->post, $pfdata->time, $pfdata->uri);
        break;
      case "believed_alive":
        $template = sprintf("「%s」さん（%s）の生存が確認されたようです。<<DESC>> by %s [ %s ] %s #pf_anpi",
                              $pfdata->name, $place->__toString(), $pfdata->post, $pfdata->time, $pfdata->uri);
        break;
      case "believed_dead":
        $template = sprintf("「%s」さん（%s）はお亡くなりになった可能性があります。<<DESC>> by %s [ %s ] %s #pf_anpi", 
                              $pfdata->name, $place->__toString(), $pfdata->post, $pfdata->time, $pfdata->uri);
        break;
    }

    return $this->cutDescription($str, $pfdata->description, $template);

  }
  
  /**
   * 取得したデータと場所情報からツイートする文章を英語で作成
   */
  protected function getEnglishText($pfdata, $place) {
    $address = ($place->__toString() == PersonFinderPlace::NO_ADDRESS) 
      ? "none"
      : $place->__toString();

    $str = "";

    switch ($pfdata->status) {
      default:
        $template = sprintf("%s（%s）Help find this missing person. <<DESC>> by %s [ %s ] %s #pf_anpi",
                              $pfdata->name, $address, $pfdata->post, $pfdata->time, $pfdata->uri);
        break;
      case "is_note_author":
        $template = sprintf("%s is surviving confirmed by him/herself. (%s) <<DESC>> [ %s ] %s #pf_anpi",
                              $pfdata->name, $address, $pfdata->time, $pfdata->uri);
        break;
      case "believed_alive":
        $template = sprintf("%s is surviving confirmed by %s. (%s) <<DESC>> [ %s ] %s #pf_anpi",
                              $pfdata->name, $pfdata->post, $address, $pfdata->time, $pfdata->uri);
        break;
      case "believed_dead":
        $template = sprintf("%s : Possibility of being perished. (%s) <<DESC>> by %s [ %s ] %s #pf_anpi",
                              $pfdata->name, $address, $pfdata->post, $pfdata->time, $pfdata->uri);
        break;
    }
    return $this->cutDescription($str, $pfdata->description, $template);
  }

  /**
   * 詳細が長過ぎる場合に140文字までカットして返す
   */
  private function cutDescription($str, $description, $template) {
    $str = str_replace("<<DESC>>", $description, $template);
    $cut_len = mb_strlen($str,"UTF-8") - 140;
    if ($cut_len > 0) {
      $cut_desc_len = mb_strlen($description,"UTF-8") - $cut_len - 1;
      $description = mb_substr($description, 0, $cut_desc_len, "UTF-8");
      $str = str_replace("<<DESC>>", $description."…", $template);
    }
    return $str;
  }

  /**
   * すでにツイートされているかどうか
   */
  protected function hasTweeted($uri) {
    $uri = trim($uri);
    $script_dir = dirname(__FILE__);
    $path = $script_dir. $this->tweeted_filename;
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


  /**
   * PlaceオブジェクトからTwitterアカウントのトークンを取得
   */
  protected function getTokenByPlace($place) {
    return $this->getTokenByName($place->getTwitterKey());
  }

  /**
   * "岩手", "宮城" といった名称からTwitterアカウントのトークンを取得
   */
  protected function getTokenByName($twitter_region_name) {
    $this->l("twitter region name: ".$twitter_region_name);
    $this->loadTokens();
    return $this->tokens[$twitter_region_name];
  }


  /**
   * Twitterアカウントのトークンを読み込む 
   */
  protected function loadTokens() {
    if ( !isset($this->tokens) ) {
      $tokens = array();
      $script_dir = dirname(__FILE__);
      $lines = file($script_dir. $this->token_filename);
      foreach ($lines as $line) {
        if ( substr($line,0,1) == "#")
          continue;

        $linearr = preg_split("/\t/", $line);
        $pref = $linearr[0];
        $akey = $linearr[1];
        $asec = $linearr[2];
        $tokens[$pref] = array("akey"=>$akey, "asec"=>$asec);
      }
      $this->tokens = $tokens;
    }
    return $this->tokens;
  }

  /* 
   * ツイートする 
   */
  protected function tweet($token, $str, $geo) {
    $this->l("token[akey]:".$token["akey"]);
    $this->l("token[asec]:".$token["asec"]);
    if (mb_strlen($str, "UTF-8") > 140 ) {
      $str = mb_substr($str,0,140, "UTF-8");
    }

    if ($this->test) {
      $this->l("tweet test finished. str: ". $str);
      return;
    }
				
    $to=new PersonFinderTwitterOAuth(TWITTER_CKEY, TWITTER_CSEC, trim($token["akey"]), trim($token["asec"]));
		$result = $to->OAuthRequest("http://api.twitter.com/1/statuses/update.json","POST",array("status"=>$str,"lat"=>$geo["lat"],"long"=>$geo["long"]));
    $this->l("tweet request. result in detail is as follows.");
    $json=json_decode($result, true);
    if (isset($json["error"])) {
      trigger_error($json["error"]);
    }
    $json["user"] = "";
    print_r($json);
  }
}

