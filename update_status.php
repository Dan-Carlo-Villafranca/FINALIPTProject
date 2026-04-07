<?php
require_once 'auth.php'; 
check_login(); // This ensures only authorized users (Staff or Admin) can run this

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id']) && isset($_POST['status'])) {
    $car_id = $_POST['car_id'];
    $status = $_POST['status'];

    try {
        // This works for any car in the tbl_cars table
        $stmt = $pdo->prepare("UPDATE tbl_cars SET status = ? WHERE id = ?");
        $stmt->execute([$status, $car_id]);

        // Returns the user back to whatever page they came from
        $referer = $_SERVER['HTTP_REFERER'] ?? 'car_manage.php';
        header("Location: " . $referer . "?success=status_updated");
        exit();
    } catch (PDOException $e) {
        header("Location: car_manage.php?error=db_error");
        exit();
    }
} else {
    header("Location: car_manage.php");
    exit();
}