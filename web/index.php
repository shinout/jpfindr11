<?php
//セッションを有効にする
session_start();
$script_dir = dirname(__FILE__);
require("${script_dir}/../keys.php"); // make your own key file.
require("${script_dir}/../lib/twitteroauth/twitteroauth/twitteroauth.php");

//////////Twitter OAUTH/////////////////////////
////////////////////////////////////////////////
// twitterOAuth を読み込む
//require_once('twitter/twitteroauth.php');

/* Twitterアプリケーション申請で取得したコンシューマ key */
$consumer_key = TWITTER_CKEY;

/* Twitterアプリケーション申請で取得したコンシューマ secret */
$consumer_secret = TWITTER_CSEC;

/* 状態 */
$state = $_SESSION['oauth_state'];

/* oauth_token がセットされているかをチェック */
$session_token = $_SESSION['oauth_request_token'];

/* oauth_token がセットされているかをチェック */
$oauth_token = $_REQUEST['oauth_token'];


/* Set section var */
$section = $_REQUEST['section'];

/* PHP セッションをクリア */
if ($_REQUEST['test'] === 'clear') {
  session_destroy();
  session_start();
}

if ($_REQUEST['oauth_token'] != NULL && $_SESSION['oauth_state'] === 'start') {
  $_SESSION['oauth_state'] = $state = 'returned';
}

/*
 * どのプロセスにいるかによって処理を変える
 *
 * 'default': 新しいユーザにたいしてRequest Tokenをとりに行く
 * 'returned': Twitterから認証されたユーザ
 */

switch ($state) {
  default:

    $to = new TwitterOAuth($consumer_key, $consumer_secret);

    $tok = $to->getRequestToken();

    /* Tokenをセッションに格納 */
    $_SESSION['oauth_request_token'] = $token = $tok['oauth_token'];
    $_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];
    $_SESSION['oauth_state'] = "start";

    /* authorization URL を生成*/
    $request_link = $to->getAuthorizeURL($token);

    $content = 'Click on the link to go to twitter to authorize your account.';
    $content .= '<br /><a href="'.$request_link.'">'.$request_link.'</a>';


    header("Location: $request_link");
    break;

  case 'returned':

    ///* もし access tokens がすでにセットされている場合は、 API call にいく
    if ($_SESSION['oauth_access_token'] === NULL && $_SESSION['oauth_access_token_secret'] === NULL) {

      $to = new TwitterOAuth($consumer_key, $consumer_secret, $_SESSION['oauth_request_token'], $_SESSION['oauth_request_token_secret']);

      $tok = $to->getAccessToken();

      ///* Tokenをセッションに格納 
      $_SESSION['oauth_access_token'] = $tok['oauth_token'];
      $_SESSION['oauth_access_token_secret'] = $tok['oauth_token_secret'];
    }

	// Twitter名をセッションに格納
	$_SESSION['username'] = $tok["screen_name"];

	//Topページへ戻る
	header("Location: /");

}

?>
