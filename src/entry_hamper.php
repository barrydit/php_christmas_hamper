<?php
if (isset($_REQUEST['hamper']))
  if ($_REQUEST['hamper'] == '')
    $_SESSION['hamper_id'] = NULL;
  else if (is_string($_REQUEST['hamper']))
    if (ctype_digit($_REQUEST['hamper'])) {
      $stmt = $pdo->prepare("SELECT `id`, `created_date` FROM `hampers` WHERE `id` = :id LIMIT 1;");
      $stmt->execute([
        ":id" => filter_var($_REQUEST['hamper'], FILTER_VALIDATE_INT)
      ]);
      $row = $stmt->fetch();
      if (!empty($row))
        $_SESSION['hamper_id'] = $_REQUEST['hamper'] = filter_var($_REQUEST['hamper'], FILTER_VALIDATE_INT);
      else 
        exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([])));
    }
  else
    $_SESSION['hamper_id'] = intval($_REQUEST['hamper']);
else 
  $_SESSION['hamper_id'] = NULL;


switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    //die(var_dump($_POST));

    $stmt = $pdo->prepare("SELECT `id`, `hamper_no` FROM `hampers` WHERE `id` = :hamper_id LIMIT 1;");
    $stmt->execute([
      ":hamper_id" => $_POST['hamper_id']
    ]);
    $row = $stmt->fetch();

    if (!empty($row)) {
      if (!empty($_POST["hamper_delete"]) && $_POST['hamper_delete'] == 'yes') {
        $stmt = $pdo->prepare('DELETE FROM `hampers` WHERE `hampers`.`id` = :hamper_id');
        $stmt->execute([
          ":hamper_id" => $_POST["hamper_id"]
        ]);

        exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
          'hamper' => $_POST['hamper_id']
        ])));

      }
        
      $stmt = $pdo->prepare("UPDATE `hampers` SET `hamper_no` = :hamper_no, `transport_method` = :transport_method, `address` = :address, `attention` = :attention WHERE `hampers`.`id` = :hamper_id;");
      $stmt->execute([
        ':hamper_no' => (!empty($_POST['hamper_no']) ? $_POST['hamper_no'] : $row['hamper_no']),
        ':transport_method' => (!empty($_POST['transport_method']) ? $_POST['transport_method'] : ''),
        ':address' => (!empty($_POST['address']) ? (!empty($_POST['transport_method'] && $_POST['transport_method'] != 'DELIVERY') ? '' : $_POST['address']) : ''),
        ':attention' => (!empty($_POST['attention']) ? $_POST['attention'] : ''),
        ':hamper_id' => $_POST['hamper_id']
      ]);
    }
    
    exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
      'hamper' => $_SESSION['hamper_id']
    ])));
    break;
  case 'GET':
    $stmt = $pdo->prepare("SELECT c.`id` AS c_id, c.`last_name`, c.`first_name`, h.* FROM `hampers` as h LEFT JOIN `clients` as c ON h.`client_id` = c.`id` WHERE h.`id` = :id ORDER BY c.`id` ASC;"); // WHERE `id` = :id;
    $stmt->execute([
      ":id" => $_SESSION['hamper_id']
    ]);
    $row_hamper = $stmt->fetch();
    break;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=APP_NAME?> -- Hamper Entry</title>

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
  background-color: #EEE0F2;
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
          <a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /> Home</a> | <a href="?reports">Reports</a> | <a href="?search=hampers" style="text-decoration: none;">Hamper</a> &#11106;
          <span style="font-weight: normal;">[ <?=(!empty($row_hamper)) ? $row_hamper['hamper_no'] : '<i>New Hamper</i>' ?> ]</span>

          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>

        </h3>
      </form>
    </div>
  </div>
  
  <div style="border: 1px solid #000; width: 700px; margin: 20px auto; height: 55px;">
    <form method="POST" action="<?='?' . http_build_query(['search' => 'clients'])?>" autocomplete="off">
      <div style="display: table; margin: 0px auto; padding: 15px 0px 15px 0px; width: 98%;">
        <!-- <div style="display: table-cell; padding-left: 10px;">
          Client / <input type="tel" size="14" name="phone_number" value="" style="margin-right: 8px;" title="Format: 123-456-7890" placeholder="(123) 456-7890" />
        </div> -->
        <div style="display: table-cell; text-align: left; padding-left: 10px;">
          <label>Last Name:&nbsp;&nbsp;
          <input id="full_name" type="text" name="q" list="full_names" pattern="[a-zA-Z\W+]{1,64}" placeholder="" value="" <?= ( $_GET['hamper'] != 'entry' ? 'autofocus=""' : '') ?> onclick="this.form.submit();" />
          <datalist id="full_names">
            <option value="" />
          </datalist>&nbsp;&nbsp;&nbsp;
        </div>
        <div style="display: tale-cell; text-align: right; padding-right: 25px;">
          <input type="submit" value="  Search  " />
        </div>
      </div>
    </form>
  </div>

  <div class="overflowAuto" style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px; padding: 10px 0px;">
    <div style="padding: 0px 20px 10px 20px;">
      <form action="<?='?' . http_build_query(array_merge(APP_QUERY, []), '', '&amp;')?>" autocomplete="off" method="POST" accept-charset="utf-8">
