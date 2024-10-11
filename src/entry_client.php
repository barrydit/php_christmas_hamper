<?php

$setting['update_prev_hamper_year'] = false;
$setting['auto_display_hamper_check'] = true;
$setting['str_pad_hamper_no'] = 3;


if (isset($_REQUEST['client'])) {
  if ($_REQUEST['client'] == '')
    $_SESSION['client_id'] = NULL;
  elseif (is_string($_REQUEST['client'])) {
    if (ctype_digit($_REQUEST['client'])) {
      $stmt = $pdo->prepare("SELECT `id`, `minor_children`, `created_date` FROM `clients` WHERE `id` = :id LIMIT 1;");
      $stmt->execute([
        ":id" => filter_var($_REQUEST['client'], FILTER_VALIDATE_INT)
      ]);
      $row = $stmt->fetch();
      
      if (!empty($row))
        $_SESSION['client_id'] = $_REQUEST['client'] = filter_var( $_REQUEST['client'], FILTER_VALIDATE_INT);
      else
        exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([])));        
    } else
      $_SESSION['client_id'] = intval($_REQUEST['client']);
  }
} else $_SESSION['client_id'] = NULL;

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    $_POST["minor_children_old"] = (!isset($row['minor_children']) ?: $row['minor_children']);
    if (empty($_POST["minor_children"]) && !isset($_POST["minor_children"]) || $_POST["minor_children"] == '') {
      $_POST["minor_children"] = NULL;
      if (!empty($_POST["children_gender"]) && !empty($_POST["children_age"])) {
        $children = array_map(function($gender, $age){ return $gender . $age; }, $_POST["children_gender"], $_POST["children_age"]); // array_combine($_POST["children_gender"], $_POST["children_age"]);
        foreach($children as $key => $value) {
          $_POST["minor_children"] .= $value . ($key === array_key_last($children) ? '' : ',');
        }
      }
    }
    
    if (@$_POST["last_name"] == '' || @$_POST["first_name"] == '')
      break;

    $stmt = $pdo->prepare("SELECT `id` FROM `clients` WHERE `id` = :client_id LIMIT 1;");
    $stmt->execute([
      ":client_id" => $_POST["client_id"]
    ]);
    $row = $stmt->fetch();

    if (empty($row)) {
      $stmt = $pdo->prepare(<<<QUERY
SELECT a.id + 1 AS start, MIN(b.id) - 1 AS end
FROM `clients` AS a, `clients` AS b
WHERE a.id < b.id AND a.id >= 1 AND b.id <= (SELECT id FROM `clients` ORDER BY id DESC LIMIT 1)
GROUP BY a.id HAVING start < MIN(b.id) LIMIT 1;
QUERY
);
      $stmt->execute([]);
      $row_recycle = $stmt->fetch();
    
      $stmt = $pdo->prepare("INSERT IGNORE INTO `clients` (`id`, `hamper_id`, `last_name`, `first_name`, `phone_number_1`, `phone_number_2`, `address`, `group_size`, `minor_children`, `diet_vegetarian`, `diet_gluten_free`, `pet_cat`, `pet_dog`, `notes`, `active_status`, `bday_date`, `modified_date`, `created_date`) VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
      $stmt->execute([
        (!empty($row_recycle) ? $row_recycle['start'] : NULL),
        (!empty($_POST["last_name"]) ? $_POST["last_name"] : ''),
        (!empty($_POST["first_name"]) ? $_POST["first_name"] : ''),
        (!empty($_POST["phone_number_1"]) ? $_POST["phone_number_1"] : ''),
        (!empty($_POST["phone_number_2"]) ? $_POST["phone_number_2"] : ''),
        (!empty($_POST["address"]) ? $_POST['address'] : ''),
        (!empty($_POST["group_size"]) ? $_POST["group_size"] : NULL),
        (!empty($_POST["minor_children"]) ? $_POST["minor_children"] : ''),
        (!empty($_POST["diet_vegetarian"]) ? '1' : '0'),
        (!empty($_POST["diet_gluten_free"]) ? '1' : '0'),
        (!empty($_POST["pet_cat"]) ? '1' : '0'),
        (!empty($_POST["pet_dog"]) ? '1' : '0'),
        (!empty($_POST["notes"]) ? $_POST["notes"] : ''),
        $_POST["active"] = 0,
        date('Y-m-d'),
        date('Y-m-d'),
        date('Y-m-d')
      ]);
        
      //$stmt = $pdo->prepare("SELECT `id` FROM `clients` ORDER BY `id` DESC LIMIT 1;");
      //if ($stmt->execute([])) $row = $stmt->fetch();
      $_SESSION['client_id'] = $pdo->lastInsertId();
    } else {
      if (!empty($_POST["client_delete"]) && $_POST["client_delete"] == 'yes') {
        $stmt = $pdo->prepare('DELETE FROM `clients` WHERE `clients`.`id` = :client_id');
        $stmt->execute([
          ":client_id" => (!empty($_POST["client_id"]) ? $_POST["client_id"] : NULL)
        ]);

        $stmt = $pdo->prepare('DELETE FROM `hampers` WHERE `hampers`.`client_id` = :client_id');
        $stmt->execute([
          ":client_id" => (!empty($_POST["client_id"]) ? $_POST["client_id"] : NULL)
        ]);
  
        exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
          'client' => $_POST['client_id']
        ])));

      }

      $stmt = $pdo->prepare("UPDATE `clients` SET `hamper_id` = :hamper_id, `last_name` = :last_name, `first_name` = :first_name, `phone_number_1` = :phone_number_1, `phone_number_2` = :phone_number_2, `address` = :address, `group_size` = :group_size, `minor_children` = :minor_children, `diet_vegetarian` = :diet_vegetarian, `diet_gluten_free` = :diet_gluten_free, `pet_cat` = :pet_cat, `pet_dog` = :pet_dog, `notes` = :notes, `active_status` = :active_status, " . ($_POST['minor_children'] == $_POST['minor_children_old'] ? '' : ' `bday_date` = "' . date('Y-m-d') . '",') . " `modified_date` = :modified_date WHERE `clients`.`id` = :client_id;");

      $stmt->execute([
        ":hamper_id" => (!empty($_POST['hamper_id']) ? $_POST['hamper_id'] : NULL),
        ":last_name" => (!empty($_POST['last_name']) ? $_POST['last_name'] : ''),
        ":first_name" => (!empty($_POST['first_name']) ? $_POST['first_name'] : ''),
        ":phone_number_1" => (!empty($_POST['phone_number_1']) ? $_POST['phone_number_1'] : ''),
        ":phone_number_2" => (!empty($_POST["phone_number_2"]) ? $_POST["phone_number_2"] : ''),
        ":address" => (!empty($_POST['address']) ? $_POST['address'] : ''),
        ":group_size" => (!empty($_POST['group_size']) ? $_POST['group_size'] : ''),
        ":minor_children" => (!empty($_POST['minor_children']) ? $_POST['minor_children'] : ''),
        ":diet_vegetarian" => (!empty($_POST['diet_vegetarian']) ? '1' : '0'),
        ":diet_gluten_free" => (!empty($_POST['diet_gluten_free']) ? '1' : '0'),
        ":pet_cat" => (!empty($_POST["pet_cat"]) ? '1' : '0'),
        ":pet_dog" => (!empty($_POST["pet_dog"]) ? '1' : '0'),
        ":notes" => (!empty($_POST["notes"]) ? $_POST["notes"] : ''),
        ":active_status" => (!empty($_POST['active_status']) ? $_POST['active_status'] : 1),
        ":modified_date" => date('Y-m-d'),
        ":client_id" => $_GET['client']
      ]);

      $_SESSION['client_id'] = $_GET['client'];
