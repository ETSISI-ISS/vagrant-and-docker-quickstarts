<?php
$servername = "database";  // Name of the MySQL container
$username = "root";
$password = "rootpassword"; // This should match MYSQL_ROOT_PASSWORD
$dbname = "my_database";    // Change this to your desired database name

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the database exists
$db_exists_query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'";
$result = $conn->query($db_exists_query);

if ($result->num_rows > 0) {
    echo "The database '$dbname' already exists.";
} else {
    // Create database
    $sql = "CREATE DATABASE " . $dbname;
    if ($conn->query($sql) === TRUE) {
        echo "Database '$dbname' created successfully.";
    } else {
        echo "Error creating database: " . $conn->error;
    }
}

$conn->close();
?>

