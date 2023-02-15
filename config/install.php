<?php

/*
$ob_contents = null;
try {
  $pdo = new PDO($dsn, DB_UNAME, DB_PWORD, $options);
} catch (PDOException $e) {
  ob_start();
  echo '<i>' . $e->getMessage() . '</i>';
  $ob_contents = ob_get_contents();
  ob_end_clean();
}
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if ($_POST['method'] == 'backup') {

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
      }
    } else {
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
    }

    if (!empty($output)) {
      ob_start();
      echo $output;
      $ob_contents = ob_get_contents();
      ob_end_clean();
    }
  }
}

switch ($_SERVER['REQUEST_METHOD']) {
  default:

    if (key($_GET) == 'db') {
      
      switch ($_GET['db']) {
        case '':
          //if (!$ob_contents) header('Location: ' . APP_URL_BASE . '?session');
          foreach (DB_NAME as $db_name) {
            if (!$ob_contents) {
              $stmt = $pdo->query('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'' . $db_name . '\';')
              or print_r($pdo->errorInfo(), true);

              if ((bool) $stmt->fetchColumn() == FALSE) 
                die(header('Location: ' . APP_URL_BASE . '?db=' . $db_name));

            } else {
              die(header('Location: ' . APP_URL_BASE));
            }
          }
          if (!$ob_contents) header('Location: ' . APP_URL_BASE . '?session');
          break;
        default:

          if (!in_array($_GET['db'], DB_NAME))
            header('Location: ' . APP_URL_BASE);

          if (key($_POST) == 'method') {
            switch ($_POST['method']) {
              case 'create':           
                $dsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
                $pdo = new PDO($dsn, DB_UNAME, DB_PWORD, $options);

                ob_start();
                $file = $_POST[$_POST['method']];
                $stmt = $pdo->query('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'' . $_GET['db'] . '\';')
                  or print_r($pdo->errorInfo(), true);

                if ((bool) $stmt->fetchColumn() == TRUE) {
                  $sql = 'DROP DATABASE `' . $_GET['db'] . '`;';
                  $pdo->exec($sql);
                }
                $sql = 'CREATE DATABASE `' . $_GET['db'] . '`;';
                $pdo->exec($sql);
                echo "<p>Database created successfully</p>";

                //$pdo->close();

                $command = 'mysql'
                . ' --host=' . DB_HOST
                . ' --user=' . DB_UNAME
                . (empty(DB_PWORD) ? '' : ' --password=' . DB_PWORD)
                . ' ' . $_GET['db']
                . ' < ' . '"..' . APP_DB . DB_NAME[0] . '_schema.sql' . '"';
                //die(var_dump($command));
                exec($command,$output,$worked);
                switch($worked){
                  case 0:
                    echo '<p>Import successful to database <b>' . $file . '</b></p>';
                    break;
                  case 1:
                    echo '<p>There was an error during import. You may need to run the following command manually.<br >' . $command . '<br /><br />Error: ' . print_r($output) . '</p>';
                    break;
                }
                
                $ob_contents = ob_get_contents();
                ob_end_clean();
                // array(2) { ["method"]=> string(7) "install" ["install"]=> string(11) "sandbox.sql" } 
                break;
              case 'restore':
                ob_start();
                $file = $_POST[$_POST['method']];
                $stmt = $pdo->query('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'' . $_GET['db'] . '\';')
                  or print_r($pdo->errorInfo(), true);

                if ((bool) $stmt->fetchColumn() == TRUE) {
                  $sql = 'DROP DATABASE `' . $_GET['db'] . '`;';
                  $pdo->exec($sql);
                }
                $sql = 'CREATE DATABASE `' . $_GET['db'] . '`;';
                $pdo->exec($sql);
                echo "<p>Database created successfully</p>";

                // array(2) { ["restore_mthd"]=> string(7) "Restore" ["restore"]=> string(11) "sandbox.sql" } 

                $command='mysql'
                . ' --host=' . DB_HOST
                . ' --user=' . DB_UNAME
                . (empty(DB_PWORD) ? '' : ' --password=' . DB_PWORD)
                . ' ' . $_GET['db'] 
                . ' < ' . '"' . DB_BACK_PATH . $file . '"';
                //. ' source ' . DB_BACK_PATH . $_REQUEST['restore'];
        
                //die($command);

                exec($command,$output,$worked);
                switch($worked){
                  case 0:
                    echo '<p>Import successful to database <b>' . $_REQUEST['restore'] .'</b></p>';
                    break;
                  case 1:
                    echo '<p>There was an error during import. You may need to run the following command manually.<br >' . $command  . '<br /><br />Error: ' . print_r($output) . '</p>';
                    break;
                }
                $ob_contents = ob_get_contents();
                ob_end_clean();
                break;
            }
          }
        break;
      }
    } else {
//die('test');
      if (isset($pdo)) {
        $stmt = $pdo->query('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'' . DB_NAME[0] . '\';')
          or print_r($pdo->errorInfo(), true);

        ob_start();
        if ((bool) $stmt->fetchColumn() == FALSE)
          echo 'The Database was not found: <strong>' . DB_NAME[0] . '</strong>';
  
        $ob_contents = ob_get_contents();
        ob_end_clean();
        if ($ob_contents) die(header('Location: ' . APP_URL_BASE . '?db=' . DB_NAME[0] ));
        //else die(header('Location: ' . APP_URL_BASE));
      }
    }
    break;
}

$files = array(/*0 => array('path', 'filesize')*/ );