/*
        $stmt = $pdo->prepare('SELECT `hamper_no` FROM `hampers` WHERE `group_size` = "' . $_POST['group_size'] . '" AND YEAR(`created_date`) = "' . date('Y') . '" ORDER BY `hamper_no` DESC LIMIT 1;');
        if ($stmt->execute([])) $row = $stmt->fetch();
        if (!empty($row)) {
          list($alpha,$numeric) = sscanf($row['hamper_no'], "%[A-Z]%d");
          $numeric++;
        } else {
          list($alpha,$numeric) = sscanf($_POST['group_size'][0], "%[A-Z]%d");
          $numeric++;
        }
*/
      if (empty($_POST['create_hamper']))
        if (!empty($_POST['hamper_id']) && (int) $_POST['hamper_id']) {
          $stmt = $pdo->prepare("SELECT `id`, `address`, `group_size` FROM `hampers` WHERE `id` = :hamper_id " . (! $setting['update_prev_hamper_year'] ? ' AND YEAR(`created_date`) = "' . date('Y') . '"' : '') . " LIMIT 1;");
          $stmt->execute([":hamper_id" => $_POST['hamper_id']]);
          $row_hamper = $stmt->fetch();
          if (!empty($row_hamper)) {
            if ($row_hamper['group_size'] != $_POST['group_size']) {
              $stmt = $pdo->prepare('SELECT `hamper_no` FROM `hampers` WHERE `group_size` = "' . $_POST['group_size'] . '" AND YEAR(`created_date`) = "' . date('Y') . '" ORDER BY `hamper_no` DESC LIMIT 1;');
              $stmt->execute([]);
              $row = $stmt->fetch();
              if (!empty($row)) {
                $stmt = $pdo->query('SELECT COUNT(*) FROM `hampers` WHERE `group_size` = "' . $_POST['group_size'] . '" AND YEAR(`created_date`) = "' . date('Y') . '";');
                $row_count = $stmt->fetchColumn();
        
                $stmt = $pdo->prepare('SELECT `hamper_no` FROM `hampers` WHERE `group_size` = "' . $_POST['group_size'] . '" AND YEAR(`created_date`) = "' . date('Y') . '" ORDER BY `hamper_no` ASC;');
                $stmt->execute([]);
          
                $i = 0;
                $hamper_no = NULL; 
                while($row = $stmt->fetch()) {
                  $i++;
                  //echo $row['hamper_no'] . ' == row hamper_no' . "<br />\n";
                  list($alpha,$numeric) = sscanf($row['hamper_no'], "%[A-Z]%d");
                  if ($numeric > $i) {
                    $hamper_no = $alpha . str_pad($i, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT);
                    break;
                  }
                  if ($row_count == $i)
                    $hamper_no = $alpha . str_pad($i + 1, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT);
                  else 
                    $hamper_no = $alpha . str_pad($i, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT);
                  if ($i != $numeric)
                    break;
                }
          
                if (!empty($hamper_no))
                  [$alpha, $numeric] = sscanf($hamper_no, "%[A-Z]%d"); 
                else {
                  [$alpha, $numeric] = sscanf($row['hamper_no'], "%[A-Z]%d");
                  $numeric++;   
                }
                
              } else {
                [$alpha, $numeric] = sscanf($_POST['group_size'][0], "%[A-Z]%d");
                $numeric++;
              }
              $stmt = $pdo->prepare("UPDATE `hampers` SET `hamper_no` = :hamper_no WHERE `hampers`.`id` = :hamper_id;");
              $stmt->execute([
                ":hamper_no" => $alpha . str_pad($numeric, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT),
                ":hamper_id" => $row_hamper['id']
              ]);
            }
                
            $stmt = $pdo->prepare("UPDATE `hampers` SET `transport_method` = :transport_method, `phone_number_1` = :phone_number_1, `phone_number_2` = :phone_number_2, `address` = :address, `group_size` = :group_size, `minor_children` = :minor_children, `diet_vegetarian` = :diet_vegetarian, `diet_gluten_free` = :diet_gluten_free, `pet_cat` = :pet_cat, `pet_dog` = :pet_dog WHERE `hampers`.`id` = :hamper_id;");
            $stmt->execute([
              ":transport_method" => (!empty($_POST['transport_method']) ? $_POST['transport_method'] : ''),
              ":phone_number_1" => (!empty($_POST['phone_number_1']) ? $_POST['phone_number_1'] : ''),
              ":phone_number_2" => (!empty($_POST['phone_number_2']) ? $_POST['phone_number_2'] : ''),
              ":address" => (empty($row_hamper['address']) ? (!empty($_POST['address']) ? (!empty($_POST['transport_method'] && $_POST['transport_method'] != 'DELIVERY') ? '' : $_POST['address']) : '') : (!empty($_POST['transport_method'] && $_POST['transport_method'] != 'DELIVERY') ? '' : $row_hamper['address'])),
              ":group_size" => (!empty($_POST['group_size']) ? $_POST['group_size'] : ''),
              ":minor_children" => (!empty($_POST['minor_children']) ? $_POST['minor_children'] : ''),
              ":diet_vegetarian" => (!empty($_POST['diet_vegetarian']) ? '1' : '0'),
              ":diet_gluten_free" => (!empty($_POST['diet_gluten_free']) ? '1' : '0'),
              ":pet_cat" => (!empty($_POST["pet_cat"]) ? '1' : '0'),
              ":pet_dog" => (!empty($_POST["pet_dog"]) ? '1' : '0'),
              ":hamper_id" => $_POST['hamper_id']
            ]);
          }
        }
    }

    if (!empty($_POST['create_hamper']) && $_POST['create_hamper'] == 'true') {
      $stmt = $pdo->prepare("SELECT `id` FROM `hampers` WHERE `client_id` = :client_id AND YEAR(`created_date`) = :hamper_year LIMIT 1;");
      $stmt->execute([
        ':client_id' => $_SESSION['client_id'],
        ':hamper_year' => date('Y')
      ]);
      $row = $stmt->fetch();
      if (empty($row)) {
        $stmt = $pdo->prepare('SELECT `hamper_no` FROM `hampers` WHERE `group_size` = "' . $_POST['group_size'] . '" AND YEAR(`created_date`) = "' . date('Y') . '" ORDER BY `hamper_no` DESC LIMIT 1;');
        $stmt->execute([]);
        $row = $stmt->fetch();
        if (!empty($row)) {
        
          $stmt = $pdo->query('SELECT COUNT(*) FROM `hampers` WHERE `group_size` = "' . $_POST['group_size'] . '" AND YEAR(`created_date`) = "' . date('Y') . '";');
          $row_count = $stmt->fetchColumn();
        
          $stmt = $pdo->prepare('SELECT `hamper_no` FROM `hampers` WHERE `group_size` = "' . $_POST['group_size'] . '" AND YEAR(`created_date`) = "' . date('Y') . '" ORDER BY `hamper_no` ASC;');
          $stmt->execute([]);

          $i = 0;
          $hamper_no = NULL; 
          while($row = $stmt->fetch()) {
            $i++;
            //echo $row['hamper_no'] . ' == row hamper_no' . "<br />\n";
            [$alpha, $numeric] = sscanf($row['hamper_no'], "%[A-Z]%d");
            if ($numeric > $i) {
              $hamper_no = $alpha . str_pad($i, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT);
              break;
            }
            
            $hamper_no = ($row_count == $i) ? $alpha . str_pad($i + 1, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT) : $alpha . str_pad($i, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT);

            if ($i != $numeric)
              break;
          }
          
          if (!empty($hamper_no))
            [$alpha, $numeric] = sscanf($hamper_no, "%[A-Z]%d"); 
          else {
            [$alpha, $numeric] = sscanf($row['hamper_no'], "%[A-Z]%d");
            $numeric++;   
          }

        } else {
          [$alpha, $numeric] = sscanf($_POST['group_size'][0], "%[A-Z]%d");
          $numeric++;
        }
        
        
        //die($alpha . str_pad($numeric, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT));
        
        $stmt = $pdo->prepare(<<<QUERY
SELECT a.id + 1 AS start, MIN(b.id) - 1 AS end
FROM `hampers` AS a, `hampers` AS b
WHERE a.id < b.id AND a.id >= 1 AND b.id <= (SELECT id FROM `hampers` ORDER BY id DESC LIMIT 1)
GROUP BY a.id HAVING start < MIN(b.id) LIMIT 1;
QUERY
);
        $stmt->execute([]);
        $row_recycle = $stmt->fetch();

        $stmt = $pdo->prepare("INSERT INTO `hampers` (`id`, `client_id`, `hamper_no`, `transport_method`, `phone_number_1`, `phone_number_2`, `address`, `attention`, `group_size`, `minor_children`, `diet_vegetarian`, `diet_gluten_free`, `pet_cat`, `pet_dog`, `created_date`) VALUES (?, ?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?, ?, ?, ?);");
        $stmt->execute([
          (!empty($row_recycle) ? $row_recycle['start'] : NULL),
          (!empty($_SESSION['client_id']) ? $_SESSION['client_id'] : NULL),
          $alpha . str_pad($numeric, $setting['str_pad_hamper_no'], "0", STR_PAD_LEFT),
          (!empty($_POST['transport_method']) ? $_POST['transport_method'] : ''),
          (!empty($_POST['phone_number_1']) ? $_POST['phone_number_1'] : ''),
          (!empty($_POST['phone_number_2']) ? $_POST['phone_number_2'] : ''),
          (!empty($_POST['address']) ? (!empty($_POST['transport_method'] && $_POST['transport_method'] != 'DELIVERY') ? '' : $_POST['address']) : ''),
          (!empty($_POST['group_size']) ? $_POST['group_size'] : ''),
          (!empty($_POST['minor_children']) ? $_POST['minor_children'] : ''),
          (!empty($_POST['diet_vegetarian']) ? $_POST['diet_vegetarian'] : '0'),
          (!empty($_POST['diet_gluten_free']) ? $_POST['diet_gluten_free'] : '0'),
          (!empty($_POST["pet_cat"]) ? '1' : '0'),
          (!empty($_POST["pet_dog"]) ? '1' : '0'),
          date('Y-m-d')
        ]);
              
        $hamper_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("UPDATE `clients` SET `hamper_id` = :hamper_id WHERE `clients`.`id` = :client_id;");
        $stmt->execute([
          ":hamper_id" => (!empty($hamper_id) ? $hamper_id : NULL),
          ":client_id" => (!empty($_SESSION['client_id']) ? $_SESSION['client_id'] : NULL)
        ]);
      }
      exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
        'client' => $_SESSION['client_id']
      ])));
    }
    exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
      'search' => 'clients'
    ])));
    //break;
  case 'GET':
    $stmt = $pdo->prepare("SELECT h.`id` AS h_id, h.`hamper_no`, h.`transport_method`, YEAR(h.`created_date`) AS h_year, c.* FROM `clients` as c LEFT JOIN `hampers` as h ON c.`id` = h.`client_id` WHERE c.`id` = :client_id ORDER BY h.`id` DESC LIMIT 1;"); // WHERE `id` = :id

    $stmt->execute([
      ":client_id" => $_SESSION['client_id']
    ]);

    $row_client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    //if (empty($row_client)) // This doesn't work because there is no client=entry
    //  exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
    //    'search' => 'clients'
    //  ])));

    break;
} ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=APP_NAME?> -- Client Entry</title>

  <base href="<?= !defined('APP_URL_BASE') ? 'http://' . APP_DOMAIN . APP_URL_PATH : APP_URL_BASE ?>" />
  
  <link rel="shortcut icon" type="image/x-icon" href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/images/favicon.ico" />
  <link rel="shortcut icon" type="image/png" href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/images/favicon.png" />
  
  <link rel="stylesheet" type="text/css" href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/css/styles.css" />

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

