<?php
function l($str) { echo $str."\n"; }

$script_dir = dirname(__FILE__);
require("${script_dir}/../lib/PersonFinderPlace.php");

$pfp1 = new PersonFinderPlace("岩手", "山田", "大沢");
  l("岩手山田大沢の__toString");
  var_dump( $pfp1->__toString() == "岩手 山田 大沢");

  l("岩手山田大沢のTwitterKeyは 岩手 である");
  var_dump( $pfp1->getTwitterKey() == "岩手");


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


$pfp6 = new PersonFinderPlace("", "仙台市", "");
  l("仙台 => 宮城");
  var_dump( $pfp6->getTwitterKey() == "宮城");

$pfp = new PersonFinderPlace("", "つくばみらい市", "");
  l("つくばみらい");
  var_dump( $pfp->getTwitterKey() == "茨城");

$pfp = new PersonFinderPlace("仙台市", "", "");
  l("仙台市 in state");
  var_dump( $pfp->getTwitterKey() == "宮城");

$pfp = new PersonFinderPlace("陸前高田市", "", "");
  l("陸前高田市 in state");
  var_dump( $pfp->getTwitterKey() == "岩手");

$pfp = new PersonFinderPlace("", "", "陸前高田市");
  l("陸前高田市 in street");
  var_dump( $pfp->getTwitterKey() == "岩手");

$pfp = new PersonFinderPlace("", "", "Kamaishi");
  l("Kamaishi in street");
  var_dump( $pfp->getTwitterKey() == "岩手");

$pfp = new PersonFinderPlace("", "仙台市", "南大野田");
  l("仙台市南大野田");
  var_dump( $pfp->getTwitterKey() == "宮城");

$pfp = new PersonFinderPlace("", "仙台市", "階上");
  l("仙台市階上");
  var_dump( $pfp->getTwitterKey() == "宮城");

$pfp = new PersonFinderPlace("青森", "階上", "");
  l("青森県階上");
  var_dump( $pfp->getTwitterKey() == "青森");
