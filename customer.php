<?php
require_once 'config.php'; 
session_start();

// Security Check: Only Admins/Staff should see the customer list
$currentRole = $_SESSION['role'] ?? '';
if (!in_array(strtolower($currentRole), ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

try {
    // Fetch users who are NOT Admin or Staff
    $query = "SELECT id, username, role FROM tbl_users WHERE role NOT IN ('ADMIN', 'Admin', 'STAFF', 'Staff')";
    $stmt = $pdo->query($query);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Directory | DriveElite</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-sidebar { background: #0f172a; min-width: 256px; }
    </style>
</head>
<body class="bg-slate-50" x-data="{ showLogoutModal: false, showEditModal: false, editData: { id: '', username: '' } }">
    

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
                <a href="customers.php" class="flex items-center space-x-3 p-3 bg-indigo-600 rounded-xl text-white shadow-lg">
                    <i class="fa-solid fa-users w-5"></i> <span>Customers Section</span>
                </a>
                <a href="car_manage.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-car w-5"></i> <span>Full Inventory</span>
                </a>
                <a href="manage_rentals.php" class="flex items-center space-x-3 p-3 <?= ($current_page == 'manage_rentals.php') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?> rounded-xl transition">
                    <i class="fa-solid fa-file-invoice w-5"></i> 
                    <span>Booking Requests</span>
                </a>
                <a href="admin_profile.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
                    <i class="fa-solid fa-circle-user w-5"></i> <span>My Profile</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-4 px-2">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white ring-2 ring-indigo-500/20">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold truncate w-32"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-[10px] text-indigo-400 font tracking-wider">Administrator</p>
                    </div>
                </div>
                <button @click.prevent="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">
            <header class="mb-10">
                <h2 class="text-3xl font-bold text-slate-800">Customer Section</h2>
                <p class="text-slate-500">View and manage registered non-staff accounts.</p>
            </header>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Account Type</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400">No customers found in the database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                                            <?= strtoupper(substr($customer['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="block font-bold text-slate-700"><?= htmlspecialchars($customer['username']) ?></span>
                                            <span class="text-xs text-slate-400">ID: #<?= $customer['id'] ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-600">
                                        <?= htmlspecialchars($customer['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-center items-center gap-2">
                                        <button @click="showEditModal = true; editData = { id: '<?= $customer['id'] ?>', username: '<?= addslashes($customer['username']) ?>' }" 
                                                class="p-2 w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <a href="process_customers.php?delete=<?= $customer['id']; ?>" 
                                           onclick="return confirm('Permanently remove this customer?')" 
                                           class="p-2 w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
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

    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>
        <form action="process_customers.php" method="POST" class="relative bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl">
            <h3 class="text-2xl font-bold text-slate-800 mb-6 text-center">Update Customer</h3>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" x-model="editData.id">
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Username</label>
                    <input type="text" name="username" x-model="editData.username" required class="w-full px-5 py-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showEditModal = false" class="flex-1 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl">Cancel</button>
                    <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-xl hover:bg-indigo-700 transition">Update Account</button>
                </div>
            </div>
        </form>
    </div>
<div x-show="showLogoutModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
    
    <div x-show="showLogoutModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="relative bg-white rounded-[2rem] max-w-sm w-full p-8 shadow-2xl text-center border border-slate-100">
        
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 mb-6">
            <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-xl"></i>
        </div>
        
        <h3 class="text-xl font-bold text-slate-800">Sign Out?</h3>
        <p class="text-slate-500 mb-8">Ready to end your administrative session?</p>
        
        <div class="flex flex-col gap-3">
            <a href="auth.php?action=logout" class="w-full py-4 bg-red-500 text-white font-bold rounded-2xl shadow-lg shadow-red-100 hover:bg-red-600 transition">
                Log Me Out
            </a>
            <button @click="showLogoutModal = false" class="w-full py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition">
                Cancel
            </button>
        </div>
    </div>
</div>


</body>
</html>