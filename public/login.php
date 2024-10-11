<?php

if (defined('APP_DEBUG') && APP_DEBUG == TRUE)
  echo '<pre>' . var_export(get_required_files()) . "</pre>";

if (session_status() == PHP_SESSION_NONE):
  require_once dirname(__DIR__, 1) . '/config/session.php'; // session_start();
endif;

header('Cache-Control: no-cache, no-store, must-revalidate'); 
header('Pragma: no-cache'); 
header('Expires: 0');

// https://stackoverflow.com/questions/1717495/check-if-a-database-table-exists-using-php-pdo
try {
    $result = $pdo->query('SELECT 1 FROM ' . DB_TABLES[0] . ' LIMIT 1;');
} catch (Exception $e) {
    // We got an exception == table not found
/*
    $pdo->exec('CREATE TABLE `' . DB_TABLES[6] . '` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `username` varchar(25) NOT NULL,
    `password` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;')
    or die(print_r($pdo->errorInfo(), true));
    return FALSE;
*/
}

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':

    /*if (isset($_REQUEST['submit-register'])) {
      $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `password`) VALUES (?, ?, ?);");
      $stmt->execute([
        (($_REQUEST['name'] != false) ? $_REQUEST['name'] : NULL),
        (!empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL),
        (!empty($_REQUEST['password']) ? password_hash($_REQUEST['password'], PASSWORD_DEFAULT) : NULL)
      ]);
      exit(header('Location: ' . APP_URL_BASE));
    } else */
    if (isset($_REQUEST['submit-login'])) {
    //die(var_dump($_POST));

  //$username = stripslashes($_POST['username']);
  //$username = mysqli_real_escape_string($myiconnect, $username);

  //$query=mysqli_query($myiconnect, $sql);	
	
  //if (mysqli_num_rows($query) == 1) {
	
  //  $row = mysqli_fetch_assoc($query);

  //$sql = '';

      $stmt = $pdo->prepare("SELECT `id`, `username`, `password` FROM `users` WHERE `username` = :username;");
      $stmt->execute([
        ":username" => $_POST['username']
      ]);
      $row_login = $stmt->fetch();

      if (!empty($row_login))
        if (password_verify($_POST['password'], $row_login['password'])) {
          $_SESSION['user_id'] = $_SESSIONS[session_id()]['user_id'] = (int)$row_login['id'];
          (isset($_REQUEST['enable_ssl']) || @$_REQUEST['enable_ssl'] == 'on') ? $_SESSION['enable_ssl'] = TRUE : $_SESSION['enable_ssl'] = FALSE;
          unset($_SESSIONS['shutdown']);
          $session_save();
          exit(header('Location: ' . APP_URL_BASE));	
        } else
          $login_error = 'Username / Password did not match.';
    }
    break;
  
  case 'GET':
    $stmt = $pdo->prepare("SELECT `id` FROM `users` LIMIT 1;");
    $stmt->execute([]);
    $row_login = $stmt->fetch();
    
    $_SESSION['token'] = md5(uniqid(mt_rand(), true)); // bin2hex(random_bytes(35));

    break;
}

/*
$hash = '$2y$12$Cz/AlKIOBS7aAJ8Qoy2AFOua4A9VLzHLyX0vaweWc7SP3JA/MwU2C'; // password

$options = ['cost' => 12];
echo 'Password: ' . password_hash("", PASSWORD_BCRYPT, $options) . '<br />'; 

die();
*/
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?=APP_NAME?> -- Login</title>

    <base href="<?= !is_array(APP_URL) ? APP_URL : APP_URL_BASE?>" />
    
    <link rel="shortcut icon" href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH?>favicon.ico" />

    <link rel="stylesheet" type="text/css" href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH?>assets/css/styles.css" />

    <style type="text/css">
table, td {
  border: none; 
}

td {
  padding: 1px;	
}

.debug{
  display: none;
}

.showHideMe {
  cursor: pointer;
  border: 1px dashed #000;
  border-radius: 5px;
  padding: 4px;
  background-color: #fff;
  font-size: 13px;
}
    </style>
  </head>

  <body>
<?= /*$ob_contents;  var_dump($_SESSION) | */ NULL; ?>
  <div class="debug" style="position: absolute; left: 27%; right: 42%; margin: auto; width: 600px; height: 400px;border: 1px solid #000; z-index: 10;">
  
    <iframe id="debug-frame" src="?debug" style="width: 600px; height: 400px;"></iframe>
    <div style="text-align: right;"><a class="showHideMe"><em>Close Debug</em> &#9650;</a></div>
  </div>
  
    <div class="log-form">
      <div style="border-bottom: 1px dotted black; margin: 5px; line-height: 0px;">
        <a class="showHideMe"><em>Open Debug</em> &#9650;</a>
        <p style="text-align: right; font-size: 12px; font-weight: bold; margin-top: 5px;"><a href="release-notes.php"><?=APP_NAME?></a> (<a href="release-notes.php?v=<?=APP_VERSION?>">v. <?=APP_VERSION?></a>)</p>
      </div>
