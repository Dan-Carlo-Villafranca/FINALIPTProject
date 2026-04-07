<?php
session_start();
require_once 'config.php';
require_once 'auth.php'; // Using your existing auth file for consistency

// 1. Role Check: Match the capitalization used in your database/session
$user_role = strtoupper($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || ($user_role !== 'ADMIN' && $user_role !== 'STAFF')) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 2. Optional but Recommended: Delete the physical image file
        $stmt_img = $pdo->prepare("SELECT image FROM tbl_cars WHERE id = ?");
        $stmt_img->execute([$id]);
        $car = $stmt_img->fetch();

        if ($car && $car['image']) {
            $image_path = "Cars/" . $car['image'];
            if (file_exists($image_path)) {
                unlink($image_path); // Deletes the file from the folder
            }
        }

        // 3. Delete the record
        $stmt = $pdo->prepare("DELETE FROM tbl_cars WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            // 4. Redirect back to Management, NOT index.php
            header("Location: car_manage.php?msg=deleted");
            exit;
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>