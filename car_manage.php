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

// 2. Fetch Fleet Inventory
$stmt_cars = $pdo->query("SELECT * FROM tbl_cars");
$cars = $stmt_cars->fetchAll();

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent N' Ride | Staff Management</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-sidebar { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
        
        /* Smooth fade-in for alerts */
        .alert-in { animation: slideIn 0.4s ease-out forwards; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-50" x-data="{ 
    showAddModal: new URLSearchParams(window.location.search).has('openModal'), 
    showLogoutModal: false,
    showEditModal: false,
    showDeleteModal: false,
    selectedCar: {},
    confirmDelete(car) {
        this.selectedCar = car;
        this.showDeleteModal = true;
    },
    openEdit(car) {
        this.selectedCar = JSON.parse(JSON.stringify(car)); 
        this.showEditModal = true;
    }
}">

    <?php if (isset($_GET['msg'])): ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-cloak
             class="fixed top-6 right-6 z-[100] alert-in">
            <?php if ($_GET['msg'] == 'deleted'): ?>
                <div class="bg-red-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3">
                    <i class="fa-solid fa-trash-can"></i> <span>Vehicle removed successfully!</span>
                </div>
            <?php elseif ($_GET['msg'] == 'updated'): ?>
                <div class="bg-emerald-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3">
                    <i class="fa-solid fa-check-circle"></i> <span>Vehicle updated successfully!</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="flex min-h-screen">
        <aside class="w-64 glass-sidebar text-white flex flex-col fixed h-full">
            <div class="p-8">
                <h1 class="text-2xl font-bold tracking-tighter text-indigo-400">Rent N' Ride</h1>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="dashboardstaff.php" class="flex items-center space-x-3 p-3 <?= $current_page == 'dashboardstaff.php' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800' ?> rounded-xl transition">
                    <i class="fa-solid fa-chart-pie w-5"></i> <span>Dashboard</span>
                </a>
                <a href="car_manage.php" class="flex items-center space-x-3 p-3 <?= $current_page == 'car_manage.php' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800' ?> rounded-xl transition">
                    <i class="fa-solid fa-car-side w-5"></i> <span>Fleet Management</span>
                </a>
                <hr class="border-slate-800 my-4 mx-2">
                <a href="staff_profile.php" class="flex items-center space-x-3 p-3 <?= $current_page == 'staff_profile.php' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800' ?> rounded-xl transition">
                    <i class="fa-solid fa-circle-user w-5"></i> <span>My Profile</span>
                </a>
            </nav>
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-4 px-2">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white">
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
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800">Fleet Overview</h2>
                    <p class="text-slate-500">Manage your vehicle inventory and status.</p>
                </div>
                <button @click="showAddModal = true" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 hover:bg-black transition shadow-lg">
                    <i class="fa-solid fa-plus"></i> Add New Car
                </button>
            </header>

            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-400 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-4">Vehicle</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Daily Rate</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach (array_reverse($cars) as $car): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img src="Cars/<?= htmlspecialchars($car['image']); ?>" class="w-20 h-12 object-cover rounded-lg bg-slate-100 border border-slate-100" alt="Car">
                                    <span class="font-semibold text-slate-700"><?= htmlspecialchars($car['model']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500"><?= htmlspecialchars($car['type']); ?></td>
                            <td class="px-6 py-4">
                                <form action="update_status.php" method="POST">
                                    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" 
                                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase cursor-pointer outline-none
                                        <?php 
                                            // Grouping 'Rented' and 'Booked' for the same visual style
                                            if ($car['status'] == 'Available') {
                                                echo 'bg-green-100 text-green-600 border-green-200';
                                            } elseif ($car['status'] == 'Booked') {
                                                echo 'bg-amber-100 text-amber-600 border-amber-200';
                                            } elseif ($car['status'] == 'Repairs') {
                                                echo 'bg-red-100 text-red-600 border-red-200';
                                            } else {
                                                echo 'bg-slate-100 text-slate-600 border-slate-200';
                                            }
                                        ?> border">
                                        <option value="Available" <?= $car['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                                        <option value="Booked" <?= $car['status'] == 'Booked' ? 'selected' : '' ?>>Booked</option>
                                        <option value="Repairs" <?= $car['status'] == 'Repairs' ? 'selected' : '' ?>>Repairs</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-indigo-600 font-bold">₱<?= number_format($car['daily_rate'], 2); ?></p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click="openEdit(<?= htmlspecialchars(json_encode($car)) ?>)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button @click="confirmDelete(<?= htmlspecialchars(json_encode($car)) ?>)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-lg w-full p-8 shadow-2xl border border-slate-100">
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
                        <input type="text" name="model" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">Daily Rate (₱)</label>
                        <input type="number" name="daily_rate" step="0.01" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Car Type</label>
                    <select name="type" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <option value="Business">Business</option>
                        <option value="Family">Family</option>
                        <option value="Camping">Camping</option>
                        <option value="Luxury">Luxury</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Vehicle Image</label>
                    <input type="file" name="image" class="w-full p-2 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-600">
                </div>
                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg mt-4 hover:bg-indigo-700 transition">Confirm Registration</button>
            </form>
        </div>
    </div>

    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-lg w-full p-8 shadow-2xl border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-slate-800">Edit Vehicle</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <form action="edit_car.php" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="return confirm('Proceed with changes?')">
                <input type="hidden" name="car_id" :value="selectedCar.id">
                <div class="flex flex-col items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <img :src="'Cars/' + selectedCar.image" class="w-32 h-20 object-cover rounded-xl shadow-md">
                    <input type="file" name="image" class="text-xs">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Model</label>
                    <input type="text" name="model" :value="selectedCar.model" class="w-full p-3 bg-slate-50 border rounded-xl">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">Daily Rate</label>
                        <input type="number" name="daily_rate" :value="selectedCar.daily_rate" class="w-full p-3 bg-slate-50 border rounded-xl">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">Type</label>
                        <select name="type" class="w-full p-3 bg-slate-50 border rounded-xl" x-model="selectedCar.type">
                            <option value="Business">Business</option>
                            <option value="Family">Family</option>
                            <option value="Camping">Camping</option>
                            <option value="Luxury">Luxury</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">Current Status</label>
                    <select name="status" class="w-full p-3 bg-slate-50 border rounded-xl" x-model="selectedCar.status">
                        <option value="Available">Available</option>
                        <option value="Booked">Booked</option>
                        <option value="Repairs">Repairs</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl mt-4">Save Changes</button>
            </form>
        </div>
    </div>

    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-sm w-full p-8 shadow-2xl text-center border border-slate-100">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 mb-6">
                <i class="fa-solid fa-triangle-exclamation text-red-500 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Remove Vehicle?</h3>
            <p class="text-slate-500 mb-8">Are you sure you want to delete <span class="font-bold" x-text="selectedCar.model"></span>?</p>
            <div class="flex flex-col gap-3">
                <a :href="'delete_car.php?id=' + selectedCar.id" class="w-full py-4 bg-red-500 text-white font-bold rounded-2xl shadow-lg">Yes, Delete Car</a>
                <button @click="showDeleteModal = false" class="w-full py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl">Cancel</button>
            </div>
        </div>
    </div>

    <div x-show="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-sm w-full p-8 shadow-2xl text-center border border-slate-100">
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

    <script>
        if (window.location.search.includes('openModal')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>