.overflowAuto {
  overflow-x: hidden;
  overflow-y: auto;
/*   height: calc(100vh - 163px); */
}

.clearfix::after {
  content: "";
  clear: both;
  display: table;
}
</style>

</head>
<body>
  <div style="border: 1px solid #000; width: 700px; margin: auto;">
    <div style="padding: 0px 20px 0px 20px;">
      <form action autocomplete="off" method="GET">
        <h3>
          <a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /> Home</a> | <a href="?reports">Reports</a> | <a href="?search=clients" style="text-decoration: none;">Client</a> &#11106;
          <span style="font-weight: normal;"><?=(!empty($row_client)) ? $row_client['last_name'] . ', ' . $row_client['first_name'] : '(<i>New Client</i>)' ?></span>
          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>
        </h3>
      </form>
    </div>
    <div style="padding: 0px 20px 10px 20px;">
      Client [ <?= $client_dup_count > 0 ? "<a href=\"?client=duplicate\"> (<code style=\"color: red;\">$client_dup_count</code>) Duplicates</a> | " : '' ?><a href="?client=children">Children</a> ]
    </div>
  </div>

  <div style="border: 1px solid #000; width: 700px; margin: 20px auto; height: 55px;">
    <form id="full_name_frm" name="client_search" method="POST" action="<?='?' . http_build_query(['search' => 'clients'])?>" autocomplete="off">
      <div style="display: table; margin: 0px auto; padding: 15px 0px 15px 0px; width: 98%;">
        <!-- <div style="display: table-cell; padding-left: 10px;">
          Client / <input type="tel" size="14" name="phone_number" value="" style="margin-right: 8px;" title="Format: 123-456-7890" placeholder="(123) 456-7890" />
        </div> -->
        <div style="display: table-cell; text-align: left; padding-left: 10px;">
          <label>Last Name:&nbsp;&nbsp;
            <input id="full_name" type="text" name="q" list="full_names" pattern="[a-zA-Z\W+]{1,64}" placeholder=""  value="" oninput="full_name_input()" /> <!-- onclick="this.form.submit();" -->
          </label>
          <datalist id="full_names">
            <option value="" />
          </datalist>&nbsp;&nbsp;&nbsp;
        </div>
        <div style="display: tale-cell; text-align: right; padding-right: 25px;">
          <input type="submit" value="  Search  " style="margin: 2px 0; border: none; cursor: pointer; box-shadow: 0 2px 5px 0 rgb(94, 158, 214); min-width: 90px; border-radius: 2px; padding: 2px 4px; outline: none; border: 1px solid  rgb (94, 158, 214); border-radius:0;" />
        </div>
      </div>
    </form>
  </div>

  <div class="overflowAuto" style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px; padding: 10px 0px;">
