<?php
require_once 'auth.php';
check_login();

if (isset($_GET['id']) && isset($_GET['action'])) {
    $rental_id = $_GET['id'];
    $action = $_GET['action'];
    $newStatus = ($action == 'approve') ? 'Approved' : 'Declined';

    try {
        $pdo->beginTransaction();

        // 1. Update the rental status
        $stmt = $pdo->prepare("UPDATE tbl_rentals SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $rental_id]);

        // 2. If declined, set the car back to 'Available' automatically
        if ($action == 'decline') {
            $stmt_car = $pdo->prepare("UPDATE tbl_cars SET status = 'Available' WHERE id = (SELECT car_id FROM tbl_rentals WHERE id = ?)");
            $stmt_car->execute([$rental_id]);
        }

        $pdo->commit();
        header("Location: manage_rentals.php?msg=success");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}