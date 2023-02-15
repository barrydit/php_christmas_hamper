<?php
/*
switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':

    break;
}
*/
$json = file_get_contents('session/sessions.json', true);
$json_decode = json_decode($json, true);

$visitor_count = 0;
$user_count = 0;

foreach($json_decode as $key => $file) {
  if (!empty($file['user_id']) || is_numeric($file['user_id'])) $user_count++;
  elseif (!empty($file['visitor_id']) || is_numeric($file['visitor_id'])) $visitor_count++;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=APP_NAME?> -- Search</title>

  <base href="<?=(!defined('APP_URL_BASE') ? 'http://' . APP_DOMAIN . APP_URL_PATH : APP_URL_BASE )?>" />
  
  <link rel="shortcut icon" type="image/x-icon" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/images/favicon.ico" />
  <link rel="shortcut icon" type="image/png" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/images/favicon.png" />
  
  <link rel="shortcut icon" type="image/png" href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/css/styles.css" />

<style>
html, body {
  height: 100vh;
  margin: 0px;
  font-family: 'Open Sans', sans-serif;
  font-size: 12px;
  overflow: hidden;
}

body {
  background-color: #E4F2E0; // #E0EDF2, #EEE0F2, #F2E5E0 
}

a {
  color: #0066CC;
  text-decoration: none;
}

div {
  background-color: #FFF;
}

table, td {
  font-size: 13px;
  margin: 0px auto;
  border: 1px solid black; 
  border-collapse: collapse;
}

td {
  padding: 5px;	
}

#foo {
  position: fixed;
  bottom: 0;
  right: 0;
}

.overflowAuto {
  overflow-x: hidden;
  overflow-y: auto;
/*   height: calc(100vh - 163px); */
}
</style>

</head>
<body>
  <?= /* $ob_contents; */ NULL; ?>
  <div id="foo">
    <table style="width: 100px;">
      <thead>
        <tr>
          <th>Users:</th>
          <th>Visitors:</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="text-align: center;"><?=$user_count?></td>
          <td style="text-align: center;"><?=$visitor_count?></td>
        </tr>
      </tbody>
    </table>
  </div>
<?php
if (!extension_loaded('gd')) { ?>
  <div style="width: 100%; background-color: #F08A80;">
    <div style="padding: 2px; width: 696px; margin: auto; background-color: #E8CFC0;">PHP Extension: <b>gd</b> must be loaded inorder to export to xls (PHPSpreadsheet).</div>
  </div>
<?php } ?>
  <div style="border: 1px solid #000; width: 700px; margin: auto;">
    <div style="padding: 0px 20px 0px 20px;">
      <h3><a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /></a> Home | <a href="?reports">Reports</a> | Search &#11106; <a href="?search=clients" style="text-decoration: none;">Clients</a> : <a href="?search=hampers" style="text-decoration: none;">Hampers</a>
        <form style="float: right;" action="<?=APP_URL_PATH . '?'?>" autocomplete="off" method="GET">
          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>
        </form>
        <form style="float: right; margin-right: 10px;" action="<?='?'?>" autocomplete="off" method="GET">
          <button type="submit" name="db" value="<?= DB_NAME[0]; ?>" style="float: right; width: 7em;">Database</button>
        </form>
      </h3>
    </div>
  </div>
  
 
  <div style="border: 1px solid #000; width: 700px; margin: 20px auto; height: 55px;">
    <form id="full_name_frm" method="POST" action="<?=APP_URL_BASE . '?' . http_build_query( array( 'search' => 'clients' ))?>" autocomplete="off">
      <div style="display: table; margin: 0px auto; padding: 15px 0px 15px 0px; width: 98%;">
        <!-- <div style="display: table-cell; padding-left: 10px;">
          Client / <input type="tel" size="14" name="phone_number" value="" style="margin-right: 8px;" title="Format: 123-456-7890" placeholder="(123) 456-7890" />
        </div> -->
        <div style="display: table-cell; text-align: left; padding-left: 10px;">
          <label>Last Name:&nbsp;&nbsp;
            <input id="full_name" type="text" name="q" list="full_names" pattern="[a-zA-Z\W+]{1,64}" placeholder=""  value="" autofocus=""  oninput="full_name_input()" /> <!-- onclick="this.form.submit();" -->
          </label>
          <datalist id="full_names">
            <option value="" />
          </datalist>&nbsp;&nbsp;&nbsp;
        </div>
        <div style="display: tale-cell; text-align: right; padding-right: 25px;">
          <input type="submit" value="  Search  " style="border: none; cursor: pointer; box-shadow: 0 2px 5px 0 rgb(94, 158, 214); min-width: 90px; border-radius: 2px; padding: 2px 4px; outline: none; border: 1px solid  rgb (94, 158, 214); border-radius:0;" />
        </div>
      </div>
    </form>
  </div>

  <!-- <div style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px;">
    <div style="padding: 0px 20px 0px 20px;">
      <h4>New Client | New Hamper</h4>
    </div>
  </div> -->

  <div class="overflowAuto" style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px; padding: 10px 0px;">
