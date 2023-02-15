<?php
if (!defined('APP_BASE_PATH')) exit('No direct script access allowed');

//require(COMPOSER_AUTOLOAD_PATH.'autoload.php'); // composer dump -o

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    // Make sure to reset the array's current index
    reset($_GET);

    $get_key = key($_GET);
    $get_value = current($_GET);
    unset($_GET[$get_key]);

    //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`client_id`, h.`hamper_no`, h.`transport_method`, h.`phone_number_1`, h.`address`, h.`group_size`, c.`last_name`, c.`first_name` FROM `clients` as c LEFT JOIN `hampers` as h ON c.`id` = h.`client_id` WHERE YEAR(h.`created_date`) = "' . (empty($_GET['date']) ? date('Y') : date_parse($_GET['date'].'-01-01')['year']) . '"' . (!empty($_GET['group_size']) ? ' AND h.`group_size` = "' . $_GET['group_size'] . '"' : '') . ' ORDER BY h.`id` DESC;');


    $created_date = (empty($_GET['date']) ? date('Y') : date_parse($_GET['date'].'-01-01')['year']);
    $group_size = (empty($_GET['group_size']) ? '' : ' AND h1.`group_size` = "' . $_GET['group_size'] . '"');
    
    $stmt = $pdo->prepare(<<<HERE
SELECT h1.`id`                    AS h_id,
       h1.`client_id`             AS c_id,
       h1.`hamper_no`             AS hamper_no,
       h1.`transport_method`,
       h1.`phone_number_1`, 
       h1.`address`,
       h1.`group_size`,
       c.`last_name`,
       c.`first_name`
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
WHERE h2.id IS NULL AND YEAR(h1.`created_date`) = {$created_date} {$group_size}
ORDER BY h1.`id`;
HERE);

    //die($stmt->debugDumpParams());

    $stmt->execute(array());

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_GET[$get_key] = $get_value;

    if (!empty($_GET['print']) && $_GET['print'] == 'labels') {
?>
<!DOCTYPE html>
<html>
<head>
<style>
</style>
</head>
<body>
  <div style="width: 600px; height: 400px; margin: auto; border: 1px solid #000; background-color: orange;">
    Testing 
    <form style="float: right;">
      <button>Next</button>
    </form>
    
    <div style="font-size: 120pt; float: right;">S001</div>
  </div>
</body>
</html>
<?php
    exit();
    } elseif (isset($_GET['export']) && $_GET['export'] == '') {
      $spreadsheet = new Spreadsheet();
      /* Set document properties */
      $spreadsheet->getProperties()->setCreator('Gospel Church GF')
      ->setLastModifiedBy('Gospel Church GF')
      ->setTitle('Christmast Hamper ' . date('Y'))
      ->setSubject('Christmast Hamper ' . date('Y'))
      ->setDescription('A Christmas Hamper for the Gospel Church')
      ->setKeywords('Christmas Hamper ' . date('Y'))
      ->setCategory('Hamper');

      if (!empty($_GET['group_size']) && $_GET['group_size'] != '') {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hamper #');
        $sheet->setCellValue('B1', 'Delivery');
        $sheet->setCellValue('C1', 'Group');
        $sheet->setCellValue('D1', 'Client');
        $sheet->setCellValue('E1', 'Phone #');
        $sheet->setCellValue('F1', 'Address');

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $rowCount = 2;

        while($row = array_shift($rows)) {
          $sheet->setCellValue('A' . $rowCount, $row['hamper_no']);
          $sheet->setCellValue('B' . $rowCount, $row['transport_method']);
          $sheet->setCellValue('C' . $rowCount, $row['group_size']);
          $sheet->setCellValue('D' . $rowCount, $row['last_name'] . ', ' . $row['first_name']);
          $sheet->setCellValue('E' . $rowCount, $row['phone_number_1']);
          $sheet->setCellValue('F' . $rowCount, $row['address']);
          $rowCount++;
        }
      
        $spreadsheet->getActiveSheet()->getStyle('D2:D' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $spreadsheet->getActiveSheet()->getStyle('E2:E' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        for ($i = 'A'; $i != $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
          $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
      } else {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hamper #');
        $sheet->setCellValue('B1', 'Delivery');
        $sheet->setCellValue('C1', 'Group');
        $sheet->setCellValue('D1', 'Client');
        $sheet->setCellValue('E1', 'Phone #');
        $sheet->setCellValue('F1', 'Address');

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $spreadsheet->getActiveSheet()->setTitle('SINGLE');

        $spreadsheet->createSheet();

        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setCellValue('A1', 'Hamper #');
        $sheet->setCellValue('B1', 'Delivery');
        $sheet->setCellValue('C1', 'Group');
        $sheet->setCellValue('D1', 'Client');
        $sheet->setCellValue('E1', 'Phone #');
        $sheet->setCellValue('F1', 'Address');

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $spreadsheet->getActiveSheet()->setTitle('COUPLE');

        $spreadsheet->createSheet();

        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setCellValue('A1', 'Hamper #');
        $sheet->setCellValue('B1', 'Delivery');
        $sheet->setCellValue('C1', 'Group');
        $sheet->setCellValue('D1', 'Client');
        $sheet->setCellValue('E1', 'Phone #');
        $sheet->setCellValue('F1', 'Address');

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $spreadsheet->getActiveSheet()->setTitle('FAMILY');
           
        $spreadsheet->createSheet();

        $sheet = $spreadsheet->setActiveSheetIndex(3);
        $sheet->setCellValue('A1', 'Hamper #');
        $sheet->setCellValue('B1', 'Delivery');
        $sheet->setCellValue('C1', 'Group');
        $sheet->setCellValue('D1', 'Client');
        $sheet->setCellValue('E1', 'Phone #');
        $sheet->setCellValue('F1', 'Address');

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $spreadsheet->getActiveSheet()->setTitle('XLFAMILY');

        $rowCount['single'] = 2;
        $rowCount['couple'] = 2;
        $rowCount['family'] = 2;
        $rowCount['xlfamily'] = 2;

        while($row = array_shift($rows)) {
          if ($row['group_size'] == 'SINGLE') {
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setCellValue('A' . $rowCount['single'], $row['hamper_no']);
            $sheet->setCellValue('B' . $rowCount['single'], $row['transport_method']);
            $sheet->setCellValue('C' . $rowCount['single'], $row['group_size']);
            $sheet->setCellValue('D' . $rowCount['single'], $row['last_name'] . ', ' . $row['first_name']);
            $sheet->setCellValue('E' . $rowCount['single'], $row['phone_number_1']);
            $sheet->setCellValue('F' . $rowCount['single'], $row['address']);
            $rowCount['single']++;
          } elseif ($row['group_size'] == 'COUPLE') {
            $sheet = $spreadsheet->setActiveSheetIndex(1);
            $sheet->setCellValue('A' . $rowCount['couple'], $row['hamper_no']);
            $sheet->setCellValue('B' . $rowCount['couple'], $row['transport_method']);
            $sheet->setCellValue('C' . $rowCount['couple'], $row['group_size']);
            $sheet->setCellValue('D' . $rowCount['couple'], $row['last_name'] . ', ' . $row['first_name']);
            $sheet->setCellValue('E' . $rowCount['couple'], $row['phone_number_1']);
            $sheet->setCellValue('F' . $rowCount['couple'], $row['address']);
            $rowCount['couple']++;
          } elseif ($row['group_size'] == 'FAMILY') {
            $sheet = $spreadsheet->setActiveSheetIndex(2);
            $sheet->setCellValue('A' . $rowCount['family'], $row['hamper_no']);
            $sheet->setCellValue('B' . $rowCount['family'], $row['transport_method']);
            $sheet->setCellValue('C' . $rowCount['family'], $row['group_size']);
            $sheet->setCellValue('D' . $rowCount['family'], $row['last_name'] . ', ' . $row['first_name']);
            $sheet->setCellValue('E' . $rowCount['family'], $row['phone_number_1']);
            $sheet->setCellValue('F' . $rowCount['family'], $row['address']);
            $rowCount['family']++;
          } elseif ($row['group_size'] == 'XLFAMILY') {
            $sheet = $spreadsheet->setActiveSheetIndex(3);
            $sheet->setCellValue('A' . $rowCount['xlfamily'], $row['hamper_no']);
            $sheet->setCellValue('B' . $rowCount['xlfamily'], $row['transport_method']);
            $sheet->setCellValue('C' . $rowCount['xlfamily'], $row['group_size']);
            $sheet->setCellValue('D' . $rowCount['xlfamily'], $row['last_name'] . ', ' . $row['first_name']);
            $sheet->setCellValue('E' . $rowCount['xlfamily'], $row['phone_number_1']);
            $sheet->setCellValue('F' . $rowCount['xlfamily'], $row['address']);
            $rowCount['xlfamily']++;
          }
        }

        $spreadsheet->setActiveSheetIndex(0)->getStyle('D2:D' . $rowCount['single'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->setActiveSheetIndex(0)->getStyle('E2:E' . $rowCount['single'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $spreadsheet->setActiveSheetIndex(1)->getStyle('D2:D' . $rowCount['couple'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->setActiveSheetIndex(1)->getStyle('E2:E' . $rowCount['couple'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $spreadsheet->setActiveSheetIndex(2)->getStyle('D2:D' . $rowCount['family'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->setActiveSheetIndex(2)->getStyle('E2:E' . $rowCount['family'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $spreadsheet->setActiveSheetIndex(3)->getStyle('D2:D' . $rowCount['xlfamily'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $spreadsheet->setActiveSheetIndex(3)->getStyle('E2:E' . $rowCount['xlfamily'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        for ($i = 'A'; $i != $spreadsheet->setActiveSheetIndex(0)->getHighestColumn(); $i++) {
          $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        for ($i = 'A'; $i != $spreadsheet->setActiveSheetIndex(1)->getHighestColumn(); $i++) {
          $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        for ($i = 'A'; $i != $spreadsheet->setActiveSheetIndex(2)->getHighestColumn(); $i++) {
          $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        for ($i = 'A'; $i != $spreadsheet->setActiveSheetIndex(3)->getHighestColumn(); $i++) {
          $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
 
        $spreadsheet->setActiveSheetIndex(0);

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
    // Make sure to reset the array's current index
    reset($_GET);

    $get_key = key($_GET);
    $get_value = current($_GET);
    unset($_GET[$get_key]);

    //SELECT h.`id` AS h_id, h.`hamper_no`, h.`transport_method`, h.`phone_number_1`, h.`address`, h.`group_size` FROM `clients` as c LEFT JOIN `hampers` as h ON c.`id` = h.`client_id` WHERE h.`created_date` = "2021-01-01" ORDER BY h.`id` DESC;

    //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`client_id`, h.`hamper_no`, h.`transport_method`, h.`phone_number_1`, h.`address`, h.`group_size`, c.`last_name` FROM `clients` as c LEFT JOIN `hampers` as h ON c.`id` = h.`client_id` WHERE YEAR(h.`created_date`) = "' . (empty($_GET['date']) ? date('Y') : date_parse($_GET['date'].'-01-01')['year']) . '"' . (!empty($_GET['group_size']) ? ' AND h.`group_size` = "' . $_GET['group_size'] . '"' : '') . ' ORDER BY h.`hamper_no` ASC;');
    
    $created_date = (empty($_GET['date']) ? date('Y') : date_parse($_GET['date'].'-01-01')['year']);
    $group_size = (empty($_GET['group_size']) ? '' : ' AND h1.`group_size` = "' . $_GET['group_size'] . '"');
    $transport_method = (empty($_GET['transport_method']) ? '' : ' AND h1.`transport_method` = "' . $_GET['transport_method'] . '"');
    
    $stmt = $pdo->prepare(<<<HERE
SELECT h1.`id`                    AS h_id,
       h1.`client_id`             AS c_id,
       h1.`hamper_no`             AS hamper_no,
       h1.`transport_method`,
       h1.`phone_number_1`, 
       h1.`address`,
       h1.`group_size`,
       c.`last_name`,
       c.`first_name`
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
WHERE h2.id IS NULL AND YEAR(h1.`created_date`) = {$created_date} {$group_size} {$transport_method}
ORDER BY hamper_no;
HERE);

//'SELECT `id`, `client_id`, `hamper_no`, `transport_method`, `phone_number_1`, `address`, `group_size` FROM `hampers` WHERE YEAR(`created_date`) = "' . (empty($_GET['date']) ? date('Y') : $_GET['date']) . '"' . (!empty($_GET['group_size']) ? ' AND `group_size` = "' . $_GET['group_size'] . '"' : '') . ' ORDER BY `id` DESC;'

    $stmt->execute(array());

    //die($stmt->debugDumpParams());

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_GET[$get_key] = $get_value;

    break;
}

// https://www.studentstutorial.com/php-spreadsheet/multiple-worksheet-phpspreadsheet

//die(print_r(date_parse($_GET['date'])));
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=APP_NAME?> -- Hamper Search</title>

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
      <h3><a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /> Home</a> | <a href="?reports">Reports</a> | <a href="?search">Search</a> &#11106;  <a href="?search=clients">Clients</a> : Hampers
        <form action="<?='?'?>" method="GET" autocomplete="off" style="display: inline; float: right;">
          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>
        </form>
      </h3>
    </div>
  </div>
<?php

$search = (!empty($_GET['search']) ? $_GET['search'] : '');
$date = (!empty($_GET['date']) ? $_GET['date'] : '');
$transport_method = (!empty($_GET['transport_method']) ? $_GET['transport_method'] : '');
$group_size = (!empty($_GET['group_size']) ? $_GET['group_size'] : '');

?>  
  <div style="position: relative; padding-top: 10px; width: 700px; margin: auto; background-color: #EEE0F2;">
    <div style="position: absolute; margin-top: -10px; margin-left: -1px; width: 702px; ">
      <div class="head" style="position: relative; height: 24px; display: none;">
        <form style="float: right;" action="<?='?' . http_build_query((array) ['search' => (empty($search) ? '' : $search), 'date' => (empty($date) ? date('Y') : date_parse($date.'-01-01')['year'])] + $_GET + ['export' => ''], '', '&amp;')?>" autocomplete="off" method="POST">
          <caption>Each group is found in separate WORKSHEETS.</caption> <button>Download</button>
        </form>
      </div>
    <div style="position:absolute; right:-1px; top:i0px;">
      <a class="showHideMe">Export *.XLSX &#9660;</a>
    </div>
    </div>
  </div>

  <div style="border: 1px solid #000; width: 700px; margin: 10px auto; height: 55px;">
    <form method="POST" action="<?='?' . http_build_query((array) ['search' => (empty($search) ? '' : $search), 'date' => (empty($date) ? date('Y') : date_parse($date.'-01-01')['year'])] + $_GET + ['print' => 'labels'], '', '&amp;')?>" autocomplete="off">
    <div style="display: table; margin: 15px auto; padding: 0px 10px; width: 97%;">

        <div style="display: table-cell; padding-left: 10px;">

          <button type="submit" name="print" value="labels" style="width: 7em;" disabled="">Print Labels</button>

        </div>
    <div style="display: table-cell; text-align: right;">
        <div style="display: inline; margin-right: 10px;">
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

$_GET = ['search' => $search];
(!empty($date) ? $_GET = $_GET + (array) ['date' => $date] : '');
(!empty($group_size) ? $_GET = $_GET + (array) ['group_size' => $group_size] : '');
$_GET = $_GET + (array) ['transport_method' => ''];
?>
        <label for="transport_method">PU/D:</label>
        <select id="transport_method" onchange="window.location.href=('<?='?' . http_build_query($_GET, '', '&amp;')?>' + this.value).replace(/&amp;/g, '&amp;');">
          <option value="" selected=""></option>
          <option value="PICK-UP" <?=(!empty($transport_method) && $transport_method == 'PICK-UP' ? 'selected="selected"' : '' )?>>Pick-up</option>
          <option value="DELIVERY" <?=(!empty($transport_method) && $transport_method == 'DELIVERY' ? 'selected="selected"' : '' )?>>Delivery</option>
        </select>
        </div>
        <div style="display: inline;">
          <label for="group_size">Group Size: </label>
<?php
//if (empty($_GET['group_size']))
//  $_GET = $_GET + (array) ['group_size' => $group_size];

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
//die(var_dump($_GET));

$_GET = ['search' => $search];
(!empty($date) ? $_GET = $_GET + (array) ['date' => $date] : '' );
(!empty($transport_method) ? $_GET = $_GET + (array) ['transport_method' => $transport_method] : '' );
$_GET = $_GET + (array) ['group_size' => ''];
?>
          <select name="group_size" onchange="window.location.href=('<?='?' . http_build_query($_GET, '', '&amp;')?>' + this.value).replace(/&amp;/g, '&');">
            <option value=""></option>
            <option value="single" <?=(!empty($group_size) && $group_size == 'single' ? 'selected="selected"' : '' )?>>Single</option>
            <option value="couple" <?=(!empty($group_size) && $group_size == 'couple' ? 'selected="selected"' : '' )?>>Couple</option>
            <option value="family" <?=(!empty($group_size) && $group_size == 'family' ? 'selected="selected"' : '' )?>>Family</option>
            <option value="xlfamily" <?=(!empty($group_size) && $group_size == 'xlfamily' ? 'selected="selected"' : '' )?>>XLFamily</option>
          </select>
        </div>&nbsp;&nbsp;
        <div style="display: inline; margin-right: 10px;">
          <label>Hamper Year: </label>
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

$_GET = (array) ['search' => $search];
(!empty($transport_method) ? $_GET = $_GET + (array) ['transport_method' => $transport_method] : '');
(!empty($group_size) ? $_GET = $_GET + (array) ['group_size' => $group_size] : '');
$_GET = $_GET + (array) ['date' => ''];
?>
          <select onchange="window.location.href=('<?='?' . http_build_query($_GET, '', '&amp;')?>' + this.value).replace(/&amp;/g, '&');">
            <option value=""></option>
<?php
  $stmt = $pdo->prepare('SELECT DISTINCT YEAR(`created_date`) FROM `hampers` ORDER BY `created_date` DESC;');
  $stmt->execute(array());

  while ($row_dates = $stmt->fetch()) {
    echo '                <option value="' . $row_dates['YEAR(`created_date`)'] . '"'. (!empty($date) && $date == $row_dates['YEAR(`created_date`)'] ? ' selected="selected"' : '') . '>' . $row_dates['YEAR(`created_date`)'] . '</option>' . "\n";
  }
?>
          </select>
        </div>
      </form>
    </div>

    </div>

  </div>

  <div class="overflowAuto" style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px; padding: 10px 0px;">
    <table style="margin: 0px auto; width: 675px;">
      <caption style="text-align: left;">Hampers: (<?= count($rows); ?>)</caption>
      <colgroup>
        <col style="width: 10%;" />
        <col style="width: 10%;" />
        <col style="width: 10%;" />
        <col style="width: 6%;" />
        <col style="width: 17%;" />
        <col style="width: 39%;" />
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
          <td style="text-align: center;"><a href="?hamper=<?=$row['h_id']?>"><?=$row['hamper_no']?></a></td>
          <td><?=$row['transport_method']?></td>
          <td><?=$row['group_size']?></td>
          <td><a href="?client=<?=$row['c_id']?>"><?=$row['last_name']?></a></td>
          <td style="text-align: center;"><?=$row['phone_number_1']?></td>
          <td style="text-align: right;"><?=$row['address']?></td>
        </tr>
<?php }
} else {
    $stmt = $pdo->prepare('ALTER TABLE hampers AUTO_INCREMENT=1;');
    $stmt->execute(array());
?> 
      <tr style="text-indent: 3px;">
        <td></td>
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
<script src="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>assets/js/bootstrap/bootstrap.min.js"></script>
 
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
