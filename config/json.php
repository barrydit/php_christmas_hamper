<?php
/*
require_once 'database.php';

  $query = <<<HERE
SELECT * FROM clients;
HERE;
  
  $stmt = $pdo->prepare($query); // ORDER BY sort, hamper_no,  ... hamper_no IS NOT NULL ASC, hamper_no ASC,  

  $stmt->execute([]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // handle POST/GET results
  
  var_dump($rows);
*/

if (extension_loaded('sqlite3')) {
    //echo "SQLite is enabled in your PHP installation.";
} else {
    //echo "SQLite is not enabled in your PHP installation.";
}

/*
// Open SQLite database (create if not exists)
$db = new SQLite3('hello.db');

// Create table
$db->exec('CREATE TABLE IF NOT EXISTS greetings (message TEXT)');

// Insert data
$db->exec("INSERT INTO greetings (message) VALUES ('Hello, World!')");

// Retrieve data
$result = $db->query('SELECT * FROM greetings');
while ($row = $result->fetchArray()) {
    echo $row['message'] . "\n";
}

// Close database connection
$db->close();
*/ 


$jsonString = <<<JSON
{
    "users": [{"id": 0, "name": "Darrell", "username": "darrell", "password": "\$2y\$10\$2kSaY7MfwBDVNR0uEg5fle/PV0X61/s.ERCLUjv0B1Cno2qlPBlni"}],
    "clients": [
      ["id", "hamper_id", "last_name", "first_name", "phone_number_1", "phone_number_2", "address", "group_size", "minor_children", "diet_vegetarian", "diet_gluten_free", "pet_cat", "pet_dog", "notes", "active_status", "bday_date", "modified_date", "created_date"]
    ],
    "current_hampers": [
      ["id", "client_id", "hamper_no", "transport_method", "phone_number_1", "phone_number_2", "address", "attention", "group_size", "minor_children", "diet_vegetarian", "diet_gluten_free", "pet_cat", "pet_dog", "created_date"]
    ],
    "prior_hampers": [
      ["id", "client_id", "hamper_no", "transport_method", "phone_number_1", "phone_number_2", "address", "attention", "group_size", "minor_children", "diet_vegetarian", "diet_gluten_free", "pet_cat", "pet_dog", "created_date"]
    ]
}
JSON;

$data = json_decode($jsonString, true);

//exit();
/*
foreach($rows as $row_key => $row) {
  
  foreach ($row as $col_key => $column) {

    $row_data[] = $column;
    
  }
    //echo 'Row: ' . $row_key;
  $data['clients'][] = $row_data;
}
*/
  //dd($data);
  
/*

// Define schema validation rules
var schemaValidation = {
  $jsonSchema: {
    bsonType: "object",
    required: ["users", "clients", "current_hampers", "prior_hampers"],
    properties: {
      users: {
        bsonType: "array",
        items: {
          bsonType: "object",
          required: ["id", "name", "username", "password"],
          properties: {
            id: { bsonType: "int" },
            name: { bsonType: "string" },
            username: { bsonType: "string" },
            password: { bsonType: "string" }
          }
        }
      },
      clients: {
        bsonType: "array",
        items: {
          bsonType: "array",
          minItems: 18,
          maxItems: 18,
          items: {
            bsonType: ["int", "string", "string", "string", "string", "string", "string", "string", "string", "bool", "bool", "bool", "bool", "string", "int", "date", "date", "date"]
          }
        }
      },
      current_hampers: {
        bsonType: "array",
        items: {
          bsonType: "array",
          minItems: 15,
          maxItems: 15,
          items: {
            bsonType: ["int", "int", "string", "string", "string", "string", "string", "string", "string", "bool", "bool", "bool", "bool", "string", "date"]
          }
        }
      },
      prior_hampers: {
        bsonType: "array",
        items: {
          bsonType: "array",
          minItems: 15,
          maxItems: 15,
          items: {
            bsonType: ["int", "int", "string", "string", "string", "string", "string", "string", "string", "bool", "bool", "bool", "bool", "string", "date"]
          }
        }
      }
    }
  }
};

// Create or update collection with schema validation
db.createCollection("your_collection_name", { validator: schemaValidation });

*/