<?php
//$stmt = $pdo->prepare('SELECT c.`id`, `hamper_id`, `first_name`, `last_name`, c.`phone_number_1`, c.`address`, h.`hamper_no` FROM `clients` as c LEFT JOIN `hampers` as h ON c.`id` = h.`client_id` AND c.`hamper_id` = h.`id` ORDER BY h.`id` DESC LIMIT 5;'); // WHERE c.`created_date` >= ( CURDATE() - INTERVAL 30 DAY ) 

//$stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`hamper_no`, h.`client_id` AS c_id, c.`id`, `hamper_id`, `first_name`, `last_name`, c.`phone_number_1`, c.`address`, YEAR(h.`created_date`) AS h_year, IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) AS hamper_no, IF(IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) IS NULL,1,0) AS sort FROM `clients` as c LEFT JOIN `hampers` AS h ON c.`id` = h.`client_id` WHERE c.`hamper_id` = h.`id` OR c.`hamper_id` IS NULL ORDER BY h.`id` DESC LIMIT 5;');

$stmt = $pdo->prepare(<<<HERE
SELECT h1.`id`                                                  AS h_id,
       h1.`client_id`                                           AS c_id,
       IF(YEAR(h1.`created_date`) = YEAR(CURDATE()), h1.`hamper_no`, NULL) AS hamper_no,
       YEAR(h1.`created_date`)                                  AS h_year,
       c.`id`,
       `hamper_id`,
       `last_name`,
       `first_name`,
       c.`phone_number_1`,
       c.`address`,
       IF(IF(YEAR(h1.`created_date`)=YEAR(CURDATE()), h1.`hamper_no`, NULL) IS NULL,1,0) AS sort
FROM `clients` AS c
         LEFT JOIN `hampers` AS h1
              ON c.`id` = h1.`client_id`
         LEFT OUTER JOIN `hampers` AS h2
                         ON (
                                     c.id = h2.client_id
                                 AND
                                     (
                                                 h1.created_date < h2.created_date
                                             OR
                                                 (
                                                             h1.created_date = h2.created_date
                                                         AND h1.id < h2.id
                                                     )
                                         )
                             )
WHERE h2.id IS NULL
ORDER BY h1.`id` DESC LIMIT 5;
HERE); // ORDER BY sort, hamper_no,  ... hamper_no IS NOT NULL ASC, hamper_no ASC,  

$stmt->execute(array());
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>  
    <table style="margin: 0px auto; width: 675px;">
      <caption style="text-align: left;">Recent (5) Client Entries</caption>
      <colgroup>
        <!-- <col style="width: 8%;"> -->
        <col style="width: 25%;">
        <col style="width: 33%;">
        <col style="width: 12%;">
        <col style="width: 3%;">
      </colgroup>
      <thead>
        <tr>
          <!-- <th>Client</th> -->
          <th>Name</th>
          <th>Address</th>
          <th>Phone #</th>
          <th>Hamper</th>
        </tr>
      </thead>
      <tbody>
<?php if (!empty($rows))
  while($row = array_shift($rows)) { //$result = $stmt->fetch() 
    if ($row['h_year'] == date('Y'))
      if ($row['h_id'] == $row['hamper_id'] && $row['id'] == $row['c_id']) { ?>
        <tr style="text-indent: 3px;">
          <td><a style="font-weight: bold;" href="?client=<?=$row['id']?>"><?=$row['last_name'] . ', ' . $row['first_name']?></a></td>
          <td><?=$row['address']?></td>
          <td style="text-align: center;"><?=$row['phone_number_1']?></td>
          <td style="text-align: center;"><a href="?hamper=<?=$row['h_id']?>"><?= $row['hamper_no'] ?></a></td>
        </tr>
<?php } else {
        $stmt = $pdo->prepare('SELECT `hamper_no` FROM `hampers` WHERE YEAR(`created_date`) = ? AND `client_id`= ? LIMIT 1;');
        $stmt->execute([date('Y') , $row['id']]);
        $row_err_lookup = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($row_err_lookup) { ?>
        <tr style="text-indent: 3px;">
          <td><a style="font-weight: bold;" href="?client=<?=$row['id']?>"><?=$row['last_name'] . ', ' . $row['first_name']?></a></td>
          <td><?=$row['address']?></td>
          <td style="text-align: center;"><?=$row['phone_number_1']?></td>
          <td style="text-align: center;"><a href="?client=<?=$row['id']?>" style="color: red;" alt="Client missing hamper_id"><?= $row['hamper_no'] ?></a></td>
        </tr>
<?php  }
      }
    else { ?>
        <tr style="text-indent: 3px;">
          <td><a style="font-weight: bold;" href="?client=<?=$row['id']?>"><?=$row['last_name'] . ', ' . $row['first_name']?></a></td>
          <td><?=$row['address']?></td>
          <td style="text-align: center;"><?=$row['phone_number_1']?></td>
          <td style="text-align: center;"><a href="?hamper=<?=$row['h_id']?>"><?= $row['hamper_no'] ?></a></td>
        </tr>
<?php  }
  }
