<?php
/*
  "1.0.1":{
    "0":{
      "title": "",
      "task": {
        "0":""
      },
      "status": "",
      "feature": ""
    }
  },
*/


defined('APP_PATH') // $_SERVER['DOCUMENT_ROOT']
  or define('APP_PATH', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);

include('../config/config.php');

$release_notes = json_decode(<<<JSON
{
  "1.0.1":{
    "0":{
      "title": "Base Code Upgrades",
      "task": {
        "0":"Configuration files Necessary for the operation of the PHP Application, needed updated information for the correct operation of the application."
      },
      "status": "",
      "feature": ""
    }
  },
  "1.0.0":{
    "0":{
      "title": "Main Index (Home / Search)",
      "task": {
        "0":"Create a front page to access the application in steps. The front page consists of links to other parts including client listing and their corrsponding hampers. You can view and visit the top 5 previously (recently) modified clients and hampers. It also includes the client search toolbar.<br />(Phone search is not enabled.)"
      },
      "status": "Done",
      "feature": ""
    },
    "1":{
      "title": "Reports",
      "task": {
        "0":"Create a separate page where by the hampers could be shown, but also allow for multiple filter criterias to be performed, as well as export the list into a single worksheet (XLS), using the shown column names. It also has a separate page where it can show numeric statistics such as Groups and PU/Delivery, as well as children age/gender stats."
      },
      "status": "Done",
      "feature": ""
    },
    "2":{
      "title": "Clients",
      "task": {
        "0":"Create a page where existing clients can be searched, listed, and modified. Priority Listing is enabled with clients, so new hampers that are made for the CURR_YEAR, will show up on the list sooner (on top). This will be obvious as they will be displaying hamper numbers. It can also be exported to a worksheet (XLS), this particular export h/e will include all the column fields in the db->table. This is the best client backup feature so far.",
        "1":"Create a page where by an existing, or a new entry form, can allow for modifiable values and through the process of updating, create (by option) a new hamper for that CURR_YEAR. If the hamper however already exists, and is recent, then no new hamper will occure. However, if any updates are made to the clients, the information is there for also applied to the associatve hamper.<br />Attention: <b>If a client is deleted from the application, then their associative hampers will also be deleted. Hampers need a client, and a client needs hampers.</b>"
      },
      "status": "Done",
      "feature": "Calculate childrens ages automatically."
    },
    "3":{
      "title": "Hampers",
      "task": {
        "0":"Create a page where hampers can be displayed, based on filter criteria either of Groups or Year. When this page is exported, it is split into 4 groups inside worksheets (XLS). However, if a particular group is choosen, it will only create a single worksheet (XLS)",
        "1":"Create a page where hampers are stored/updated, however client information (part of the hamper) is updated on the previous page."
      },
      "status": "Done",
      "feature": "Create printable labels for the hampers."
    }
  }
}
JSON
, true);


(empty($_GET['v']))
  and $_GET['v'] = ''; //array_key_last($release_notes); // '1.0.0'

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Christmas Hamper -- Release Notes <?=(!empty($_GET['v']) ? '(v' . $_GET['v'] . ')' : '')?></title>

    <link rel="shortcut icon" href="favicon.ico" />

    <!-- BOOTSTRAP STYLES-->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/bootstrap.min.css" />
    
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />

<style>
body {
  background-color: #E4F2E0;
}

a {
  color: #0066CC;
  text-decoration: none;
}

div {
  /* background-color: #FFF; */
}
</style>

</head>
<body>
    <div class="container">
      <div class="card card-primary">
        <div class="card-header">
          <div style="float: left; padding-top: 5px;">
		    <a href="./" style="text-decoration: none;">
			  <img src="assets/images/favicon.png" width="32" height="32" /> Christmas Hamper</a> -
			<a href="<?=basename(__FILE__)?>">Release Notes <?=(!empty($_GET['v']) ? '(v' . $_GET['v'] . ')' : '')?></a></div>
          <div style="float: right;">
            <form action method="GET">
              <span>Release Notes</span>
              <select name="v" onchange="this.form.submit();">
                <option value="" <?= (empty($_GET['v']) ? 'selected="selected"' : '')?>>--</option>
<?php foreach ($release_notes as $key => $version) { ?>
                <option value="<?=$key?>" <?= (!empty($_GET['v']) && $_GET['v'] == $key ? 'selected="selected"' :'')?>><?=$key?></option>
<?php } ?>
              </select>
              <a class="btn btn-primary" href="?login" style="">Login</a>
            </form>
          </div>
          <div class="clearfix"></div>
        </div>
        <div class="card-body" style="background-color: rgba(0,0,0,.03);">
          <div class="row">
            <div class="overflowAuto">
<?php if (!empty($release_notes[$_GET['v']])) { ?>
<?php foreach ($release_notes[$_GET['v']] as $note) { ?>
              <div class="card-header" style="background-color: #6FA7D7; color: #000;">
<?=$note['title']?></div>
              <ul class="card-footer" style="background-color: #C6DCEF; font-size: 14px; text-align: justify;">
<?php foreach ($note['task'] as $task) { ?>
                <span style="font-weight: bolder;">Task</span>: <?=$task?><br /><br />
<?php } ?>
                <span style="font-weight: bolder;">Status</span>: <?=$note['status'] . "<br />\n"?>
                <?php if (!empty($note['feature'])) { ?><span style="font-weight: bolder;">Feature</span>: <?= $note['feature']; } ?>
              </ul>

<?php } ?>
              <div style="margin: 0 auto; text-align: center; width: 100%;"><img src="https://user-images.githubusercontent.com/6217010/214609801-8e2ce2c6-28a1-4e52-9c4f-e9cae5c2be5e.gif" width="620" height="500" /></div>
<?php } else { ?>
              <div class="card-header" style="background-color: #6FA7D7; color: #000;">Build a (reusable) Christmas Hamper</div>
              <ul class="card-footer" style="background-color: #C6DCEF; font-size: 14px; text-align: justify;">
                <span style="font-weight: bolder;">Task</span>: Create a database and interface to guide the user through the process of generating a (new) client and associative hamper for that year. The application should be able to backup and clear the hampers table for each year, while holding onto the clients tabular data.<br /><br /><span style="float: right;">[Lorraine Dick -> <a href="https://github.com/barrydit/php_christmas_hamper/blob/main/resource/Quick%20Start.pdf">Quick Start.pdf</a>]</span><br />
                <span style="font-weight: bolder;">Status</span>: Completed. Dec. 2022
                <span style="float: right;">[GitHub <a href="http://github.com/barrydit/php_christmas_hamper">v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?></a>] [Barry Dick &lt;<a href="mailto:barryd.it@gmail.com">barryd.it@gmail.com</a>&gt;]</span><br />
              </ul>
              <div style="margin: 0 auto; text-align: center; width: 100%;"><img src="https://user-images.githubusercontent.com/6217010/228450154-130751f9-54e4-4081-bcba-f54cd358f4a9.gif" /></div>
<?php } ?>

            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="assets/js/jquery/jquery.min.js"></script>
    
    <script src="assets/js/bootstrap/bootstrap.min.js"></script>
 
    <script>  
var overflowAuto = document.getElementsByClassName('overflowAuto')[0];

//Get the distance from the top and add 30px for the padding
var maxHeight = overflowAuto.getBoundingClientRect().top + 30;

overflowAuto.style.height = "calc(100vh - " + maxHeight + "px)"; 
    </script>
  
</body>
</html>