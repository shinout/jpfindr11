<?php
// PHPはgetter/setter自動生成できるんか？
class PersonFinderDTO {

  private $arr=array();

  public static function createFromXML($v) {
    $uri =(string)$v->id;
		
    $name=(string)$v->title;
		$name=self::ifUrlDecode($name);
    
		$time=(string)$v->updated;
    $time=date("m/d H:i",strtotime($time));
		
    $post=(string)$v->author->name;
    $post=self::ifUrlDecode($post);
		
		$home_state=(string)$v->person->home_state;
		$home_state=self::ifUrlDecode($home_state);
		
    $home_city=(string)$v->person->home_city;
		$home_city=self::ifUrlDecode($home_city);
		
    $home_street=(string)$v->person->home_street;
    $home_street=self::ifUrlDecode($home_street);
		
		$description=(string)$v->person->other;
		$description=self::ifUrlDecode($description);
		$description=str_replace("description:","",$description);
		
    if(preg_match("/ /",$name)){
      $name=explode(" ",$name);
      $name=$name[1]." ".$name[0];
    }
    if(preg_match("/ /",$post)){
      $post=explode(" ",$post);
      $post=$post[1]." ".$post[0];
    }
    $status = (string)$v->person->note->status;
    return new PersonFinderDTO($uri, $name, $time, $post, $home_state, $home_city, $home_street, $description, $status);
  }
	
	// $srcが%から始まっていたらurldecodeする
	public static function ifUrlDecode($src) {
		if(strcmp(mb_substr($src,0,1,"UTF-8"),"%")==0) {
			$src=mb_convert_encoding(urldecode($src), "UTF-8", "SHIFT_JIS");
		}
		return $src;
	}

  public function __construct($uri, $name, $time, $post, $home_state, $home_city, $home_street, $description, $status) {
    $this->arr["uri"] = $uri;
    $this->arr["name"] = $name;
    $this->arr["time"] = $time;
    $this->arr["post"] = $post;
    $this->arr["home_state"] = $home_state;
    $this->arr["home_city"] = $home_city;
    $this->arr["home_street"] = $home_street;
    $this->arr["description"] = $description;
    $this->arr["status"] = $status;
  }

  public function getValues() {
    return $this->arr;
  }

  public function isEnglish() {
    $arr = array(
      $this->name,
      $this->home_state,
      $this->home_city,
      $this->home_street,
      $this->description,
    );
    foreach ($arr as $v) {
      if (!preg_match("/^[a-zA-Z0-9,\.\s\-_@]*$/s",$v)) {
        return false;
      }
    }
    return true;
  }

  public function __get($name) {
    if (isset($this->arr[$name])) {
      return $this->arr[$name];
    } else {
      trigger_error("$name doesn't exist in PersonFinderDTO");
      return null;
    }
  }

  public function __set($name, $value) {
    if (isset($this->arr[$name])) {
      $this->arr[$name] = $value;
    } else {
      trigger_error("$name doesn't exist in PersonFinderDTO");
    }
  }

}
