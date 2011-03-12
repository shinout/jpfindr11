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

  /**
   *
   *Twitter地域名と市町村名正規表現のペア
   *
   */
  private $cities  = array(
    "山田" => "山田|yamada",
    //"気仙沼" => "気仙沼|kesennuma",
    //"仙台" => "仙台|sendai",
  );


  private $streets = array(); // 未登録です

  private $state   = "";
  private $city    = "";
  private $street  = "";

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
    foreach ($this->cities as $name => $city) {
      if ( preg_match("/".$city."/", strtolower($this->city)) ) {
        return $name;
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