<!-- hamper_id  last_name  first_name  phone_number_1  address  group_size  occupants  special_diet  active_status  modified_date 	created_date -->
    <form name="client_entry" action="<?='?' . http_build_query(array_merge(APP_QUERY, []), '', '&amp;')?>" autocomplete="off" method="POST" accept-charset="utf-8">
      <input type="hidden" name="hamper_year" value="<?=date('Y')?>" />
      <input type="hidden" name="client_id" value="<?= !empty($row_client['id']) ? $row_client['id'] : '' ?>" />
<?php if (!empty($row_client['hamper_id']) && is_int((int) $row_client['hamper_id'])) { ?>
      <input type="hidden" name="hamper_id" value="<?= !empty($row_client['hamper_id']) ? $row_client['hamper_id'] : '' ?>" />
<?php } ?>
      <div style="padding: 0px 20px 10px 20px;">
        <div style="border-bottom: 1px dashed #000;">
        
          <!-- 3 Stages If (found) missing hamper_id is discovered use select/option to choose recent h_id -->
            <div style="display: inline-block; margin-top: 10px; float: left;">
<?php
if (!empty($row_client['h_id']) && is_int((int) $row_client['h_id'])) {
  if ((int) $row_client['h_id'] == (int) $row_client['hamper_id']) {
    if ($row_client['h_year'] > date('Y')) {
      // find the latest available hamper id ?>
          <input type="checkbox" id="hamper" style="border-color: red; background-color: red; cursor: pointer;" name="hamper_id" value="<?=$row_client['h_id'];?>" <?=(!empty($row_client['h_id']) ? 'checked' : '')?> />
          <label for="hamper" style="cursor: pointer; color: red;">Hamper: [<?=$row_client['hamper_no'];?>] was found!</label>
<?php  } else if ($row_client['h_year'] == date('Y')){ ?>
          <a href="?hamper=<?= $row_client['hamper_id']; ?>">Hamper: [ <?= $row_client['hamper_no'];?> ]</a>

<?php } else { ?>
          <input type="checkbox" id="hamper" style="cursor: pointer;" name="create_hamper" value="true" <?=(empty($row_client['h_id']) ?: (!$setting['auto_display_hamper_check'] ?: 'checked'))?> />
          <label for="hamper" style="color: red; text-decoration: underline; cursor: pointer; font-weight: bold;">Create Hamper (<?=date('Y')?>)</label>
<?php }
    } else if ($row_client['hamper_id'] != $row_client['h_id']) {
      if ($row_client['h_year'] == date('Y')) {
?>
          <input type="checkbox" id="hamper" style="border-color: red; background-color: red; cursor: pointer;" name="hamper_id" value="<?=$row_client['h_id'];?>" <?= !empty($row_client['h_id']) ? 'checked' : ''?> />
          <label for="hamper" style="cursor: pointer; color: red;">Hamper: [<?=$row_client['hamper_no'];?>] was found!</label>
<?php } else { ?>
          <input type="checkbox" id="hamper" style="cursor: pointer;" name="create_hamper" value="true" <?=(empty($row_client['h_id']) ?: (!$setting['auto_display_hamper_check'] ?: 'checked'))?> />
          <label for="hamper" style="color: red; text-decoration: underline; cursor: pointer; font-weight: bold;">Create Hamper (<?=date('Y')?>)</label>
<?php }
} else { ?>
          <input type="checkbox" id="hamper" style="cursor: pointer;" name="create_hamper" value="true" <?=(!$setting['auto_display_hamper_check'] == true ?: 'checked')?> />
          <label for="hamper" style="color: red; text-decoration: underline; cursor: pointer; font-weight: bold;">Create Hamper (<?=date('Y')?>)</label>
<?php }
} else { ?>
          <input type="checkbox" id="hamper" style="cursor: pointer;" name="create_hamper" value="true" <?=(!$setting['auto_display_hamper_check'] == true ?: 'checked')?> />
          <label for="hamper" style="color: red; text-decoration: underline; cursor: pointer; font-weight: bold;">Create Hamper (<?=date('Y')?>)</label>
<?php } ?>
           </div>
