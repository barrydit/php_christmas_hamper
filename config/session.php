<?php
if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

require_once 'config.php';
require_once 'database.php';

/* https://stackoverflow.com/questions/3791667/php-sessions-not-extending-cookie-expiration-on-each-request?rq=1 */

defined('SESSION_SAVE_PATH')
  or define('SESSION_SAVE_PATH', APP_PATH . rtrim(APP_BASE['session'], DIRECTORY_SEPARATOR)); // session_save_path() | ini_get ('session.save_path')

defined('SESSION_LIFETIME')
  or define('SESSION_LIFETIME', APP_TIMEOUT);

ini_set('session.save_path', SESSION_SAVE_PATH);

$_SESSIONS = array();

/*
if (ini_get("session.use_cookies"))
  if (key($_GET) == 'logout' && isset($_COOKIE['PHPSESSID'])) {
    exit(require APP_PATH . APP_BASE['public'] . 'logout.php');
  }
*/

!isset($_COOKIE['PHPSESSID']) || $_COOKIE['PHPSESSID'] == '' // $_COOKIE['PHPSESSID'] = session_create_id();
  and setcookie('PHPSESSID', session_create_id(), 0 /* time()+3600 */) . exit(header('Location: ' . APP_URL_BASE));

$session_save = function () {
  global $_SESSIONS;
  file_put_contents(APP_PATH . APP_BASE['session'] . 'sessions.json', $json = json_encode($_SESSIONS), LOCK_EX);
};

function sess_serialize($array, $safe = true) {
  if( $safe = true ) $array = unserialize(serialize( $array ));
  $raw = '' ;
  $line = 0 ;
  $keys = array_keys( $array ) ;
  foreach( $keys as $key ) {
    $value = $array[ $key ] ;
    $line ++ ;
    $raw .= $key .'|' ;
    if( is_array( $value ) && isset( $value['huge_recursion_blocker_we_hope'] ))
      $raw .= 'R:'. $value['huge_recursion_blocker_we_hope'] . ';';
    else
     $raw .= serialize( $value ) ;
    $array[$key] = Array( 'huge_recursion_blocker_we_hope' => $line ) ;
  }
  return $raw;
}

function sess_unserialize($str_data) {
  $session = array();
  while ($i = strpos($str_data, '|'))
  {
    $k = substr($str_data, 0, $i);
    $v = unserialize(substr($str_data, 1 + $i));
    $str_data = substr($str_data, 1 + $i + strlen(serialize($v)));
    $session[$k] = $v;
  }
  return $session;
}

/*
$json_temp = <<<JSON
{
    "cdyt6hrd6ed5y65y4e554r4ree": {
        "status": "active",
	    "created": "114836599",
		"last_access": "114836599",
		"user_id": 1,
		"visitor_id": null,
		"ip_addr": "192.168.0.254",
		"user_agent": "Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko\\/20100101 Firefox\\/110.0"
	}
}
JSON;
*/
// < less-than
// > greater-than

$session = [(is_file(APP_PATH . APP_BASE['session'] . 'sess_' . $_COOKIE["PHPSESSID"]) ? $_COOKIE["PHPSESSID"] : $_COOKIE["PHPSESSID"] ) => ['created' => time(), 'last_access' => time(), 'user_id' => '', 'visitor_id' => '', 'patient_id' => '', 'status' => 'inactive', 'ip_addr' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]];

$_SESSIONS = json_decode((!is_file(APP_PATH . APP_BASE['session'] . 'sessions.json') ? 
  (!@touch(APP_PATH . APP_BASE['session'] . 'sessions.json') ? 
    (!file_get_contents(APP_PATH . APP_BASE['session'] . 'sessions.json', true) ? json_encode($session) : file_get_contents(APP_PATH . APP_BASE['session'] . 'session.json', true)) :
    (!@file_put_contents(APP_PATH . APP_BASE['session'] . 'sessions.json', $json = json_encode($session), LOCK_EX) ?: $json)
  ) :
  (!file_get_contents(APP_PATH . APP_BASE['session'] . 'sessions.json', true) ? json_encode($session) : file_get_contents(APP_PATH . APP_BASE['session'] . 'sessions.json', true))
), true);

