<?php
class PersonFinderPlace {
  const OTHER = "その他";
  const NO_ADDRESS = "住所未記入";
  /**
   *
   *Twitter地域名と都道府県名正規表現のペア
   *
   */
  private $states   = array(
    "青森" => "青森|aomori",
    "岩手" => "岩手|iwate",
    "宮城" => "宮城|miyagi",
    "福島" => "福島|([fh]ukush?ima)",
    "茨城" => "茨城|(ibara[kg]i)",
    "長野" => "長野|nagano",
  );

  private $streets = array(); // 未登録です

  private $state   = "";
  private $city    = "";
  private $street  = "";

  private $cities_filename = "/../data/cities";
  private $cities = array();

  public function __construct($home_state, $home_city, $home_street) {
    $this->state   = trim($home_state);
    $this->city    = trim($home_city);
    $this->street  = trim($home_street);
  }

  /* 住所の自然な表現を返す */
  public function __toString() {
      return (strcmp($this->state,'')==0 && strcmp($this->city,'')==0 && strcmp($this->street,'')==0)
                  ? self::NO_ADDRESS
                  : trim ( $this->state." ".$this->city." ".$this->street);

  }

  /**
   *
   *市町村名からTwitter地域名のペアをかえす
   *
   */
  private function getStateByCity($city) {
    $this->loadCitiesFile();
    if (isset($this->cities[$city])) {
      return $this->cities[$city];
    }
    else {
      return false;
    }
  }

  /**
   *
   *市町村名とその正規表現のペア
   *
   */
  private function getCityExps() {
    $ret = array();
    $this->loadCitiesFile();
    foreach (array_keys($this->cities) as $city) {
      //$ret[$city] = preg_quote($city);
      $ret[$city] = $city;
    }
    return $ret;
  }

  /**
   * 市町村データファイルの読み込み
   */
  private function loadCitiesFile() {
    if (empty($this->cities)) {
      $script_dir = dirname(__FILE__);
      $path = $script_dir.$this->cities_filename;
      $lines = file($path);

      $arr   = array();
      $state = "";
      foreach ($lines as $line) {
        if ( trim($line) == "") 
          continue;
        // 県名
        if (preg_match("/\[(.*)\]/", $line, $matched)) {
          $state = $matched[1];
        } 
        // 市町村名
        else {
          $arr[trim($line)] = $state;
        }
      }
      $this->cities = $arr;
    }
  }

  /*
   * twitterアカウントの地域名を取得します 
   * 例： 岩手
   * Street, 市町村, 都道府県 の順に探索する
   */
  public function getTwitterKey() {
    if ($this->__toString() == self::NO_ADDRESS)
      return self::OTHER;

    $ret = $this->getTwitterKeyByStreet();
    if ($ret) return $ret;

    $ret = $this->getTwitterKeyByCity();
    if ($ret) return $ret;

    $ret = $this->getTwitterKeyByState();
    if ($ret) return $ret;

    return self::OTHER;
  }

  /* State（都道府県）情報からTwitter地域名を取得 */
  private function getTwitterKeyByState() {
    foreach ($this->states as $name => $state) {
      if ( preg_match("/".$state."/", strtolower($this->state)) ) {
        return $name;
      }
    }
    return false;
  }

  /* City情報からTwitter地域名を取得 */
  private function getTwitterKeyByCity() {
    foreach ($this->getCityExps() as $name => $city) {
      if ( preg_match("/ ".$city."/", strtolower(" ".$this->__toString()))) {
                    //  ^  ここにスペースを入れているのは、南大野田が大野にマッチしてしまわないようにするためだ
        return $this->getStateByCity($name);
      }
    }
    return false;
  }

  /* Street情報からTwitter地域名を取得 */
  private function getTwitterKeyByStreet() {
    foreach ($this->streets as $name => $street) {
      if ( preg_match("/".$street."/", strtolower($this->street)) ) {
        return $name;
      }
    }
    return false;
  }
}

