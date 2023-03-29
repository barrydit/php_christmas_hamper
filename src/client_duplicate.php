<?php
if (!defined('APP_BASE_PATH')) exit('No direct script access allowed');

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    if (isset($_POST['q'])) {
      if (!empty(preg_match('/\s*,\s*/', $_POST['q']))) {
        list($full_name[0],$full_name[1]) = preg_split('/\s*,\s*/', $_POST['q']);
        $full_name[1] = substr($full_name[1], 2);
      } else {
        $full_name = preg_split('/\s*,\s*/', $_POST['q']);
      }

      if (!empty($_POST['phone_number'])) {
        preg_match('/([0-9]{3})-([0-9]{0,3})-([0-9]{0,4})/', $_POST['phone_number'], $matches);
        $_POST['phone_number'] = $matches[1] . ($matches[2] != '' ? '-' . $matches[2] . ($matches[3] != '' ? '-' . $matches[3] : '') : '');
        
        $stmt = $pdo->prepare(<<<HERE
SELECT
    id,
    last_name,
    first_name,
    phone_number_1,
    COUNT(phone_number_1) as count
FROM
    clients
WHERE `phone_number` LIKE :phone_number
GROUP BY phone_number_1
HAVING COUNT(phone_number_1) > 1;
HERE); // ORDER BY sort, hamper_no,  ... hamper_no IS NOT NULL ASC, hamper_no ASC,  

        $stmt->execute(array(
          ":phone_number" => (!empty($_POST['phone_number']) ? $_POST['phone_number'] . '%' : '%'),
        ));
        
        break;
      }
      
      //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, c.`id`, `hamper_id`, `first_name`, `last_name`, c.`phone_number_1`, c.`address`, h.`hamper_no`, YEAR(h.`created_date`) AS h_year, IF(YEAR(h.`created_date`)=' . date('Y') . ',hamper_no,\'\') AS hamper_no FROM `clients` as c LEFT JOIN `hampers` AS h ON c.`id` = h.`client_id` AND c.`hamper_id` = h.`id` WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name ORDER BY hamper_no IS NOT NULL ASC, hamper_no DESC , `last_name` ASC;');

      $stmt = $pdo->prepare(<<<HERE
SELECT
    id,
    last_name,
    first_name,
    phone_number_1,
    COUNT(phone_number_1) as count
FROM
    clients
WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name 
GROUP BY phone_number_1
HAVING COUNT(phone_number_1) > 1;
HERE); // ORDER BY sort, hamper_no,  ... hamper_no IS NOT NULL ASC, hamper_no ASC,  

      //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`hamper_no`, h.`client_id` AS c_id, c.`id`, `hamper_id`, `first_name`, `last_name`, c.`phone_number_1`, c.`address`, YEAR(h.`created_date`) AS h_year, IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) AS hamper_no, IF(IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) IS NULL,1,0) AS sort FROM `clients` as c LEFT JOIN `hampers` AS h ON c.`id` = h.`client_id` AND c.`hamper_id` = h.`id` OR c.`hamper_id` IS NULL WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name ORDER BY `last_name`;'); // ORDER BY sort, hamper_no,

      $stmt->execute(array(
        ":last_name" => (!empty($full_name[0]) ? $full_name[0] . '%' : '%'),
        ":first_name" => (!empty($full_name[1]) ? $full_name[1] . '%' : '%')
      ));
      
      
      break;
      
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results
      
      if (!$rows) {
        $stmt = $pdo->prepare(<<<HERE
SELECT
    id,
    last_name,
    first_name,
    phone_number_1,
    COUNT(phone_number_1) as count
FROM
    clients
WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name 
GROUP BY phone_number_1
HAVING COUNT(phone_number_1) > 1;
HERE);
        $stmt->execute(array(
          ":last_name" => (!empty($full_name[0]) ? $full_name[0] . '%' : '%'),
          ":first_name" => (!empty($full_name[1]) ? $full_name[1] . '%' : '%')
        ));
        
        //die($stmt->debugDumpParams());
      
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results
      }

    } 
    break;
  case 'GET':
    if (!empty($_GET['q'])) {
      header('Content-type: application/json');
      
      if (!empty(preg_match('/\s*,\s*/', $_GET['q'])))
        list($full_name[0],$full_name[1]) = preg_split('/\s*,\s*/', $_GET['q']);
      else
        $full_name = preg_split('/\s*,\s*/', $_GET['q']);

      $stmt = $pdo->prepare("SELECT `last_name`, `first_name` FROM `clients` WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name GROUP BY `last_name`" . (count($full_name) == 2 ? ', `first_name`' : '') . (count($full_name) == 1 ? ' HAVING COUNT(`last_name`) >= 1' : '') . ";");

      $stmt->execute(array(
        ":last_name" => $full_name[0] . '%',
        ":first_name" => (!empty($full_name[1]) ? $full_name[1] . '%' : '%')
      ));

      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $data['results'] = [];
      
      while($row = array_shift($rows)) {
        if (count($full_name) == 1)
          $data['results'][] = array('name' => $row['last_name']); // . ', ' . $row['first_name']
        else
          $data['results'][] = array('name' => $row['last_name'] . ',&nbsp;' . $row['first_name']);
      }

      exit(json_encode($data));
    }

    $stmt = $pdo->prepare(<<<HERE
SELECT
    id,
    last_name,
    first_name,
    phone_number_1,
    COUNT(last_name) as count
FROM
    clients
WHERE last_name != ''
GROUP BY last_name
HAVING COUNT(last_name) > 1;
HERE);
    $stmt->execute(array());

    break;
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=APP_NAME?> -- Client Search</title>

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
  background-color: #E0EDF2;
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

