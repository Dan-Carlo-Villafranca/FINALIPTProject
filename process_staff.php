<?php
require_once 'config.php';
session_start();

// Check if Admin
$role = $_SESSION['role'] ?? '';
if ($role !== 'ADMIN' && $role !== 'Admin') { exit("Unauthorized Access"); }

// Handle ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $user = trim($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO tbl_users (username, password, role) VALUES (?, ?, 'STAFF')");
    $stmt->execute([$user, $pass]);
    header("Location: manage_staff.php?success=1");
}

// HANDLE EDIT (New Logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $user = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($password)) {
        // Update both
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE tbl_users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$user, $passHash, $id]);
    } else {
        // Update only username
        $stmt = $pdo->prepare("UPDATE tbl_users SET username = ? WHERE id = ?");
        $stmt->execute([$user, $id]);
    }
    header("Location: manage_staff.php?updated=1");
    exit();
}

// HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM tbl_users WHERE id = ? AND role != 'ADMIN'");
        $stmt->execute([$id]);
    }
    header("Location: manage_staff.php?deleted=1");
    exit();
}