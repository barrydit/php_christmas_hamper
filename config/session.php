<?php
if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

require_once 'config.php';

// require_once 'database.php';

defined('SESSION_SAVE_PATH')
  or define('SESSION_SAVE_PATH', APP_BASE_PATH . DIRECTORY_SEPARATOR . 'session'); // basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . 

defined('SESSION_LIFETIME')
  or define('SESSION_LIFETIME', APP_TIMEOUT);  // 0


//echo '<pre>';
//print_r(get_defined_constants(true)['user']);
//echo '</pre>';
/*
session_set_cookie_params([
  'lifetime' => SESSION_LIFETIME, // expires in 15 minutes
  'path' => '/', // ;SameSite=none', // <-- this way! // any path on same domain 
  'secure' => APP_HTTPS, // $session_secure
  'httponly' => true, // $cookie_httponly
  'samesite' => 'Lax'
]);
*/
//if (!headers_sent()) {
// server should keep session data for 1 hour
ini_set('session.gc_maxlifetime', SESSION_LIFETIME); // 
//ini_set('session.gc_probability', 1);
//ini_set('session.gc_divisor', 1);

ini_set('session.cookie_lifetime', SESSION_LIFETIME);
//ini_set('session.cache_expire', APP_TIMEOUT);
//ini_set('session.name', 'sessions'); // APP_NAME . '.ca'

//ini_set('session.hash_function', 1);
//ini_set('session.hash_bits_per_character', 6);

//ini_set('session.save_path', APP_PATH . '.sessions');

// each client remember their session id for exactly 1 hour
// session_set_cookie_params(APP_TIMEOUT); << 

//ini_set("session.use_cookies", 0);
//ini_set('session.use_only_cookies', '0');
//ini_set("session.use_trans_sid", 1);
ini_set('session.save_path', SESSION_SAVE_PATH);
ini_set('session.gc_probability', 100);
ini_set('session.gc_divisor', 1);

if ( version_compare(phpversion(), '5.4.0', '>=') ) {
  if (session_status() === PHP_SESSION_NONE) // PHP_SESSION_ACTIVE
    session_start(['cookie_lifetime' => 86400, 'gc_maxlifetime' => 86400, 'cookie_secure' => true, 'cookie_httponly' => true]);
} else if (session_id() === '')
  session_start();
//}
// check active sessions
// sesion_id
//   sess_<mpi8vetfkctomau946o880rusc>
// ip_addr[esses]
// user_id
// sessions.json


/*
  get a list of sessions <file> to update sessions.json
*/
//session_start();
/*
var_dump($_COOKIE["PHPSESSID"]);

//$_SESSION['test'] = 'testing'; // 

session_write_close();

die(var_dump($_SESSION));
*/
$time = $_SERVER['REQUEST_TIME'];
$ob_contents = NULL;

