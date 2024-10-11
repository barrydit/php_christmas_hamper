<?php

require_once('../config/config.php');

//require_once('../config/constants.php');

if (session_status() == PHP_SESSION_NONE):
  session_start();
endif;

ob_start();
if (isset($_SESSION['user_id'])) { ?>
        <h3 style="margin: 10px 5px;">You are now Logged out.</h3>
<?php } else { ?>
        <h3 style="margin: 10px 5px;">There is no active Session.</h3>
<?php }
$session_msg = ob_get_contents();

ob_end_flush();          // flush ob2 to ob1
ob_end_clean();          // flush ob1 to browser

unset($_SESSIONS[session_id()]);

$_SESSION['user_id'] = '';

// preg_match('/^[a-zA-Z0-9,-]{22,40}$/', session_id())

// Destroying All Sessions
session_unset(); // $_SESSION = [];

//if (ini_get("session.use_cookies"))
  //$params = session_get_cookie_params(); 

session_destroy();
session_write_close();

unset($_COOKIE['PHPSESSID']);
setcookie(session_name(), '', -1, '/'); // time() - 3600

// die(var_dump($_SESSIONS)); // $_SESSION

/*
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) { // last request was more than 30 minates ago
session_destroy(); // destroy session data in storage
session_unset(); // unset $_SESSION variable for the runtime
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
*/

//exit(header('Refresh: 5; URL=' . APP_URL_PATH));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Refresh" content="3; url='<?=APP_URL_BASE?>'" /> 
    <title>Dr. David Raymant - <?=APP_NAME?> -- Logout</title>

    <base href="<?= !is_array(APP_URL) ? APP_URL : APP_URL_BASE ?>" />
  
    <link rel="shortcut icon" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>favicon.ico" />

    <link rel="stylesheet" type="text/css" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/css/styles.css" />

    <script type="text/javascript">
<!--
//$('#form-login').disableAutoFill();
//-->
    </script>
    <style></style>
  </head>

  <body>
    <div id="page-inner">
      <div class="log-form">
        <div style="border-bottom: 1px dotted black; margin-top: 0px; line-height: 0px;">
          <p style="padding-right: 5px; text-align: right; font-size:10px; font-weight: bold;"><a href="release-notes-<?=APP_VERSION?>.html"><?=APP_NAME?> (v. <?=APP_VERSION?>)</a></p>
        </div>
        <?= $session_msg ?>
        <div id="form-logout">
          <p style="text-align: right; font-size:14px;">
            <a href="?">Click here to Log In.</a> <img src="assets/images/giphy.gif" alt="Loading..." width="25" height="25"/>
          </p>
        </div>
        <div style="position: relative; white-space: nowrap; border-top: 1px dotted black; margin: 5px; line-height: 0px; width: 385px;">
          <p style="text-align: left; font-size:10px; font-weight: bold;">PHP Version: <a href="https://www.php.net/releases/<?=strtr(PHP_VERSION, ['.' => '_'])?>.php"><?=PHP_VERSION?></a> | MySQL (<a href="https://pecl.php.net/package/PDO_MYSQL">pdo</a>): <a href="https://mariadb.com/kb/en/mariadb-<?=preg_replace("/[^0-9]/", "", strtr($pdo->query('select version()')->fetchColumn(), ['.' => '']));?>-release-notes/"><?=$pdo->query('select version()')->fetchColumn();?></a></p>
        </div>
      </div><!--end log-form -->
    </div>
    <!-- JQUERY SCRIPTS -->
    <script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery/jquery.min.js"></script>
    <script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery.disableAutoFill/jquery.disableAutoFill.min.js"></script>
  </body>
</html>