define('SESSION', [
  'created' => (isset($_SESSIONS[$_COOKIE["PHPSESSID"]]['created']) ? $_SESSIONS[$_COOKIE["PHPSESSID"]]['created'] : time()),
  'last_access' => (isset($_SESSIONS[$_COOKIE["PHPSESSID"]]['last_access']) ? $_SESSIONS[$_COOKIE["PHPSESSID"]]['last_access'] : time()),
  'user_id' => (isset($_SESSIONS[$_COOKIE["PHPSESSID"]]['user_id']) ? $_SESSIONS[$_COOKIE["PHPSESSID"]]['user_id'] : NULL)
] //sess_unserialize(file_get_contents(APP_PATH . APP_BASE['session'] . 'sess_' . $_COOKIE['PHPSESSID'], true))
);

if (empty($_SESSIONS))
  $_SESSIONS[$_COOKIE["PHPSESSID"]] = $session[$_COOKIE["PHPSESSID"]];
else if (array_key_exists($_COOKIE["PHPSESSID"], $_SESSIONS)) {
  if (isset($_SESSIONS[$_COOKIE["PHPSESSID"]]['status']) && (time() - $_SESSIONS[$_COOKIE["PHPSESSID"]]['last_access']) < SESSION_LIFETIME) {
    $_SESSIONS[$_COOKIE["PHPSESSID"]] = [
      'created' => time(),
      'last_access' => time(),
      'user_id' => $_SESSIONS[$_COOKIE["PHPSESSID"]]['user_id'], /* stage 1 */
      'visitor_id' => '',
      'patient_id' => '',
      'status' => 'active',
      'ip_addr' => $_SERVER['REMOTE_ADDR'],
      'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];
  } else
    $_SESSIONS[$_COOKIE["PHPSESSID"]]['status'] = 'inactive';
} else
  $_SESSIONS[$_COOKIE["PHPSESSID"]] = $session[$_COOKIE["PHPSESSID"]];

foreach (array_keys($_SESSIONS) as $session_id) {
  if ( empty($session_id) ) continue;
  if (!is_file(APP_BASE['session'] . 'sess_' . $session_id)) {
    if (@touch(APP_PATH . APP_BASE['session'] . 'sess_' . $session_id))
      file_put_contents(APP_PATH . APP_BASE['session'] . 'sess_' . $session_id, sess_serialize(['created' => (isset($session['created']) ? $session['created'] : time()), 'last_access' => (isset($_SESSIONS[$session_id]['last_access']) ? $_SESSIONS[$session_id]['last_access'] : time()), 'user_id' => (!empty($_SESSIONS[$session_id]['user_id']) ? $_SESSIONS[$session_id]['user_id'] : 2) /* stage 2 */]), LOCK_EX); // 
  }
}

foreach (glob(APP_BASE['session'] . 'sess_*') as $filename) {
  $session = sess_unserialize(file_get_contents(APP_PATH . APP_BASE['session'] . basename($filename), true)); //array();
  $session_id = substr(basename($filename), 5);
  if (!empty($session)) {
    if (!array_key_exists($session_id, $_SESSIONS)) {
      if ((time() - (!isset($session['last_access'])?: $session['last_access'])) > SESSION_LIFETIME) {
        //die((time() - (!isset($session['last_access'])?: $session['last_access'])) . ' is > than ' . SESSION_LIFETIME);
        (!isset($_SESSIONS[$session_id])?: $_SESSIONS[$session_id]['status'] = 'inactive');
        unset($_SESSIONS[$session_id]);
        //array_map('unlink', [APP_PATH . APP_BASE['session'] . "sess_" . $session_id]);
        continue;
      }
      $_SESSIONS[$session_id] = ['created' => (isset($session['created']) ? $session['created'] : time()), 'last_access' => (isset($session['last_access']) ? $session['last_access'] : time()), 'user_id' => 4, 'visitor_id' => '', 'status' => 'active', 'ip_addr' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']];
    }
  } else {
    array_map('unlink', [APP_PATH . APP_BASE['session'] . "sess_" . $session_id]);
    continue;
  }

  if (!in_array($session_id, array_keys($_SESSIONS)))
    array_map('unlink', [APP_PATH . APP_BASE['session'] . "sess_" . $session_id]);
  else {
    $array = ['created' => (isset($session['created']) ? $session['created'] : time()), 'last_access' => (isset($session['last_access']) ? $session['last_access'] : time()), 'user_id' => 5 ]; // user_id error on logout | $_SESSIONS[$session_id]['user_id'] == '' $_SESSIONS[$session_id]['user_id']
    //file_put_contents(APP_PATH . APP_BASE['session'] . basename($filename), sess_serialize($array), LOCK_EX);
  }
}

if ( version_compare(phpversion(), '5.4.0', '>=') ) {
  session_set_cookie_params(0, session_get_cookie_params()['path'], APP_DOMAIN, session_get_cookie_params()['secure'], session_get_cookie_params()['httponly']);
  if (session_status() == PHP_SESSION_NONE) // PHP_SESSION_ACTIVE
    session_start(['cookie_lifetime' => 0 /*delete on window close*/, 'gc_maxlifetime' => APP_TIMEOUT, 'cookie_secure' => defined('APP_HTTPS'), 'cookie_httponly' => true]); // <<
  elseif (session_id() == '')
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  if (empty($_SESSION['user_id'])) {
    if (!empty($_SESSIONS[session_id()]['user_id'])) {
      $_SESSION['user_id'] = $_SESSIONS[session_id()]['user_id'];
    }
  } elseif (is_numeric($_SESSION['user_id'])) {
    //if ($_SESSIONS[session_id()]['user_id'] == '' || is_null($_SESSIONS[session_id()]['user_id']))
    $_SESSION['user_id'] = $_SESSIONS[session_id()]['user_id'];
    //$_SESSION['user_id'] = $_SESSIONS[session_id()]['user_id']
  }
}

$_SESSIONS[$_COOKIE['PHPSESSID']] = array_replace($_SESSIONS[$_COOKIE['PHPSESSID']], SESSION);

if ($_SERVER['REQUEST_METHOD'] == 'GET')
  $session_save();

if ((!defined('APP_DEBUG') ? define('APP_DEBUG', FALSE) . (APP_DEBUG == FALSE ? TRUE : FALSE) : TRUE))
  switch ($_SERVER['REQUEST_METHOD']) {
    default:
      if (key($_GET) == 'session')
        if (in_array($_GET['session'], $_SESSIONS)) {
          $_SESSIONS[session_id()]['patient_id'] = $_SESSIONS[$_GET['session']]['patient_id'];
          $_SESSIONS[session_id()]['user_id'] = $_SESSIONS[$_GET['session']]['user_id'];
        } else
          exit(header('Location: ' . APP_URL_BASE . '?login'));
      elseif (key($_GET) == 'login')
        // $_SESSION['enable_ssl'] = (isset($_POST['enable_ssl']) ? true : (defined('APP_HTTPS') ? false : true));
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) // $_SESSION['user_id'] >= 0     
          exit(header('Location: ' . APP_URL_BASE));
        else
          exit(require APP_PATH . APP_BASE['public'] . 'login.php');
      elseif (key($_GET) == 'logout')
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) // $_SESSION['user_id'] >= 0
          exit(require APP_PATH . APP_BASE['public'] . 'logout.php');
        else
          exit(header('Location: ' . APP_URL_BASE));
      else {
        !isset($_SESSION['created'])
          and $_SESSION['created'] = (!isset($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME']);
          
        if ((!isset($_SESSION['last_access'])?: time() - $_SESSION['last_access']) > SESSION_LIFETIME)
          if ($_SERVER['REQUEST_METHOD'] == "POST") break;

        if (!isset($_SESSION['user_id']) || is_null($_SESSION['user_id']) || is_string($_SESSION['user_id']))
          exit(header('Location: ' . APP_URL_BASE . '?login'));
      }
      break;
  }

ob_start();              // start output buffer 1
echo '<div style="float: right; text-align: right; font-size: 12px; margin: 10px;">Session Lifetime: ' . (time() - ($_SESSION['last_access'])) . ' / ' . ini_get('session.gc_maxlifetime') . '<br />' . 'Cookie Lifetime: ' . session_get_cookie_params()['lifetime'] . ' / ' . ini_get('session.cookie_lifetime') . '<br />' . 'Session ID: ' . (!empty(session_id()) ? session_id() : $_COOKIE["PHPSESSID"]);

$_SESSION['last_access'] = (!isset($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME']);

echo '<pre>' . 'Date: ' . date('Y-m-d h:i:s', $_SESSION['last_access']) . "\n";
foreach ($_SESSION as $key => $value) {
  echo '["' . $key . '"] => '; var_export($value); echo "\n";
}
echo '</pre></div>' . "\n";
  
$ob_contents = NULL;

$ob_contents = ob_get_contents(); // read ob2 ("b")
  
ob_end_flush();          // flush ob2 to ob1
ob_end_clean();          // flush ob1 to browser