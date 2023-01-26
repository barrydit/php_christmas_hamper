<?php
if (session_status() == PHP_SESSION_NONE):
  require_once dirname(__DIR__, 1) . '/config/session.php'; // session_start();
endif;

switch ($_SERVER['REQUEST_METHOD']) {
  default:
    //die(var_dump($_POST));
    //var_dump($_SESSION);
    if (!isset($_SESSION['token']))
      $_SESSION['token'] = md5(uniqid(mt_rand(), true)); // bin2hex(random_bytes(35));
  
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $stmt = $pdo->prepare("SELECT `id` FROM `users` LIMIT 1;");
      $stmt->execute(array());
      $row_login = $stmt->fetch();
      break;
    }
//die(var_dump($_POST));
    if (isset($_POST['register'])) {
/*
      $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `password`) VALUES (?, ?, ?);");
      $stmt->execute(array(
        (($_REQUEST['name'] != false) ? $_REQUEST['name'] : NULL),
        (!empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL),
        (!empty($_REQUEST['password']) ? password_hash($_REQUEST['password'], PASSWORD_DEFAULT) : NULL)
      ));
      header('Location: ' . APP_BASE_URL);
      exit();
      
*/
      $register_error = 'You can not register at this time.';
    } elseif (isset($_POST['login'])) {

  //$username = stripslashes($_POST['username']);
  //$username = mysqli_real_escape_string($myiconnect, $username);

  //$query=mysqli_query($myiconnect, $sql);	
	
  //if (mysqli_num_rows($query) == 1) {
	
  //  $row = mysqli_fetch_assoc($query);

  //$sql = '';

      $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

      if (!$token || $token !== $_SESSION['token']) $login_error = 'This site currently does not support non-ssl login. <a href="" title="' . $_SESSION['token'] . '">' . $_SESSION["token"] . '</a> != ' . $token;

      //die($token . ' !== ' . $_SESSION['token']);
      //if (!$token || $token !== $_SESSION['token'])
      //  exit(header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed')); // return 405 http status code
  
    // https://stackoverflow.com/questions/1717495/check-if-a-database-table-exists-using-php-pdo

    //die('Working hard at debugging the program ... please stand by... ');

      try {
        $result = $pdo->query('SELECT 1 FROM ' . DB_TABLES[0] . ' LIMIT 1;');
      } catch (Exception $e) {      

      // We got an exception == table not found
        if ($_POST['username'] == APP_UNAME && $_POST['password'] == APP_PWORD) {
          $_SESSION['user_id'] = (int) 0;

          $_SESSION['enable_ssl'] = (APP_HTTPS ? TRUE : FALSE);
          (!$_SESSION['enable_ssl']) ?: exit(header('Location: ' . 'https://'.APP_DOMAIN.APP_BASE_URI)); // APP_BASE_URL
        }

/*
    $pdo->exec('CREATE TABLE `' . DB_TABLES[2] . '` (
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


      $stmt = $pdo->prepare("SELECT `id`, `username`, `password` FROM `users` WHERE `username` = :username;");
      $stmt->execute(array(
        ":username" => $_POST['username']
      ));

      $row_login = $stmt->fetch();

      if (!empty($row_login))
        if (password_verify($_POST['password'], $row_login['password'])) {
        
          $_SESSION['user_id'] = (int) $row_login['id'];
          $_SESSION['enable_ssl'] = (APP_HTTPS ? TRUE : FALSE);
          (!$_SESSION['enable_ssl']) ? exit(header('Location: ' . 'http://'.APP_DOMAIN.APP_BASE_URI)) : exit(header('Location: ' . 'https://'.APP_DOMAIN.APP_BASE_URI)); // APP_BASE_URL
        } else
          $login_error = 'Username / Password did not match.';

     //die(var_dump($_POST));

    }
    break;
}
//die('test');
/*
$hash = '$2y$12$Cz/AlKIOBS7aAJ8Qoy2AFOua4A9VLzHLyX0vaweWc7SP3JA/MwU2C'; // password

$options = array('cost' => 12);
echo 'Password: ' . password_hash("", PASSWORD_BCRYPT, $options) . '<br />'; 

die();
*/

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?=APP_NAME?> -- Login</title>

    <base href="<?=APP_BASE_URL?>" />
    
    <link rel="shortcut icon" href="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/images/favicon.ico" />

    <link rel="stylesheet" type="text/css" href="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/css/styles.css" />

    <style type="text/css">
table, td {
  border: none; 
}

td {
  padding: 1px;	
}
    </style>
  </head>

  <body>
    <div class="log-form">
      <div style="border-bottom: 1px dotted black; margin: 5px; line-height: 0px;">
        <p style="text-align: right; font-size:10px; font-weight: bold;"><a href="release-notes-<?=APP_VERSION?>.html"><?=APP_NAME?> (v. <?=APP_VERSION?>)</a></p>
      </div>
 <?php if (!empty($row_login)) { ?>
      <h3 style="margin: 10px 5px;">Log-in to your account</h3>
      <form id="form-login" action="<?=htmlentities($_SERVER['REQUEST_URI'])?>" autocomplete="off" method="post" accept-charset="utf-8">
        <input type="hidden" name="token" value="<?=$_SESSION['token'] ?? '' ?>" />
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
              <input type="checkbox" id="enable_ssl" name="enable_ssl" <?=(APP_HTTPS ? 'checked': '')?> />
              <small><label for="enable_ssl"> Enable SSL?</label></small>
            </td>
            <td style="text-align: left;">
              <button style="float: right; padding: 0px 10px;" type="submit" id="submitBtn" name="login">  Login  </button>
            </td>
          </tr>
        </table>
<?php if (isset($login_error)) { ?>
          <small style="padding-left: 5px;color: #f00;">Failed to login: <?=$login_error?></small>
<?php } ?>
      </form>
<?php } elseif (!empty($row_login)) { ?>
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
              <input id="enable_ssl" type="checkbox" name="enable_ssl" checked />
              <small><label for="enable_ssl"> Enable SSL?*?</label></small>
            </td>
            <td style="text-align: right">
              <button type="submit" id="submitBtn" name="register">Register</button>
            </td>
          </tr>
        </table>
<?php if (isset($register_error)) { ?>
          <small style="padding-left: 5px;color: #f00;">Failed to register: <?=$register_error?></small>
          </tr>
<?php } ?>
      </form>
<?php } ?>
      <div style="border-top: 1px dotted black; margin: 5px; line-height: 0px;">
        <p style="text-align: right; font-size:10px; font-weight: bold;">PHP Version: <a href="https://www.php.net/releases/<?=strtr(PHP_VERSION, array('.' => '_'))?>.php"><?=PHP_VERSION?></a> | MySQL (<a href="https://pecl.php.net/package/PDO_MYSQL">pdo</a>): <a href="https://mariadb.com/kb/en/mariadb-<?=preg_replace("/[^0-9]/", "", strtr($pdo->query('select version()')->fetchColumn(), array('.' => '')));?>-release-notes/"><?=$pdo->query('select version()')->fetchColumn();?></a></p>
      </div>
    </div><!--end log-form -->
  
    <!-- JQUERY SCRIPTS -->
    <script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery/jquery.min.js"></script>
    <script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery.disableAutoFill/jquery.disableAutoFill.min.js"></script>
  
    <script type="text/javascript">
$(document).ready(function(){
    $("#submitBtn").click(function(){      
        $("#login-form").submit(); // Submit the form
    });
});

//$('#login-form').disableAutoFill();
    </script>
  </body>
</html>