.head {
  position: relative;
  //width: 100%;
  border: 1px solid #222;
  padding: 10px;
  background-color: #fff;
}

.showHideMe {
  cursor: pointer;
  border: 1px dashed #000;
  border-radius: 5px;
  padding: 4px;
  background-color: #fff;
}

.overflowAuto {
  overflow-x: hidden;
  overflow-y: auto;
/*   height: calc(100vh - 163px); */
}

::-webkit-input-placeholder { /* Edge */
  color: #222;
}

</style>

</head>
<body>
  <div style="border: 1px solid #000; width: 700px; margin: auto;">
    <div style="padding: 0px 20px 0px 20px;">
      <h3><a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /> Home</a> | <a href="?reports">Reports</a> | <a href="?search">Search</a> &#11106; <a href="?search=clients">Clients</a> : <a href="?search=hampers">Hampers</a>
        <form action method="GET" autocomplete="off" style="display: inline; float: right;">
          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>
        </form>
      </h3>
    </div>
    <div style="padding: 0px 20px 10px 20px;">
      Client [ <?= ($client_dup_count > 0 ? '<a href="?client=duplicate"> (<code style="color: red;">' . $client_dup_count . '</code>) Duplicates</a> | ' : '' ) ?><a href="?client=children">Children</a> ]
    </div>
  </div>

  <div style="border: 1px solid #000; width: 700px; margin: 10px auto; height: 55px;">
    <form method="POST" action="<?='?search=clients'; ?>" autocomplete="off">
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
          <input type="submit" value="  Search  " style="margin: 2px 0; border: none; cursor: pointer; box-shadow: 0 2px 5px 0 rgba(0, 0, 0, .26); min-width: 90px; border-radius: 2px; padding: 2px 4px;" />
        </div>
      </div>
    </form>
  </div>

  <div class="overflowAuto" style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px; padding: 10px 0px;">
    <table style="margin: 0px auto; width: 675px;">
      <caption style="text-align: left;">Duplicate Client Entries</caption>  
      <colgroup>
        <col style="width: 30%;">
        <col style="width: 50%;">
        <col style="width: 20%;">
      </colgroup>
      <thead>
        <tr>
          <th>ID</th>
          <th>Address</th>
          <th>Phone Number</th>
        </tr>
      </thead>
      <tbody>
