<?php
if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

require_once 'functions.php';

// https://code.tutsplus.com/tutorials/organize-your-next-php-project-the-right-way--net-5873

// require_once(substr(__FILE__, 0, (strpos(__FILE__, 'lib/')))."resources.php");
// defined("LIBRARY_PATH") or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));
// defined("TEMPLATES_PATH") or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));

if (!isset($_SERVER['REQUEST_URI']))  {
  $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],0 );

  if (isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING'] != "")
    $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
}

date_default_timezone_set('America/Vancouver');

define('APP_NAME',      'Christmas Hamper ' . date('Y'));
define('APP_VERSION',   number_format(1.0, 1) . '.0');
//define( 'APP_PHP_VERSION', PHP_VERSION ); // intval(PHP_VERSION)>4
define('APP_ENV',       ($_SERVER['SERVER_NAME'] == 'localhost' ? 'development' : 'production')); // development
define('APP_TIMEOUT',   strtotime('1970-01-01 23:59:59' . 'GMT')); // 86400
define('APP_UNAME',     'root');
define('APP_PWORD',     'password');
define('APP_DOMAIN',    isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
define('APP_PATH',      dirname(__DIR__, 1));
define('APP_DB',        '/database/');
define('APP_EXPORT',    '/export/');
define('APP_SRC',       '/src/');
define('APP_SESSION',   '/session/');
define('APP_TMP',       '/var/tmp/');

//ini_set("include_path", "src");
ini_set('log_errors', 1);
ini_set('error_log', APP_PATH . '/error_log'); // APP_BASE_PATH . "../tmp/error_log"
error_reporting(E_STRICT | E_ALL);

/* DocumentRoot / basePath */

//define('doc_root', $_SERVER['DOCUMENT_ROOT']);
//define('ABSPATH',  $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']) );
define('APP_BASE_URI', // https://stackoverflow.com/questions/8037266/get-the-url-of-a-file-included-by-php
  //substr( str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']), strrpos(str_replace('\\', '/', __FILE__), '/') - strlen($_SERVER['DOCUMENT_ROOT']) + 1 )
  substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) // https://stackoverflow.com/questions/7921065/manipulate-url-serverrequest-uri
);

defined('APP_HTTPS') or
  define('APP_HTTPS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false);
  
define('APP_BASE_URL',   // BASEURL
  preg_replace('!([^:])(//)!', "$1/",
    str_replace('\\', '/', (
      APP_HTTPS ?
        'https://'.APP_DOMAIN.APP_BASE_URI : // dirname($_SERVER['PHP_SELF']) 
        'http://'.APP_DOMAIN.APP_BASE_URI // dirname($_SERVER['REQUEST_URI'])
      )
    )
  )
);

// Only works if the Query exists --> parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $query);
define('APP_QUERY',     $_GET); // array( key($_GET) => current($_GET) )); // ? . key($_REQUEST) . '=' . current($_REQUEST)
define('APP_ROOT',      getRelativePath(substr(APP_BASE_URI, 0, strrpos(APP_BASE_URI, APP_SRC) + 1), APP_BASE_URI)); // $_SERVER['PHP_SELF']
define('APP_SELF',      APP_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR . basename($_SERVER["SCRIPT_NAME"])); // $_SERVER['PHP_SELF'] | __DIR__ . DIRECTORY_SEPARATOR

define('READ_LEN', 4096);

/*
function shutdown()
{
	global $pdo; //$myiconnect;
    // This is our shutdown function, in 
    // here we can do any last operations
    // before the script is complete.
	//mysqli_close($myiconnect);
    
    //$time_end = microtime(true);
    
    //echo 'Time Start\End: ' . $time_start . ' - ' . $time_end . ' = '. round($time_end - $time_start, 4);
	
	unset($pdo);
}
register_shutdown_function('shutdown');
*/
