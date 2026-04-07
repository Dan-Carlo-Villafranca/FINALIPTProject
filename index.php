<?php
session_start();
require_once 'config.php';

// 1. Protection: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Define the role from session
$role = $_SESSION['role'] ?? 'Customer';

// 3. Fetch data for dashboards (Loaded once here, used in the included files)
$carStmt = $pdo->query("SELECT * FROM tbl_cars");
$cars = $carStmt->fetchAll(PDO::FETCH_ASSOC);

// 4. ROUTING: Load exactly ONE file based on the specific role
if ($role === 'ADMIN') {
    include 'admindashboard.php';
} elseif ($role === 'STAFF') {
    include 'dashboardstaff.php';
} else {
    // Default to Customer if role is 'Customer' or anything else
    include 'customerdashboard.php';
}
exit;
?>