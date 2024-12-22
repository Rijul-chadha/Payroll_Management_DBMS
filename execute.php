<?php
include 'db_connection.php';
$conn = connectToDatabase();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $output = '';

    switch ($action) {
        case 'update':
      	    $table = escapeshellarg($_POST['table_name']);
      	    $table = trim($table, "'");
      	    $column = escapeshellarg($_POST['column_name']);
      	    $column = trim($column, "'");
	    $value = escapeshellarg($_POST['new_value']);
	    $value = trim ($value, "\"");
            if (substr($value, 0, 1) === "'" && substr($value, -1) === "'") {
                $value = substr($value, 1, -1);
            }
      	    
	    $condition = escapeshellarg($_POST['condition']);
	    $condition = trim ($condition, "\"");
      	    if (substr($condition, 0, 1) === "'" && substr($condition, -1) === "'") {
                $condition = substr($condition, 1, -1);
            }
            $output = update($conn, $table, $column, $value, $condition);
            break;

        case 'view':
      	    $table = escapeshellarg($_POST['table_name']);	
      	    $table = trim($table, "'");
      	    $output = view($conn, $table);
            break;
      
        case 'search':
      	    $table = escapeshellarg($_POST['table_name']);
      	    $table = trim($table, "'");
      	    $column = escapeshellarg($_POST['column_name']);
      	    $column = trim($column, "'");
	    $condition = escapeshellarg($_POST['condition']);
	    $condition = str_replace("\'", "", $condition);
	    $condition = str_replace("\"", "'", $condition);
	    $condition = str_replace("''", "'", $condition);
      	    if (substr($condition, 0, 1) === "'" && substr($condition, -1) === "'") {
                $condition = substr($condition, 1, -1);
            }
            $output = search($conn, $table, $column, $condition);
            break;

        case 'delete':
      	    $table = escapeshellarg($_POST['table_name']);
      	    $table = trim($table, "'");
	    $condition = escapeshellarg($_POST['condition']);
	    $condition = trim ($condition, "\"");
      	    if (substr($condition, 0, 1) === "'" && substr($condition, -1) === "'") {
                $condition = substr($condition, 1, -1);
            }
            $output = deleteRec($conn, $table, $condition);
            break;
    }

    echo '<a href="index.php">Back to Main Page</a>';
}
?>

