<?php
/*
import csv fopen();
parse each line
import into database as row(s)
don't forget to...
*/
/*
$d = dir();
echo "Pointeur : " . $d->handle . "\n";
echo "Chemin : " . $d->path . "\n";
while (false !== ($entry = $d->read())) {
   echo $entry."\n";
}
$d->close();
*/
$row = 0;

/* hamper_no, transport_method, first_name, last_name, phone_number, minor_children */
if (($handle = fopen(APP_PATH . "/resources/COUPLES_2021.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $row++;
    // if ($row == 1) continue;
    $num = count($data);
    //echo "<p> $num fields in line $row: <br /></p>\n";

    $stmt = $pdo->prepare("INSERT IGNORE INTO `clients` (`id`, `last_name`, `first_name`, `phone_number_1`, `group_size`, `minor_children`, `active_status`, `modified_date`, `created_date`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?);");
    $stmt->execute(array(
      (!empty($data[3]) ? trim($data[3]) : NULL),
      (!empty($data[2]) ? trim($data[2]) : NULL),
      (!empty($data[4]) ? preg_replace('#[ -]+#', '-', trim($data[4])) : ''),
      'COUPLE',
      (!empty($data[5]) ? trim($data[5]) : ''),
      '1',
      '2021-01-01',
      '2021-01-01'
    ));
    
    $client_id = $pdo->lastInsertId();

/* hamper_no, transport_method, first_name, last_name, phone_number, minor_children */
    $stmt = $pdo->prepare("INSERT INTO `hampers` (`id`, `client_id`, `hamper_no`, `transport_method`, `phone_number_1`, `group_size`, `minor_children`, `created_date`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?);");
    $stmt->execute(array(
      (!empty($client_id) ? $client_id : NULL),
      (!empty($data[0]) ? trim($data[0]) : NULL),
      (!empty($data[1]) ? trim($data[1]) : ''),
      (!empty($data[4]) ? preg_replace('#[ -]+#', '-', trim($data[4])) : ''),
      'COUPLE',
      (!empty($data[5]) ? trim($data[5]) : ''),
      '2021-01-01'
    ));
    
    $hamper_id = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("UPDATE `clients` SET `hamper_id` = :hamper_id WHERE `clients`.`id` = :id;");
    $stmt->execute(array(
      ":hamper_id" => (!empty($hamper_id) ? $hamper_id : NULL),
      ":id" => (!empty($client_id) ? $client_id : NULL)
    ));
  }
  fclose($handle);
}

if (($handle = fopen(APP_PATH . "/resources/FAMILIES_2021.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $row++;
    // if ($row == 1) continue;
    $num = count($data);
    //echo "<p> $num fields in line $row: <br /></p>\n";

    $stmt = $pdo->prepare("INSERT IGNORE INTO `clients` (`id`, `last_name`, `first_name`, `phone_number_1`, `group_size`, `minor_children`, `diet_vegetarian`, `diet_gluten_free`, `active_status`, `modified_date`, `created_date`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
    $stmt->execute(array(
      (!empty($data[4]) ? trim($data[4]) : NULL),
      (!empty($data[3]) ? trim($data[3]) : NULL),
      (!empty($data[5]) ? preg_replace('#[ -]+#', '-', trim($data[5])) : ''),
      (!empty($data[2]) ? trim($data[2]) : NULL),
      (!empty($data[6]) ? trim($data[6]) : ''),
      (!empty($data[7]) ? '1' : '0'),
      0,
      1,
      '2021-01-01',
      '2021-01-01'
    ));
    
    $client_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO `hampers` (`id`, `client_id`, `hamper_no`, `transport_method`, `phone_number_1`, `group_size`, `minor_children`, `diet_vegetarian`, `diet_gluten_free`, `created_date`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
    $stmt->execute(array(
      (!empty($client_id) ? $client_id : NULL),
      (!empty($data[0]) ? trim($data[0]) : NULL),
      (!empty($data[1]) ? trim($data[1]) : ''),
      (!empty($data[5]) ? preg_replace('#[ -]+#', '-', trim($data[5])) : ''),
      (!empty($data[2]) ? trim($data[2]) : NULL),
      (!empty($data[6]) ? trim($data[6]) : ''),
      (!empty($data[7]) ? '1' : '0'),
      0,
      '2021-01-01'
    ));
    
    $hamper_id = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("UPDATE `clients` SET `hamper_id` = :hamper_id WHERE `clients`.`id` = :id;");
    $stmt->execute(array(
      ":hamper_id" => (!empty($hamper_id) ? $hamper_id : NULL),
      ":id" => (!empty($client_id) ? $client_id : NULL)
    ));
  }
  fclose($handle);
}

if (($handle = fopen(APP_PATH . "/resources/SINGLES_2021.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $row++;
    // if ($row == 1) continue;
    $num = count($data);
    //echo "<p> $num fields in line $row: <br /></p>\n";

    $stmt = $pdo->prepare("INSERT IGNORE INTO `clients` (`id`, `last_name`, `first_name`, `phone_number_1`, `group_size`, `active_status`, `modified_date`, `created_date`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?);");
    $stmt->execute(array(
      (!empty($data[3]) ? trim($data[3]) : NULL),
      (!empty($data[2]) ? trim($data[2]) : NULL),
      (!empty($data[4]) ? preg_replace('#[ -]+#', '-', trim($data[4])) : ''),
      'SINGLE',
      '1',
      '2021-01-01',
      '2021-01-01'
    ));
    
    $client_id = $pdo->lastInsertId();

/* hamper_no, transport_method, first_name, last_name, phone_number*/
    $stmt = $pdo->prepare("INSERT INTO `hampers` (`id`, `client_id`, `hamper_no`, `transport_method`, `phone_number_1`, `group_size`, `minor_children`, `created_date`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?);");
    $stmt->execute(array(
      (!empty($client_id) ? $client_id : NULL),
      (!empty($data[0]) ? trim($data[0]) : NULL),
      (!empty($data[1]) ? trim($data[1]) : ''),
      (!empty($data[4]) ? preg_replace('#[ -]+#', '-', trim($data[4])) : ''),
      'SINGLE',
      (!empty($data[5]) ? trim($data[5]) : ''),
      '2021-01-01'
    ));
    
    $hamper_id = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("UPDATE `clients` SET `hamper_id` = :hamper_id WHERE `clients`.`id` = :id;");
    $stmt->execute(array(
      ":hamper_id" => (!empty($hamper_id) ? $hamper_id : NULL),
      ":id" => (!empty($client_id) ? $client_id : NULL)
    ));
  }
  fclose($handle);
}

