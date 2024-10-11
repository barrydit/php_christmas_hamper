<?php

// https://stackoverflow.com/questions/38396046/how-to-run-composer-update-on-php-server


//die(var_dump(get_required_files()));

define('HOME_DIRECTORY', APP_PATH . 'composer' );
define('COMPOSER_INITED', file_exists(APP_PATH . 'vendor'));

set_time_limit(100);
ini_set('memory_limit',-1);

if (!getenv('HOME') && !getenv('COMPOSER_HOME'))
  putenv("COMPOSER_HOME=".HOME_DIRECTORY);

if (!file_exists(HOME_DIRECTORY)) {
  ini_set('phar.readonly',0) or $errors['phar.readonly'] = 'phar.readyonly Must be set to false.';
  if (!is_dir(APP_PATH . 'composer')) {
    if (!is_file(APP_PATH . 'composer.phar'))
      file_put_contents(APP_PATH . 'composer.phar', file_get_contents('https://getcomposer.org/download/latest-stable/composer.phar'));
    (new Phar("composer.phar"))->extractTo("./composer");
  }
}
//This requires the phar to have been extracted successfully.
require_once APP_PATH . 'composer/vendor/autoload.php';

//Use the Composer classes
use Composer\Console\Application;
use Composer\Command\UpdateCommand;
use Symfony\Component\Console\Input\ArrayInput;

defined('COMPOSER_AUTOLOAD_PATH')
  or define("COMPOSER_AUTOLOAD_PATH", APP_PATH . 'vendor' . DIRECTORY_SEPARATOR); // basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' .

if (!is_dir(COMPOSER_AUTOLOAD_PATH)) {

  //chdir();
  //shell_exec("cd ../ && php -f composer.phar", $output, $worked); // --dry-run --no-interaction --ansi
  // config --global --auth github-oauth.github.com ghp_1XhQL4hgdghjjyuuyyuTfux51ZDHZz

//Create the commands
//$args = array('command' => 'self-update');
  $args = ['command' => 'update'];
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
  die(header('Location: http://' . APP_URL_BASE));
}