<?php
/* echo (!empty($row_client['modified_date']) ? '<div style="display: inline-block; margin-top: 10px; width: 170px;">&nbsp;&nbsp;&nbsp;<span>Modified: ' . $row_client['modified_date'] . '</span></div>' : '') */
?>
           <div style="display: inline-block; margin-top: 5px; text-align: right; float: right;">
<?php if ($_GET['client'] != 'entry') { ?>
             <button id="client_delete" type="submit" name="client_delete" value="yes" disabled="">Delete Client</button>&nbsp;
             <label for="enable_cb" style="cursor: pointer;">Are you sure?</label>
             <input id="enable_cb" style="cursor: pointer;" type="checkbox" onchange="if ( this.checked == true ) {
      document.getElementById('client_delete').removeAttribute('disabled'); document.getElementById('client_delete').style.color = 'red'; } else { document.getElementById('client_delete').setAttribute('disabled','disabled'); document.getElementById('client_delete').style.color = '';  }" />
             
<?php } ?>
             <input type="submit" style="" name="client_save" value="   Save Client   " />
           </div>
           <div class="clearfix"></div>
        </div>
        <div id="panel" style="border: 1px dashed #000; margin-top: -1px;">

        <div style="margin: 5px auto;">
          <div style="display: inline-block; margin-left: 20px;">
            <label for="transport_method" <?= (!empty($row_client) && empty($row_client['transport_method']) ? 'style="color: red; text-decoration: underline; cursor: pointer;"' : '') ?>>PU/D:</label>
            <select id="transport_method" name="transport_method">
              <option value="PICK-UP" <?=(!empty($row_client['transport_method']) && $row_client['transport_method'] == 'PICK-UP' ? 'selected': '')?>>Pick-up</option>
              <option value="DELIVERY" <?=(!empty($row_client['transport_method']) && $row_client['transport_method'] == 'DELIVERY' ? 'selected': '')?>>Delivery</option>
            </select>
          </div>
          <div style="display: inline-block; margin-left: 20px;">
            <label for="group_size" <?=(!empty($row_client) && empty($row_client['group_size']) ? 'style="color: red; text-decoration: underline; cursor: pointer;"' : '') ?>>Group Size:</label>
            <select id="group_size" name="group_size">
              <?php if (!empty($row_client) && empty($row_client['group_size'])) { ?><option value="" <?=(empty($row_client['group_size']) ? 'selected': '') ?>></option> <?php } ?>
              <option value="SINGLE" <?= !empty($row_client['group_size']) && $row_client['group_size'] == 'SINGLE' ? 'selected' : '' ?>>Single</option>
              <option value="COUPLE" <?= !empty($row_client['group_size']) && $row_client['group_size'] == 'COUPLE' ? 'selected' : '' ?>>Couple</option>
              <option value="FAMILY" <?= !empty($row_client['group_size']) && $row_client['group_size'] == 'FAMILY' ? 'selected' : '' ?>>Family</option>
              <option value="XLFAMILY" <?= !empty($row_client['group_size']) && $row_client['group_size'] == 'XLFAMILY' ? 'selected' : '' ?>>XLFamily</option>
            </select>
          </div>
          <div style="display: inline-block; margin: 5px 0 0 auto; text-align: right; width: 326px;">
            Entry Date: <?= !empty($row_client['created_date']) ? $row_client['created_date'] : date('Y-m-d')?>
          </div>
        </div>
        <div style="margin: 10px auto;">
          <div style="display: inline; margin-left: 20px;">
            <label for="last_name" <?= !empty($row_client['last_name']) ?: 'style="color: red; text-decoration: underline; cursor: pointer;"' ?>>Last Name:</label> <input id="last_name" type="text" name="last_name" value="<?= !empty($row_client['last_name']) ? $row_client['last_name'] : '' ?>" onkeyup="this.value = this.value.toUpperCase();" <?= $_GET['client'] == 'entry' ? 'autofocus=""' : '' ?> />
          </div>
      
          <div style="display: inline; margin-left: 20px;">
            <label for="first_name" <?= !empty($row_client['last_name']) ?: 'style="color: red; text-decoration: underline; cursor: pointer;"' ?>>First Name:</label> <input id="first_name" type="text" name="first_name" value="<?= !empty($row_client['first_name']) ? $row_client['first_name'] : '' ?>" onkeyup="this.value = this.value.toUpperCase();" />
          </div>
        </div>
        <div style="margin-top: 10px; margin-left: 20px;">
          <div>
            <label for="phone_number_1" <?= !empty($row_client['phone_number_1']) ? (preg_match('/^(\+?\(?[0-9]{2,3}\)?)([ -]?[0-9]{2,4}){3}$/', $row_client['phone_number_1']) ? '' : 'style="color: red; text-decoration: underline; cursor: pointer;"') : 'style="cursor: pointer;"'?> title="Incomplete.">Phone #:</label> <input id="phone_number_1" type="tel" size="14" name="phone_number_1" value="<?= !empty($row_client['phone_number_1']) ? $row_client['phone_number_1'] : '' ?>" style="margin-right: 8px; <?= !empty($row_client['phone_number_1']) ? (preg_match('/^(\+?\(?[0-9]{2,3}\)?)([ -]?[0-9]{2,4}){3}$/', $row_client['phone_number_1']) ? '' : 'border: 1px solid red;') : ''?>" title="Format: 123-456-7890" placeholder="(123) 456-7890" />&nbsp;
            <label for="phone_number_2" <?= !empty($row_client['phone_number_2']) ? (preg_match('/^(\+?\(?[0-9]{2,3}\)?)([ -]?[0-9]{2,4}){3}$/', $row_client['phone_number_2']) ? '' : 'style="color: red; text-decoration: underline; cursor: pointer;"') : ''?> title="Incomplete.">Alternate #:</label> <input id="phone_number_2" type="tel" size="14" name="phone_number_2" value="<?= !empty($row_client['phone_number_2']) ? $row_client['phone_number_2'] : '' ?>" style="margin-right: 8px; <?= !empty($row_client['phone_number_2']) ? (preg_match('/^(\+?\(?[0-9]{2,3}\)?)([ -]?[0-9]{2,4}){3}$/', $row_client['phone_number_2']) ? '' : 'border: 1px solid red;') : ''?>" title="Format: 123-456-7890" placeholder="(123) 456-7890" />
          </div>
          <div style="display: table;">
            <div style="display: table-cell; width: 450px;">
          <div class="showhideaddress" style="margin-top: 10px; display: <?= !empty($row_client['transport_method']) && $row_client['transport_method'] == 'DELIVERY' ? 'block' : 'none' ?>;">
            <label for="address">Address:</label> <input id="address" type="text" size="30" name="address" value="<?=(!empty($row_client['address']) ? $row_client['address'] : '') ?>" title="Address must be filled in." placeholder="123 General Street"  />
          </div>
          <div style="margin-top: 10px;">
