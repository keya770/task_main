<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (isset($_POST['id'])) {
    $user_id = $_POST['id'];
    $conn->query("UPDATE users SET is_delete = 1 WHERE id = $user_id ");
    echo 'success'; 
}}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (isset($_POST['role']) && isset($_POST['user_id'])) {
    $role = $_POST['role'];
    $user_id = $_POST['user_id'];
    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $role, $user_id); 
    if ($stmt->execute()) {
        echo 'success'; 
    } else {
        echo 'error'; 
    }
    $stmt->close();
} else {
    // echo 'Invalid input'; 
}
}

// status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (isset($_POST['status']) && isset($_POST['user_id'])) {
    $status = $_POST['status'];
    $user_id = $_POST['user_id'];
    $conn->query("UPDATE users SET status = $status WHERE id = $user_id ");
    echo 'success'; 
}}


?>