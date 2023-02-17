<?php
if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

require_once 'functions.php';

// https://code.tutsplus.com/tutorials/organize-your-next-php-project-the-right-way--net-5873

// require_once(substr(__FILE__, 0, (strpos(__FILE__, 'lib/')))."resources.php");
// defined("LIBRARY_PATH") or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));
// defined("TEMPLATES_PATH") or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));

date_default_timezone_set('America/Vancouver');

isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] == 'on'
  and define('APP_HTTPS', TRUE);

define('APP_NAME',      'Christmas Hamper ' . date('Y'));
define('APP_DOMAIN',    isset($_SERVER['HTTP_HOST']) === true ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
define('APP_VERSION',   number_format(1.0, 1) . '.1');
define('APP_TIMEOUT',   strtotime('1970-01-01 24:00:00'.'GMT'));
define('APP_START',     microtime(true));
define('APP_UNAME',     '');
define('APP_PWORD',     '');

preg_match('/^(\/home\/\w+\/).+$/', dirname(__DIR__, 1), $matches)
  and define('APP_HOME', $matches[1]);

// absolute pathname 
basename(__DIR__) == 'config' ?
  define('APP_PATH', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR)
  . chdir('../')
  . define('APP_BASE', [ // https://stackoverflow.com/questions/8037266/get-the-url-of-a-file-included-by-php
    'config' => (!is_dir(APP_PATH . 'config') ? NULL : 'config' . DIRECTORY_SEPARATOR),
    'database' => (!is_dir(APP_PATH . 'database') ? NULL : 'database' . DIRECTORY_SEPARATOR),
    'public' => (!is_dir(APP_PATH . 'public') ? NULL : 'public' . DIRECTORY_SEPARATOR),
    'src' => (!is_dir(APP_PATH . 'src') ? NULL : 'src' . DIRECTORY_SEPARATOR),
    'var/tmp' => (!is_dir(APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR) ? NULL : 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR),
    'export' => (!is_dir(APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR) ? NULL : 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR),
    'session' => (!is_dir(APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR) ? NULL : 'var' . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR),
  ]) : // ./localhost/../
  define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR); // /var/www/html/

//ini_set("include_path", "src");
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'error_log'); // APP_PATH . 'error_log'
error_reporting(E_STRICT | E_ALL);

!is_file((!empty(get_included_files()) ? get_included_files()[0] : __FILE__))
  or define('APP_SELF', (!empty(get_included_files()) ? get_included_files()[0] : __FILE__)); // $_SERVER['PHP_SELF'] | __DIR__ . DIRECTORY_SEPARATOR

if (!isset($_SERVER['REQUEST_URI']))  {
  $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 0);

  if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "")
    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
}

if (APP_DOMAIN != 'localhost')
  (!is_file(APP_PATH.'.env.production') ? 
    (!@touch(APP_PATH.'.env.production') ? define('APP_ENV', 'production') : define('APP_ENV', 'production') . file_put_contents(APP_PATH.'.env.' . APP_ENV, "DB_UNAME=\nDB_PWORD=")) :
    define('APP_ENV', 'production')
  );
else
  (!is_file(APP_PATH.'.env.development') ?
    (!@touch(APP_PATH.'.env.development') ? define('APP_ENV', 'development') : define('APP_ENV', 'development') . file_put_contents(APP_PATH.'.env.' . APP_ENV, "DB_UNAME=root\nDB_PWORD=")) :
      define('APP_ENV', 'development')
  );

// substr( str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']), strrpos(str_replace('\\', '/', __FILE__), '/') - strlen($_SERVER['DOCUMENT_ROOT']) + 1 )
substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) == '/' ? // $_SERVER['DOCUMENT_ROOT']
  define('APP_URL', 'http' . (defined('APP_HTTPS') ? 's':'') . '://' . APP_DOMAIN . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1)) :
  define('APP_URL', [
    'scheme' => 'http' . (defined('APP_HTTPS') ? 's':''), // ($_SERVER['HTTPS'] == 'on', (isset($_SERVER['HTTPS']) === true ? 'https' : 'http')
    'host' => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']),
    'port' => $_SERVER['SERVER_PORT'],
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
define('APP_URL_PATH', (!is_array(APP_URL) ? substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) : APP_URL['path'])  );

define('APP_QUERY', (!empty(parse_url($_SERVER['REQUEST_URI'])['query']) ? (parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $query) ? [] : $query) : []));

!is_array(APP_URL)
  or define('APP_URI',   // BASEURL
    preg_replace('!([^:])(//)!', "$1/",
      str_replace('\\', '/', (
        htmlspecialchars(APP_URL['scheme'] . '://' . (isset($_SERVER['PHP_AUTH_USER']) ? APP_URL['user'] . ':' . APP_URL['pass'] . '@': '') . APP_URL['host'] . (APP_URL['port'] !== '80' ? ':' . APP_URL['port'] : '') . APP_URL['path'] . (!basename($_SERVER["SCRIPT_NAME"]) ? '' : basename($_SERVER["SCRIPT_NAME"])) . (!empty(APP_URL['query']) ? '?' . http_build_query(APP_URL['query']) : '')) // dirname($_SERVER['PHP_SELF'])  dirname($_SERVER['REQUEST_URI'])
        )
      )
    )
  );
/*
switch(get_included_files()[0]) {
  case APP_PATH . 'assets' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'jquery.tinymce-config.js.php':

  break;

  case APP_PATH . 'index.php':

  break;

  case APP_PATH . 'install.php':

  break;

  case APP_PATH . 'login.php':

  break;
  
  case APP_PATH . 'logout.php':

  break;

  default:
  
    //var_dump(get_included_files());
    //header('Location: ' . APP_BASE_URL);
    //exit;
    
  break;
}
*/
//die('hello world');
//if (basename(get_included_files()[0]) == 'jquery.tinymce-config.js.php') {
  //exit;
//} else if (basename($_SERVER["SCRIPT_FILENAME"]) !== 'index.php') {
//  header('Location: ' . APP_BASE_URL . basename($_SERVER["SCRIPT_FILENAME"]));
//  exit;
//}

//var_dump($_REQUEST);

//$str_1 = htmlentities($_REQUEST['history']);

/*
function shutdown()
{
	global $pdo; //$myiconnect;
    // This is our shutdown function, in 
    // here we can do any last operations
    // before the script is complete.
	//mysqli_close($myiconnect);

  unset($pdo);
}*/

register_shutdown_function( // 'shutdown'
  function() {
    defined('APP_END') or define('APP_END', microtime(true));
    //print_r(get_defined_constants(true)['user']);
    //include('checksum_md5.php'); // your_logger(get_included_files());
    unset($pdo);
  }
);


//die();