<?php if (!empty($row_login)) { ?>
      <h3 style="margin: 10px 5px;">Log-in to your account</h3>
      <form id="form-login" action<?= /* '="' . htmlentities($_SERVER['REQUEST_URI']) . '"' */ NULL; ?> autocomplete="off" method="post" accept-charset="utf-8">
        <table>
          <tr>
            <td><label for="username">Username:</label></td>
            <td><input type="text" name="username" title="username" placeholder="username" autocomplete="username" value="<?=/*APP_UNAME*/NULL?>" autofocus required /></td>
          </tr>
          <tr>
            <td><label for="password">Password:</label></td>
            <td><input type="password" name="password" title="password" placeholder="password" autocomplete="new-password" value="<?=/*APP_PWORD*/NULL?>" required /></td>
          </tr>
          <tr>
            <td><small><a class="forgot" href="#" title="Feature does not work.">Forgot Password?</a></small></td>
            <td style="text-align: center">
              <input type="checkbox" id="enable_ssl" name="enable_ssl" <?=
(defined('APP_ENV') && APP_ENV == 'production' ?
  (defined('APP_HTTPS') AND 'checked') : (defined('APP_HTTPS') AND 'checked')
) ?>>
              <small><label for="enable_ssl"> Enable SSL?</label></small>
            </td>
            <td style="text-align: left;">
              <button style="float: right;" type="submit" id="submitBtn" name="submit-login">Login</button>
            </td>
          </tr>
        </table>
<?php if (isset($login_error)) { ?>
          <small style="padding-left: 5px;color: #f00;">Failed to login: <?=$login_error?></small>
          </tr>
<?php } ?>
      </form>
<?php } else { ?>
      <h3 style="margin: 10px 5px;">Register for your account</h3>
      <form id="form-register" action="<?=htmlentities($_SERVER['REQUEST_URI'])?>" autocomplete="off" method="post" accept-charset="utf-8">
        <table>
          <tr>
            <td><label for="name">Name:</label></td>
            <td><input type="text" id="name" name="name" title="name" placeholder="name" autocomplete="" autofocus required /></td>
          </tr>
          <tr>
            <td><label for="username">Username:</label></td>
            <td><input type="text" id="username" name="username" title="username" placeholder="username" autocomplete="username" required /></td>
          </tr>
          <tr>
            <td><label for="password">Password:</label></td>
            <td><input type="password" id="password" name="password" title="username" placeholder="password" autocomplete="new-password" required /></td>
          </tr>
          <tr>
            <td><small><a class="forgot" href="#" title="Feature does not work.">Forgot Password?</a></small></td>
            <td style="text-align: center">
              <input type="checkbox" id="enable_ssl" name="enable_ssl" <?=(defined('APP_ENV') && APP_ENV == 'production' ?
  (defined('APP_HTTPS') AND 'checked') : (defined('APP_HTTPS') AND 'checked')
)?>>
              <small><label for="enable_ssl"> Enable SSL?</label></small>
            </td>
            <td style="text-align: right">
              <button type="submit" id="submitBtn" name="submit-register">Register</button>
            </td>
          </tr>
        </table>
      </form>
<?php } ?>
      <div style="position: relative; white-space: nowrap; border-top: 1px dotted black; margin: 5px; line-height: 0px; width: 385px;">
        <p style="text-align: left; font-size:10px; font-weight: bold;">PHP Version: <a href="https://www.php.net/releases/<?=strtr(PHP_VERSION, ['.' => '_'])?>.php"><?=PHP_VERSION?></a> | MySQL (<a href="https://pecl.php.net/package/PDO_MYSQL">pdo</a>): <a href="https://mariadb.com/kb/en/mariadb-<?=preg_replace("/[^0-9]/", "", strtr($pdo->query('select version()')->fetchColumn(), ['.' => '']));?>-release-notes/"><?=$pdo->query('select version()')->fetchColumn();?></a></p>
      </div>
    </div><!--end log-form -->
  
    <!-- JQUERY SCRIPTS -->
    <script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery/jquery.min.js"></script>
    <script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery.disableAutoFill/jquery.disableAutoFill.min.js"></script>
  
    <script type="text/javascript">
$(document).ready(function(){
  $('.showHideMe').click(function() {
    if ($( ".debug" ).css('display') == 'none') {
      $('.showHideMe').html("Close Debug &#9650;");
      document.getElementById('debug-frame').contentWindow.location.reload();
      $( '.debug' ).slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('.showHideMe').html("Open Debug &#9660;");
      $( ".debug" ).slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });
  $("#submitBtn").click(function(){      
    $("#login-form").submit(); // Submit the form
  });
});

//$('#login-form').disableAutoFill();
    </script>
  </body>
</html>
