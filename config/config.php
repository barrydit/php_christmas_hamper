<?php
declare(strict_types=1); // First Line Only!
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_log', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'error_log');
ini_set('log_errors', 1);
error_reporting(E_ALL); // E_STRICT | 

if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

//ini_set("include_path", "src");

date_default_timezone_set('America/Vancouver');

// Enable output buffering
ini_set('output_buffering', 'On');

$errors = NULL;

$ob_content = NULL;

ob_start();
// write content

!is_file(get_included_files()[0] ?? __FILE__) // (!empty(get_included_files()) ? get_included_files()[0] : __FILE__)
  or define('APP_SELF', get_included_files()[0] ?? __FILE__); // $_SERVER['PHP_SELF'] | __DIR__ . DIRECTORY_SEPARATOR

$additionalPaths = [
  __DIR__ . DIRECTORY_SEPARATOR . 'constants.php',
  __DIR__ . DIRECTORY_SEPARATOR . 'functions.php',
  __DIR__ . DIRECTORY_SEPARATOR . 'database.php',
  __DIR__ . DIRECTORY_SEPARATOR . 'session.php',
  __DIR__ . DIRECTORY_SEPARATOR . 'composer.php',
  dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'
]; //require('constants.php'); 


$paths = array_merge(array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . 'classes/*.php'), 'is_file'), $additionalPaths);

while ($path = array_shift($paths)) {
  if ($path = realpath($path))
    require_once $path;
  else die(var_dump(basename($path) . ' was not found. file=classes/' . basename($path)));
}

// Check if the directory structure is /public_html/
if (strpos(APP_SELF, '/public_html/') !== false) { // basename(__DIR__) == 'public_html'
  
  $errors['APP_PUBLIC'] = "The `public_html` scenario was detected.\n";
  
  if (is_dir(dirname(APP_SELF, 2) . '/config')) {
    $errors['APP_PUBLIC'] .= "\t" . dirname(APP_SELF, 2) . '/config/*' . ' was found. This is not safe.'; 
  }

    // It is under the public_html scenario
    // Perform actions or logic specific to the public_html directory
    // For example:
    // include '/home/user_123/public_html/config.php';
} elseif (strpos(APP_SELF, '/public/') !== false) {
  if (basename(get_required_files()[0]) !== 'release-notes.php')
    if (is_dir('../config')) {
/*
      foreach(glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*.php') as $includeFile) {
      
        if ($includeFile == realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'composer.php'))
          if (!defined('APP_ENV') || APP_ENV != 'development') continue;

      
        if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__
        if (!file_exists($includeFile)) {
          error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL); //exit(1);
          break;
        }
        require $includeFile;
      }
*/
    } elseif (is_file('config.php')) require_once 'config.php';
}

(!extension_loaded('gd'))
  and $errors['ext/gd'] = 'PHP Extension: <b>gd</b> must be loaded inorder to export to xls for (PHPSpreadsheet).';

(!extension_loaded('xml')) // DOM
  and $errors['ext/xml'] = 'PHP Extension: <b>xml</b>-dom must be loaded inorder to export to xls for (PHPSpreadsheet).';

(!extension_loaded('zip')) // ZIP
  and $errors['ext/zip'] = 'PHP Extension: <b>zip</b> must be loaded inorder to export to xls for (PHPSpreadsheet).';

//var_dump(get_defined_constants(true)['user']);

//echo ;
/*
if (is_array($errors) && !empty($errors)) { ?>
<html>
<head><title>Error page</title></head>
<body>
<ul>
<?php foreach ($errors as $key => $error) { ?>
  <li><?= $key . ' => ' . $error ?></li>
<?php } ?>
</ul>
</body>
</html>
<?php
  die();
} */

define('APP_ERRORS', $errors ?? [/*ob_get_contents()*/]);

ob_end_clean();

//var_dump(APP_ERRORS);

//if (!empty(APP_ERRORS) && APP_ERRORS) // is_array($ob_content)
//  dd(APP_ERRORS); // get_defined_constants(true)['user']'


/* function shutdown()
{
	global $pdo; //$myiconnect;
    // This is our shutdown function, in 
    // here we can do any last operations
    // before the script is complete.
	//mysqli_close($myiconnect);

  unset($pdo);
} */

register_shutdown_function( // 'shutdown'
  function() {
    global $pdo, $session_save;

    isset($session_save) and $session_save();

    defined('APP_END') or define('APP_END', microtime(true));
    //include('checksum_md5.php'); // your_logger(get_included_files());
    unset($pdo);

  }
);