<?php (!empty($row_client['bday_date']) ? $date = DateTime::createFromFormat("Y-m-d", $row_client['bday_date']) : $date = DateTime::createFromFormat("Y-m-d", date('Y-m-d'))) ?>
            <label for="minor_children" <?= !empty($row_client['minor_children']) ? (date('Y') - $date->format("Y") >= 1 ? 'style="color: red; text-decoration: underline; cursor: pointer;"' : '') : ''?> onClick="document.getElementById('minor_children').removeAttribute('disabled');" title="Override: Add/Remove Children">Children: *</label> <input id="minor_children" type="text" name="minor_children" value="<?= !empty($row_client['minor_children']) ? $row_client['minor_children'] : '' ?>" <?= !empty($row_client['minor_children']) ? (date('Y') - $date->format("Y") >= 1 ? 'disabled' : '') : ''?> onkeyup="this.value = this.value.toUpperCase();" placeholder="M6,F12,N..." title="M6[,F3...]>"/>
            <div style="">
<?php
$children = !empty($row_client['minor_children']) ? explode(",", $row_client['minor_children']) : [];
$numOfChild = count($children);
$i = 0;

if (date('Y') - $date->format("Y") >= 1)
  while ($i < $numOfChild) {
    $gender = $age = NULL;
    if (!is_numeric($children[$i]))
      [$gender, $age] = sscanf($children[$i], "%[A-Z]%d");
    else
      [$age] = sscanf($children[$i], "%d");
    if ($age >= 18) { $i++; continue; }
?>
              <div style="display: inline-block; margin-left: 20px; <?= (date('Y') - $date->format("Y") >= 1 ? 'border: 1px solid red;' : '')?>">
                <select name="children_gender[]">
                  <option value="M" <?= !empty($gender) && $gender == 'M' ? 'selected' : '' ?>>Male</option>
                  <option value="F" <?= !empty($gender) && $gender == 'F' ? 'selected' : '' ?>>Female</option>
                  <option value="" <?= !empty($gender) && $gender == 'N' || $gender == 'O' || $gender == '' ? 'selected' : '' ?>>Neutral</option>
                </select>
                <input type="number" name="children_age[]" value="<?= !empty($age) || $age == 0 ? (date('Y') - $date->format('Y')) + $age : '' ?>" min="0" max="18" size="3" />
              </div><br />
<?php   $i++;
  } ?>
            </div>
          </div>
          <div style="margin-top: 10px;">
            Diet:
            <input id="regular_diet" style="cursor: pointer;" type="radio" name="special_diet" value="" onchange="if (document.getElementById('vegetarian_diet').checked == true || document.getElementById('gluten-free_diet').checked == true ) { document.getElementById('regular_diet').setAttribute('checked','checked'); } else { document.getElementById('regular_diet').removeAttribute('checked'); }" <?=(!empty($row_client['diet_vegetarian']) || !empty($row_client['diet_gluten_free']) ? '' : 'checked')?> />
            <label for="regular_diet">Regular</label>
            <input id="vegetarian_diet" style="cursor: pointer;" type="checkbox" name="diet_vegetarian" onchange="if ( this.checked == true ) { document.getElementById('regular_diet').removeAttribute('checked'); }" value="1" <?=(!empty($row_client['diet_vegetarian']) && $row_client['diet_vegetarian'] == '1' ? 'checked' : '')?> />
            <label for="vegetarian_diet">Vegetarian</label>
            <input id="gluten-free_diet" style="cursor: pointer;" type="checkbox" name="diet_gluten_free" onchange="if ( this.checked == true ) { document.getElementById('regular_diet').removeAttribute('checked'); }" value="1" <?=(!empty($row_client['diet_gluten_free']) && $row_client['diet_gluten_free'] == '1' ? 'checked' : '')?>/>
            <label for="gluten-free_diet">Gluten-Free</label>
          </div>
          <div style="margin-top: 10px;">
            Pets:
            <input id="pet_cat" type="checkbox" name="pet_cat" value="yes" <?= !empty($row_client['pet_cat']) && $row_client['pet_cat'] == '1' ? 'checked' : ''?> /><label for="pet_cat">Cat</label>
            <input id="pet_dog" type="checkbox" name="pet_dog" value="yes" <?= !empty($row_client['pet_dog']) && $row_client['pet_dog'] == '1' ? 'checked' : ''?> /><label for="pet_dog">Dog</label>
              </div>
          <div style="margin-top: 10px;">

