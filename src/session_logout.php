<?php
if (session_status() == PHP_SESSION_NONE):
  require_once __DIR__ . DIRECTORY_SEPARATOR . 'config/session.php'; // session_start();
endif;
// preg_match('/^[a-zA-Z0-9,-]{22,40}$/', session_id())

if (ini_get("session.use_cookies")) { 
    $params = session_get_cookie_params(); 
    setcookie(session_name(), '', time() - 42000,  // setcookie(session_name(),'',0,'/');
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

// Destroying All Sessions
session_unset(); // $_SESSION = array();
session_destroy();
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Refresh" content="5; url='<?=APP_BASE_URI?>'" /> 
    <title>Dr. David Raymant - <?=APP_NAME?> -- Logout</title>

    <base href="<?=APP_BASE_URL?>" />
  
    <link rel="shortcut icon" href="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/images/favicon.ico" />

    <link rel="stylesheet" type="text/css" href="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/css/styles.css" />

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
<?php if (isset($_SESSION['user_id'])) { ?>
        <h3 style="margin: 10px 5px;">You are now Logged out.</h3>
<?php } else { ?>
        <h3 style="margin: 10px 5px;">There is no active Session.</h3>
<?php } ?>
        <div id="form-logout">
          <p style="text-align: right; font-size:14px;">
            <a href="<?=APP_BASE_URL ?>">Click here to Log In.</a><img src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/images/giphy.gif" alt="Loading..." width="25" height="25"/>
          </p>
        </div>
        <div style="border-top: 1px dotted black; margin: 5px; line-height: 0px;">
          <p style="text-align: right; font-size:10px; font-weight: bold;">PHP Version: <a href="https://www.php.net/releases/<?=strtr(PHP_VERSION, array('.' => '_'))?>.php"><?=PHP_VERSION?></a> | MySQL (<a href="https://pecl.php.net/package/PDO_MYSQL">pdo</a>): <a href="https://mariadb.com/kb/en/mariadb-<?=preg_replace("/[^0-9]/", "", strtr($pdo->query('select version()')->fetchColumn(), array('.' => '')));?>-release-notes/"><?=$pdo->query('select version()')->fetchColumn();?></a></p>
        </div>
      </div><!--end log-form -->
    </div>
    <!-- JQUERY SCRIPTS -->
    <script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery/jquery.min.js"></script>
    <script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery.disableAutoFill/jquery.disableAutoFill.min.js"></script>
  </body>
</html>
