<?php
require_once 'auth.php';
check_login(); 
prevent_back_button(); 

// --- FETCH DATA (Universal for all features) ---
$cars = []; 
try {
    $stmt = $pdo->query("SELECT * FROM tbl_cars ORDER BY id DESC");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_db = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent N' Ride | Admin Control</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-sidebar { background: #0f172a; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50" x-data="{ showLogoutModal: false }">
    <div class="flex min-h-screen">
        <aside class="w-64 glass-sidebar text-white flex flex-col fixed h-full">
            <div class="p-8">
                <h1 class="text-2xl font-bold tracking-tighter text-indigo-400">Rent N' Ride</h1>
                <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-2 py-1 rounded-md uppercase font-bold mt-2 inline-block italic">Admin Portal</span>
            </div>
            
            <nav class="flex-1 px-4 space-y-2">
                <a href="index.php" class="flex items-center space-x-3 p-3 bg-indigo-600 rounded-xl text-white shadow-lg shadow-indigo-500/20 transition">
                    <i class="fa-solid fa-gauge-high w-5"></i> <span>System Overview</span>
                </a>
                <a href="manage_staff.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl">
                    <i class="fa-solid fa-users w-5"></i> <span>Manage Staff</span>
                </a>
                <a href="customer.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-users w-5"></i> <span>Customers Section</span>
                </a>
                <a href="car_manage.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-car w-5"></i> <span>Full Inventory</span>
                </a>
                <a href="manage_rentals.php" class="flex items-center space-x-3 p-3 <?= ($current_page == 'manage_rentals.php') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?> rounded-xl transition">
                    <i class="fa-solid fa-file-invoice w-5"></i> 
                    <span>Booking Requests</span>
                </a>
                <a href="admin_profile.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition group">
                    <i class="fa-solid fa-circle-user w-5 group-hover:text-indigo-400 transition"></i> 
                    <span>My Profile</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-4 px-2 p-2 rounded-xl">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white ring-2 ring-indigo-500/20">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold truncate w-32"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                        <p class="text-[10px] text-indigo-400 font tracking-wider">Administrator</p>
                    </div>
                </div>
                <button @click="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">
            <?php if(isset($_GET['status']) || isset($_GET['msg'])): ?>
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 flex items-center gap-3">
                    <i class="fa-solid fa-circle-check w-10"></i>
                    <span class="font-bold text-sm">Action completed successfully.</span>
                </div>
            <?php endif; ?>

            <header class="flex justify-between items-center mb-10">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">System Control</h2>
                    <p class="text-slate-500">Global fleet oversight & administrative access.</p>
                </div>
                <button onclick="openModal('addCarModal')" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 hover:bg-indigo-700 transition shadow-xl shadow-indigo-200">
                    <i class="fa-solid fa-plus text-sm"></i> Add New Car
                </button>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm transition">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-4">
                        <i class="fa-solid fa-car-side text-xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Total Fleet</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?php echo count($cars); ?> Vehicles</h3>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm transition">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-4">
                        <i class="fa-solid fa-shield-halved text-xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Access Protocol</p>
                    <i class="fa-solid fa-shield-halved text-xl"></i>
                    <h3 class="text-2xl font-bold text-slate-800">Admin-Only</h3>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm transition">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-4">
                        <i class="fa-solid fa-money-bill-trend-up text-xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Monthly Revenue</p>
                    <h3 class="text-2xl font-bold text-slate-800">₱74,250.00</h3>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Vehicle Inventory</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">Vehicle Model</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Daily Rate</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($cars as $car): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-700"><?php echo htmlspecialchars($car['model']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-md"><?php echo htmlspecialchars($car['type']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-indigo-600 font-bold">₱<?php echo number_format($car['daily_rate'], 2); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-[10px] font-bold uppercase"><?php echo htmlspecialchars($car['status']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center flex justify-center gap-2">
                                    <button onclick='openEditModal(<?php echo json_encode($car); ?>)' class="text-slate-400 hover:text-indigo-600 transition p-2">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="delete_car.php?id=<?php echo $car['id']; ?>" onclick="return confirm('Remove vehicle?')" class="text-slate-400 hover:text-red-600 transition p-2">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="addCarModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="p-8 bg-slate-900 text-white flex justify-between items-center">
                <h3 class="text-xl font-bold">Add New Vehicle</h3>
                <button onclick="closeModal('addCarModal')" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="add_car.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-5">
                
                <div class="relative group">
                    <label class="block mb-2 text-xs font-bold text-slate-400 uppercase tracking-widest">Vehicle Image</label>
                    <div class="relative border-2 border-dashed border-slate-200 rounded-2xl p-4 transition hover:border-indigo-400 bg-slate-50">
                        <input type="file" name="car_image" accept="image/*" required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="text-center">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-300 mb-2 group-hover:text-indigo-500 transition"></i>
                            <p class="text-xs text-slate-500">Click or drag image to upload</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <input type="text" name="model" required placeholder="Vehicle Model (e.g. Toyota Vios)" 
                        class="w-full px-5 py-4 bg-slate-50 border border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl outline-none transition">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="type" required placeholder="Category" 
                            class="px-5 py-4 bg-slate-50 border border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl outline-none transition">
                        <input type="number" name="daily_rate" step="0.01" required placeholder="Daily Rate (₱)" 
                            class="px-6 py-4 bg-slate-50 border border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl outline-none transition">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition">
                    Add to Fleet
                </button>
            </form>
        </div>
    </div>

    <div x-show="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-sm w-full p-8 shadow-2xl">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 rounded-full bg-red-50 flex items-center justify-center mb-6">
                    <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Sign Out?</h3>
                <p class="text-slate-500 mb-8">Ready to leave Rent N' Ride?</p>
                <div class="flex flex-col gap-3">
                    <a href="auth.php?action=logout" class="w-full py-4 bg-red-500 text-white font-bold rounded-2xl text-center">Log Out</a>
                    <button @click="showLogoutModal = false" class="w-full py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openEditModal(car) { console.log("Editing:", car); }
    </script>
</body>
</html>