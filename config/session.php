<?php
if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1 : 0)) {
  exit('Direct access is not allowed.');
}

require_once '../config/config.php';
require_once '../config/database.php';

if (stripos(PHP_OS, 'LIN') === 0 && is_dir(APP_PATH . APP_BASE['session'])) {
  $sessionFolder = APP_PATH . APP_BASE['session']; // Path to your session folder

  // Get the folder's owner UID
  $folderUid = fileowner($sessionFolder);

  if (!$folderUid) {
    //die('Unable to get the owner of the session folder.');
  }

  // Get the web server's user UID
  $webServerUser = getenv('USER') ?: getenv('APACHE_RUN_USER');
  if ($webServerUser) {
    $webServerInfo = posix_getpwnam($webServerUser);
    $webServerUid = $webServerInfo['uid'];
  } else {
    //die('Could not get the web server user.');
  }

  // Compare the folder UID with the web server's UID
  if ($folderUid === $webServerUid) {
    //echo "The owner of the var/session folder is the same as the web server user.";
    !defined('SESSION_SAVE_PATH')
      and define('SESSION_SAVE_PATH', rtrim(APP_PATH . APP_BASE['session'], DIRECTORY_SEPARATOR)); // session_save_path() | ini_get ('session.save_path')

    ini_set('session.save_path', SESSION_SAVE_PATH); // SESSION_SAVE_PATH

    $_SESSIONS = [];

    $session_save = function () {
      global $_SESSIONS;
      file_put_contents(APP_PATH . APP_BASE['session'] . 'sessions.json', json_encode($_SESSIONS), LOCK_EX);
    };

/**
 * Summary of sess_serialize
 * @param mixed $array
 * @param mixed $safe
 * @return string
 */
function sess_serialize($array, $safe = true) {
  if( $safe = true ) $array = unserialize(serialize( $array ));
  $raw = '' ;
  $line = 0 ;
  $keys = array_keys( $array ) ;
  foreach( $keys as $key ) {
    $value = $array[ $key ] ;
    $line ++ ;
    $raw .= "$key|" ;
    if( is_array( $value ) && isset( $value['huge_recursion_blocker_we_hope'] ))
      $raw .= 'R:'. $value['huge_recursion_blocker_we_hope'] . ';';
    else
     $raw .= serialize( $value ) ;
    $array[$key] = ['huge_recursion_blocker_we_hope' => $line] ;
  }
  return $raw;
}

/**
 * Summary of sess_unserialize
 * @param mixed $str_data
 * @return array
 */
function sess_unserialize($str_data) {
  $session = [];
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

$session = [
  $_COOKIE["PHPSESSID"] => [
    'created' => time(),
    'last_access' => time(),
    'user_id' => '',
    'visitor_id' => '',
    'patient_id' => '',
    'status' => 'inactive',
    'ip_addr' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT']
  ]
];

if (is_file(APP_PATH . APP_BASE['session'] . 'sessions.json')) {
  $sessionData = file_get_contents(APP_PATH . APP_BASE['session'] . 'sessions.json');
  $_SESSIONS = json_decode($sessionData, true);
} else {
  $sessionData = json_encode($session);
  if (@touch(APP_PATH . APP_BASE['session'] . 'sessions.json')) {
    @file_put_contents(APP_PATH . APP_BASE['session'] . 'sessions.json', $sessionData, LOCK_EX);
  }
  $_SESSIONS = json_decode($sessionData, true);
}

define('SESSION', [
  'created' => ($_SESSIONS[$_COOKIE["PHPSESSID"]]['created'] ?? time()),
  'last_access' => ($_SESSIONS[$_COOKIE["PHPSESSID"]]['last_access'] ?? time()),
  'user_id' => ($_SESSIONS[$_COOKIE["PHPSESSID"]]['user_id'] ?? NULL)
] //sess_unserialize(file_get_contents(APP_PATH . APP_BASE['session'] . 'sess_' . $_COOKIE['PHPSESSID'], true))
);

if (empty($_SESSIONS)) {
  $_SESSIONS[$_COOKIE["PHPSESSID"]] = $session[$_COOKIE["PHPSESSID"]];
} elseif (isset($_SESSIONS[$_COOKIE["PHPSESSID"]])) {
  if (isset($_SESSIONS[$_COOKIE["PHPSESSID"]]['status']) && (time() - $_SESSIONS[$_COOKIE["PHPSESSID"]]['last_access']) < SESSION_LIFETIME) {
    $_SESSIONS[$_COOKIE["PHPSESSID"]] = array_merge($_SESSIONS[$_COOKIE["PHPSESSID"]], [
      'last_access' => time(),
      'status' => 'active',
      'ip_addr' => $_SERVER['REMOTE_ADDR'],
      'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
  } else {
    $_SESSIONS[$_COOKIE["PHPSESSID"]]['status'] = 'inactive';
  }
} else {
  $_SESSIONS[$_COOKIE["PHPSESSID"]] = $session[$_COOKIE["PHPSESSID"]];
}

foreach (array_keys($_SESSIONS) as $session_id) {
  if (empty($session_id)) continue;
  $session_file = APP_PATH . APP_BASE['session'] . 'sess_' . $session_id;
  if (!is_file($session_file)) {
    if (@touch($session_file)) {
      $session_data = [
        'created' => $_SESSIONS[$session_id]['created'] ?? time(),
        'last_access' => $_SESSIONS[$session_id]['last_access'] ?? time(),
        'user_id' => $_SESSIONS[$session_id]['user_id'] ?? 2
      ];
      file_put_contents($session_file, sess_serialize($session_data), LOCK_EX);
    }
  }
}

foreach (glob(APP_BASE['session'] . 'sess_*') as $filename) {
  $session = sess_unserialize(file_get_contents(APP_PATH . APP_BASE['session'] . basename($filename), true));
  $session_id = substr(basename($filename), 5);
  
  if (!empty($session)) {
    if (!array_key_exists($session_id, $_SESSIONS)) {
      if ((time() - ($session['last_access'] ?? time())) > SESSION_LIFETIME) {
        $_SESSIONS[$session_id]['status'] = 'inactive';
        unset($_SESSIONS[$session_id]);
        continue;
      }
      $_SESSIONS[$session_id] = [
        'created' => $session['created'] ?? time(),
        'last_access' => $session['last_access'] ?? time(),
        'user_id' => 4,
        'visitor_id' => '',
        'status' => 'active',
        'ip_addr' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
      ];
    }
  } else {
    unlink(APP_PATH . APP_BASE['session'] . "sess_" . $session_id);
    continue;
  }

  if (!array_key_exists($session_id, $_SESSIONS)) {
    unlink(APP_PATH . APP_BASE['session'] . "sess_" . $session_id);
  } else {
    $array = [
      'created' => $session['created'] ?? time(),
      'last_access' => $session['last_access'] ?? time(),
      'user_id' => 5
    ];
    // file_put_contents(APP_PATH . APP_BASE['session'] . basename($filename), sess_serialize($array), LOCK_EX);
  }
}


$_SESSIONS[$_COOKIE['PHPSESSID']] = array_replace($_SESSIONS[$_COOKIE['PHPSESSID']], SESSION);



  } else {
    //echo "The owner of the var/session folder is different from the web server user.";
  }
} 

!defined('SESSION_LIFETIME')
  and define('SESSION_LIFETIME', APP_TIMEOUT);


if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
  $cookieParams = session_get_cookie_params();
  session_set_cookie_params([
    'lifetime' => 0, // delete on window close
    'path' => $cookieParams['path'],
    'domain' => APP_DOMAIN,
    'secure' => $cookieParams['secure'],
    'httponly' => $cookieParams['httponly']
  ]);
  
  if (session_status() == PHP_SESSION_NONE) {
    session_start([
      'cookie_lifetime' => 0, // delete on window close
      'gc_maxlifetime' => APP_TIMEOUT,
      'cookie_secure' => defined('APP_HTTPS'),
      'cookie_httponly' => true
    ]);
  }
} else {
    //session_set_cookie_params(0, APP_BASE['public'], APP_DOMAIN, defined('APP_HTTPS'), true);
    //session_start();
}

/* https://stackoverflow.com/questions/3791667/php-sessions-not-extending-cookie-expiration-on-each-request?rq=1 */


//dd(SESSION_SAVE_PATH);

/*
if (ini_get("session.use_cookies"))
  if (key($_GET) == 'logout' && isset($_COOKIE['PHPSESSID'])) {
    exit(require APP_PATH . APP_BASE['public'] . 'logout.php');
  }
*/

if (!isset($_COOKIE['PHPSESSID']) || $_COOKIE['PHPSESSID'] == '') { // $_COOKIE['PHPSESSID'] = session_create_id();
  setcookie('PHPSESSID', session_create_id(), 0 /* time()+3600 */);
  exit(header('Location: ' . APP_URL_BASE));
}

!defined('APP_DEBUG')
  and define('APP_DEBUG', false);

//if (APP_DEBUG)


switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':

    if (!isset($_SESSION['created'])) {
      $_SESSION['created'] = $_SERVER['REQUEST_TIME'] ?? time();
    }

    if (empty($_SESSION['user_id'])) {
      if (!empty($_SESSIONS[session_id()]['user_id'])) {
        $_SESSION['user_id'] = $_SESSIONS[session_id()]['user_id'];
      }
    } elseif (is_numeric($_SESSION['user_id'])) {
      //if ($_SESSIONS[session_id()]['user_id'] == '' || is_null($_SESSIONS[session_id()]['user_id']))
      $_SESSION['user_id'] = $_SESSIONS[session_id()]['user_id'];
      //$_SESSION['user_id'] = $_SESSIONS[session_id()]['user_id']
    }
    if (isset($_SESSION['last_access']) && (time() - $_SESSION['last_access']) > SESSION_LIFETIME) {
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        break;
      }
    }

    break;
  case 'GET':
    //$session_save();
    $action = key($_GET);
    switch ($action) {
      case 'session':
        if (array_key_exists($_GET['session'], $_SESSIONS)) {
          $_SESSIONS[session_id()]['patient_id'] = $_SESSIONS[$_GET['session']]['patient_id'];
          $_SESSIONS[session_id()]['user_id'] = $_SESSIONS[$_GET['session']]['user_id'];
        } else {
          exit(header('Location: ' . APP_URL_BASE . '?login'));
        }
        break;
      case 'login':
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
          //exit(header('Location: ' . APP_URL_BASE));
          break 2;
        } else {
          require_once APP_PATH . APP_BASE['public'] . 'login.php';
          die();
        }
        break;
      case 'logout':
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
          exit(require APP_PATH . APP_BASE['public'] . 'logout.php');
        } else {
          exit(header('Location: ' . APP_URL_BASE));
        }
        break;

    }

    break;
  default:


    //if (!isset($_SESSION['user_id']) || is_string($_SESSION['user_id'])) {
    //  exit(header('Location: ' . APP_URL_BASE . '?login'));
    //}
    break;
}


ob_start();              // start output buffer 1
echo '<div style="float: right; text-align: right; font-size: 12px; margin: 10px;">' . "\n"
. 'Session Lifetime: ' . (time() - ($_SESSION['last_access'] ?? time())) . ' / ' . ini_get('session.gc_maxlifetime') . '<br />' . "\n"
. 'Cookie Lifetime: ' . session_get_cookie_params()['lifetime'] . ' / ' . ini_get('session.cookie_lifetime') . '<br />' . "\n"
. 'Session ID: ' . (!empty(session_id()) ? session_id() : $_COOKIE["PHPSESSID"]) . "\n";

$_SESSION['last_access'] = !isset($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME'];

echo '<pre>' . 'Date: ' . date('Y-m-d h:i:s', $_SESSION['last_access']) . "\n";
foreach ($_SESSION as $key => $value) {
  echo "[\"$key\"] => "; var_export($value); echo "\n";
}
echo "</pre></div>\n";
  
$ob_contents = NULL;

$ob_contents = ob_get_contents(); // read ob2 ("b")
  
ob_end_flush();          // flush ob2 to ob1
ob_end_clean();          // flush ob1 to browser


// dd($ob_contents, false);