<?php
require_once 'auth.php'; 
check_login(); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];

// 1. Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Fetch User's Specific Rentals joined with Car details
$stmt_rentals = $pdo->prepare("
    SELECT r.*, c.model, c.image, c.type, c.daily_rate 
    FROM tbl_rentals r 
    JOIN tbl_cars c ON r.car_id = c.id 
    WHERE r.user_id = ? 
    ORDER BY r.rental_date DESC
");
$stmt_rentals->execute([$user_id]);
$rentals = $stmt_rentals->fetchAll();

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent N' Ride | My Rentals</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-sidebar { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50" x-data="{ showLogoutModal: false }">

    <div class="flex min-h-screen">
        <aside class="w-64 glass-sidebar text-white flex flex-col fixed h-full z-20">
            <div class="p-8">
                <h1 class="text-2xl font-bold tracking-tighter text-indigo-400">Rent N' Ride</h1>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="customerdashboard.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-car-side w-5"></i> <span>Browse Cars</span>
                </a>
                <a href="my_rentals.php" class="flex items-center space-x-3 p-3 bg-indigo-600 text-white shadow-lg rounded-xl transition">
                    <i class="fa-solid fa-clock-rotate-left w-5"></i> <span>My Rentals</span>
                </a>
                <hr class="border-slate-800 my-4 mx-2">
                <a href="customer_profile.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-circle-user w-5"></i> <span>My Profile</span>
                </a>
            </nav>
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-4 px-2">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-xs text-slate-500">Customer</p>
                    </div>
                </div>
                <button @click="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">
            <header class="mb-10">
                <h2 class="text-3xl font-bold text-slate-800">My Rental History</h2>
                <p class="text-slate-500">Track your current and past vehicle bookings.</p>
            </header>

            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-400 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-4">Vehicle</th>
                            <th class="px-6 py-4">Rental Duration</th>
                            <th class="px-6 py-4">Total Days</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($rentals)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark text-4xl mb-4 block"></i>
                                    No rental records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rentals as $rental): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img src="Cars/<?= htmlspecialchars($rental['image']); ?>" class="w-20 h-12 object-cover rounded-lg bg-slate-100 border border-slate-100" alt="Car">
                                        <div>
                                            <p class="font-semibold text-slate-700"><?= htmlspecialchars($rental['model']); ?></p>
                                            <p class="text-[10px] uppercase font-bold text-slate-400"><?= htmlspecialchars($rental['type']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-indigo-600"><?= date('M d, Y', strtotime($rental['start_date'])); ?></span>
                                        <span class="text-slate-300 text-xs">to</span>
                                        <span class="font-medium text-slate-700"><?= date('M d, Y', strtotime($rental['end_date'])); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-600">
                                    <?= $rental['total_days']; ?> Days
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase border
                                        <?php 
                                            if ($rental['status'] == 'Approved') echo 'bg-green-100 text-green-600 border-green-200';
                                            elseif ($rental['status'] == 'Pending') echo 'bg-amber-100 text-amber-600 border-amber-200';
                                            else echo 'bg-slate-100 text-slate-600 border-slate-200';
                                        ?>">
                                        <?= htmlspecialchars($rental['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php $total_price = $rental['daily_rate'] * $rental['total_days']; ?>
                                    <p class="text-sm text-indigo-600 font-bold">₱<?= number_format($total_price, 2); ?></p>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div x-show="showLogoutModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        <div x-show="showLogoutModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" class="relative bg-white rounded-[2rem] max-w-sm w-full p-8 shadow-2xl border border-slate-100">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-6">
                    <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Sign Out?</h3>
                <p class="text-slate-500 mb-8">Are you sure you want to log out of your Rent N' Ride account?</p>
                <div class="flex flex-col gap-3">
                    <a href="auth.php?action=logout" class="w-full py-4 bg-red-500 hover:bg-red-600 text-white font-bold rounded-2xl transition shadow-lg shadow-red-100">Yes, Log Me Out</a>
                    <button @click="showLogoutModal = false" class="w-full py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-2xl transition">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>