<?php
$rows_duplicate = $rows;
$rows_clients = array();

if (!empty($rows))
  while($row = array_shift($rows)) { //$result = $stmt->fetch()
  //  GROUP BY phone_number_1 HAVING COUNT(phone_number_1) > 1;
    $stmt1 = $pdo->prepare('SELECT id, last_name, first_name, phone_number_1, address FROM clients WHERE last_name LIKE :last_name AND first_name LIKE :first_name GROUP BY first_name HAVING COUNT(first_name) > 1;');
    $stmt1->execute(array(
      ":last_name" => (!empty($row['last_name']) ? $row['last_name'] . '%' : '%'),
      ":first_name" => (!empty($row['first_name']) ? $row['first_name'] . '%' : '%'))
    );

    $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results    
    while($row1 = array_shift($rows1)) {
      $rows_clients[] = array(
        'id' => $row1['id'],
        'last_name' => $row1['last_name'],
        'first_name' => $row1['first_name'],
        'phone_number_1' => $row1['phone_number_1'],
        'address' => $row1['address'],
      );
    }
  }
/*
if (!empty($rows_duplicate))
  while($row = array_shift($rows_duplicate)) { //$result = $stmt->fetch()
    $stmt2 = $pdo->prepare('SELECT id, last_name, first_name, phone_number_1, address FROM clients WHERE first_name LIKE :first_name ;'); // GROUP BY phone_number_1 HAVING COUNT(phone_number_1) > 1
    $stmt2->execute(array(":first_name" => (!empty($row['first_name']) ? $row['first_name'] . '%' : '%')));

    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results    
    while($row2 = array_shift($rows2)) {
        $rows_clients[] = array(
          'id' => $row2['id'],
          'last_name' => $row2['last_name'],
          'first_name' => $row2['first_name'],
          'phone_number_1' => $row2['phone_number_1'],
          'address' => $row2['address'],
        );
    }
  }
*/

$rows_clients = array_map("unserialize", array_unique(array_map("serialize", $rows_clients)));

if (!empty($rows_clients))
  foreach ($rows_clients as $client) { ?>
        <tr style="text-indent: 3px; <?=(!empty($row3['sort']) == '1' ? 'box-shadow: 0px 0px 0px 1px red; background-color: #ffd9dc;' : '')?> ">
          <td><a href="?client=<?=$client['id']?>"><?=$client['last_name'] . ', ' . $client['first_name']?></a></td>
          <td style="text-align: right;"><?= ($client['address'] != '' ? $client['address'] :  '')?></td>
          <td style="text-align: center;"><?= ($client['phone_number_1'] == ''  ?  '' : $client['phone_number_1'] ) ?></td>
        </tr>
<?php  }
else { ?>
        <tr style="text-indent: 3px;">
          <td></td>
          <td></td>
          <td></td>
        </tr>
<?php } ?>
      </tbody>
    </table>
  </div>

<script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/jquery/jquery.min.js"></script>
<script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/bootstrap/bootstrap.min.js"></script>
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
  url = '<?=APP_URL_BASE . '?' . http_build_query( array( 'search' => 'clients' ) )?>&q=' + val;
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
  
function get_full_name() { // onSelect="get_package()"

}

$(document).ready(function() {
  $('.showHideMe').click(function() {
    if ($( ".head" ).css('display') == 'none') {
      $('.showHideMe').html("Export *.XLSX &#9650;");
      $( '.head' ).slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('.showHideMe').html("Export *.XLSX &#9660;");
      $( ".head" ).slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });
});

jQuery(document).ready(function() {
  jQuery('input[type="tel"]').inputmask({
    "mask": "(999) 999-9999",
    removeMaskOnSubmit: true,
    onUnMask: function(maskedValue, unmaskedValue) {
      newvalue = unmaskedValue.replace(/(\d{3})(\d{0,3})(\d{0,3})/, function(match, p1, p2, p3) {
        return p1 + "-" + p2 + "-" + p3;
      });
      return newvalue;
    }
  });
});
    </script>
</body>
</html>