foreach (glob("config/*.php") as $filename) {
    //echo "$filename size " . filesize($filename) . "\n";
    $files[] = array('path'=>$filename, 'filesize' => filesize($filename) );
}
foreach (glob("database/*.sql") as $filename) {
    //echo "$filename size " . filesize($filename) . "\n";
    $files[] = array('path'=>$filename, 'filesize' => filesize($filename) );
}
foreach (glob("public/*.php") as $filename) {
    //echo "$filename size " . filesize($filename) . "\n";
    $files[] = array('path'=>$filename, 'filesize' => filesize($filename) );
}
foreach (glob("src/*.php") as $filename) {
    //echo "$filename size " . filesize($filename) . "\n";
    $files[] = array('path'=>$filename, 'filesize' => filesize($filename) );
}

$total_files = 0;
$total_filesize = 0;
$total_lines = 0;

foreach($files as $file) {
  $total_files++;
  $total_filesize += $file['filesize'];
  $total_lines += count(file($file['path']));
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?=APP_NAME?> -- Installation</title>
    
    <base href="<?=(!defined('APP_URL_BASE') ? 'http://' . APP_DOMAIN . APP_URL_PATH : APP_URL_BASE )?>" />

    <link rel="shortcut icon" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>favicon.ico" />

    <!-- BOOTSTRAP STYLES-->
    <link rel="stylesheet" type="text/css" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/css/bootstrap/bootstrap.min.css" />
    
    <link rel="stylesheet" type="text/css" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/css/styles.css" />

<?php
switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
?>
    <meta http-equiv="refresh" content="5;url=<?=APP_URL_BASE?>" />
<?php
    break;
}
?>

    <style>
ul {
  margin: 0;
}
ul.dashed {
  list-style-type: none;
}
ul.dashed > li {
  text-indent: -5px;
}
ul.dashed > li:before {
  content: "-";
  text-indent: -5px;
}
    </style>

  </head>
  <body>
    <div style="float: right; text-align: right; margin: 10px 10px;">
    <?= formatSizeUnits($total_filesize); ?><br />
    <?= $total_files; ?> files<br />
    <?= $total_lines; ?> lines<br />
    </div>
    <div class="container">
      <div class="card card-primary">
        <!-- <div class="card-header">
          <div style="float: left; padding-top: 5px;"><a href="/" style="color: white; text-decoration:none;"><img src="assets/images/favicon.png" width="32" height="32"> Patient Clinic Files - Installation v<?=APP_VERSION?></a></div>
          <div style="float: right;">
            <a class="btn btn-primary" href="/?session=login" style="">Login</a>
          </div>
          <div class="clearfix"></div>
        </div> -->
        <div class="card-body">
          <div class="row">
            <div class="overflowAuto">
