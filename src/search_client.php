<?php
if (!defined('APP_BASE_PATH')) exit('No direct script access allowed');

//require(COMPOSER_AUTOLOAD_PATH.'autoload.php'); // composer dump -o

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;     

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':

    if (isset($_POST['q'])) {        // die(var_dump($_POST));

      $_POST['q'] = trim(preg_replace('/\t+/', '', $_POST['q']));
      $client_search = preg_split('/\s*,\s*/', $_POST['q']);

      if (is_array($client_search)) {      //die($_POST['q']);

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

      if (!is_array($client_search)) {
        list($full_name[0],$full_name[1]) = $client_search;
        $full_name[1] = substr($full_name[1], 2);
      } else {
        $full_name = preg_split('/\s*,\s*/', $_POST['q']);
        
        $stmt = $pdo->prepare(<<<HERE
SELECT `id` FROM `clients` WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name LIMIT 1;
HERE);
        $stmt->execute(array(
          ":last_name" => (!empty($client_search[0]) ? $client_search[0] . '%' : $_POST['q'] . '%'),
          ":first_name" => (!empty($client_search[1]) ? substr($client_search[1], 2) . '%' : '')
        )); // die($stmt->debugDumpParams());
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //die(var_dump($client_search));        
        if (isset($row['id']))
          exit(header('Location: ' . APP_URL_BASE . '?client=' . $row['id']));
        
        if (empty($_POST['q']))
          exit(header('Location: ' . APP_URL_BASE . '?search=clients'));
        else {
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
WHERE h2.id IS NULL AND `last_name` LIKE :last_name OR `first_name` LIKE :first_name
ORDER BY `last_name`;
HERE);
          $stmt->execute(array(
            ":last_name" => (!empty($client_search[0]) ? $client_search[0] . '%' : $_POST['q'] . '%' ),
            ":first_name" => (!empty($client_search[1]) ? substr($client_search[1], 2) . '%' : ''),
          )); // die($stmt->debugDumpParams());
          break;
        }
      }

      
      //die(var_dump($_POST));

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
WHERE h2.id IS NULL AND `last_name` LIKE :last_name OR `first_name` LIKE :first_name
ORDER BY `last_name`;
HERE); // ORDER BY sort, hamper_no,  ... hamper_no IS NOT NULL ASC, hamper_no ASC,  

      $stmt->execute(array(
        ":last_name" => (!empty($full_name[0]) ? (strlen($full_name[0]) == 1 ? $full_name[0] . '%' : '%' . $full_name[0] . '%') : '%'),
        ":first_name" => (!empty($full_name[0]) ? (strlen($full_name[0]) == 1 ? $full_name[0] . '%' : '%' . $full_name[0] . '%') : '%'), // $full_name[1]
      ));





      //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`hamper_no`, h.`client_id` AS c_id, c.`id`, `hamper_id`, `first_name`, `last_name`, c.`phone_number_1`, c.`address`, YEAR(h.`created_date`) AS h_year, IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) AS hamper_no, IF(IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) IS NULL,1,0) AS sort FROM `clients` as c LEFT JOIN `hampers` AS h ON c.`id` = h.`client_id` AND c.`hamper_id` = h.`id` OR c.`hamper_id` IS NULL WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name ORDER BY `last_name`;'); // ORDER BY sort, hamper_no,



    } else {
      //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`hamper_no`, c.*, YEAR(h.`created_date`) AS h_year FROM `clients` as c LEFT JOIN `hampers` AS h ON c.`id` = h.`client_id` AND c.`hamper_id` = h.`id` ORDER BY `last_name` ASC;'); // ORDER BY `last_name` ASC


      $stmt = $pdo->prepare(<<<HERE
SELECT h1.`id`                                                  AS h_id,
       IF(YEAR(h1.`created_date`) = YEAR(CURDATE()), h1.`hamper_no`, NULL) AS hamper_no,
       c.*,
       YEAR(h1.`created_date`)                                  AS h_year
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
ORDER BY `last_name`;
HERE); // ORDER BY sort, hamper_no, 

      $stmt->execute(array()); 

      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); 
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
        //$sheet->setCellValue('A1', 'Hamper No.');
        $sheet->setCellValue('A1', 'Last Name');
        $sheet->setCellValue('B1', 'First Name');
        $sheet->setCellValue('C1', 'Phone #');
        $sheet->setCellValue('D1', 'Alt. Phone #');
        $sheet->setCellValue('E1', 'Address');
        $sheet->setCellValue('F1', 'Group');
        $sheet->setCellValue('G1', 'Children');
        $sheet->setCellValue('H1', '(Diet) Vegetarian');
        $sheet->setCellValue('I1', '(Diet) Gluten Free');
        $sheet->setCellValue('J1', '(Pet) Cat');
        $sheet->setCellValue('K1', '(Pet) Dog');
        //$sheet->setCellValue('L1', 'Notes');

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $rowCount = 2;

        while($row = array_shift($rows)) {
          //$sheet->setCellValue('A' . $rowCount, $row['hamper_no']);
          $sheet->setCellValue('A' . $rowCount, $row['last_name']);
          $sheet->setCellValue('B' . $rowCount, $row['first_name']);
          $sheet->setCellValue('C' . $rowCount, $row['phone_number_1']);
          $sheet->setCellValue('D' . $rowCount, $row['phone_number_2']);
          $sheet->setCellValue('E' . $rowCount, $row['address']);
          $sheet->setCellValue('F' . $rowCount, $row['group_size']);
          $sheet->setCellValue('G' . $rowCount, $row['minor_children']);
          $sheet->setCellValue('H' . $rowCount, (!empty($row['diet_vegetarian']) ? ($row['diet_vegetarian'] == '0' ?: 'yes') : ''));
          $sheet->setCellValue('I' . $rowCount, (!empty($row['diet_gluten_free']) ? ($row['diet_gluten_free'] == '0' ?: 'yes') : ''));
          $sheet->setCellValue('J' . $rowCount, (!empty($row['pet_cat']) ? ($row['pet_cat'] == '0' ?: 'yes') : ''));
          $sheet->setCellValue('K' . $rowCount, (!empty($row['pet_dog']) ? ($row['pet_dog'] == '0' ?: 'yes') : ''));
          //$sheet->setCellValue('L' . $rowCount, $row['notes']);
          $rowCount++;
        }
      
        //$spreadsheet->getActiveSheet()->getStyle('C2:C' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        //$spreadsheet->getActiveSheet()->getStyle('D2:D' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

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
    }

    break;
  case 'GET':

    if (!empty($_GET['q'])) {
      header('Content-type: application/json');
      
      if (!empty(preg_match('/\s*,\s*/', $_GET['q'])))
        list($full_name[0],$full_name[1]) = preg_split('/\s*,\s*/', $_GET['q']);
      else
        $full_name = preg_split('/\s*,\s*/', $_GET['q']);

      //$stmt = $pdo->prepare("SELECT `last_name`, `first_name`, COUNT(last_name) as count FROM `clients` WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name GROUP BY `last_name`" . (count($full_name) == 2 ? ', `first_name`' : '') . (count($full_name) == 1 ? ' HAVING COUNT(`last_name`) >= 1' : '') . ";");
      
      $stmt = $pdo->prepare("SELECT `last_name`, `first_name` FROM `clients` WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name ORDER BY `last_name` ASC;");

      $stmt->execute(array(
        ":last_name" => (!empty($full_name[0]) ? (strlen($full_name[0]) == 1 ? $full_name[0] . '%' : '%' . $full_name[0] . '%') : '%'),
        ":first_name" => (!empty($full_name[1]) ? '%' . $full_name[1] . '%' : '%') // $full_name[1]
      ));

      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $data['results'] = [];
      
      while($row = array_shift($rows)) {
        if (count($full_name) == 1)
          $data['results'][] = array('name' => $row['last_name']. ',&nbsp;' . $row['first_name']); // . ', ' . $row['first_name']
        else
          $data['results'][] = array('name' => $row['last_name'] . ',&nbsp;' . $row['first_name']);
      }

      $data = array_map("unserialize", array_unique(array_map("serialize", $data)));

      exit(json_encode($data));
    }

