<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oracle DB Operations</title>
</head>
<body>
    <h1>Oracle Database Operations</h1>
    <form method="post">
        <button type="submit" name="action" value="create">Create Tables</button>
        <button type="submit" name="action" value="drop">Drop Tables</button>
        <button type="submit" name="action" value="insert">Insert Data</button>
        <button type="submit" name="action" value="queries">Run Queries</button>
    </form>
    <br>
    <h2>Operations Requiring Input</h2>
    <ul>
        <li><a href="update.php">Update Column</a></li>
        <li><a href="view.php">View Table</a></li>
        <li><a href="search.php">Search Value</a></li>
        <li><a href="delete.php">Delete Record</a></li>
    </ul>
    <h2>Output</h2>
    <pre>
    <?php
    include 'db_connection.php';
    $conn = connectToDatabase();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'];
        $output = '';
        
        switch ($action) {
            case 'create':
                $output =  createSchema($conn);
                break;
            case 'drop':
                $output = dropSchema($conn);
                break;
            case 'insert':
                $output = insertData($conn);
                break;
            case 'queries':
                $output = queries($conn);
                break;
        }
        echo htmlspecialchars($output);
    }
    ?>
    </pre>
</body>
</html>
