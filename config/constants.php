<?php

const DOMAIN_EXP = '/^(?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?$/'; // /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/

require_once 'functions.php';

// Enable output buffering
//ini_set('output_buffering', 'On');

// Increase the maximum execution time to 60 seconds
//ini_set('max_execution_time', 60);

/* This code sets up some basic configuration constants for a PHP application. */


// Define APP_START constant
!defined('APP_START') and define('APP_START', microtime(true)) ?: is_float(APP_START) or $errors['APP_START'] = 'APP_START is not a valid float value.';

// Define APP_SELF constant
!defined('APP_SELF') and define('APP_SELF', get_included_files()[0] ?? __FILE__) and is_string(APP_SELF) ?: $errors['APP_SELF'] = 'APP_SELF is not a valid string value.';

// Define APP_PATH constant
!defined('APP_PATH') and define('APP_PATH', realpath(dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR) and is_string(APP_PATH) ?: $errors['APP_PATH'] = 'APP_PATH is not a valid string value.';

//echo 'Checking Constants: ' . "\n\n";

// Application configuration

const APP_VERSION = '1.0.0'; // number_format(1.0, 1) . '.1'

!is_string(APP_VERSION) and $errors['APP_VERSION'] = 'APP_VERSION is not a valid string value.';

(version_compare(APP_VERSION, '1.0.0', '>=') == 0)
  and $errors['APP_VERSION'] = 'APP_VERSION is not a valid version (' . APP_VERSION . ').';

define('APP_NAME', 'Christmas Hamper ' . date('Y'));
(!is_string(APP_NAME))
  and $errors['APP_NAME'] = 'APP_NAME is not a string => ' . var_export(APP_NAME, true); // print('Name: ' . APP_NAME  . ' v' . APP_VERSION . "\n");

//define('APP_HOURS', ['open' => '08:00', 'closed' => '17:00']);
//if (defined('APP_HOURS')) echo 'Hours of Operation: ' . APP_HOURS['open'] . ' -> ' . APP_HOURS['closed']  . "\n";


// Check if the request is using HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') { // $_SERVER['REQUEST_SCHEME']    strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'
  define('APP_HTTPS', TRUE);
}
if (defined('APP_HTTPS') && APP_HTTPS) {
  $errors['APP_HTTPS'] = (bool) var_export(APP_HTTPS, true); // print('HTTPS: ' . APP_HTTPS . "\n");
}
/*
// Check if the script is running in CLI or HTTP environment
if (php_sapi_name() === 'cli' || defined('STDIN')) {
  // CLI environment: set a default URL or placeholder
  define('APP_URL', 'http://localhost/');
} else {
  // HTTP environment: construct the URL dynamically
  define('APP_URL', 
      'http' . (defined('APP_HTTPS') ? 's' : '') . '://' .
      ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_ADDR'] ?? 'localhost') . 
      parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)
  );
}
*/
define('APP_DOMAIN', array_key_exists('host', $domain = parse_url($_SERVER['REQUEST_URI'])) ? $domain['host'] : 'localhost');
!is_string(APP_DOMAIN) and $errors['APP_DOMAIN'] = 'APP_DOMAIN is not valid. (' . APP_DOMAIN . ')' . "\n";

define('APP_TIMEOUT',   strtotime("1970-01-01 08:00:00GMT"));
(defined('APP_TIMEOUT') && !is_int(APP_TIMEOUT)) and $errors['APP_TIMEOUT'] = APP_TIMEOUT; // print('Timeout: ' . APP_TIMEOUT . "\n");

(!empty($login = [/*'UNAME' => '', 'PWORD' => ''*/]))
and define('APP_LOGIN', $login);
(defined('APP_LOGIN') && is_array(APP_LOGIN)) and (empty(APP_LOGIN['UNAME']) || empty(APP_LOGIN['PWORD']) ?: $errors['APP_LOGIN'] = APP_LOGIN); //print('Auth: ' . "\n\t" . '(User => ' . APP_LOGIN['UNAME'] . ' Password => ' . APP_LOGIN['PWORD'] . ")\n");

// absolute pathname 
switch (__DIR__) {
  case APP_PATH . 'config':
    define('APP_BASE', [ // https://stackoverflow.com/questions/8037266/get-the-url-of-a-file-included-by-php
      'config' => 'config' . DIRECTORY_SEPARATOR,
      'database' => 'database' . DIRECTORY_SEPARATOR,
      'public' => 'public' . DIRECTORY_SEPARATOR,
      'src' => 'src' . DIRECTORY_SEPARATOR,
      'tmp' => 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
      'export' => 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
      'session' => 'var' . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR,
    ]);
    break;
  default:
    define('APP_PATH', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);
    define('APP_BASE', []);
    break;
}

if (defined('APP_DOMAIN') && !in_array(APP_DOMAIN, ['localhost', '127.0.0.1', '::1'])) {
  if (!is_file(APP_PATH . '.env.production')) {
    if (@touch(APP_PATH . '.env.production'))
      file_put_contents(APP_PATH . '.env.production', "DB_UNAME=\nDB_PWORD=");
  }
  define('APP_ENV', 'production');
} else {
  if (!is_file(APP_PATH . '.env.development')) {
    if (@touch(APP_PATH . '.env.development')) {
      file_put_contents(APP_PATH . '.env.development', "DB_UNAME=root\nDB_PWORD=");
    }
  }
  define('APP_ENV', 'development');
}

(defined('APP_ENV') && !is_string(APP_ENV)) and $errors['APP_ENV'] = APP_ENV; // print('App Env: ' . APP_ENV . "\n");


