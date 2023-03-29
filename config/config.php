<?php
if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

error_reporting(E_STRICT | E_ALL);

date_default_timezone_set('America/Vancouver');

// Enable output buffering
//ini_set('output_buffering', 'On');

// Increase the maximum execution time to 60 seconds
//ini_set('max_execution_time', 60);

/* This code sets up some basic configuration constants for a PHP application. */
isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] == 'on'
  and define('APP_HTTPS', TRUE);

// Application configuration
define('APP_START',     microtime(true));
define('APP_NAME',      'Christmas Hamper ' . date('Y'));
/* This code defines a constant named APP_DOMAIN that represents the domain name of the current website. */
(isset($_GET['debug']) ? define('APP_DEBUG', TRUE) : NULL);
define('APP_DOMAIN',    isset($_SERVER['HTTP_HOST']) === true ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
define('APP_VERSION',   number_format(1.0, 1) . '.1');
define('APP_TIMEOUT',   strtotime('1970-01-01 08:00:00'.'GMT'));
define('APP_UNAME',     '');
define('APP_PWORD',     '');

preg_match('/^(\/home\/\w+\/).+$/', dirname(__DIR__, 1), $matches)
  and define('APP_HOME', $matches[1]);

/* This code sets up constants that define the application's base path and directory structure, and ensures that the application is able to access files and directories located in the parent directory. */
(basename(__DIR__) == 'config') ?
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
ini_set('error_log', APP_PATH . 'error_log');
ini_set('log_errors', 1);

(defined('APP_DOMAIN') && APP_DOMAIN != 'localhost') ?
// This code sets the APP_ENV constant to 'production' and creates a file named .env.production in the application directory if it doesn't already exist.
  // The file is used to store configuration settings for the production environment
  (!is_file(APP_PATH.'.env.production') ? 
    (!@touch(APP_PATH.'.env.production') ? define('APP_ENV', 'production') : define('APP_ENV', 'production') . file_put_contents(APP_PATH.'.env.' . APP_ENV, "DB_UNAME=\nDB_PWORD=")) :
    define('APP_ENV', 'production')
  ) :
  // The file is used to store configuration settings for the development environment
  (!is_file(APP_PATH.'.env.development') ?
    (!@touch(APP_PATH.'.env.development') ? define('APP_ENV', 'development') : define('APP_ENV', 'development') . file_put_contents(APP_PATH.'.env.' . APP_ENV, "DB_UNAME=root\nDB_PWORD=")) :
    define('APP_ENV', 'development')
  );

/* This code checks if the current file is being executed directly (i.e. as the main script) or if it has been included by another file. */
!is_file((!empty(get_included_files()) ? get_included_files()[0] : __FILE__))
  or define('APP_SELF', (!empty(get_included_files()) ? get_included_files()[0] : __FILE__)); // $_SERVER['PHP_SELF'] | __DIR__ . DIRECTORY_SEPARATOR

/* The purpose of this code is to ensure that $_SERVER['REQUEST_URI'] is set with the correct value, which can be used by the application to determine the requested URL and any parameters passed to it. */

!isset($_SERVER['REQUEST_URI'])
  and $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 0) . ((isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") AND '?' . $_SERVER['QUERY_STRING']);


// substr( str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']), strrpos(str_replace('\\', '/', __FILE__), '/') - strlen($_SERVER['DOCUMENT_ROOT']) + 1 )
/* This code checks if the first part of the URI is equal to a single slash (/). If it is, this means that the script is being accessed at the root level of the domain,
and not within a specific directory. If it isn't, this means that the script is being accessed within a directory. */
!is_array(APP_BASE) ?
  substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) == '/' // $_SERVER['DOCUMENT_ROOT']
    and define('APP_URL', 'http' . (defined('APP_HTTPS') ? 's':'') . '://' . APP_DOMAIN . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1)) :
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
define('APP_URL_PATH', (!is_array(APP_URL) ? substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) : APP_URL['path']));

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
    global $pdo, $session_save;

    isset($session_save) and $session_save();

    defined('APP_END') or define('APP_END', microtime(true));
    //include('checksum_md5.php'); // your_logger(get_included_files());
    unset($pdo);
  }
);

$includeFiles = glob(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*.php');

foreach($includeFiles as $includeFile) {
  if ($includeFile == realpath('config' . DIRECTORY_SEPARATOR . 'composer.php'))
    if (defined('APP_ENV') && APP_ENV != 'development') continue;

  if ($includeFile == realpath('config' . DIRECTORY_SEPARATOR . 'install.php')) continue;
    
  if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__
  if (!file_exists($includeFile)) {
    error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL); //exit(1);
    break;
  }
  require $includeFile;
}
