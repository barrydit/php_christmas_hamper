<?php

defined('APP_PATH') // $_SERVER['DOCUMENT_ROOT']
  or define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
//die('test');
//

// Check if the config file exists in various locations based on the current working directory
$path = null;
$publicDir = basename(getcwd()) == 'public';

// Determine the path based on current location and check if file exists
if ($publicDir) {
    // We are in the public directory
    if (is_file('../config/config.php')) {
        $path = realpath('../config/config.php');
    } elseif (is_file('config.php')) {
        $path = realpath('config.php');
    }
} else {
  
chdir(APP_PATH . 'public');

    exit(header('Location: '));
    // We are not in the public directory
    if (is_file('config/config.php')) {
        $path = realpath('config/config.php');
    } elseif (is_file(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
        $path = realpath(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
    }
}

// Load the config file if found
if ($path) {
    require_once $path;
} else {
    die(var_dump("Config file was not found."));
}

if (is_dir(APP_PATH . 'config')) {
  $dirs = [];

  foreach(glob(APP_PATH . 'config' . DIRECTORY_SEPARATOR . '*.php') as $includeFile) {
    //if ($includeFile == realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php'))
    //  continue;      
    if ($includeFile == realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'debug.php'))
      continue;
    elseif ($includeFile == realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'install.php'))
      continue;
    elseif (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__
    elseif (!file_exists($includeFile)) {
      error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL); //exit(1);
      break;
    }
    else $dirs = array_merge($dirs, [$includeFile]); // require $includeFile;
  }

  $dirs = array_merge($dirs, [APP_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php']); //require('constants.php');

usort($dirs, function ($a, $b) {
  if (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.php')
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.php')
    return 1; // $a comes before $b
  elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'functions.php') // DIRECTORY_SEPARATOR
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'functions.php')
    return 1; // $a comes before $b
  elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'database.php') // DIRECTORY_SEPARATOR
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'database.php')
    return 1; // $a comes before $b
  elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'session.php')
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'session.php')
    return 1; // $a comes before $b
  elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'composer.php')
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'composer.php')
    return 1; // $a comes before $b
  else 
    return strcmp(basename($a), basename($b)); // Compare other filenames alphabetically
});


foreach ($dirs as $includeFile) {
  $path = dirname($includeFile);

  if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__

  if (basename($includeFile) === 'composer-setup.php') continue;

  if (basename($includeFile) === 'session.php') {

    require_once $includeFile;

    if (isset($_GET['logout']))
      require_once APP_PATH . APP_BASE['public'] . 'logout.php';
      //dd($_SESSION, false);
    if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
      if (isset($_GET['login'])) {
        exit(header('Location: ' . APP_URL_BASE));
        //break;
      }
      if (isset($_GET['debug'])) {
        require_once APP_PATH . APP_BASE['config'] . 'debug.php';
        exit();
        //break;
      }
      continue;
    } else {
      require_once APP_PATH . APP_BASE['public'] . 'login.php'; // break;
    }

    die(); // break; // continue;
  }

  if (!file_exists($includeFile)) {
    error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL);
    break;
  } else {
    $currentFilename = substr(basename($includeFile), 0, -4);
    
    // $pattern = '/^' . preg_quote($previousFilename, '/')  . /*_[a-zA-Z0-9-]*/'(_\.+)?\.php$/'; // preg_match($pattern, $currentFilename)

    if (!empty($previousFilename) && strpos($currentFilename, $previousFilename) !== false) continue;

    // dd('file:'.$currentFilename,false);
    //dd("Trying file: $includeFile", false);
    require_once $includeFile;

    $previousFilename = $currentFilename;     
  }
}

}

//dd($_SESSION, false);
//dd(get_required_files(), false);

//dd($_SESSION, false);

//dd(null, true);

//die(var_dump(get_required_files()));

if (!extension_loaded('session')) {

// Check if the user has requested logout
if (filter_input(INPUT_GET, 'logout')) { // ?logout=true
  // Set headers to force browser to drop Basic Auth credentials
  header('WWW-Authenticate: Basic realm="Logged Out"');
  header('HTTP/1.0 401 Unauthorized');
    
  // Add cache control headers to prevent caching of the authorization details
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Pragma: no-cache");
    
  // Unset the authentication details in the server environment
  unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    
  // Optional: Clear any existing headers related to authorization
  function_exists('header_remove')
    and header_remove('HTTP_AUTHORIZATION') . header_remove('PHP_AUTH_USER') . header_remove('PHP_AUTH_PW');


  // Provide feedback to the user and exit the script
  //header('Location: http://test:123@localhost/');
  exit('You have been logged out.');
}

//die(var_dump($_SERVER));
if (PHP_SAPI !== 'cli') {
  // Ensure the HTTP_AUTHORIZATION header exists
  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    // Decode the HTTP Authorization header
    $authHeader = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));
    if ($authHeader) {
      // Split the decoded authorization string into user and password
      [$user, $password] = explode(':', $authHeader);

      // Set the PHP_AUTH_USER and PHP_AUTH_PW if available
      $_SERVER['PHP_AUTH_USER'] = $user ?? '';
      $_SERVER['PHP_AUTH_PW'] = $password ?? '';
    }
  }

  // Check if user credentials are provided
  if (empty($_SERVER['PHP_AUTH_USER'])) {
    // Prompt for Basic Authentication if credentials are missing
    header('WWW-Authenticate: Basic realm="PHP Christmas Hamper"');
    header('HTTP/1.0 401 Unauthorized');
  
    // Stop further script execution
    exit('Authentication required.');
  } else {
    // Display the authenticated user's details
    //echo "<p>Hello, {$_SERVER['PHP_AUTH_USER']}.</p>";
    //echo "<p>You entered '{$_SERVER['PHP_AUTH_PW']}' as your password.</p>";
    //echo "<p>Authorization header: {$_SERVER['HTTP_AUTHORIZATION']}</p>";
  }
}
}
/*
if (isset($_GET['debug'])) 
  require_once 'public/index.php';
else
  die(header('Location: public/index.php'));
*/