<?php
if (!empty($row_client['created_date']))
if ($row_client['h_year'] == date_parse($row_client['created_date'])['year'] || date_parse($row_client['created_date'])['year'] > date('Y')){
?>
            Recent Hamper:    
           <select id="transport_method" name="" disabled>
              <option><?=(!empty($row_client['created_date']) ? date_parse($row_client['created_date'])['year'] : '')?></option>
            </select>
<?php
} else { ?>
            Last Modified:
            <select id="transport_method" name="" disabled>
              <option><?=(!empty($row_client['created_date']) ? $row_client['created_date'] : '')?></option>
            </select>    
<?php } ?>


          </div>
            </div>
            <div style="display: table-cell; padding-left: 30px;">
              <label for="notes" style="display: block;">Notes:</label>
              <textarea id="notes" style="display: block;" name="notes" cols="40" rows="7"><?=(!empty($row_client['notes']) ? $row_client['notes'] : '')?></textarea>
            </div>
          </div>
        </div>
        </div>

        <div style="">
        
        </div>
      </div>
    </form>
<?php
$stmt = $pdo->prepare('SELECT `id`, `hamper_no`, `transport_method`, `group_size`, `phone_number_1`, `address`, YEAR(`created_date`) FROM `hampers` WHERE `client_id` = :client_id ORDER BY `id` DESC;');
$stmt->execute([
  ":client_id" => (!empty($_SESSION['client_id']) ? $_SESSION['client_id'] : NULL),
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) >= 1) {
?>
    <hr />
    <div style="padding: 10px 0px 10px 20px; font-weight: bold;">History Hamper(s)</div>
    <table style="margin: 0px auto; width: 650px;">
      <colgroup>
        <col style="width: 10%;">
        <col style="width: 8%;">
        <col style="width: 12%;">
        <col style="width: 10%;">
        <col style="width: 15%;">
        <col style="width: 45%;">
      </colgroup>
      <thead>
        <tr>
          <th>Hamper</th>
          <th>Year</th>
          <th>Delivery</th>
          <th>Group</th>
          <th>Phone #</th>
          <th>Address</th>
        </tr>
      </thead>
      <tbody>
<?php while($row = array_shift($rows)) { //$result = $stmt->fetch() ?>
        <tr style="text-indent: 3px;">
          <td style="text-align: center;"><a href="?hamper=<?=$row['id']?>"><?=$row['hamper_no']?></a></td>
          <td style="text-align: center;"><?=$row['YEAR(`created_date`)']?></td>
          <td><?=$row['transport_method']?></td>
          <td><?=$row['group_size']?></td>
          <td><?=$row['phone_number_1']?></td>
          <td style="text-align: right;"><?=$row['address']?></td>
        </tr>
<?php } ?>
      </tbody>
    </table>
<?php } ?>
  </div>
  
<script src="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/js/jquery/jquery.min.js"></script>
    
<script src="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/js/jquery/jquery.min.js"></script>
<script src="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/js/jquery.inputmask/jquery.inputmask.min.js"></script>
<script src="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/js/jquery-mask/jquery.mask.min.js"></script> 

<script>
var overflowAuto = document.getElementsByClassName('overflowAuto')[0];

//Get the distance from the top and add 30px for the padding
var maxHeight = overflowAuto.getBoundingClientRect().top + 30;

overflowAuto.style.maxheight = "calc(100vh - " + maxHeight + "px)";