$query = <<<HERE
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
HERE;
    $query .= ' ORDER BY ' . (!empty($_GET['sort_by']) && $_GET['sort_by'] == 'sort-hamper' ? 'sort, hamper_no, ' : '' ) . '`last_name` ' . (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'DESC' : 'ASC' ) . ';';
    $stmt = $pdo->prepare($query); // ORDER BY sort, hamper_no, ... hamper_no IS NOT NULL ASC, hamper_no ASC

    // $stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`client_id` AS c_id, IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) AS hamper_no, IF(YEAR(h.`created_date`) IS NULL, YEAR(c.`created_date`), NULL) AS h_year, c.`id`, `hamper_id`, `last_name`, `first_name`, c.`phone_number_1`, c.`address`,  IF(IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) IS NULL,1,0) AS sort FROM `clients` AS c LEFT JOIN `hampers` AS h ON (c.`id` = h.`client_id` AND IF(c.`hamper_id` IS NULL, NULL, c.`hamper_id`) = h.`id`) ORDER BY sort, hamper_no, `last_name`;'); // ORDER BY sort, hamper_no,


    //$stmt = $pdo->prepare('SELECT h.`id` AS h_id, h.`hamper_no`, h.`client_id` AS c_id, c.`id`, `hamper_id`, c.`first_name`, c.`last_name`, c.`phone_number_1`, c.`address`, YEAR(h.`created_date`) AS h_year, IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) AS hamper_no, IF(IF(YEAR(h.`created_date`)=' . date('Y') . ', h.`hamper_no`, NULL) IS NULL,1,0) AS sort FROM `hampers` as h LEFT JOIN `clients` as c ON c.`id` = h.`client_id` ORDER BY last_name;');
    $stmt->execute(array());


    break;
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results

