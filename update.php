<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Column</title>
</head>
<body>
    <h1>Update Column</h1>
    <form method="post" action="execute.php">
        <input type="hidden" name="action" value="update">
        <label>Table Name: <input type="text" name="table_name" required></label><br>
        <label>Column Name: <input type="text" name="column_name" required></label><br>
	<label>New Value: <input type="text" name="new_value" required></label><br>
	<label>Condition: <input type="text" name="condition" required></label><br>
        <button type="submit">Update</button>
    </form>
    <br>
    <a href="index.php">Back to Main Page</a>
</body>
</html>