<?php if (!empty($row_hamper['id']) && is_int((int) $row_hamper['id'])) { ?>
        <input type="hidden" name="hamper_id" value="<?=(!empty($row_hamper['id']) ? $row_hamper['id'] : '') ?>" />
<?php } ?>
        <div style="">
          <div style="display: inline-block; margin-top: 5px; float: left;">
          <?php if (!empty($row_hamper['c_id'])) { ?><a href="?client=<?=$row_hamper['c_id']?>">Client: <?=$row_hamper['last_name']?>, <?=$row_hamper['first_name']?></a><?php } else { ?> Unknown Client <?php } ?>
          </div>
<?php
/* echo (!empty($row_hamper['created_date']) ? '<div style="display: inline-block; margin-top: 5px; width: 170px;">&nbsp;&nbsp;&nbsp;<span>Created: ' . $row_hamper['created_date'] . '</span></div>' : ''); */
?>
          <div style="display: inline-block; margin-bottom: 5px; text-align: right; float: right;">
            <button id="hamper_delete" type="submit" name="hamper_delete" value="yes" disabled="">Delete Hamper</button>&nbsp;
            <label for="enable_cb" style="cursor: pointer;">Are you sure?</label>
            <input id="enable_cb" style="" type="checkbox" onchange="if ( this.checked == true ) {
      document.getElementById('hamper_delete').removeAttribute('disabled'); document.getElementById('hamper_delete').style.color = 'red'; } else { document.getElementById('hamper_delete').setAttribute('disabled','disabled'); document.getElementById('hamper_delete').style.color = ''; }" />
            <input type="submit" value="   Save Hamper   " />
          </div>
          <div class="clearfix"></div>
        </div>

        <div id="panel" style="border: 1px dashed #000; margin-top: -1px;">
        <div style="margin: 5px auto;">
          <div style="display: inline-block; margin-left: 20px;">
            <label for="transport_method" <?= (!empty($row_hamper) && empty($row_hamper['transport_method']) ? 'title="Prefered method was not selected" style="color: red; text-decoration: underline; cursor: pointer;"' : '') ?>>PU/D:</label>
            <select id="transport_method" name="transport_method">
<?php if (!empty($row_hamper) && empty($row_hamper['transport_method'])) { ?><option value="" <?=(empty($row_hamper['transport_method']) ? 'selected': '') ?>></option> <?php } ?>
              <option value="PICK-UP" <?=(!empty($row_hamper['transport_method']) && $row_hamper['transport_method'] == 'PICK-UP' ? 'selected': '')?>>Pick-up</option>
              <option value="DELIVERY" <?=(!empty($row_hamper['transport_method']) && $row_hamper['transport_method'] == 'DELIVERY' ? 'selected': '')?>>Delivery</option>
            </select>
          </div>
          <div style="display: inline-block; width: 200px; margin-left: 20px;">
<?php
  $stmt = $pdo->prepare('SELECT `hamper_no` FROM `hampers` WHERE YEAR(`created_date`) = :created_date ORDER BY `id` DESC LIMIT 1;');
  $stmt->execute([
  ':created_date' => date('Y')
]);
  $row = $stmt->fetch();
  if (!empty($row)) {
    list($alpha,$numeric) = sscanf($row['hamper_no'], "%[A-Z]%d");
    $numeric++;
  }
?>
            <label for="hamper_no" style="text-decoration: underline; cursor: pointer; color: #0066CC;" title="Override: Change (manual) Hamper #" onClick="if (document.getElementById('hamper_no').disabled == true) { document.getElementById('hamper_no').removeAttribute('disabled'); } else { document.getElementById('hamper_no').setAttribute('disabled', ''); }" />Hamper #:</label>
            <input id="hamper_no" type="text" name="hamper_no" size="4" value="<?=(!empty($row_hamper) ? (!empty($row_hamper['hamper_no']) ? $row_hamper['hamper_no'] : '') : $alpha . str_pad($numeric, 3, "0", STR_PAD_LEFT))?>" disabled />
          </div>
      
          <div style="display: inline-block; width: 260px; margin: 10px 0 0 auto; text-align: right;">
            <label for="group_size">Group Size:</label>
            <select id="group_size" disabled>
              <option value="" <?=(empty($row_hamper['group_size']) ? 'selected': '') ?>></option>
              <option value="SINGLE" <?=(!empty($row_hamper['group_size']) && $row_hamper['group_size'] == 'SINGLE' ? 'selected': '')?>>Single</option>
              <option value="COUPLE" <?=(!empty($row_hamper['group_size']) && $row_hamper['group_size'] == 'COUPLE' ? 'selected': '')?>>Couple</option>
              <option value="FAMILY" <?=(!empty($row_hamper['group_size']) && $row_hamper['group_size'] == 'FAMILY' ? 'selected': '')?>>Family</option>
              <option value="XLFAMILY" <?=(!empty($row_hamper['group_size']) && $row_hamper['group_size'] == 'XLFAMILY' ? 'selected': '')?>>XLFamily</option>
            </select>
          </div>
        </div>
        <div style="margin-top: 10px; margin-left: 20px;">
          <label for="phone_number_1">Phone #:</label>
          <input id="phone_number_1" type="tel" size="14" name="phone_number_1" value="<?=(!empty($row_hamper['phone_number_1']) ? $row_hamper['phone_number_1'] : '')?>" style="margin-right: 8px;" title="Format: 123-456-7890" placeholder="(123) 456-7890" disabled />
          <label for="phone_number_2">Alternate #:</label>
          <input id="phone_number_2" type="tel" size="14" name="phone_number_2" value="<?=(!empty($row_hamper['phone_number_2']) ? $row_hamper['phone_number_2'] : '')?>" style="margin-right: 8px;" title="Format: 123-456-7890" placeholder="(123) 456-7890" disabled />

          <div style="display: table;">
          <div style="display: table-cell; width: 450px;">
          <div class="showhideaddress" style="display: table-cell; margin-top: 10px; display: <?=(!empty($row_hamper['transport_method']) && $row_hamper['transport_method'] == 'DELIVERY' ? 'block' : 'none')?>;">
            <label for="address">Address:</label> <input id="address" type="text" size="30" name="address" value="<?=(!empty($row_hamper['address']) ? $row_hamper['address'] : '')?>" />
          </div>

