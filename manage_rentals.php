<?php
require_once 'auth.php'; 
check_login(); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Only allow Admin or Staff
$currentRole = $_SESSION['role'] ?? '';
if (!in_array(strtolower($currentRole), ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

// Fetch all rentals with user and car details
$stmt = $pdo->query("
    SELECT r.*, u.username, c.model, c.image, c.daily_rate 
    FROM tbl_rentals r
    JOIN tbl_users u ON r.user_id = u.id
    JOIN tbl_cars c ON r.car_id = c.id
    ORDER BY r.rental_date DESC
");
$rentals = $stmt->fetchAll();
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent N' Ride | Rental Requests</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-sidebar { background: #0f172a; min-width: 256px; }
    </style>
</head>
<body class="bg-slate-50" x-data="{ showLogoutModal: false }">

    <div class="flex min-h-screen">
        <aside class="w-64 glass-sidebar text-white flex flex-col fixed h-full z-40">
            <div class="p-8">
                <h1 class="text-2xl font-bold tracking-tighter text-indigo-400">Rent N' Ride</h1>
                <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-2 py-1 rounded-md uppercase font-bold mt-2 inline-block italic">Admin Portal</span>
            </div>
            
            <nav class="flex-1 px-4 space-y-2">
                <a href="index.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-gauge-high w-5"></i> <span>System Overview</span>
                </a>
                <a href="manage_staff.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-users-gear w-5"></i> <span>Manage Staff</span>
                </a>
                <a href="customers.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-users w-5"></i> <span>Customers Section</span>
                </a>
                <a href="car_manage.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-car w-5"></i> <span>Full Inventory</span>
                </a>
                <a href="manage_rentals.php" class="flex items-center space-x-3 p-3 bg-indigo-600 rounded-xl text-white shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-file-invoice w-5"></i> <span>Booking Requests</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-4 px-2">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white ring-2 ring-indigo-500/20">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'S', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold truncate w-32"><?= htmlspecialchars($_SESSION['username'] ?? 'Staff'); ?></p>
                        <p class="text-[10px] text-indigo-400 font tracking-wider">Operator</p>
                    </div>
                </div>
                <button @click="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">
            <header class="mb-10">
                <h2 class="text-3xl font-bold text-slate-800">Rental Requests</h2>
                <p class="text-slate-500">Monitor and manage customer vehicle bookings.</p>
            </header>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Vehicle Details</th>
                            <th class="px-6 py-4">Schedule</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($rentals)): ?>
                            <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400">No rental requests found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rentals as $r): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                                            <?= strtoupper(substr($r['username'], 0, 1)) ?>
                                        </div>
                                        <span class="font-bold text-slate-700"><?= htmlspecialchars($r['username']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <img src="Cars/<?= htmlspecialchars($r['image']); ?>" class="w-16 h-10 object-cover rounded-lg bg-slate-100 border border-slate-100">
                                        <div>
                                            <span class="block font-bold text-slate-700 text-sm"><?= htmlspecialchars($r['model']); ?></span>
                                            <span class="text-[10px] text-indigo-500 font-bold uppercase tracking-widest">₱<?= number_format($r['daily_rate'], 0); ?> / day</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-xs space-y-1">
                                        <p class="text-slate-700 font-semibold italic">
                                            <?= date('M d, Y', strtotime($r['start_date'])) ?> — <?= date('M d, Y', strtotime($r['end_date'])) ?>
                                        </p>
                                        <p class="text-slate-400 font-bold"><?= $r['total_days'] ?> Total Days</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider 
                                        <?= ($r['status'] == 'Approved') ? 'bg-emerald-100 text-emerald-600' : ($r['status'] == 'Declined' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'); ?>">
                                        <?= $r['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-center items-center gap-2">
                                        <?php if ($r['status'] == 'Pending'): ?>
                                            <a href="process_approval.php?id=<?= $r['id'] ?>&action=approve" 
                                               class="p-2 w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                            <a href="process_approval.php?id=<?= $r['id'] ?>&action=decline" 
                                               onclick="return confirm('Decline this request?')"
                                               class="p-2 w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-slate-300 uppercase italic">Decision Logged</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div x-show="showLogoutModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-[2.5rem] max-w-sm w-full p-10 shadow-2xl text-center border border-slate-100">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-8">
                <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Sign Out?</h3>
            <p class="text-slate-500 mb-10">Are you sure you want to end your administrative session?</p>
            <div class="flex flex-col gap-3">
                <a href="auth.php?action=logout" class="w-full py-4 bg-red-500 text-white font-bold rounded-2xl hover:bg-red-600 transition shadow-lg shadow-red-500/20">Yes, Log Me Out</a>
                <button @click="showLogoutModal = false" class="w-full py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition">Cancel</button>
            </div>
        </div>
    </div>

</body>
</html>