//(defined('APP_PATH') && truepath(APP_PATH)) and $errors['APP_PATH'] = truepath(APP_PATH); // print('App Path: ' . APP_PATH . "\n" . "\t" . '$_SERVER[\'DOCUMENT_ROOT\'] => ' . $_SERVER['DOCUMENT_ROOT'] . "\n");

if (defined('APP_BASE'))
  if (empty(APP_BASE))
    $errors['APP_BASE'] = json_encode(array_keys(APP_BASE)); // print('App Base: ' .  . "\n");
  else {
    foreach (APP_BASE as $key => $path) { // << -- This only works when debug=true
      if ($path == 'var/session/') continue;
      if (!is_dir(APP_PATH . $path) && APP_DEBUG)
        (@!mkdir(APP_PATH . $path, 0755, true) ?: $errors['APP_BASE'][$key] = $path . ' could not be created.' );
    //else $errors['APP_BASE'][$key] = $path;
    }
  }

  define('APP_PUBLIC',  str_replace(APP_PATH, '', (basename(dirname(get_included_files()[0])) == 'public' ? dirname(APP_SELF, 2) . DIRECTORY_SEPARATOR . APP_BASE['public'] . basename(APP_SELF) : basename(APP_SELF))) );

  define('APP_CONFIG',  str_replace(APP_PATH, '', (basename(dirname(get_included_files()[1])) == 'config' ? dirname(APP_SELF, 2) . DIRECTORY_SEPARATOR . APP_BASE['config'] . basename(get_included_files()[1]) : basename(get_included_files()[1]))) );

  //var_dump(APP_PATH . basename(dirname(__DIR__, 2)) . '/' . basename(dirname(__DIR__, 1)));

if (APP_ENV == 'development') { // APP_DEV |  APP_PROD
/* Non-useable code?
  $dirs = array_filter(glob(APP_PATH . 'clientele/' . (isset($_GET['client']) ? $_GET['client'] . '/*' : '/*' )), 'is_dir');
  $dirs = [0 => $dirs[array_key_first($dirs)]];

  define('APP_BACKUP', [
    'client' => $_GET['client'] ?? basename(APP_PATH . 'clientele'),
    'domain' => (preg_match('/^(?:[-a-z0-9]+\.)?[-a-z0-9]+\.[a-z]{2,6}$/', strtolower(basename($dirs[0]))) ? basename($dirs[0]) : 'invalid-domain'),
    'path' => (!str_ends_with(APP_PATH, basename(dirname(__DIR__, 2)) . '/' . basename(dirname(__DIR__, 1)) . '/') ?: APP_PATH . 'clientele')
  ]);
  preg_match('/^(\/home\/\w+\/).+$/', dirname(__DIR__, 1), $matches)
    and define('APP_HOME', $matches[1]) or define('APP_HOME', '/home/barryd/');
*/
}

!isset($_SERVER['REQUEST_URI'])
  and $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 0) . ((isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") AND '?' . $_SERVER['QUERY_STRING']);

// substr( str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']), strrpos(str_replace('\\', '/', __FILE__), '/') - strlen($_SERVER['DOCUMENT_ROOT']) + 1 )

!is_array(APP_BASE) ?
  substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) == '/' // $_SERVER['DOCUMENT_ROOT']
    and define('APP_URL', 'http' . (defined('APP_HTTPS') ? 's':'') . '://' . APP_DOMAIN . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1)) :
  define('APP_URL', [
    'scheme' => 'http' . (defined('APP_HTTPS') && APP_HTTPS ? 's': ''), // ($_SERVER['HTTPS'] == 'on', (isset($_SERVER['HTTPS']) === true ? 'https' : 'http')
    'host' => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']),
    'port' => (int) $_SERVER['SERVER_PORT'],
    /* https://www.php.net/manual/en/features.http-auth.php */
    'user' => (!isset($_SERVER['PHP_AUTH_USER']) ? NULL : $_SERVER['PHP_AUTH_USER']),
    'pass' => (!isset($_SERVER['PHP_AUTH_PW']) ? NULL : $_SERVER['PHP_AUTH_PW']),
    'path' => substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1), // https://stackoverflow.com/questions/7921065/manipulate-url-serverrequest-uri
    'query' => (!empty(parse_url($_SERVER['REQUEST_URI'])['query']) ? (parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $query) ? '' : $query) : ''), // $_SERVER['QUERY_STRING'], // array( key($_REQUEST) => current($_REQUEST) )
    'fregment' => '',
  ]);

// APP_BASE_URL
!is_array(APP_URL) ? define('APP_URL_BASE', APP_URL) :
  define('APP_URL_BASE', APP_URL['scheme'] . '://' . APP_URL['host'] . APP_URL['path']);

// APP_BASE_URI
define('APP_URL_PATH', !is_array(APP_URL) ? substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) : APP_URL['path']);

define('APP_QUERY', !empty(parse_url($_SERVER['REQUEST_URI'])['query']) ? (parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $query) ? [] : $query) : []);

!is_array(APP_URL)
  or define('APP_URI',   // BASEURL
    preg_replace('!([^:])(//)!', "$1/",
      str_replace('\\', '/',
        htmlspecialchars(APP_URL['scheme'] . '://' . (isset($_SERVER['PHP_AUTH_USER']) ? APP_URL['user'] . ':' . APP_URL['pass'] . '@' : '') . APP_URL['host'] . (APP_URL['port'] !== '80' ? ':' . APP_URL['port'] : '') . APP_URL['path'] . (!basename($_SERVER["SCRIPT_NAME"]) ? '' : basename($_SERVER["SCRIPT_NAME"])) . (!empty(APP_URL['query']) ? '?' . http_build_query(APP_URL['query']) : '')) // dirname($_SERVER['PHP_SELF'])  dirname($_SERVER['REQUEST_URI'])
      )
    )
  );
