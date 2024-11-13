<?php
$dbHost = getenv('MYSQL_HOST');
$dbUsername = getenv('MYSQL_USER');
$dbPassword = getenv('MYSQL_PASS');
$dbName = getenv('MYSQL_DATABASE');
$dbPort = getenv('MYSQL_PORT');

$conn = mysqli_init();

$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
$conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
$conn->real_connect($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort, NULL, MYSQLI_CLIENT_SSL);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

mysqli_select_db($conn, $dbName) or die ("Invalid Database");

?>
