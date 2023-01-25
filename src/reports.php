<?php
if (!defined('APP_BASE_PATH')) exit('No direct script access allowed');

//require COMPOSER_AUTOLOAD_PATH.'autoload.php'; // composer dump -o

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
        // Make sure to reset the array's current index
      reset($_GET);

      $get_key = key($_GET);
      $get_value = current($_GET);
      unset($_GET[$get_key]);
      
      //'SELECT `id`, `client_id`, `hamper_no`, `transport_method`, `phone_number_1`, `address`, `group_size`, `minor_children` FROM `hampers` WHERE YEAR(`created_date`) = "' . (empty($_GET['date']) ? date('Y') : date_parse($_GET['date'].'-01-01')['year']) . '"'
      
      //'SELECT h.`id` AS h_id, h.`hamper_no`, c.* FROM `clients` as c LEFT JOIN `hampers` AS h ON c.`id` = h.`client_id` AND c.`hamper_id` = h.`id` ORDER BY `last_name` ASC;'
      
      $year = (empty($_GET['date']) ? date('Y') : date_parse($_GET['date'].'-01-01')['year']);
      
      $prepare_query = 'SELECT h.`id` AS h_id, h.`hamper_no`, h.`transport_method`, h.`group_size`, c.* FROM `clients` as c JOIN `hampers` AS h ON c.`id` = h.`client_id` WHERE h.`created_date` ' . (empty($_GET['date']) ? "BETWEEN '" . date('Y') . "-01-01' AND '" . date('Y') . "-12-31'" : "BETWEEN '$year-01-01' AND '$year-12-31'"); // AND c.`hamper_id` = h.`id` 

      foreach($_GET AS $key => $element) {
        if (in_array($key, (array) ['transport_method', 'group_size']))
          if (!empty($element))
            $prepare_query .= ' AND h.`' . $key . '` = "' . $element . '"';
      }

      $prepare_query .= ' ORDER BY h.`transport_method` ASC,  h.`hamper_no` ASC;'; // c.`last_name` ASC;';

      //die($prepare_query);

      $stmt = $pdo->prepare($prepare_query);
    
      $stmt->execute(array());

      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $_GET[$get_key] = $get_value;

      if (isset($_GET['export']) && $_GET['export'] == '') {
        $spreadsheet = new Spreadsheet();
        /* Set document properties */
        $spreadsheet->getProperties()->setCreator('Gospel Church GF')
        ->setLastModifiedBy('Gospel Church GF')
        ->setTitle('Christmast Hamper ' . date('Y'))
        ->setSubject('Christmast Hamper ' . date('Y'))
        ->setDescription('A Christmas Hamper for the Gospel Church')
        ->setKeywords('Christmas Hamper ' . date('Y'))
        ->setCategory('Hamper');
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hamper #');
        $sheet->setCellValue('B1', 'PU/Delivery');
        $sheet->setCellValue('C1', 'Group');
        $sheet->setCellValue('D1', 'Client Name');
        $sheet->setCellValue('E1', 'Phone Number');
        $sheet->setCellValue('F1', 'Address');

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $rowCount = 2;

        while($row = array_shift($rows)) {
          $sheet->setCellValue('A' . $rowCount, $row['hamper_no']);
          $sheet->setCellValue('B' . $rowCount, $row['transport_method']);
          $sheet->setCellValue('C' . $rowCount, $row['group_size']);
          $sheet->setCellValue('D' . $rowCount, $row['last_name'] . ', ' . $row['first_name']); // 
          $sheet->setCellValue('E' . $rowCount, $row['phone_number_1']);
          $sheet->setCellValue('F' . $rowCount, $row['address']);
          $rowCount++;
        }
      
        $spreadsheet->getActiveSheet()->getStyle('E2:E' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $spreadsheet->getActiveSheet()->getStyle('F2:F' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        
        for ($i = 'A'; $i != $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
          $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Write an .xlsx file  
        $date = date('d-m-y-'.substr((string)microtime(), 1, 8));
        $date = str_replace(".", "", $date);
        $filename = "export_".$date.".xlsx";
        $filePath = APP_PATH . APP_EXPORT . $filename; //make sure you set the right permissions and change this to the path you want

        //$writer = new Xlsx($spreadsheet);
        //$writer->save('hello_world.xlsx');

        try {
          $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
          $writer->save($filePath);
        } catch(Exception $e) {
          exit($e->getMessage());
        }

        // redirect output to client browser
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
      }
    break;
  case 'GET':
    //die(var_dump($_GET));

    // Make sure to reset the array's current index

    reset($_GET);

    $get_key = key($_GET);
    $get_value = current($_GET);
    unset($_GET[$get_key]);

    $prepare_query = 'SELECT h.`id`, h.`client_id`, h.`hamper_no`, h.`transport_method`, h.`phone_number_1`, h.`address`, h.`group_size`, h.`minor_children`, c.`last_name` FROM `hampers` as h LEFT JOIN `clients` as c ON c.`id` = h.`client_id` WHERE YEAR(h.`created_date`) = ' . (empty($_GET['date']) ? $_GET['date'] = date('Y') : date_parse($_GET['date'].'-01-01')['year']);

    foreach($_GET AS $key => $element) {
      if (in_array($key, (array) ['transport_method', 'group_size']))
        if (!empty($element))
           $prepare_query .= ' AND h.`' . $key . '` = "' . $element . '"';
    }

    $prepare_query .= ' ORDER BY `hamper_no` ASC;';
      
    //die($prepare_query);

    $stmt = $pdo->prepare($prepare_query);
    $stmt->execute(array());
      
    //$stmt->debugDumpParams();
      
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_GET[$get_key] = $get_value;

    break;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=APP_NAME?> -- Reports</title>

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
      <div style="float: right;">
        <form action="<?=APP_BASE_URI . '?'?>" method="GET" autocomplete="off" style="display: inline; float: right;">
          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>
        </form>
      </div>
      <h3><a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /> Home</a> | Reports | <a href="./">Search</a> &#11106; <a href="?search=clients" style="text-decoration: none;">Clients</a> : <a href="?search=hampers" style="text-decoration: none;">Hampers</a></h3>
    </div>
  </div>
<?php
foreach($_GET as $key => $val) {
  if($key == 'date') {
    $item = $_GET[$key];
    unset($_GET[$key]);
    //array_push($_GET, $item);
    $_GET = $_GET + (array) [$key => $item];
    break;
  }
}

$report = (!empty($_GET['reports']) ? $_GET['reports'] : '');
$date = (!empty($_GET['date']) ? $_GET['date'] : '');
$transport_method = (!empty($_GET['transport_method'])? $_GET['transport_method'] : '');
$group_size = (!empty($_GET['group_size'])? $_GET['group_size'] : '');

?>
  <div style="position: relative; padding-top: 10px; width: 700px; margin: auto; background-color: #EEE0F2;">
    <div style="position: absolute; margin-top: -10px; margin-left: -1px; width: 702px; ">
      <div class="head" style="position: relative; height: 24px; display: none;">
        <form style="float: right;" action="<?=APP_BASE_URI . '?' . http_build_query((array) ['reports' => (empty($report) ? '' : $report), 'date' => (empty($date) ? date('Y') : date_parse($date.'-01-01')['year'])] + $_GET + ['export' => ''], '', '&amp;')?>" autocomplete="off" method="POST">
          <button>Download</button>
        </form>
      </div>
    <div style="position:absolute; right:-1px; top:i0px;">
      <a class="showHideMe">Export *.XLSX &#9660;</a>
    </div>
    </div>
  </div>


  <div style="border: 1px solid #000; width: 700px; margin: 10px auto; height: 55px;">
    <div style="margin: 0px auto; padding: 15px 0px 20px 0px; width: 98%;">

      <form style="display: inline; padding-left: 15px;" action="<?=APP_BASE_URI . '?' . http_build_query( array('reports' => ''))?>" autocomplete="off" method="GET">
        <input type="hidden" name="reports" value="" />
        <input type="hidden" name="date" value="<?=(empty($date) ? date('Y') : date_parse($date.'-01-01')['year'])?>" />
<?php if (!empty($_GET['transport_method'])) { ?>
        <input type="hidden" name="transport_method" value="<?=$_GET['transport_method'];?>" />
<?php }

    if (!empty($_GET['group_size'])) {
?>
        <input type="hidden" name="group_size" value="<?=$_GET['group_size'];?>" />
<?php    }?>
        
        <caption style="font-weight: bolder;"><input type="checkbox" checked="" disabled="" />Hampers: (<?=count($rows)?>)</caption>
      </form>

      <form style="float: right; margin-right: 10px;" action="<?=APP_BASE_URI . '?' . http_build_query( array('reports' => ''))?>" autocomplete="off" method="POST">
      <div style="display: inline; margin-right: 10px;">
        <label for="transport_method">PU/D:</label>
<?php
foreach($_GET as $key => $val) {
  if($key == 'transport_method') {
    $item = $_GET[$key];
    unset($_GET[$key]);
    //array_push($_GET, $item);
    $_GET = $_GET + (array) [$key => $item];
    break;
  }
}

$_GET = ['reports' => $report];
(!empty($date) ? $_GET = $_GET + (array) ['date' => $date] : '');
(!empty($group_size) ? $_GET = $_GET + (array) ['group_size' => $group_size] : '');
$_GET = $_GET + (array) ['transport_method' => ''];
?>
        <select id="transport_method" onchange="window.location.href=('<?=APP_BASE_URI . '?' . http_build_query($_GET, '', '&amp;')
?>' + this.value).replace(/&amp;/g, '&');">
          <option value="" selected></option>
          <option value="PICK-UP" <?=(!empty($transport_method) && $transport_method == 'PICK-UP' ? 'selected="selected"' : '' )?>>Pick-up</option>
          <option value="DELIVERY" <?=(!empty($transport_method) && $transport_method == 'DELIVERY' ? 'selected="selected"' : '' )?>>Delivery</option>
        </select>
     </div>
     <div style="display: inline;">
        <label for="group_size">Group Size:</label>
<?php
foreach ($_GET AS $key => $val) {
  //die(end($_GET));
  if ($key == 'group_size') {
    $item = $_GET[$key];
    unset($_GET[$key]);
    //array_push($_GET, $item);
    $_GET = $_GET + (array) [$key => $item];
    //die(var_dump($_GET));
    break;
  }
}

$_GET = ['reports' => $report];
(!empty($date) ? $_GET = $_GET + (array) ['date' => $date] : '' );
(!empty($transport_method) ? $_GET = $_GET + (array) ['transport_method' => $transport_method] : '' );
$_GET = $_GET + (array) ['group_size' => ''];
?>

        <select name="group_size" onchange="window.location.href=('<?=APP_BASE_URI . '?' . http_build_query($_GET, '', '&amp;')?>' + this.value).replace(/&amp;/g, '&');">
          <option value=""></option>
          <option value="single" <?=(!empty($group_size) && $group_size == 'single' ? 'selected="selected"' : '' )?>>Single</option>
          <option value="couple" <?=(!empty($group_size) && $group_size == 'couple' ? 'selected="selected"' : '' )?>>Couple</option>
          <option value="family" <?=(!empty($group_size) && $group_size == 'family' ? 'selected="selected"' : '' )?>>Family</option>
          <option value="xlfamily" <?=(!empty($group_size) && $group_size == 'xlfamily' ? 'selected="selected"' : '' )?>>XLFamily</option>
        </select>
      </div>
<?php
$_GET = (array) ['reports' => $report];
(!empty($transport_method) ? $_GET = $_GET + (array) ['transport_method' => $transport_method] : '');
(!empty($group_size) ? $_GET = $_GET + (array) ['group_size' => $group_size] : '');
$_GET = $_GET + (array) ['date' => ''];
?>
      <div style="display: inline; margin-right: 10px;">
        <label for="">Hamper Year</label>
          <select onchange="window.location.href=('<?=APP_BASE_URI . '?' . http_build_query($_GET, '', '&amp;')?>' + this.value).replace(/&amp;/g, '&');">
<?php
  $stmt = $pdo->prepare('SELECT DISTINCT YEAR(`created_date`) FROM `hampers` ORDER BY `created_date` DESC;');
  $stmt->execute(array());
  
  $rows_date = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  echo '                <option value=""></option>' . "\n";
  if (!empty($rows_date))
    while ($row = array_shift($rows_date)) { // $row_dates = $stmt->fetch()
      echo '                <option value="' . $row['YEAR(`created_date`)'] . '"' . (!empty($date) && $date == $row['YEAR(`created_date`)'] ? ' selected="selected"' : '') . '>' . $row['YEAR(`created_date`)'] . '</option>' . "\n";
    }
  // else echo '            <option value="' . date('Y') . '">' . date('Y') . '</option>' . "\n";
?>
          </select>
</div>
      </form>
<!--
      <div style="float: right"><a href="?hamper=entry">New Hamper</a></div>
      Hamper / <input type="submit" value="Search" />
      <div style="display: inline; margin: 20px;">Name: <input type="text" /></div>
      <div style="display: inline;">Phone #: <input type="text" /></div>
-->
    </div>
  </div>
  <div class="overflowAuto" style="border: 1px solid #000; width: 680px; margin: auto; margin-top: 20px; padding: 10px;">
<?php
// `id`, `client_id`, `hamper_no`, `minor_children
  //$stmt = $pdo->prepare('SELECT `id`, `client_id`, `hamper_no`, `minor_children` FROM `hampers` WHERE YEAR(`created_date`) = "' . date('2021') . '" AND `minor_children` != "" ORDER BY `id` ASC;');
    
  //$stmt->execute(array());

  //$children_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  //while($row = array_shift($children_rows)) {      
    //$row['minor_children'] = 'M6,5,F5,Krusty';
  //  break;
  //}
?>
<?php
  $transport_method = (array) [0 => 0, 1 => 0];
  $group_size = (array) [0 => 0, 1 => 0, 2 => 0, 3 => 0];

  $child_count = 0;
  $female_count = 0;
  $male_count = 0;
  $neutral_count = 0;
  
  $child_age_group[0] = (array) [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];
  $child_age_group['male'] = (array) [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];
  $child_age_group['female'] = (array) [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];

  $raw_output = NULL;

  while ($row = array_shift($rows)) {
    if ($row['transport_method'] == 'PICK-UP') $transport_method[0]++;
    else if ($row['transport_method'] == 'DELIVERY') $transport_method[1]++;
    
    if ($row['group_size'] == 'SINGLE') $group_size[0]++;
    elseif ($row['group_size'] == 'COUPLE') $group_size[1]++;
    elseif ($row['group_size'] == 'FAMILY') $group_size[2]++;
    elseif ($row['group_size'] == 'XLFAMILY') $group_size[3]++;
    
    if (empty($row['minor_children'])) continue;
    else $minor_children = explode(',', $row['minor_children']);

    foreach($minor_children AS $child) {
      $gender = $age = NULL;
      if (!is_numeric($child)) list($gender,$age) = sscanf(trim($child), "%[A-Z]%d");
      else list($age) = sscanf(trim($child), "%d");

      if ($gender == 'F') {
        $female_count++;
        if ($age <= 3) $child_age_group['female'][0]++;
        else if ($age <= 6) $child_age_group['female'][1]++;
        else if ($age <= 9) $child_age_group['female'][2]++;
        else if ($age <= 12) $child_age_group['female'][3]++;
        else if ($age <= 18) $child_age_group['female'][4]++;
      }
      elseif ($gender == 'M') { 
        $male_count++;
        if ($age <= 3) $child_age_group['male'][0]++;
        else if ($age <= 6) $child_age_group['male'][1]++;
        else if ($age <= 9) $child_age_group['male'][2]++;
        else if ($age <= 12) $child_age_group['male'][3]++;
        else if ($age <= 18) $child_age_group['male'][4]++;
      } else {
        $neutral_count++;
        if ($age <= 3) $child_age_group[0][0]++;
        else if ($age <= 6) $child_age_group[0][1]++;
        else if ($age <= 9) $child_age_group[0][2]++;
        else if ($age <= 12) $child_age_group[0][3]++;
        else if ($age <= 18) $child_age_group[0][4]++;
      }
    }
  }
  //die(var_dump($child_age_group[0]));
  $child_count = $neutral_count + $male_count + $female_count;
?>
  <div style="display: table; margin-top: 0px; margin-left: 30px;">
    <div style="display: table-cell;">
      <p style="padding-left: 0px; font-weight: bold;">PU/D: </p>
      <div style="display: table; margin-left: 20px;">
        <div style="display: table-header-group; padding: 15px;">
          <div style="display: table-cell; border: 1px solid #000; width: 60px; padding: 5px; font-weight: bold;">Pick-up</div>
          <div style="display: table-cell; border: 1px solid #000; width: 55px; padding: 5px; font-weight: bold;">Delivery</div>
        </div>
        <div style="display: table-row; border: 1px solid #000; padding: 15px;">
          <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $transport_method[0];?></div>
          <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $transport_method[1];?></div>
        </div>
      </div>
    </div>
    <div style="display: table-cell;">
      <p style="padding-left: 20px; font-weight: bold;">Group Sizes: </p>
      <div style="display: table; margin-left: 30px;">
        <div style="display: table-row; padding: 15px;">
          <div style="display: table-cell; border: 1px solid #000; width: 40px; padding: 5px; font-weight: bold;">SINGLE</div>
          <div style="display: table-cell; border: 1px solid #000; width: 55px; padding: 5px; font-weight: bold;">COUPLE</div>
          <div style="display: table-cell; border: 1px solid #000; width: 55px; padding: 5px; font-weight: bold;">FAMILY</div>
          <div style="display: table-cell; border: 1px solid #000; width: 55px; padding: 5px; font-weight: bold;">XLFAMILY</div>
        </div>
        <div style="display: table-row; border: 1px solid #000; padding: 15px;">
          <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $group_size[0];?></div>
          <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $group_size[1];?></div>
          <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $group_size[2];?></div>
          <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $group_size[3];?></div>
        </div>
      </div>
    </div>
  </div>
  <p style="margin-top: 30px; padding-left: 30px; font-weight: bold;">Children: <?=(string) $child_count?></p>
  <div style="display: table; border: 1px solid #000; margin-left: 50px;">
    <div style="display: table-row; padding: 15px;">
      <div style="display: table-cell; border: 1px solid #000; width: 40px; padding: 5px; font-weight: bold;">Male</div>
      <div style="display: table-cell; border: 1px solid #000; width: 55px; padding: 5px; font-weight: bold;">Female</div>
      <div style="display: table-cell; border: 1px solid #000; width: 55px; padding: 5px; font-weight: bold;">Neutral</div>
    </div>
    <div style="display: table-row; border: 1px solid #000; padding: 15px;">
      <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $male_count?></div>
      <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $female_count?></div>
      <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $neutral_count?></div>
    </div>
  </div>
  
  <div style="display: inline-block; margin-left: 20px;">
    <p style="padding-left: 30px; font-weight: bold;">Male (Age Range):</p>
    <div style="display: table; margin-left: 40px;">
      <div style="display: table-row; padding: 5px; text-align: center;">
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">&lt;3</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">4-6</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">7-9</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">10-12</div>
        <div style="display: table-cell; border: 1px solid #000; width: 35px; padding: 5px; font-weight: bold;">13-18</div>
      </div>
      <div style="display: table-row;">
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['male'][0];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['male'][1];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['male'][2];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['male'][3];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['male'][4];?></div>
      </div>
    </div>
  </div>
  <div style="display: inline-block; margin-left: 10px;">
    <p style="padding-left: 30px; font-weight: bold;">Female (Age Range): </p>
    <div style="display: table; margin-left: 40px;">
      <div style="display: table-row; padding: 5px; text-align: center;">
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">&lt;3</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">4-6</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">7-9</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">10-12</div>
        <div style="display: table-cell; border: 1px solid #000; width: 35px; padding: 5px; font-weight: bold;">13-18</div>
      </div>
      <div style="display: table-row;">
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['female'][0]; ?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['female'][1]; ?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['female'][2]; ?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['female'][3]; ?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group['female'][4]; ?></div>
      </div>
    </div>
  </div>
  <div style="display: inline-block; margin-left: 20px;">
    <p style="padding-left: 30px; font-weight: bold;">Neutral (Age Range): </p>
    <div style="display: table; margin-left: 40px;">
      <div style="display: table-row; padding: 5px; text-align: center;">
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">&lt;3</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">4-6</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">7-9</div>
        <div style="display: table-cell; border: 1px solid #000; width: 25px; padding: 5px; font-weight: bold;">10-12</div>
        <div style="display: table-cell; border: 1px solid #000; width: 35px; padding: 5px; font-weight: bold;">13-18</div>
      </div>
      <div style="display: table-row;">
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group[0][0];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group[0][1];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group[0][2];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group[0][3];?></div>
        <div style="display: table-cell; border: 1px solid #000; padding: 5px;"><?=(string) $child_age_group[0][4];?></div>
      </div>
    </div>
  </div>

<?php
/*
    <table style="margin: 0px auto; width: 675px;">
      <colgroup>
        <col style="width: 10%;" />
        <col style="width: 11%;" />
        <col style="width: 10%;" />
        <col style="width: 10%;" />
        <col style="width: 15%;" />
        <col style="width: 30%;" />
      </colgroup>
      <thead>
        <tr>
          <th>Hamper</th>
          <th>Delivery</th>
          <th>Group</th>
          <th>Client</th>
          <th>Phone #</th>
          <th>Address</th>
        </tr>
      </thead>
      <tbody>
<?php

  if (!empty($rows)) {
    while($row = array_shift($rows)) { //$result = $stmt->fetch() ?>
        <tr style="text-indent: 3px;">
          <td style="text-align: center;"><a href="?hamper=<?=$row['id']?>"><?=$row['hamper_no']?></td>
          <td><?=$row['transport_method']?></td>
          <td><?=$row['group_size']?></td>
          <td><a href="?client=<?=$row['client_id']?>"><?=$row['last_name']?></a></td>
          <td><?=$row['phone_number_1']?></td>
          <td style="text-align: right;"><?=$row['address']?></td>
        </tr>
<?php
    }
  } else { ?>
      <tr style="text-indent: 3px;">
        <td style="text-align: center;"></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align: right;"></td>
      </tr>
<?php
  }
?>
    </tbody>
  </table>
*/?>

  </div>
  
<script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/jquery/jquery.min.js"></script>
    
<script src="<?='//' . APP_DOMAIN . APP_BASE_URI?>assets/js/bootstrap/bootstrap.min.js"></script>
 
    <script>  
var overflowAuto = document.getElementsByClassName('overflowAuto')[0];

//Get the distance from the top and add 30px for the padding
var maxHeight = overflowAuto.getBoundingClientRect().top + 30;

overflowAuto.style.maxHeight = "calc(100vh - " + maxHeight + "px)"; 

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
    </script>
</body>
</html>
