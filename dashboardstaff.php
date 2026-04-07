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
$role = $user['role'] ?? 'Staff';

// 2. Fetch Dashboard Stats
$total_cars = $pdo->query("SELECT COUNT(*) FROM tbl_cars")->fetchColumn();
$available_cars = $pdo->query("SELECT COUNT(*) FROM tbl_cars WHERE status = 'Available'")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM tbl_users WHERE role = 'Customer'")->fetchColumn();

// 3. Fetch Recent Activity
$recent_stmt = $pdo->query("SELECT model, status, daily_rate FROM tbl_cars ORDER BY id DESC LIMIT 5");
$recent_cars = $recent_stmt->fetchAll();

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent N' Ride | Staff Dashboard</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-sidebar { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-slate-50" x-data="{ showLogoutModal: false, showAddModal: false }">
    <div class="flex min-h-screen">
        <aside class="w-64 glass-sidebar text-white flex flex-col fixed h-full">
            <div class="p-8">
                <h1 class="text-2xl font-bold tracking-tighter text-indigo-400">Rent N' Ride</h1>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="dashboardstaff.php" 
                class="flex items-center space-x-3 p-3 <?= $current_page == 'dashboardstaff.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-800' ?> rounded-xl transition">
                    <i class="fa-solid fa-chart-pie w-5"></i> <span>Dashboard</span>
                </a>
                <a href="car_manage.php" 
                class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-car-side w-5"></i> <span>Fleet Management</span>
                </a>
                <a href="manage_rentals.php" class="flex items-center space-x-3 p-3 <?= ($current_page == 'manage_rentals.php') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?> rounded-xl transition">
                    <i class="fa-solid fa-file-invoice w-5"></i> 
                    <span>Booking Requests</span>
                </a>
                <hr class="border-slate-800 my-4 mx-2">
                <a href="staff_profile.php" 
                class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-circle-user w-5"></i> <span>My Profile</span>
                </a>
            </nav>
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-4 px-2">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'S', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['username'] ?? 'Staff'); ?></p>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($role); ?></p>
                    </div>
                </div>
                <button @click="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">
            <header class="mb-10">
                <h2 class="text-3xl font-bold text-slate-800">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Staff'); ?>!</h2>
                <p class="text-slate-500">Here's what's happening with the fleet today.</p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 stat-card">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-4">
                        <i class="fa-solid fa-car text-xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-semibold">Total Fleet</p>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $total_cars ?></h3>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 stat-card">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-4">
                        <i class="fa-solid fa-circle-check text-xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-semibold">Ready to Rent</p>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $available_cars ?></h3>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 stat-card">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-4">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-semibold">Total Customers</p>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $total_customers ?></h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800">Recently Added Vehicles</h3>
                            <a href="car_manage.php" class="text-indigo-600 text-sm font-bold hover:underline">View All</a>
                        </div>
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-400 text-xs uppercase">
                                <tr>
                                    <th class="px-6 py-4">Model</th>
                                    <th class="px-6 py-4">Rate</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <?php foreach($recent_cars as $car): ?>
                                <tr>
                                    <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($car['model']) ?></td>
                                    <td class="px-6 py-4 font-bold text-slate-600">₱<?= number_format($car['daily_rate'], 2) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase <?= $car['status'] == 'Available' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600' ?>">
                                            <?= $car['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-indigo-600 p-8 rounded-[2rem] text-white shadow-xl shadow-indigo-200">
                        <h3 class="text-xl font-bold mb-2">Quick Actions</h3>
                        <p class="text-indigo-100 text-sm mb-6">Commonly used staff tools.</p>
                        <div class="space-y-3">
                            <button @click="showAddModal = true" class="block w-full py-3 bg-white/10 hover:bg-white/20 rounded-xl text-center font-bold transition">
                                <i class="fa-solid fa-plus mr-2"></i> Add New Car
                            </button>
                            
                            <a href="car_manage.php" class="block w-full py-3 bg-white/10 hover:bg-white/20 rounded-xl text-center font-bold transition">
                                <i class="fa-solid fa-list-ul mr-2"></i> See Cars List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-lg w-full p-8 shadow-2xl border border-slate-100" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-slate-800">Add New Vehicle</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <form action="add_car.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">Model Name</label>
                        <input type="text" name="model" required placeholder="e.g. Toyota Vios" 
                               class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">Daily Rate (₱)</label>
                        <input type="number" name="daily_rate" step="0.01" required placeholder="0.00" 
                               class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Car Type / Category</label>
                    <select name="type" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        <option value="Business">Business</option>
                        <option value="Family">Family</option>
                        <option value="Camping">Camping</option>
                        <option value="Luxury">Luxury</option>
                        <option value="SUV">SUV</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="Sedan">Sedan</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Vehicle Image</label>
                    <input type="file" name="image" class="w-full p-2 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition">
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-100 mt-4 hover:bg-indigo-700 transition">
                    Confirm Registration
                </button>
            </form>
        </div>
    </div>

    <div x-show="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-sm w-full p-8 shadow-2xl text-center border border-slate-100" x-transition>
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 mb-6">
                <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Sign Out?</h3>
            <p class="text-slate-500 mb-8">Ready to end your staff session?</p>
            <div class="flex flex-col gap-3">
                <a href="auth.php?action=logout" class="w-full py-4 bg-red-500 text-white font-bold rounded-2xl shadow-lg shadow-red-100">Log Me Out</a>
                <button @click="showLogoutModal = false" class="w-full py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl">Cancel</button>
            </div>
        </div>
    </div>
</body>
</html>