<!-- id, client_id, hamper_no, transport_method, phone_number, address, attention, group_size, minor_children, special_diet, pu-delivery_date, created_date -->
          <div style="margin-top: 10px;">
            <label for="minor_children">Children:</label> <input id="minor_children" type="text" name="minor_children" value="<?=(!empty($row_hamper['minor_children']) ? $row_hamper['minor_children'] : '')?>" disabled />
          </div>
          <div style="margin-top: 10px;">
            Diet:
            <input id="regular_diet" style="cursor: pointer;" type="radio" name="special_diet" value="" onchange="if (document.getElementById('vegetarian_diet').checked == true || document.getElementById('gluten-free_diet').checked == true ) { document.getElementById('regular_diet').setAttribute('checked','checked'); } else { document.getElementById('regular_diet').removeAttribute('checked'); }" <?=(!empty($row_hamper['diet_vegetarian']) || !empty($row_hamper['diet_gluten_free']) ? '' : 'checked')?> disabled />
            <label for="regular_diet">Regular</label>
            <input id="vegetarian_diet" style="cursor: pointer;" type="checkbox" name="diet_vegetarian" onchange="if ( this.checked == true ) { document.getElementById('regular_diet').removeAttribute('checked'); }" value="yes" <?=(!empty($row_hamper['diet_vegetarian']) && $row_hamper['diet_vegetarian'] == '1' ? 'checked' : '')?> disabled />
            <label for="vegetarian_diet">Vegetarian</label>
            <input id="gluten-free_diet" style="cursor: pointer;" type="checkbox" name="diet_gluten_free" onchange="if ( this.checked == true ) { document.getElementById('regular_diet').removeAttribute('checked'); }" value="yes" <?=(!empty($row_hamper['diet_gluten_free']) && $row_hamper['diet_gluten_free'] == '1' ? 'checked' : '')?> disabled />
            <label for="gluten-free_diet">Gluten-Free</label>
          </div>
          <div style="margin-top: 10px;">
            Pets:
            <input id="pet_cat" type="checkbox" name="pet_cat" value="yes" <?=(!empty($row_hamper['pet_cat']) && $row_hamper['pet_cat'] == '1' ? 'checked' : '')?> disabled /><label for="pet_cat">Cat</label>
            <input id="pet_dog" type="checkbox" name="pet_dog" value="yes" <?=(!empty($row_hamper['pet_dog']) && $row_hamper['pet_dog'] == '1' ? 'checked' : '')?> disabled /><label for="pet_dog">Dog</label>
          </div>
          </div>
          <div style="display: table-cell; padding-left: 30px;">
            <div style="margin-top: 8px;">Special Instructions:<br />
              <textarea name="attention" cols="36" rows="7"><?=(!empty($row_hamper['attention']) ? $row_hamper['attention'] : '')?></textarea>
            </div>
          </div>
        </div>
        </div>
      </form>
    </div>
  </div>
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
  url = '<?=APP_URL_BASE . '?' . http_build_query(['search' => 'clients'])?>&q=' + val;
  document.getElementById('full_names').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    $(data.results).each(function() {
      packagesOption = "<option value=\"" + this.name + "\" />";
      $('#full_names').append(packagesOption);
      //console.log(this.favers);
    });
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


$(document).ready(function() {
  $('#transport_method').change(function() {
    if ($( ".showhideaddress" ).css('display') == 'none') {
      //$('.').html("&#9650; Settings");
      $( '.showhideaddress' ).slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      //$('.').html("&#9660; Settings");
      $( ".showhideaddress" ).slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });
});
</script>
</body>
</html>
