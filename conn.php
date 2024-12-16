<?php
$db = "test";
$db_user = "root";
$db_pass = "";
$db_host = "localhost";

$conn = new mysqli($db_host,$db_user,$db_pass,$db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