<?php
switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
?>
              <div class="card-header" style="position: relative; background-color: #6FA7D7; color: #000;">
              Installation was a success.
              </div>
              <div class="card-footer" style="position: relative; background-color: #C6DCEF; font-size: 14px; text-align: justify;">
              <?=(isset($ob_contents) ? $ob_contents: '')?>
              </div>
<?php
    break;
  default:
    if (key($_GET) == 'db')
      switch (in_array($_GET['db'], DB_NAME)) {
        case TRUE:
          //die('<code>' . var_dump($e) . '</code>');
          if ($pdo->errorInfo()[1] == '1146') { // $e->getCode() == '42S02'; // 1146
            //preg_match("/^Table\s['](.*?)[']\sdoesn't\sexist$/", $pdo->errorInfo()[2], $matches);
            preg_match("/^Table\s['](\w+).(\w+)[']\sdoesn't\sexist$/", $pdo->errorInfo()[2], $matches);
            
            
            //die(var_dump($matches));
          
?>
              <div class="card-header" style="background-color: #6FA7D7; color: #000;">
              An error has occured:  <span style="color: red;">Base table or view not found</span>
              </div>
              <div class="card-footer"style="background-color: #C6DCEF; font-size: 14px; text-align: justify;">
<?= $ob_contents; ?>
                <br /><br />
                <ol>
                  <li></li>
                </ol>
              </div>
<?php } else { ?>


        <div class="card-header" style="position: relative; background-color: #6FA7D7; color: #000;">
        <div style="float: left; padding-top: 5px;"><a href="." style="color: black; text-decoration:none;"><img src="assets/images/favicon.png" width="32" height="32" style=" background-color: white;"> <?= APP_NAME; ?> - Installation v<?=APP_VERSION?></a></div>
        <div style="float: right;">
        <a class="btn btn-primary" href="?session=login" style="">Login</a>
        </div>
        <div class="clearfix"></div>
        
</div>
              <div class="card-footer" style="position: relative; background-color: #C6DCEF; font-size: 14px; text-align: justify;">
<?= (isset($ob_contents) && $ob_contents !== NULL ? 'Database (error) `' . $_GET['db'] . '` was not found:<br />' . $ob_contents : '' ); ?>
              <div style="height: 10px;"></div>
              <div style="height: 10px; float: right;">
              <form style="float: right; margin-right: 10px;" action="<?='?' . 'db=' . DB_NAME[0]?>" autocomplete="off" method="POST">
                <?=(is_file(DB_BACK_PATH . DB_NAME[0] . '___(' . date('Y') . ').sql') ? (strtotime(date("Y-m-d", filemtime(DB_BACK_PATH . DB_NAME[0] . '___(' . date('Y') . ').sql' ))) > strtotime(date('Y-m-d')) ? '' : '<caption><div style="color: red;">Please Backup!</div></caption>') : '<caption><div style="color: red;">Please Backup First!</div></caption>')?>
                <button type="submit" name="method" value="backup" style="float: right; width: 7em;" <?=(is_file(DB_BACK_PATH . DB_NAME[0] . '___(' . date('Y') . ').sql') ? (strtotime(date("Y-m-d", filemtime(DB_BACK_PATH . DB_NAME[0] . '___(' . date('Y') . ').sql' ))) < strtotime(date('Y-m-d')) ? '' : 'disabled=""') : '')?>>Backup</button>
              </form>
              </div>
              <div>Please use this feature at your very OWN discretion...</div><br />
              <ul>
              <li>
                <fieldset id="group1">
                Is this a fresh (new) install? <input type="radio" id="q_n" name="q_fresh-install" value="no" onclick="handleClick(this);" />
                <label for="q_n">No</label>
                <input type="radio" id="q_y" name="q_fresh-install" value="yes" onclick="handleClick(this);" />
                <label for="q_y">Yes</label>
                </fieldset>
              </li>
              <li>
                <fieldset id="group2" <?=(is_file(DB_BACK_PATH . DB_NAME[0] . '___(' . date('Y') . ').sql') ? (strtotime(date("Y-m-d", filemtime(DB_BACK_PATH . DB_NAME[0] . '___(' . date('Y') . ').sql' ))) > strtotime(date('Y-m-d')) ? '' : 'disabled=""') : '')?>>
                Or are you trying to recover from a backup? <input type="radio" id="q2_n" name="q2_recover" value="no" onclick="handleClick(this);" />
                <label for="q2_n">No</label>
                <input type="radio" id="q2_y" name="q2_recover" value="yes" onclick="handleClick(this);" />
                <label for="q2_y">Yes</label>
                </fieldset>

                  <ul>
                    <li><small>Make sure that you have the restored (backup) file inside the <br /><i>./database/backup/<?=$_GET['db']?>.sql</i></small></li>
                  </ul>
                </li>
              </ul>
              </div>
              <div style="position: absolute; width:90%; max-width:684px;">
                <div id="installForm" style="z-index: 1; display: none; position: fixed; width: inherit; max-width: inherit; border: 1px solid #C6DCEF; padding: 10px; background-color: #E2EDF7; font-size: 14px; text-align: justify;">
                  <form action="<?='?' . http_build_query(array_merge(APP_QUERY, array()), '', '&amp;')?>" style="text-align: left;" method="POST" enctype="multipart/form-data">
                  <p>Install (Fresh)</p>
                  <table style="width: 650px; margin: 0 auto;">
                    <caption><input type="submit" name="method" value="create" style="margin-left: 0px; margin-bottom: 3px;" onclick="return confirm('Are you sure you want to CREATE a new database?'); " /> &nbsp;`<?= $_GET['db'] ?>` database</caption>
                    <thead>
                      <tr>
                        <th>Install</th><th>Date</th><th>Filename</th><th>Filesize</th>
                      </tr>
                    </thead>
                    <tbody>
<?php
$iterator = new DirectoryIterator(DB_BACK_PATH . '../' );
$FoundFiles = [];

while($iterator->valid())
{
  if ($iterator->isDot() && is_file($iterator->getFilename())) {
    $iterator->next();	//if ($file == '.htaccess') continue;
    continue;
  }
  $fileName = NULL; // $iterator->getFilename();
  
  if (preg_match('/^' . $_GET['db'] . '_schema\.sql/i', $iterator->getFilename()))
    $fileName = $iterator->getFilename();

  elseif (preg_match('/^' . $_GET['db'] . '___\(.+\)\.sql/i', $iterator->getFilename()))
    $fileName = $iterator->getFilename();


  if (!$fileName) {
    $iterator->next();
    continue;
  }

  $filetypes = array("sql");
  $filetype = pathinfo($fileName, PATHINFO_EXTENSION);
  if (in_array(strtolower($filetype), $filetypes))
    $FoundFiles[$iterator->getMTime()] = $fileName;

  $iterator->next();
}

if (!empty($FoundFiles)) {
  krsort($FoundFiles); //arsort($FoundFiles);

  $FoundFiles = array_values($FoundFiles);
  foreach ($FoundFiles as $key => $file) {
    echo '  <tr>' . "\n"
    . '                      <td style="text-align: center;"><input type="radio" name="create" value="' . $file . '" ' . ($key == 0 ? 'checked' : '') . '></td>' . "\n"
    . '                      <td style="padding-left: 10px; padding-right: 10px;">' . date("Y-m-d", filemtime(DB_BACK_PATH . '../' . $file)) .'</td>' . "\n"
    . '                      <td style="padding-left: 10px; padding-right: 10px;">' . "\n"
    . '                        <a href="?' . http_build_query(array_merge(APP_QUERY, array('download'=>$file)), '', '&amp;') . '"><img src="./assets/images/dl_ico.gif" style="vertical-align: middle;" alt="Load Icon" />&nbsp;' . $file . '</a>' . "\n"  
    . '                      </td>' . "\n"
    . '                      <td>' . formatSizeUnits(filesize(DB_BACK_PATH . '../' . $file)) . '    </td>' . "\n"
    . '                    </tr>' . "\n";
  }
}
?>
                      </tbody>
                    </table>
                  </form>
                </div>
                <div id="recoveryForm" style="z-index: 0; display: none; position: fixed; width: inherit; max-width: inherit; border: 1px solid #C6DCEF;  padding: 10px 10px 0 10px; background-color: #E2EDF7; font-size: 14px; text-align: justify;">
                  <form action="<?='?' . http_build_query(array_merge(APP_QUERY, array()), '', '&amp;')?>" style="text-align: left;" method="POST" enctype="multipart/form-data">
                  <p>Recovery</p>
                  <table style="width: 650px; margin: 0 auto;">
                    <caption><input type="submit" name="method" value="restore" style="margin-left: 0px; margin-bottom: 3px;" onclick="return confirm('Are you sure you want to RESTORE a previous database?'); " /> `<?= $_GET['db'] ?>` database <span style="color: #000; float: right;"><small>**Restore files with the same <a title="Modified Timestamp" style="cursor: pointer;">getMTime();</a> will not be shown.</small></span></caption>
                    <thead>
                      <tr>
                        <th>Restore</th><th>Date</th><th>Filename</th><th>Filesize</th>
                      </tr>
                    </thead>
                    <tbody>
<?php
$iterator = new DirectoryIterator(DB_BACK_PATH);
$FoundFiles = [];

while($iterator->valid()){
  if ($iterator->isDot() && is_file($iterator->getFilename())) {
    $iterator->next();	//if ($file == '.htaccess') continue;
    continue;
  }
  $fileName = NULL; // $iterator->getFilename();
  
  if (preg_match('/^' . $_GET['db'] . '\.sql/i', $iterator->getFilename()))
    $fileName = $iterator->getFilename();

  elseif (preg_match('/^' . $_GET['db'] . '___\(.+\)\.sql/i', $iterator->getFilename()))
    $fileName = $iterator->getFilename();

  if (!$fileName) {
    $iterator->next();
    continue;
  }

  $filetypes = array("sql");
  $filetype = pathinfo($fileName, PATHINFO_EXTENSION);
  if (in_array(strtolower($filetype), $filetypes))
    $FoundFiles[$iterator->getMTime()] = $fileName;

  $iterator->next();
}

if (!empty($FoundFiles)) {
  krsort($FoundFiles); //arsort($FoundFiles);

  $FoundFiles = array_values($FoundFiles);
  foreach ($FoundFiles as $key => $file) {
    preg_match('/^.+\((.+)\)\.\w+$/', $file, $matches);
    if ($matches[1] != date('Y')) @touch('../database/backup/' . $file, strtotime($matches[1] . '-12-31'), strtotime($matches[1] . '-12-31'));
      //exec('touch -d "' . '31 December ' . $matches[1] . '" "../database/backup/' . $file . '"',$output,$worked); // $matches[1] . '-12-31"
    echo '  <tr>' . "\n"
    . '                      <td style="text-align: center;"><input type="radio" name="restore" value="' . $file . '" ' . ($key == 0 ? 'checked' : '') . '></td>' . "\n"
    . '                      <td style="padding-left: 10px; padding-right: 10px;">' . date("Y-m-d", filemtime(DB_BACK_PATH . $file)) .'</td>' . "\n"
    . '                      <td style="padding-left: 10px; padding-right: 10px;">' . "\n"
    . '                        <a href="?' . http_build_query(array_merge(APP_QUERY, array('download'=>$file)), '', '&amp;') . '"><img src="./assets/images/dl_ico.gif" style="vertical-align: middle;" alt="Load Icon" />&nbsp;' . $file . '</a>' . "\n"  
    . '                      </td>' . "\n"
    . '                      <td>' . formatSizeUnits(filesize(DB_BACK_PATH . $file)) . '    </td>' . "\n"
    . '                    </tr>' . "\n";
  }
}
?>
                      </tbody>
                    </table>
                  </form>
                </div>
              </div>
<?php  }
          break;
        case FALSE:
?>
              <div class="card-header" style="background-color: #6FA7D7; color: #000;">An Error has occured:
</div>
              <div class="card-footer"style="background-color: #C6DCEF; font-size: 14px; text-align: justify;">An unknown database `<?=$_GET['db'] ?>` was found trying to install. This error may simply be ignored.
              </div>
<?php
        break;
      }
    else
      if ($ob_contents)
        if ($e->getCode() == 2002) {
?>
              <div class="card-header" style="background-color: #6FA7D7; color: #000;">
              An error has occured: <span style="color: red;">Database Server is Offline.</span>
              </div>
              <div class="card-footer"style="background-color: #C6DCEF; font-size: 14px; text-align: justify;">
<?= $ob_contents; ?>
                <br /><br /><q>MySQL has failed to connect (it maybe offline). This message is displayed as an error has occurred.</q>
                <br /><br />
                <ol>
                  <li>You need to open up services. Right-click taskbar and click <strong>Task Manager</strong></li>
                  <li>Click <strong>Services</strong> tab, and scroll down to find where <strong>MySQL</strong> is.</li>
                  <li>Right-click -&gt; <strong>Start</strong></li>
                  <li>Refresh this page.</li>
                </ol>
              </div>
<?php 
       } else if ($e->getCode() == 1049) {
?>
              <div class="card-header" style="background-color: #6FA7D7; color: #000;">
              An error has occured:  <span style="color: red;">Missing Database</span>
              </div>
              <div class="card-footer"style="background-color: #C6DCEF; font-size: 14px; text-align: justify;">
<?= $ob_contents; ?>
                <br /><br />
                <ol>
                  <li><a href="?db=<?= DB_NAME; ?>">Install `<?= DB_NAME; ?>`</a></li>
                </ol>
              </div>
<?php } } ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery/jquery.min.js"></script>
<script>

