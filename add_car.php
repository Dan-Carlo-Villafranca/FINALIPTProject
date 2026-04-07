<?php
session_start();
require_once 'config.php'; // Using your existing config

// Safety Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'ADMIN' && $_SESSION['role'] !== 'STAFF')) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $model = trim($_POST['model']);
    $type = trim($_POST['type']);
    $rate = $_POST['daily_rate'];
    $status = 'Available'; 

    // --- NEW IMAGE HANDLING LOGIC ---
    $image_name = "default_car.jpg"; // Default if upload fails

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "Cars/";
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        
        // Generate a unique name: e.g., 1710153000_toyota_vios.png
        $new_filename = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "_", strtolower($model)) . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        // Physically move the file from 'Downloads' to your 'Cars' folder
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_name = $new_filename;
        }
    }
    // --------------------------------

    if (!empty($model) && !empty($type) && !empty($rate)) {
        try {
            // Updated to include 'image' column
            $stmt = $pdo->prepare("INSERT INTO tbl_cars (model, type, daily_rate, status, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$model, $type, $rate, $status, $image_name]);
            
            header("Location: car_manage.php?msg=car_added");
            exit;
        } catch (PDOException $e) {
            die("Error adding car: " . $e->getMessage());
        }
    }
}
?>