if (empty($rows)) {
  $query = <<<HERE
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
WHERE h2.id IS NULL AND `last_name` LIKE :last_name OR `first_name` LIKE :first_name
HERE;
  $query .= ' ORDER BY ' . (!empty($_GET['sort_by']) && $_GET['sort_by'] == 'sort-hamper' ? 'sort, hamper_no, ' : '' ) . '`last_name` ' . (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'DESC' : 'ASC' ) . ';';

  $stmt = $pdo->prepare($query); // ORDER BY sort, hamper_no,  ... hamper_no IS NOT NULL ASC, hamper_no ASC,  

  $stmt->execute(array(
    ":last_name" => (!empty($full_name[0]) ? (strlen($full_name[0]) == 1 ? $full_name[0] . '%' : '%' . $full_name[0] . '%') : '%'),
    ":first_name" => (!empty($full_name[0]) ? (strlen($full_name[0]) == 1 ? $full_name[0] . '%' : '%' . $full_name[0] . '%') : '%'), // $full_name[1]
  ));
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=APP_NAME?> -- Client Search</title>

  <base href="<?=(!defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH)?>" />
  
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

</style>

</head>
<body>
  <div style="border: 1px solid #000; width: 700px; margin: auto;">
    <div style="padding: 0px 20px 0px 20px;">
      <h3><a href="./" style="text-decoration: none;"><img src="data:image/gif;base64,R0lGODlhDgAMAMQAAAAAANfX11VVVbKyshwcHP///4SEhEtLSxkZGePj42ZmZmBgYL6+vujo6CEhIXFxcdnZ2VtbW1BQUObm5iIiIoiIiO3t7d3d3Wtrax4eHiQkJAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAAAOAAwAAAVLYCGOwzCeZ+I4CZoiAIC07kTEMTGhTYbjmcbI4vj9KJYCQ/MTCH4ahuEQiVVElZjkYBA9YhfRJaY4YWIBUSC2MKPVbDcgXVgD2oUQADs=" alt="Home Page" /> Home</a> | <a href="?reports">Reports</a> | <a href="?search">Search</a> &#11106; Clients : <a href="?search=hampers">Hampers</a>
        <form action="<?='?'?>" method="GET" autocomplete="off" style="display: inline; float: right;">
          <button type="submit" name="client" value="entry" style="float: right; width: 7em;">New Client</button>
        </form>
      </h3>
    </div>
    <div style="padding: 0px 20px 10px 20px;">
      Client [ <?= ($client_dup_count > 0 ? '<a href="?client=duplicate"> (<code style="color: red;">' . $client_dup_count . '</code>) Duplicates</a> | ' : '' ) ?><a href="?client=children">Children</a> ]
    </div>
  </div>

  <div style="position: relative; padding-top: 10px; width: 700px; margin: auto; background-color: #E0EDF2;">
    <div style="position: absolute; margin-top: -10px; margin-left: -1px; width: 702px; ">
      <div class="head" style="position: relative; height: 24px; display: none;">
        <form style="float: right;" action="<?='?' . http_build_query($_GET + ['export' => ''], '', '&amp;')?>" autocomplete="off" method="POST">
          <button>Download</button>
        </form>
      </div>
    <div style="position:absolute; right:-1px; top:i0px;">
      <a class="showHideMe">Export *.XLSX &#9660;</a>
    </div>
    </div>
  </div>
 
  <div style="border: 1px solid #000; width: 700px; margin: 10px auto; height: 55px;">
    <form id="full_name_frm" method="POST" action="<?='?' . http_build_query( array( 'search' => 'clients' ))?>" autocomplete="off">
      <div style="display: table; margin: 0px auto; padding: 15px 0px 15px 0px; width: 98%;">
        <!-- <div style="display: table-cell; padding-left: 10px;">
          Client / <input type="tel" size="14" name="phone_number" value="" style="margin-right: 8px;" title="Format: 123-456-7890" placeholder="(123) 456-7890" />
        </div> -->
        <div style="display: table-cell; text-align: left; padding-left: 10px;">
          <label>Last Name:&nbsp;&nbsp;
            <input id="full_name" type="text" name="q" list="full_names" pattern="[a-zA-Z\W+]{1,64}" placeholder=""  value=""  autofocus="" oninput="full_name_input()" /> <!-- onclick="this.form.submit();" -->
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

  <div class="overflowAuto" style="border: 1px solid #000; width: 700px; margin: auto; margin-top: 20px; padding: 10px 0px;">
    <table style="margin: 0px auto; width: 675px;">
      <caption style="text-align: left;">Clients: (<?= count($rows); ?>)</caption>
      <colgroup>
        <col style="width: 30%;">
        <col style="width: 45%;">
        <col style="width: 15%;">
        <col style="width: 10%;">
      </colgroup>
      <thead>
        <tr>
          <th><a href="<?='?' . http_build_query(array_merge($_GET, ['sort_by' => 'name', 'order_by' => (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'ASC' : 'DESC')]) , '', '&amp;')?>" <?= (!empty($_GET['sort_by']) && $_GET['sort_by'] == 'name' ? (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'onmouseout="document.getElementById(\'name_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII=\'"' . ' ' . 'onmouseover="document.getElementById(\'name_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII=\'"' : 'onmouseover="document.getElementById(\'name_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII=\'"' . ' ' . 'onmouseout="document.getElementById(\'name_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII=\'"' ) : 'onmouseout="document.getElementById(\'name_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII=\'"' . ' ' . 'onmouseover="document.getElementById(\'name_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII=\'"') ?>>Name <img id="name_asc_desc" src="<?= (!empty($_GET['sort_by']) && $_GET['sort_by'] == 'name' ? (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII="' : 'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII="' ) : 'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII="') ?> /></a></th>
          <th>Address</th>
          <th>Phone #</th>
          <th style="white-space:nowrap"><a href="<?='?' . http_build_query(array_merge($_GET, ['sort_by' => 'sort-hamper', 'order_by' => (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'ASC' : 'DESC')]) , '', '&amp;')?>" <?= (!empty($_GET['sort_by']) && $_GET['sort_by'] == 'name' ? (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'onmouseout="document.getElementById(\'sort_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII=\'"' . ' ' . 'onmouseover="document.getElementById(\'sort_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII=\'"' : 'onmouseover="document.getElementById(\'sort_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII=\'"' . ' ' . 'onmouseout="document.getElementById(\'sort_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII=\'"' ) : 'onmouseout="document.getElementById(\'sort_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII=\'"' . ' ' . 'onmouseover="document.getElementById(\'sort_asc_desc\').src = \'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII=\'"') ?>>Hamper <img id="sort_asc_desc" src="<?= (!empty($_GET['sort_by']) && $_GET['sort_by'] == 'sort-hamper' ? (!empty($_GET['order_by']) && $_GET['order_by'] == 'DESC' ? 'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII="' : 'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOLQUH/u/6v/n/6v+L/s/43/cNi4LtX3f93/9/J1DRnP/157EoWC2x6MuK/xv/z/xf9a1QEasbJkt0fp/yv+xbrhxOR1ZJFL7OlBuCIQkAjhQKp/5zB/AAAAAASUVORK5CYII="' ) : 'data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAWUlEQVR4AWP4TwAOPQWbJVa/XiSHU8F6iWXfV/+f/K1TDquC1RKLvqz4v/H/zP9V3woVsSiY+3Xp//X/V/5f9L/tf+p5LAr6/rf8r/pf+D/zf9L/mG9DLCQB0wYJ0AeZ4ZQAAAAASUVORK5CYII="') ?> /></a></th>
        </tr>
      </thead>
      <tbody>
<?php
if (!empty($rows))
  while($row = array_shift($rows)) { //$result = $stmt->fetch() 
    //var_dump($row);
    //continue;
    if ($row['h_year'] == date('Y'))
      if ($row['h_id'] == $row['hamper_id'] && $row['id'] == $row['c_id']) { ?>
        <tr style="text-indent: 3px;">
          <td><a href="?client=<?=$row['id']?>"><?=$row['last_name'] . ', ' . $row['first_name']?></a></td>
          <td><?=$row['address']?></td>
          <td><?=$row['phone_number_1']?></td>
          <td style="text-align: center;"><a href="?hamper=<?=$row['h_id']?>"><?= $row['hamper_no'] ?></a></td>
        </tr>
<?php } else { ?>
        <tr style="text-indent: 3px;">
          <td><a href="?client=<?=$row['id']?>"><?=$row['last_name'] . ', ' . $row['first_name']?></a></td>
          <td><?=$row['address']?></td>
          <td><?=$row['phone_number_1']?></td>
          <td style="text-align: center;"><a href="?client=<?=$row['id']?>" style="color: red;" title="Client is missing hamper_id"><?= $row['hamper_no'] ?></a></td>
        </tr>
<?php }
    else { // $row['h_id'] == $row['hamper_id'] ?>
        <tr style="text-indent: 3px;">
          <td><a href="?client=<?=$row['id']?>"><?=$row['last_name'] . ', ' . $row['first_name']?></a></td>
          <td><?=$row['address']?></td>
          <td><?=$row['phone_number_1']?></td>
          <td style="text-align: center;"><a href="?hamper=<?=$row['h_id']?>"><?= $row['hamper_no'] ?></a></td>
        </tr>
<?php  }
  }
else {
    $stmt = $pdo->prepare('ALTER TABLE clients AUTO_INCREMENT=1;');
    $stmt->execute(array());
?> 
        <tr style="text-indent: 3px;">
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
  url = '<?=APP_URL_BASE . '?' . http_build_query( array( key($_GET) => current($_GET) ))?>&q=' + val;
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
      full_name_form = document.getElementById('full_name_frm');
      full_name_form.submit.click();
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
