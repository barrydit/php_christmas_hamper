<?php

// MySQL settings.
/*
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = ''; //password
$dbname = 'christmas_hamper';
$dbbackup['path'] = dirname(__FILE__) . '/backup/';
$dbbackup['file'] = $dbname."___(".date('Y-m-d')."_".date('H-i-s').").sql";
$dbtables = array(
  'users', 
  'clients', 
  'hampers'
);
*/
/**/

require_once(APP_BASE_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(APP_BASE_PATH, '.env.' . APP_ENV);
$dotenv->safeLoad();

if (empty($_ENV)) {
  $_ENV['DB_UNAME'] = 'root';
  $_ENV['DB_PWORD'] = '';
}

define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_UNAME', $_ENV['DB_UNAME']);
define('DB_PWORD', $_ENV['DB_PWORD']);

define('DB_NAME', ['christmas_hamper']);
define('DB_TABLES', array(
    0=>'users', 
    1=>'clients',   
    2=>'hampers'
));

define('DB_BACK_PATH', APP_PATH . APP_BASE['database'] . 'backup' . DIRECTORY_SEPARATOR);
define('DB_BACK_FILE', DB_NAME[0].'___('. date('Y') .').sql'); // date('Y-m-d').'_'.date('H-i-s')

$dsn = 'mysql:host=' . DB_HOST . '; dbname=' . DB_NAME[0] . ';charset=' . DB_CHARSET;

$options = [
  PDO::ATTR_EMULATE_PREPARES   => true, // turn off emulation mode for "real" prepared statements
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // turn on errors in the form of exceptions
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // make the default fetch be an associative array
];

foreach(DB_TABLES as $key => $table) {
  if ($key == 0) 
    try {
      $pdo = new PDO($dsn, DB_UNAME, DB_PWORD, $options); // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_UNAME, DB_PWORD, $options);
        try {
          $pdo->query('USE `' . DB_NAME[0] . '`;');
        } catch (PDOException $e) {

          if (!is_dir(DB_BACK_PATH)) {
            if ($pdo->errorInfo()[1] == '1049') // $pdo->errorInfo()[2] == Unknown database 'christmas_hamper'
              $pdo->query('CREATE DATABASE `' . DB_NAME[0] . '`;');
          } else {

            ob_start();
            echo '<i>' . $e->getMessage() . '</i>';
            $ob_contents = ob_get_contents();
            ob_end_clean();
            require 'install.php';
            break;
          }
        }
      } catch (PDOException $e) {
        ob_start();
        echo '<i>' . $e->getMessage() . '</i>';
        $ob_contents = ob_get_contents();
        ob_end_clean();
        require 'install.php';
        break;
      }

      $pdo = new PDO($dsn, DB_UNAME, DB_PWORD, $options);
    }

  try {
    $stmt = $pdo->query('DESCRIBE `' . DB_NAME[0] . '`.`' . $table . '`;'); // 'SELECT 1 FROM `' . DB_NAME[0] . '`.`' . $table . '` LIMIT 1;'
  } catch (PDOException $e) {
    if (!is_dir(DB_BACK_PATH)) {
      if ($pdo->errorInfo()[1] == '1146') { // $e->getCode() == '42S02';  1146
        preg_match("/^Table\s['](\w+).(\w+)[']\sdoesn't\sexist$/", $pdo->errorInfo()[2], $matches);
        if ($matches[2] == 'users') {
          $pdo->query('CREATE TABLE `users` (`id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `username` varchar(25) NOT NULL, `password` varchar(255) NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
          $pdo->query('INSERT INTO `users` (`id`, `name`, `username`, `password`) VALUES (1, \'Owner\', \'root\', \'$2y$12$Cz/AlKIOBS7aAJ8Qoy2AFOua4A9VLzHLyX0vaweWc7SP3JA/MwU2C\');');
          $pdo->query('ALTER TABLE `users` ADD PRIMARY KEY (`id`);');
          $pdo->query('ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
          continue;
        } elseif ($matches[2] == 'clients') {
          $pdo->query('CREATE TABLE `clients` ( `id` int(11) NOT NULL, `hamper_id` int(11) DEFAULT NULL, `last_name` text NOT NULL, `first_name` text NOT NULL, `phone_number_1` varchar(20) NOT NULL, `phone_number_2` varchar(20) NOT NULL, `address` text NOT NULL, `group_size` varchar(10) NOT NULL, `minor_children` text NOT NULL, `diet_vegetarian` tinyint(1) NOT NULL, `diet_gluten_free` tinyint(1) NOT NULL, `pet_cat` tinyint(1) NOT NULL, `pet_dog` tinyint(1) NOT NULL, `notes` text NOT NULL, `active_status` tinyint(1) NOT NULL, `bday_date` date NOT NULL, `modified_date` date NOT NULL, `created_date` date NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
          $pdo->query('ALTER TABLE `clients` ADD PRIMARY KEY (`id`), ADD KEY `hamper_id` (`hamper_id`);');
          $pdo->query('ALTER TABLE `clients` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
          continue;
        } else if ($matches[2] == 'hampers') {
          $pdo->query('CREATE TABLE `hampers` ( `id` int(11) NOT NULL, `client_id` int(11) DEFAULT NULL, `hamper_no` varchar(5) NOT NULL, `transport_method` text NOT NULL, `phone_number_1` varchar(20) NOT NULL, `phone_number_2` varchar(20) NOT NULL, `address` text NOT NULL, `attention` text NOT NULL, `group_size` varchar(10) NOT NULL, `minor_children` text NOT NULL, `diet_vegetarian` tinyint(1) NOT NULL, `diet_gluten_free` tinyint(1) NOT NULL, `pet_cat` tinyint(1) NOT NULL, `pet_dog` tinyint(1) NOT NULL, `created_date` date NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
          $pdo->query('ALTER TABLE `hampers` ADD PRIMARY KEY (`id`), ADD KEY `client_id` (`client_id`);');
          $pdo->query('ALTER TABLE `hampers` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
          $pdo->query('ALTER TABLE `clients` ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`hamper_id`) REFERENCES `hampers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;');
          $pdo->query('ALTER TABLE `hampers` ADD CONSTRAINT `hampers_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;');
          // continue;
        }
      }
      mkdir(DB_BACK_PATH, 0700, true);
      file_put_contents(APP_PATH . '.env.development', "DB_UNAME=root\nDB_PWORD=");
    } else {
      $command = 'mysql'
      . ' --host=' . DB_HOST
      . ' --user=' . DB_UNAME
      . (empty(DB_PWORD) ? '' : ' --password=' . DB_PWORD)
      . ' ' . DB_NAME[0]
      . ' < ' . '"../' . APP_BASE['database'] . DB_NAME[0] . '_schema.sql' . '"';

      exec($command,$output,$worked);
      ob_start();
      print_r($output); //echo '<i>' . $output . '</i>';
      $ob_contents = ob_get_contents();
      ob_end_clean();
      //require 'install.php';
      break;
    }
  }
  if (isset($_GET['db']) && $_GET['db'] == DB_NAME[0]) {
    require 'install.php';
    exit;
  }
}
