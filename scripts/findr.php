<?php
$script_dir = dirname(__FILE__);
require("${script_dir}/../lib/PersonFinderBot.php");

/* 実行スクリプト */
$pf = new PersonFinderBot();
$pf->execute();
