<?php
if (isset($_GET["oauth_token"]) && isset($_GET["oauth_verifier"])) {
  header("Location: /register.php?oauth_token=".$_GET["oauth_token"]."&oauth_verifier=".$_GET["oauth_verifier"]);
}
require("../keys.php"); 
$status = "input";
$message = "";
$result  = "";
$test    = false;
$prefs  = array("岩手", "宮城", "青森", "茨城", "福島", "長野", "その他");
if (isset($_POST["tw"])) {
  if (! isset($_POST["ps1"]) ||
      ! isset($_POST["ps2"]) ||
      ! isset($_POST["pref"]) ||
      WEB_KEY1 != $_POST["ps1"] ||
      WEB_KEY2 != $_POST["ps2"] ) {

    $status = "failure";
    $message = "認証エラーです。";

  } else {
    if ( trim($_POST["tw"]) == "" ||
         mb_strlen($_POST["tw"], "UTF-8") > 140 ) {

      $status = "failure";
      $message = "内容は空でなく、また140文字以内で入力してください。";

    // Tweetするぞ
    } else {

      require("../lib/PersonFinderBot.php");
      $pfb = new PersonFinderBot();
      $selected_pref = stripslashes($_POST["pref"]);
      $content       = stripslashes($_POST["tw"]);
      $test          = ( isset($_POST["test"]) );
      ob_start();
      if ($selected_pref == "all") {
        foreach ($prefs as $pref) {
          $pfb->tweetString($pref, $content, $test);
        }
      } else {
        $pfb->tweetString($selected_pref, $content, $test);
      }
      $result = ob_get_contents();
      ob_end_clean();
      $status = "success";
    }
  }
}




?><!DOCTYPE HTML>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>PersonFinderBot Tweeter</title>
</head>
<body>
<h1>PersonFinderBot Tweeter</h1>
<? if($status == "success") : ?>
<p style="color: blue;">ツイート<?=($test)?"テスト":"" ?>完了</p>
<? elseif ($status == "failure") : ?>
<p style="color:red;">ツイート失敗: <?=$message ?></p>
<? else :?>
<p>ツイートしましょう</p>
<? endif ?>
<form action="/" method="post">
<p>
<select id="pref" name="pref">
<option value="all">すべて</option>
<? foreach ($prefs as $pref) : ?>
<option value="<?=$pref?>" <? if($_POST["pref"] == $pref) echo 'selected="selected"'?>><?=$pref?></option>
<? endforeach ?>
</select>
<input type="checkbox" name="test" id="test" <? if($_POST["test"]) echo 'checked="checked"'?> />
<label for="test">テスト(実際にはツイートしない)</label>
</p>
<textarea id="tw" name="tw" rows="6" cols="60"><?=htmlspecialchars($_POST["tw"])?></textarea>
<p>
<input type="password" name="ps1" value="<?=htmlspecialchars($_POST["ps1"])?>" />
<input type="password" name="ps2" value="<?=htmlspecialchars($_POST["ps2"])?>" />
<input type="submit" value="送信" />
</p>
</form>
<pre>
<?=htmlspecialchars($result) ?>
</pre>
</body>
</html>
<?php