document.querySelector("#full_name").addEventListener('keyup', function (e) {
  var val = document.getElementById("full_name").value;
  var url, packagesOption;
  var start = e.target.selectionStart;
  var end = e.target.selectionEnd;
  e.target.value = e.target.value.toUpperCase();
  e.target.setSelectionRange(start, end);
  url = '<?=APP_URL_BASE . '?' . http_build_query(['search' => 'clients'])?>&q=' + val;
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

//document.querySelector("#full_name").addEventListener("keyup", forceInputUppercase, false);
document.querySelector("#last_name").addEventListener("keyup", forceInputUppercase, false);
document.querySelector("#first_name").addEventListener("keyup", forceInputUppercase, false);

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


$(document).ready(function() {

  //$("form :input").change(function() {
  //  $(this).closest('form').data('changed', true);
  //});
      
  $('select[name="transport_method"]').on('change', function() { // $('#transport_method').change(function() { });
    if ($( ".showhideaddress" ).css('display') == 'none') {
      $( '.showhideaddress' ).slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $( ".showhideaddress" ).slideUp( "slow", function() {
      // Animation complete.
      });
    }
    if ($(this).val() =='DELIVERY') {
      if ($(this).closest('form').find(':input[name="address"]').val() == '') {
        $(this).closest('form').find(':input[name="address"]').css("border-color","red");
        // $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
      } else {
        $(this).closest('form').find(':input[name="address"]').css("border-color","");
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
      }
    } else {
        $(this).closest('form').find(':input[name="address"]').css("border-color","");
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    }
  });
  
  $('select[name="group_size"]').on('change', function() { // $('#transport_method').change(function() { });
    if ($(this).val() != 'SINGLE' && $(this).val() !== 'COUPLE' ) {
      $(this).closest('form').find(':input[name="address"]').css("border-color","red");
      //$(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
  });

  $('input[name="last_name"]').on('input',function(e){
    if ($(this).val().match(/.*\S.*/)) {
      $(this).css("border-color","");
      $('label[for="last_name"]').css('color', '');
      if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') == 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    } else {
      $(this).css("border-color","red");
      $('label[for="last_name"]').css('color', 'red');
      $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
  });

  $('input[name="first_name"]').on('input',function(e){
    if ($(this).val().match(/.*\S.*/)) {
      $(this).css("border-color","");
      $('label[for="first_name"]').css('color', '');
      if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') == 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    } else {
      $(this).css("border-color","red");
      $('label[for="first_name"]').css('color', 'red');
      $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
  });

  $('input[type="tel"]').on('input',function(e){
    if ($(this).val().match(/.*\S.*/)) {
      if ($(this).val().match(/(\(\d{3}\))\s(\d{3})-(\d{4})/)) {
        $('#'+$(this).attr("id")).css("border-color","");
        $('label[for="' + $(this).attr("id") + '"]').css('color', '');
        if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') == 'disabled')
          $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
      } else {
        $('#'+$(this).attr("id")).css("border-color","red");
        $('label[for="' + $(this).attr("id") + '"]').css('color', 'red');
        if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') != 'disabled')
          $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
      }
    } else {
      $('#'+$(this).attr("id")).css("border-color","");
      $('label[for="' + $(this).attr("id") + '"]').css('color', '');
      //if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') != 'disabled')
      //  $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
  });
    
  $('input[name="address"]').on('input',function(e){
    if ($(this).val().match(/.*\S.*/)) {
      $(this).css("border-color","");
      if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') == 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    } else {
      $(this).css("border-color","red");
      //$(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
  });
  
  $('input[name="minor_children"]').on('input',function(e){
    if ($(this).val().match(/.*\S.*/)) {
      if ($(this).val().match(/^(([1-9]{1}[0-8]{0,1})|([1-9]{1}(?:[0-8])*)|([0-2]{1}\.([5]{1}))|([mfon]{1}(([1-9]{1})|([0-2]{1}\.([5]{1}))|([1]{1}[0-8]{1}))))(?:\,(([1-9]{1}[0-8]{0,1})|([1-9]{1}(?:[0-8])*)|([0-2]{1}\.([5]{1}))|([mfon]{1}(([1-9]{1})|([0-2]{1}\.([5]{1}))|([1]{1}[0-8]{1})))))*$/i)) { // ^(([0-9]{1,2})|([mfon]{1}[0-9]{1,2}))(?:\,([0-9]{1,2}|[mfon]{1}[0-9]{1,2}))*$ ... ^[mfon]{1}[0-9]{1,2}(?:\,[mfon]{1}[0-9]{1,2})*$
        $(this).css("border-color","");
        $('label[for="' + $(this).attr("id") + '"]').css('color', '');
        if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') == 'disabled')
          $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
      } else {
        $(this).css("border-color","red");
        $('label[for="' + $(this).attr("id") + '"]').css('color', 'red');
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
      }
    } else {
      //$(this).css("border-color","");
      $('#'+$(this).attr("id")).css("border-color","");
      $('label[for="' + $(this).attr("id") + '"]').css('color', '');
      if ($(this).closest('form').find(':input[name="client_save"]').attr('disabled') != 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    }
  });

  $('form[name="client_entry"]').on('keyup change paste click', 'input, select, textarea', function(){
    var form_error = $(this).closest('form').find(':input[name="client_save"]').attr('disabled');
    
    if ($('input[name="last_name"]').val().match(/.*\S.*/)) {
      $('input[name="last_name"]').css("border-color","");
      $('label[for="last_name"]').css('color', '');
      if (form_error != 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    } else {
      $('input[name="last_name"]').css("border-color","red");
      $('label[for="last_name"]').css('color', 'red');
      if (form_error != 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
    
    form_error = $(this).closest('form').find(':input[name="client_save"]').attr('disabled');

    if ($('input[name="first_name"]').val().match(/.*\S.*/)) {
      $('input[name="first_name"]').css("border-color","");
      if (form_error != 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    } else {
      $('input[name="first_name"]').css("border-color","red");
      if (form_error != 'disabled')
        $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
    
    form_error = $(this).closest('form').find(':input[name="client_save"]').attr('disabled');

    if ($('input[name="phone_number_1"]').val().match(/.*\S.*/)) {
      if ($('input[name="phone_number_1"]').val().match(/(\(\d{3}\))\s(\d{3})-(\d{4})/)) {
        $('input[name="phone_number_1"]').css("border-color","");
        if (form_error != 'disabled')
          $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
      } else {
        //$('input[name="phone_number_1"]').css("border-color","red");
        if (form_error != 'disabled')
          $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
      }
    } else {
      //$('input[name="phone_number_1"]').css("border-color","red");
      //if (form_error != 'disabled')
      //  $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
    }
    
    form_error = $(this).closest('form').find(':input[name="client_save"]').attr('disabled');

    if ($('select[name="transport_method"]').val() == 'DELIVERY')
      if ($('input[name="address"]').val().match(/.*\S.*/)) {
        $('input[name="address"]').css("border-color","");
        if (form_error != 'disabled')
          $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
      } else {
        $('input[name="address"]').css("border-color","red");
        //if (form_error != 'disabled')
        //  $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
      }
    
    form_error = $(this).closest('form').find(':input[name="client_save"]').attr('disabled');

    if ($('select[name="group_size"]').val() !== '') { //  $('select[name="group_size"]').val() !== 'SINGLE' && $('select[name="group_size"]').val() !== 'COUPLE'
      if ($('input[name="minor_children"]').val().match(/.*\S.*/))
        if ($('input[name="minor_children"]').val().match(/^(([1-9]{1}[0-8]{0,1})|([1-9]{1}(?:[0-8])*)|([0-2]{1}\.([5]{1}))|([mfon]{1}(([1-9]{1})|([0-2]{1}\.([5]{1}))|([1]{1}[0-8]{1}))))(?:\,(([1-9]{1}[0-8]{0,1})|([1-9]{1}(?:[0-8])*)|([0-2]{1}\.([5]{1}))|([mfon]{1}(([1-9]{1})|([0-2]{1}\.([5]{1}))|([1]{1}[0-8]{1})))))*$/i)) {
          $('input[name="minor_children"]').css("border-color","");
          if (form_error != 'disabled')
            $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
        } else {
          $('input[name="minor_children"]').css("border-color","red");
          //if (form_error != 'disabled')
          //  $(this).closest('form').find(':input[name="client_save"]').prop('disabled', true);
        }
      else {
        $('input[name="minor_children"]').css("border-color","");
        if (form_error != 'disabled')
          $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
      }
    } else {
      $('input[name="minor_children"]').css("border-color","");
      //if (form_error != 'disabled')
      //  $(this).closest('form').find(':input[name="client_save"]').prop('disabled', false);
    }


  });
  $('form[name="client_entry"]').click(); //.trigger('click')
});
</script>
</body>
</html>
