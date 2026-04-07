<?php
session_start();
require_once 'config.php';

// FIX 1: Allow both Staff and Admin to edit cars (or adjust to match your session capitalization)
// I've used strtoupper to make it case-insensitive for safety
$user_role = strtoupper($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || ($user_role !== 'ADMIN' && $user_role !== 'STAFF')) {
    header("Location: login.php"); // Redirect to login if unauthorized
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['car_id'])) {
    $id = $_POST['car_id'];
    $model = trim($_POST['model']);
    $type = trim($_POST['type']);
    $rate = $_POST['daily_rate'];
    
    // FIX 2: Handle status. If status isn't in the edit modal, 
    // we should fetch the current status first or remove it from the UPDATE query.
    $status = isset($_POST['status']) ? $_POST['status'] : null;

    try {
        if ($status) {
            $sql = "UPDATE tbl_cars SET model=?, type=?, daily_rate=?, status=? WHERE id=?";
            $params = [$model, $type, $rate, $status, $id];
        } else {
            // Update without touching status if it wasn't in the form
            $sql = "UPDATE tbl_cars SET model=?, type=?, daily_rate=? WHERE id=?";
            $params = [$model, $type, $rate, $id];
        }

        $stmt = $pdo->prepare($sql);
        
        // FIX 3: Check for image upload while we are at it
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $new_image_name = "car_" . time() . "." . $file_ext;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], "Cars/" . $new_image_name)) {
                // Update with image
                $sql = "UPDATE tbl_cars SET model=?, type=?, daily_rate=?, image=? WHERE id=?";
                $params = [$model, $type, $rate, $new_image_name, $id];
                $stmt = $pdo->prepare($sql);
            }
        }

        if ($stmt->execute($params)) {
            // FIX 4: Redirect back to car_manage.php, not index
            header("Location: car_manage.php?msg=updated");
            exit;
        }
    } catch (PDOException $e) {
        die("Error updating record: " . $e->getMessage());
    }
}
?>