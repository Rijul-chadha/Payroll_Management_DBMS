<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Value</title>
</head>
<body>
    <h1>Search Value</h1>
    <form method="post" action="execute.php">
        <input type="hidden" name="action" value="search">
        <label>Table Name: <input type="text" name="table_name" required></label><br>
        <label>Column Name: <input type="text" name="column_name" required></label><br>
        <label>Condition (e.g., value = 'something'): <input type="text" name="condition" required></label><br>
        <button type="submit">Search</button>
    </form>
    <a href="index.php">Back to Main Page</a>
</body>
</html>

