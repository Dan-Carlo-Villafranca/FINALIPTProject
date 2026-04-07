<?php
require_once 'auth.php'; 
check_login(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    $user_id = $_SESSION['user_id'];
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    // Calculate total days (e.g., May 1 to May 2 is 1 day)
    $date1 = new DateTime($start_date);
    $date2 = new DateTime($end_date);
    $interval = $date1->diff($date2);
    $total_days = $interval->days;

    // Ensure at least 1 day is charged
    if ($total_days < 1) $total_days = 1;

    try {
        $pdo->beginTransaction();

        // 1. Record rental with the new columns from your schema
        $stmt = $pdo->prepare("INSERT INTO tbl_rentals (user_id, car_id, rental_date, status, start_date, end_date) VALUES (?, ?, NOW(), 'Pending', ?, ?)");
        $stmt->execute([$user_id, $car_id, $start_date, $end_date]);

        // 2. Mark car as 'Booked'
        $update = $pdo->prepare("UPDATE tbl_cars SET status = 'Booked' WHERE id = ?");
        $update->execute([$car_id]);

        $pdo->commit();
        header("Location: customerdashboard.php?success=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: customerdashboard.php?error=1");
        exit();
    }
}