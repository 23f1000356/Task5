<?php
session_start();
include('db_connection.php');

if (!isset($_GET['id'])) {
    header("Location: dashboard.php"); 
}

$id = $_GET['id'];


$stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: dashboard.php"); 
exit;
?>