if (!isset($_SESSION['last_access']))
  $_SESSION['last_access'] = (!isset($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME']);

if (isset($_SESSION['last_access']) && (time() - $_SESSION['last_access']) > SESSION_LIFETIME ) {
  error_log('Client was (auto) logged out. $_SESSION["last_access"] = ' . $_SESSION["last_access"] . '   $time = ' . time() . '    == ' . (time() - $_SESSION['last_access']));
  // some reason the /session 's get clogged up ... clean them up
  if (is_file(SESSION_SAVE_PATH . "/sess_" . session_id()))
    array_map('unlink', SESSION_SAVE_PATH . "/sess_" . session_id());
  else
    array_map('unlink', glob(SESSION_SAVE_PATH . "/sess_*")); 
  //exit(header('Location: ' . APP_BASE_URL . '?session=logout')); // 
} else {
  if (ini_get('session.cookie_lifetime') !== 0) // Does this work?
    setcookie(session_name(), session_id(), [
      'expires' => time() + SESSION_LIFETIME,
      'path' => '/', 
      'domain' => $_SERVER['HTTP_HOST'],
      'secure' => (!defined('APP_HTTPS') ? false : true),
      'httponly' => true,
      'samesite' => 'None'
    ]);

  ob_start();              // start output buffer 1
  echo '<div style="float: right; text-align: right;">Session Lifetime: ' . (time() - $_SESSION['last_access']) . ' / ' . ini_get('session.gc_maxlifetime') . '<br />' . 'Cookie Lifetime: ' . session_get_cookie_params()['lifetime'] . ' / ' . ini_get('session.cookie_lifetime') . '<br />' . 'Session ID: sess_' . session_id() . '<pre>';
  foreach ($_SESSION as $key => $value) {
    if ($key == 'last_access') {
      echo 'Date: ' . date('Y-m-d h:i:s', $value) . "\n"; 
      echo '["' . $key . '"] => '; var_export($value); echo "\n";
      continue;
    }
    echo '["' . $key . '"] => '; var_export($value); echo "\n";
  }
  echo '</pre></div>' . "\n";

  $ob_contents = ob_get_contents(); // read ob2 ("b")
  
  ob_end_flush();          // flush ob2 to ob1
  ob_end_clean();          // flush ob1 to browser
}

$_SESSION['last_access'] = (!isset($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME']);

if (!isset($_SESSION['created']))
    $_SESSION['created'] = (!isset($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME']);
else if (time() - $_SESSION['created'] > 1800) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['created'] = time();  // update creation time
}

    
// die(var_dump($_SESSION));

// session_gc();
if (!is_dir(SESSION_SAVE_PATH))
  mkdir(SESSION_SAVE_PATH);

if (!is_file(SESSION_SAVE_PATH . '/sessions.json')) touch(SESSION_SAVE_PATH . '/sessions.json');
$json = file_get_contents(SESSION_SAVE_PATH . '/sessions.json', true);
$json_decode = json_decode($json, true);

$files = glob(SESSION_SAVE_PATH . '/sess_*');

foreach($files as $key => $file) {
  //$files = (array) [ltrim(basename($file), 'sess_')];
  unset($files[$key]);
  $key = ltrim(basename($file), 'sess_');
  if ($key == session_id())
    $files[$key] = (array) [
      'user_id' => (isset($_SESSION['user_id']) ? ($_SESSION['user_id'] == 0 ? $_SESSION['user_id'] : $_SESSION['user_id']) : NULL),
      'visitor_id' => (isset($_SESSION['visitor_id']) ? $_SESSION['visitor_id'] : NULL),
      'ip_addr' => $_SERVER['REMOTE_ADDR'],
      'browser' => $_SERVER['HTTP_USER_AGENT']
    ];
  else
    if (isset($json_decode[$key])) $files[$key] = $json_decode[$key];
}

file_put_contents(SESSION_SAVE_PATH . '/sessions.json', json_encode($files), LOCK_EX);


switch ($_SERVER['REQUEST_METHOD']) {
  default:
    //session_write_close();
    if (key($_GET) == 'session') {
      switch ($_GET['session']) {
        case 'login':
          if (isset($_SESSION['user_id']) && $_SESSION['user_id'] >= 0)
            exit(header('Location: ' . APP_URL_BASE));
          else
            require APP_PATH . 'src/session_login.php';
        break;
        case 'logout':
          if (isset($_SESSION['user_id']) && $_SESSION['user_id'] >= 0)
            require 'src/session_logout.php';
          else
            exit(header('Location: ' . APP_URL_BASE));
        break;
        default:
          exit(header('Location: ' . APP_URL_BASE . '?session=login'));
        break;
      }
      exit(); 
    }
    if ( !isset($_SESSION['user_id']) || $_SESSION['user_id'] === NULL ) {
      //die(var_dump($_SESSION));

      exit(header('Location: ' . APP_URL_BASE . '?session=login'));
      //require '../src/session_login.php';
      //require 'install.php';  // <<< SESSION Bug exists follow the trail ...
      //header('Location: ' . APP_URL_BASE . '?session');
    }
    break;
}
