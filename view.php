<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Table</title>
</head>
<body>
    <h1>View Table</h1>
    <form method="post" action="execute.php">
        <input type="hidden" name="action" value="view">
        <label>Table Name: <input type="text" name="table_name" required></label><br>
        <button type="submit">View</button>
    </form>
    <a href="index.php">Back to Main Page</a>
</body>
</html>

