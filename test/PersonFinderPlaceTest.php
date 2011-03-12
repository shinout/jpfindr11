<?php
function l($str) { echo $str."\n"; }

$script_dir = dirname(__FILE__);
require("${script_dir}/../lib/PersonFinderPlace.php");

$pfp1 = new PersonFinderPlace("岩手", "山田", "大沢");
  l("岩手山田大沢の__toString");
  var_dump( $pfp1->__toString() == "岩手 山田 大沢");

  l("岩手山田大沢のTwitterKeyは 山田 である");
  var_dump( $pfp1->getTwitterKey() == "山田");




$pfp2 = new PersonFinderPlace("青森県", "", "");
  l("青森県の__toString");
  var_dump( $pfp2->__toString() == "青森県");

  l("青森県のTwitterKeyは 青森 である");
  var_dump( $pfp2->getTwitterKey() == "青森");





$pfp3 = new PersonFinderPlace("宮城県大好き", "気仙沼市", "");
  l("宮城県大好きの__toString");
  var_dump( $pfp3->__toString() == "宮城県大好き 気仙沼市");

  l("宮城県大好きのTwitterKeyは 宮城 である");
  var_dump( $pfp3->getTwitterKey() == "宮城");





$pfp4 = new PersonFinderPlace("HuKuSiMa", "Fukushima", "");
  l("HuKuSiMaの__toString");
  var_dump( $pfp4->__toString() == "HuKuSiMa Fukushima");

  l("HuKuSiMaのTwitterKeyは 福島 である");
  var_dump( $pfp4->getTwitterKey() == "福島");


$pfp5 = new PersonFinderPlace("", "", "");
  l("無名の__toString");
  var_dump( $pfp5->__toString() == "住所未記入");

  l("無名のTwitterKeyは その他 である");
  var_dump( $pfp5->getTwitterKey());
  var_dump( $pfp5->getTwitterKey() == "その他");