function showInstallForm() {
  $( '#recoveryForm' ).slideUp( "slow", function() {
    // Animation complete.
  });
  $( "#recoveryForm" ).css('display') == 'none';
  console.log('testing ');
  
  $( '#installForm' ).slideDown( "slow", function() {
    // Animation complete.
  });
  $( "#installForm" ).css('display') == 'block';
  console.log('testing ');
}

function showRecoveryForm() {
  $( '#installForm' ).slideUp( "slow", function() {
    // Animation complete.
  });
  $( "#installForm" ).css('display') == 'block';
  console.log('testing ');
  $( '#recoveryForm' ).slideDown( "slow", function() {
    // Animation complete.
  });
  $( "#recoveryForm" ).css('display') == 'none';
  console.log('testing ');
}

document.getElementById("q_n").onclick = function() {
  showRecoveryForm();
  document.getElementById("q2_y").checked = true;
  //$(".q_n").trigger('click');
}

document.getElementById("q_y").onclick = function() {
  showInstallForm();
  document.getElementById("q2_n").checked = true;
  //$(".q_n").trigger('click');
}

document.getElementById("q2_n").onclick = function() {
  showInstallForm();
  document.getElementById("q_y").checked = true;
  //$(".q_n").trigger('click');
}

document.getElementById("q2_y").onclick = function() {
  showRecoveryForm();
  document.getElementById("q_n").checked = true;
  //$(".q_n").trigger('click');
}

</script>    
    <script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/bootstrap/bootstrap.min.js"></script>
 
    <script>  
var overflowAuto = document.getElementsByClassName('overflowAuto')[0];

//Get the distance from the top and add 30px for the padding
var maxHeight = overflowAuto.getBoundingClientRect().top + 30;

overflowAuto.style.height = "calc(100vh - " + maxHeight + "px)"; 
    </script>
  </body>
</html>
