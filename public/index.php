<?php

defined('APP_PATH') // $_SERVER['DOCUMENT_ROOT']
  or define('APP_PATH', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);

require APP_PATH . 'index.php'; // require_once APP_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'; // composer dump -o

//die(var_dump(get_required_files()));

switch($_SERVER['SERVER_NAME']) {
  case stristr($_SERVER['SERVER_NAME'], isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']):
    if (!isset($_SERVER['HTTPS']) && $_SESSION['enable_ssl'] == TRUE):
      //exit(header('Location: ' . preg_replace("/^http:/i", "https:", APP_URL_BASE ))); // basename($_SERVER['REQUEST_URI']));
    endif;
    if ($_SERVER['SERVER_NAME'] == 'localhost') {

    }

    break;

  default:
    if (!isset($_SERVER['HTTPS']) && $_SESSION['enable_ssl'] == TRUE):
      exit(header('Location: ' . preg_replace("/^http:/i", "https:", APP_URL_BASE . $_SERVER['QUERY_STRING'])));
    endif;
    break;
}

switch ($_SERVER['REQUEST_METHOD']) {
/*
  case 'GET':
    break;
*/
  default:
/*    if ($_SERVER['REQUEST_METHOD'] == 'GET') { // leave condition; 
      if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
      }
    } */
    $stmt = $pdo->prepare(<<<HERE
SELECT id, last_name, first_name, phone_number_1, COUNT(last_name) as count
FROM clients
WHERE last_name != ''
GROUP BY last_name
HAVING COUNT(last_name) > 1;
HERE
);
    $stmt->execute([]);
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results

    $rows_duplicate = $rows;
    $rows_clients = [];

    if (!empty($rows))
      while($row = array_shift($rows)) { //$result = $stmt->fetch()
  //  GROUP BY phone_number_1 HAVING COUNT(phone_number_1) > 1;
        $stmt1 = $pdo->prepare('SELECT id FROM clients WHERE last_name LIKE :last_name AND first_name LIKE :first_name GROUP BY first_name HAVING COUNT(first_name) > 1;');
        $stmt1->execute(
          [
            ":last_name" => (!empty($row['last_name']) ? $row['last_name'] . '%' : '%'),
            ":first_name" => (!empty($row['first_name']) ? $row['first_name'] . '%' : '%')
          ]
        );

        $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results    
        while($row1 = array_shift($rows1)) {
          $rows_clients[] = [
            'id' => $row1['id']
          ];
        }
      }

    if (!empty($rows_duplicate))
      while($row = array_shift($rows_duplicate)) { //$result = $stmt->fetch()
        $stmt2 = $pdo->prepare('SELECT id, last_name, first_name, phone_number_1, address FROM clients WHERE phone_number_1 != "" AND first_name LIKE :first_name GROUP BY phone_number_1 HAVING COUNT(phone_number_1) > 1;');
        $stmt2->execute([":first_name" => (!empty($row['first_name']) ? $row['first_name'] . '%' : '%')]);

        $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results    
        while($row2 = array_shift($rows2)) {
          $rows_clients[] = [
            'id' => $row2['id']
          ];
        }
      }

    $rows_clients = array_map("unserialize", array_unique(array_map("serialize", $rows_clients)));

    $client_dup_count = count($rows_clients);

    switch (key($_GET)) {
      case '':
        require APP_PATH . APP_BASE['src'] . 'index.php';
        break;
      case 'search':
        switch (current($_GET)) {
          case 'clients':
            require APP_PATH . APP_BASE['src'] . 'search_client.php';
            break;
          case 'hampers':
            require APP_PATH . APP_BASE['src'] . 'search_hamper.php';
            break;
          default:
            if (!headers_sent())
              exit(header('Location: ' . APP_URL_BASE));
            require APP_PATH . APP_BASE['src'] . 'index.php';
        }
        break;
      case 'client':
        if (current($_GET) == 'entry' || empty(current($_GET)))
          require APP_PATH . APP_BASE['src'] . 'entry_client.php';
        elseif (current($_GET) == 'children')
          require APP_PATH . APP_BASE['src'] . 'client_children.php';
        elseif (current($_GET) == 'duplicate')
          require APP_PATH . APP_BASE['src'] . 'client_duplicate.php';
        elseif ((int) current($_GET))
          require APP_PATH . APP_BASE['src'] . 'entry_client.php';
        else
          exit(header('Location: ' . APP_URL_BASE));
        break;
      case 'hamper':
        if (current($_GET) == 'entry' || empty(current($_GET)))
          require APP_PATH . APP_BASE['src'] . 'entry_hamper.php';
        elseif ((int) current($_GET))
          require APP_PATH . APP_BASE['src'] . 'entry_hamper.php';
        else
          exit(header('Location: ' . APP_URL_BASE));
        break;
      case 'reports':
        if (current($_GET) == '' || !empty(current($_GET))) {
          require APP_PATH . APP_BASE['src'] . 'reports.php';
        }
        break;
      case 'queue':
        if (current($_GET) == '' || !empty(current($_GET))) {
          require APP_PATH . APP_BASE['src'] . 'queue.php';
        }
        break;
      case 'debug':
        require APP_PATH . APP_BASE['src'] . 'debug.php';
        break;
      case 'database':
        switch (current($_GET)) {
          case 'patient':
            require APP_PATH . APP_BASE['src'] . 'db_patient.php';
            break;
          default:
            exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
              key($_GET) => 'patient'
            ])));
        }
        break;
    }


/*
      exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
        'search' => ''
      ])));
*/
  break;
}

/*
switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':

    break;
  case 'POST':
    var_dump($_REQUEST);
    break;
}
*/
