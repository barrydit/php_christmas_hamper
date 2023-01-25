<?php

defined('APP_BASE_PATH') // $_SERVER['DOCUMENT_ROOT']
  or define('APP_BASE_PATH', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);
 
require(APP_BASE_PATH . 'config/config.php');

// https://stackoverflow.com/questions/38396046/how-to-run-composer-update-on-php-server

define('HOME_DIRECTORY', APP_BASE_PATH . 'composer' );
define('COMPOSER_INITED', file_exists(APP_BASE_PATH.'/vendor'));

set_time_limit(100);
ini_set('memory_limit',-1);

if (!getenv('HOME') && !getenv('COMPOSER_HOME')) {
    putenv("COMPOSER_HOME=".HOME_DIRECTORY);
}

// change directory to root
chdir('../');

ini_set('phar.readonly',0);
if (!file_exists('composer'))
  (new Phar("composer.phar"))->extractTo("./composer");
    
//This requires the phar to have been extracted successfully.
require_once ('composer/vendor/autoload.php');

//Use the Composer classes
use Composer\Console\Application;
use Composer\Command\UpdateCommand;
use Symfony\Component\Console\Input\ArrayInput;

defined('COMPOSER_AUTOLOAD_PATH')
  or define("COMPOSER_AUTOLOAD_PATH", APP_BASE_PATH . 'vendor' . DIRECTORY_SEPARATOR); // basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' .

if (!is_dir(COMPOSER_AUTOLOAD_PATH)) {
  if (!is_file(APP_BASE_PATH . 'composer.phar'))
    file_put_contents(APP_BASE_PATH . 'composer.phar', file_get_contents('https://getcomposer.org/download/latest-stable/composer.phar'));
  //chdir();
  //shell_exec("cd ../ && php -f composer.phar", $output, $worked); // --dry-run --no-interaction --ansi
  // config --global --auth github-oauth.github.com ghp_1XhQL4hgdghjjyuuyyuTfux51ZDHZz

//Create the commands
//$args = array('command' => 'self-update');
$args = array('command' => 'update');
//$args = array('command' => 'config');

if(!file_exists('vendor')) { 
    echo "This is first composer run: --no-scripts option is applies\n";
    $args['--no-scripts'] = true;   
    //$args['--global'] = NULL;
    //$args['--editor'] = true;
    //$args['--auth'] = [ "github-oauth" => [ "github.com" => "ghp_1XhQL4LghjghjghfjhlXqTfux51ZDHZz" ] ] ;
    //$args['--unset'] = [ "github-oauth" => [ "github.com" => "ghp_1XhQL4LWTl3KtyJmmWlIjfghjfghjfufgh" ] ] ;
    //$args['github-oauth.github.com'] = 'ghp_1XhQLfhgjgfjhghjghjux51ZDHZz';   
}
$input = new ArrayInput($args);

//Create the application and run it with the commands
$application = new Application();
$application->setAutoExit(false);
$application->setCatchExceptions(false);
try {
    //Running commdand php.ini allow_url_fopen=1 && proc_open() function available
    $application->run($input);
    echo 'Success';
} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
/*
  die(var_dump(shell_exec("cd ../ && php -f composer.phar")));
  //die(var_dump(shell_exec('composer config --global --auth github-oauth.github.com ghp_1XhQL4LWTl3Kghjfghjfghjjgfgh')));
  $empty1=array();
  $empty2=array();
  $proc=proc_open('php composer.phar config --global --auth github-oauth.github.com ghp_1XhQLhgjghjghjghjghjHZz',$empty1,$empty2 );
  $ret = proc_close($proc);
  
  die(var_dump(passthru('php composer.phar config --global --auth github-oauth.github.com ghp_1XbnmbnmbnmbnmqTfux51ZDHZz')));
  exec('php composer.phar update --no-interaction --quiet 2>&1', $output, $worked); // self-update
*/
  die(header('Location: http://' . APP_BASE_URL));
  
} else 
  require(COMPOSER_AUTOLOAD_PATH . 'autoload.php'); // composer dump -o

switch($_SERVER['SERVER_NAME']) {
  case stristr($_SERVER['SERVER_NAME'], isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']):
    if (!isset($_SERVER['HTTPS']) && @$_SESSION['enable_ssl'] == TRUE):
      //exit(header('Location: ' . preg_replace("/^http:/i", "https:", APP_BASE_URL ))); // basename($_SERVER['REQUEST_URI']));
    endif;
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
      if (!is_file(APP_BASE_PATH . '.env.development'))
        file_put_contents(APP_BASE_PATH . '.env.development', "DB_UNAME=root\nDB_PWORD=");
      $dotenv = Dotenv\Dotenv::createImmutable(APP_BASE_PATH, '.env.development');
    } else
      $dotenv = Dotenv\Dotenv::createImmutable(APP_BASE_PATH, '.env.production');
    $dotenv->safeLoad();
    break;
    
  default:
    if (!isset($_SERVER['HTTPS']) && @$_SESSION['enable_ssl'] == TRUE):
      exit(header('Location: ' . preg_replace("/^http:/i", "https:", APP_BASE_URL . $_SERVER['QUERY_STRING'])));
    endif;
    break;
}