else { ?>
        <tr style="text-indent: 3px;">
          <!-- <td style="text-align: center;"><a href="?client=<?=/*$row['id']*/ NULL;?>"><?=/*$row['id']*/ NULL;?></a></td> -->
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
<?php } ?>
      </tbody>
    </table>
    <br /><hr />
<?php
$stmt = $pdo->prepare('SELECT `id`, `hamper_no`, `transport_method`, `group_size`, `phone_number_1`, `address` FROM `hampers` ORDER BY `id` DESC LIMIT 5;'); // WHERE `created_date` >= ( CURDATE() - INTERVAL 30 DAY ) 
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>  
    <table style="margin: 0px auto; width: 675px;">
      <caption style="text-align: left;">Recent (5) Hamper Entries</caption>
      <colgroup>
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 15%;">
        <col style="width: 55%;">
      </colgroup>
      <thead>
        <tr>
          <th>Hamper</th>
          <th>Delivery</th>
          <th>Group</th>
          <th>Phone #</th>
          <th>Address</th>
        </tr>
      </thead>
      <tbody>
<?php if (!empty($rows)) {
  while($row = array_shift($rows)) { //$result = $stmt->fetch() ?>
        <tr style="text-indent: 3px;">
          <td style="text-align: center;"><a href="?hamper=<?=$row['id']?>"><?=$row['hamper_no']?></a></td>
          <td><?=$row['transport_method']?></td>
          <td><?=$row['group_size']?></td>
          <td><?=$row['phone_number_1']?></td>
          <td><?=$row['address']?></td>
        </tr>
<?php } 
} else { ?>
        <tr style="text-indent: 3px;">
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
<?php } ?>
      </tbody>
    </table>
  </div>
  
<script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery/jquery.min.js"></script>
    
<script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery/jquery.min.js"></script>
<script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery.inputmask/jquery.inputmask.min.js"></script>
<script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery-mask/jquery.mask.min.js"></script> 
  
<script>
var overflowAuto = document.getElementsByClassName('overflowAuto')[0];

//Get the distance from the top and add 30px for the padding
var maxHeight = overflowAuto.getBoundingClientRect().top + 30;

overflowAuto.style.maxHeight = "calc(100vh - " + maxHeight + "px)";
document.querySelector("#full_name").addEventListener('keyup', function (e) {
  var val = document.getElementById("full_name").value;
  var url, packagesOption;
  var start = e.target.selectionStart;
  var end = e.target.selectionEnd;
  e.target.value = e.target.value.toUpperCase();
  e.target.setSelectionRange(start, end);
  url = '<?=APP_URL_BASE . '?' . http_build_query( array( 'search' => 'clients' ))?>&q=' + val;
  document.getElementById('full_names').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    packagesOption = "<option value=\"" + val + "\" />";
    $('#full_names').append(packagesOption);
    $(data.results).each(function() {
      packagesOption = "<option value=\"" + this.name + "\" />";
      $('#full_names').append(packagesOption);
      //console.log(this.favers);
    });
  });
});

function full_name_input() {
  var val = document.getElementById("full_name").value;
  var opts = document.getElementById('full_names').childNodes;
  for (var i = 0; i < opts.length; i++) {
    if (opts[i].value === val) {
      // An item was selected from the list!
      // yourCallbackHere()
      //alert(opts[i].value);
      full_name_form = document.getElementById ('full_name_frm');
      full_name_form.submit();
      break;
    }
  }
}

function forceInputUppercase(e)
{
  var start = e.target.selectionStart;
  var end = e.target.selectionEnd;
  e.target.value = e.target.value.toUpperCase();
  e.target.setSelectionRange(start, end);
}

jQuery(document).ready(function() {
  jQuery('input[type="tel"]').inputmask({
    "mask": "(999) 999-9999",
    removeMaskOnSubmit: true,
    onUnMask: function(maskedValue, unmaskedValue) {
      newvalue = unmaskedValue.replace(/(\d{3})(\d{0,3})(\d{0,4})/, function(match, p1, p2, p3) {
        return p1 + "-" + p2 + "-" + p3;
      });
      return newvalue;
    }
  });
});
</script>  
  
</body>
</html>
