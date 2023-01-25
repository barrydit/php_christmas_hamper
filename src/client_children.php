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
       c.`group_size`,
       c.`minor_children`,
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
WHERE h2.id IS NULL AND c.`phone_number_1` LIKE :phone_number
ORDER BY c.`phone_number_1`;
HERE); // ORDER BY sort, hamper_no,  ... hamper_no IS NOT NULL ASC, hamper_no ASC,  

        $stmt->execute(array(
          ":phone_number" => (!empty($_POST['phone_number']) ? $_POST['phone_number'] . '%' : '%'),
        ));
        
        break;
      }
      
      //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, c.`id`, `hamper_id`, `first_name`, `last_name`, c.`phone_number_1`, c.`address`, h.`hamper_no`, YEAR(h.`created_date`) AS h_year, IF(YEAR(h.`created_date`)=' . date('Y') . ',hamper_no,\'\') AS hamper_no FROM `clients` as c LEFT JOIN `hampers` AS h ON c.`id` = h.`client_id` AND c.`hamper_id` = h.`id` WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name ORDER BY hamper_no IS NOT NULL ASC, hamper_no DESC , `last_name` ASC;');

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
       c.`group_size`,
       c.`minor_children`,
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
WHERE  h2.id IS NULL AND `last_name` LIKE :last_name AND `first_name` LIKE :first_name AND c.`minor_children` != ''
ORDER BY `group_size`, `last_name`;
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
       c.`group_size`,
       c.`minor_children`,
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
WHERE h2.id IS NULL AND `last_name` LIKE :last_name AND `first_name` LIKE :first_name ;
HERE);
        $stmt->execute(array(
          ":last_name" => (!empty($full_name[0]) ? $full_name[0] . '%' : '%'),
          ":first_name" => (!empty($full_name[1]) ? $full_name[1] . '%' : '%')
        ));
      
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
       c.`group_size`,
       c.`minor_children`,
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
WHERE h2.id IS NULL AND c.`minor_children` != '' AND IF(IF(YEAR(h1.`created_date`)=YEAR(CURDATE()), h1.`hamper_no`, NULL) IS NULL,1,0) = 0
ORDER BY `group_size`, `last_name`;
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

  <base href="<?=APP_BASE_URL?>" />
  
  <link rel="shortcut icon" type="image/x-icon" href="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/images/favicon.ico" />
  <link rel="shortcut icon" type="image/png" href="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/images/favicon.png" /> 
  
  <link rel="shortcut icon" type="image/png" href="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/css/styles.css" />

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

</style>

</head>
<body>
  <div style="border: 1px solid #000; width: 700px; margin: auto;">
    <div style="padding: 0px 20px 0px 20px;">
      <h3><a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /> Home</a> | <a href="?reports">Reports</a> | <a href="?search">Search</a> &#11106; <a href="?search=clients">Clients</a> : <a href="?search=hampers">Hampers</a>
        <form action="<?=APP_BASE_URI . '?'?>" method="GET" autocomplete="off" style="display: inline; float: right;">
          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>
        </form>
      </h3>
    </div>
    <div style="padding: 0px 20px 10px 20px;">
      Client [ <?= ($client_dup_count > 0 ? '<a href="?client=duplicate"> (<code style="color: red;">' . $client_dup_count . '</code>) Duplicates</a> | ' : '' ) ?><a href="?client=children">Children</a> ]
    </div>
  </div>

  <div style="border: 1px solid #000; width: 700px; margin: 10px auto; height: 55px;">
    <form id="full_name_frm" method="POST" action="<?=APP_BASE_URL . '?' . 'client=children'?>" autocomplete="off">
      <div style="display: table; margin: 0px auto; padding: 15px 0px 15px 0px; width: 98%;">
        <!-- <div style="display: table-cell; padding-left: 10px;">
          Client / <input type="tel" size="14" name="phone_number" value="" style="margin-right: 8px;" title="Format: 123-456-7890" placeholder="(123) 456-7890" />
        </div> -->
        <div style="display: table-cell; text-align: left; padding-left: 10px;">
          <label>Last Name:&nbsp;&nbsp;
            <input id="full_name" type="text" name="q" list="full_names" pattern="[a-zA-Z\W+]{1,64}" placeholder="<click search>"  value="" autofocus=""  oninput="full_name_input()" /> <!-- onclick="this.form.submit();" -->
          </label>
          <datalist id="full_names">
            <option value="" />
          </datalist>&nbsp;&nbsp;&nbsp;
        </div>
        <div style="display: tale-cell; text-align: right; padding-right: 25px;">
          <input type="submit" value="  Search  " style="border: none; cursor: pointer; box-shadow: 0 2px 5px 0 rgb(94, 158, 214); min-width: 90px; border-radius: 2px; padding: 2px 4px; outline: none; border: 1px solid  rgb (94, 158, 214); border-radius:0;"/>
        </div>
      </div>
    </form>
  </div>

  <div class="overflowAuto" style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px; padding: 10px 0px;">
    <table style="margin: 0px auto; width: 675px;">
      <caption style="text-align: left;"><?= count($rows); ?> Clients<?php
if (!empty($rows)) {
  $rows_child = $rows;
  
  $child_count = 0;
  $female_count = 0;
  $male_count = 0;
  $neutral_count = 0;
  
  while($row = array_shift($rows_child)) { //$result = $stmt->fetch() 
    if (empty($row['minor_children'])) continue;
    else $minor_children = explode(',', $row['minor_children']);

    foreach($minor_children AS $child) {
      $gender = $age = NULL;
      if (!is_numeric($child)) list($gender,$age) = sscanf(trim($child), "%[A-Z]%d");
      else list($age) = sscanf(trim($child), "%d");

      if ($gender == 'F')
        $female_count++;
      elseif ($gender == 'M')
        $male_count++;
      else
        $neutral_count++;
    }
  }
  $child_count = $neutral_count + $male_count + $female_count;

  echo ', ' . $child_count . ' Children [' . $male_count . ' Male, ' . $female_count  . ' Female, ' . $neutral_count . ' Gender Neutral]';
 } else {
   echo ' [PRESS <code style="color: red;">SEARCH</code>]';
 }
?></caption>  
      <colgroup>
        <col style="width: 45%;">
        <col style="width: 13%;">
        <col style="width: 42%;">
      </colgroup>
      <thead>
        <tr>
          <th>Name</th>
          <th>Group</th>
          <th>Children</th>
        </tr>
      </thead>
      <tbody>
<?php
if (!empty($rows))
  while($row = array_shift($rows)) { //$result = $stmt->fetch() 
?>
        <tr style="text-indent: 3px; <?=($row['sort'] == '1' ? 'box-shadow: 0px 0px 0px 1px red; background-color: #ffd9dc;' : '')?>">
          <td><a href="?client=<?=$row['id']?>"><?=$row['last_name'] . ', ' . $row['first_name']?></a></td>
          <td style="text-align: center;"><?=$row['group_size']?></td>
          <td style="text-align: right;"><?= ($row['minor_children'] == '' ? '&lt;None&gt;' : $row['minor_children']) ?></td>
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

<script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery/jquery.min.js"></script>
<script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/bootstrap/bootstrap.min.js"></script>
<script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery.inputmask/jquery.inputmask.min.js"></script>
<script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery-mask/jquery.mask.min.js"></script> 
 
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
  url = '<?=APP_BASE_URL . '?' . http_build_query(array('search'=>'clients'))?>&q=' + val;
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