$includeFiles = [
  APP_BASE_PATH . '/config/database.php',
  APP_BASE_PATH . '/config/session.php',

];

foreach($includeFiles as $includeFile) {
  if (!file_exists($includeFile)) {
    echo "Failed to load a necessary file: " . $includeFile . PHP_EOL;
    exit(1);
    break;
  }
  require $includeFile;
}


switch ($_SERVER['REQUEST_METHOD']) {
/*
  case 'GET':
    break;
*/
  default:
    if ($_SERVER['REQUEST_METHOD'] == 'GET') { // leave condition; 
      header('Content-Type: text/html; charset=utf-8');

      $setting['del_prev_annual_hamper'] = true;
      
      if ($setting['del_prev_annual_hamper']) {
      
        $stmt = $pdo->prepare('SELECT `id` FROM `hampers` WHERE YEAR(`created_date`) < :date ORDER BY `id` DESC LIMIT 1;');
        $stmt->execute(array(
          ":date" => date('Y')
        ));
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($row)) {
        
          $stmt = $pdo->prepare('SELECT `id` FROM `hampers` WHERE YEAR(`created_date`) = :date ORDER BY `id` DESC LIMIT 1;');
          $stmt->execute(array(
            ":date" => date('Y')
          ));
        
          $row = $stmt->fetch(PDO::FETCH_ASSOC);

          if (!empty($row)) {
            $stmt = $pdo->prepare('SELECT `id`, `client_id` FROM `hampers` WHERE YEAR(`created_date`) = :date ORDER BY `id` DESC;');
            $stmt->execute(array(
              ":date" => date('Y')
            ));
        
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // $row = array_shift($rows)
              $stmt = $pdo->prepare('DELETE FROM `hampers` WHERE YEAR(`created_date`) = :date ;');
              $stmt->execute(array(
                ":date" => date('Y')
              ));
            }
          }
        }
      
        $stmt = $pdo->prepare('SELECT `id` FROM `hampers` WHERE YEAR(`created_date`) = :date ORDER BY `id` DESC LIMIT 1;');
        $stmt->execute(array(
          ":date" => date('Y')
        ));
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($row)) {
      
          $stmt = $pdo->prepare('SELECT `id` FROM `hampers` WHERE YEAR(`created_date`) < :date ORDER BY `id` DESC LIMIT 1;');
          $stmt->execute(array(
            ":date" => date('Y')
          ));
        
          $row = $stmt->fetch(PDO::FETCH_ASSOC);

          if (!empty($row)) {
            $stmt = $pdo->prepare('SELECT `id`, `client_id` FROM `hampers` WHERE YEAR(`created_date`) < :date ORDER BY `id` DESC;');
            $stmt->execute(array(
              ":date" => date('Y')
            ));
        
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // $row = array_shift($rows)
              $stmt = $pdo->prepare('UPDATE `clients` SET `hamper_id` = NULL WHERE `id` = :client_id AND `hamper_id` = :hamper_id ;');
              $stmt->execute(array(
                ":client_id" => $row['client_id'],
                ":hamper_id" => $row['id']
              ));
            }
          
            $stmt = $pdo->prepare('SELECT `id`, `client_id`, YEAR(`created_date`) FROM `hampers` WHERE YEAR(`created_date`) >= :date ORDER BY `id` DESC LIMIT 1;');
            $stmt->execute(array(
              ":date" => date('Y')
            ));
          
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
          
            if (!empty($row)) {
              $stmt = $pdo->prepare('DELETE FROM `hampers` WHERE YEAR(`created_date`) < :date ;');
              $stmt->execute(array(
                ":date" => date('Y')
              ));
            } else {
              $stmt = $pdo->prepare('SELECT `id` FROM `hampers` WHERE YEAR(`created_date`) = :date ORDER BY `id` DESC LIMIT 1;');
              $stmt->execute(array(
                ":date" => date('Y')
              ));
        
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              
              if (!empty($row)) {
                exec('mysqldump'
                . ' --user=' . DB_UNAME
                . (empty(DB_PWORD) ? '' : ' --password=' . DB_PWORD)
                . ' --host=' . DB_HOST
                . ' --default-character-set=utf8'
                . ' --single-transaction'
                //. ' --routines'
                . ' --add-drop-database'
                . ' --add-drop-table'
                . ' --databases ' . DB_NAME[0]
                . ' --result-file="' . DB_BACK_PATH . DB_BACK_FILE . '"'
                . ' 2>&1', $output, $worked);

                if (!empty($output)) die(var_dump($output));
              } else {
                $stmt = $pdo->prepare('TRUNCATE TABLE `hampers`;');
                $stmt->execute(array());
              }
            
              // check if there are any rows for curr_year ... backup, otherwise truncate
            }
          }
        }
      }
    }
    $stmt = $pdo->prepare(<<<HERE
SELECT id, last_name, first_name, phone_number_1, COUNT(last_name) as count
FROM clients
WHERE last_name != ''
GROUP BY last_name
HAVING COUNT(last_name) > 1;
HERE);
    $stmt->execute(array());
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results

    $rows_duplicate = $rows;
    $rows_clients = array();

    if (!empty($rows))
      while($row = array_shift($rows)) { //$result = $stmt->fetch()
  //  GROUP BY phone_number_1 HAVING COUNT(phone_number_1) > 1;
        $stmt1 = $pdo->prepare('SELECT id FROM clients WHERE last_name LIKE :last_name AND first_name LIKE :first_name GROUP BY first_name HAVING COUNT(first_name) > 1;');
        $stmt1->execute(array(
          ":last_name" => (!empty($row['last_name']) ? $row['last_name'] . '%' : '%'),
          ":first_name" => (!empty($row['first_name']) ? $row['first_name'] . '%' : '%'))
        );

        $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results    
        while($row1 = array_shift($rows1)) {
          $rows_clients[] = array(
            'id' => $row1['id']
          );
        }
      }

    if (!empty($rows_duplicate))
      while($row = array_shift($rows_duplicate)) { //$result = $stmt->fetch()
        $stmt2 = $pdo->prepare('SELECT id, last_name, first_name, phone_number_1, address FROM clients WHERE phone_number_1 != "" AND first_name LIKE :first_name GROUP BY phone_number_1 HAVING COUNT(phone_number_1) > 1;');
        $stmt2->execute(array(":first_name" => (!empty($row['first_name']) ? $row['first_name'] . '%' : '%')));

        $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results    
        while($row2 = array_shift($rows2)) {
          $rows_clients[] = array(
            'id' => $row2['id']
          );
        }
      }

    $rows_clients = array_map("unserialize", array_unique(array_map("serialize", $rows_clients)));

    $client_dup_count = count($rows_clients);

    if (key($_GET) == '')
      require APP_PATH . '/src/index.php';
    elseif (key($_GET) == 'search')
      if (current($_GET) == 'clients')
        require APP_PATH . '/src/search_client.php';
      elseif (current($_GET) == 'hampers')
        require APP_PATH . '/src/search_hamper.php';
      else
        exit(header('Location: ' . APP_BASE_URL));
    elseif (key($_GET) == 'client')
      if (current($_GET) == 'entry' || empty(current($_GET)))
        require APP_PATH . '/src/entry_client.php';
      elseif (current($_GET) == 'children')
        require APP_PATH . '/src/client_children.php';
      elseif (current($_GET) == 'duplicate')
        require APP_PATH . '/src/client_duplicate.php';
      elseif ((int) current($_GET))
        require APP_PATH . '/src/entry_client.php';
      else
        exit(header('Location: ' . APP_BASE_URL));
    elseif (key($_GET) == 'hamper')
      if (current($_GET) == 'entry' || empty(current($_GET)))
        require APP_PATH . '/src/entry_hamper.php';
      elseif ((int) current($_GET))
        require APP_PATH . '/src/entry_hamper.php';
      else
        exit(header('Location: ' . APP_BASE_URL));
    elseif (key($_GET) == 'reports') {
      if (current($_GET) == '' || !empty(current($_GET))) {
        require APP_PATH . '/src/reports.php';
      }
    } elseif (key($_GET) == 'queue') {
      if (current($_GET) == '' || !empty(current($_GET))) {
        require APP_PATH . '/src/queue.php';
      }
    }
    elseif (key($_GET) == 'debug')
      require APP_PATH . '/src/debug.php';  
/*
    elseif (key($_GET) == 'import') {
      if (current($_GET) == 'csv') {
        include __DIR__ . '/../src/import_csv.php';
      }
    }
*/        
/*
    elseif (key($_GET) == 'print') {
      if (current($_GET) == 'labels') {
        include __DIR__ . '/../src/print_labels.php';
      }
    }
*/
    elseif (key($_GET) == 'database')
      if (current($_GET) == 'patient')
        require APP_PATH . '/src/db_patient.php';
/*    elseif (current($_GET) == 'archive')
        require APP_PATH . '/src/db_archive.php';   */
      else
        exit(header('Location: ' . APP_BASE_URL . '?' . http_build_query(array(
          key($_GET) => 'patient'
        ))));


/*
      exit(header('Location: ' . APP_BASE_URL . '?' . http_build_query(array(
        'search' => ''
      ))));
*/
  break;
}

/*
switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':

    break;
  case 'POST':
    var_dump($_REQUEST);
    break;
}
*/

?>
