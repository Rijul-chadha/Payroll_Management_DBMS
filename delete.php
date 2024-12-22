<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Record</title>
</head>
<body>
    <h1>Delete Record</h1>
    <form method="post" action="execute.php">
        <input type="hidden" name="action" value="delete">
        <label>Table Name: <input type="text" name="table_name" required></label><br>
        <label>Condition (e.g., id = 1): <input type="text" name="condition" required></label><br>
        <button type="submit">Delete</button>
    </form>
    <a href="index.php">Back to Main Page</a>
</body>
</html>

