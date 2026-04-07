<?php
require_once 'config.php'; 
session_start();

// 1. Security Check
$currentRole = $_SESSION['role'] ?? '';
if ($currentRole !== 'ADMIN' && $currentRole !== 'Admin') {
    header("Location: login.php");
    exit();
}

// 2. Fetch staff members
try {
    $query = "SELECT id, username, role FROM tbl_users WHERE role IN ('ADMIN', 'Admin', 'STAFF', 'Staff')";
    $stmt = $pdo->query($query);
    $staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff | Rent N' Ride</title>
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

<body class="bg-slate-50" 
    x-data="{ 
        showAddModal: false, 
        showEditModal: false, 
        showLogoutModal: false,
        showSuccess: <?= (isset($_GET['success']) || isset($_GET['updated'])) ? 'true' : 'false' ?>,
        editData: { id: '', username: '' } 
    }"
    x-init="if(showSuccess) { setTimeout(() => { showSuccess = false; window.history.replaceState({}, document.title, window.location.pathname); }, 3000) }">

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
                <a href="manage_staff.php" class="flex items-center space-x-3 p-3 bg-indigo-600 rounded-xl text-white shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-users w-5"></i> <span>Manage Staff</span>
                </a>
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
                <!-- <hr class="border-slate-800 my-4 mx-2"> -->
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
                <button @click="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800">Staff Management</h2>
                    <p class="text-slate-500">Add, edit, or remove system operators.</p>
                </div>
                <button @click="showAddModal = true" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 hover:bg-indigo-700 transition shadow-lg">
                    <i class="fa-solid fa-user-plus text-sm"></i> Add New Staff
                </button>
            </header>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Username</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($staff_members as $staff): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold">
                                        <?= strtoupper(substr($staff['username'], 0, 1)) ?>
                                    </div>
                                    <span class="font-bold text-slate-700"><?= htmlspecialchars($staff['username']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider 
                                    <?= (strtolower($staff['role']) == 'admin') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600'; ?>">
                                    <?= htmlspecialchars($staff['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex justify-center items-center gap-2">
                                    <button @click="showEditModal = true; editData = { id: '<?= $staff['id'] ?>', username: '<?= addslashes($staff['username']) ?>' }" 
                                            class="p-2 w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <?php if($staff['id'] != $_SESSION['user_id']): ?>
                                    <a href="process_staff.php?delete=<?= $staff['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to remove this account?')" 
                                       class="p-2 w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>
        <form action="process_staff.php" method="POST" class="relative bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl">
            <h3 class="text-2xl font-bold text-slate-800 mb-6 text-center">New Staff Account</h3>
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <input type="text" name="username" placeholder="Username" required class="w-full px-5 py-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500">
                <!-- <input type="email" name="email" placeholder="Email Address" required class="w-full px-5 py-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500"> -->
                <input type="password" name="password" placeholder="Password" required class="w-full px-5 py-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-xl hover:bg-indigo-700 transition mt-4">Create Account</button>
            </div>
        </form>
    </div>

    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>
        <form action="process_staff.php" method="POST" class="relative bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl border border-slate-100">
            <h3 class="text-2xl font-bold text-slate-800 mb-6 text-center">Edit Profile</h3>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" x-model="editData.id">
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Username</label>
                    <input type="text" name="username" x-model="editData.username" required class="w-full px-5 py-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">New Password (Optional)</label>
                    <input type="password" name="password" placeholder="Leave blank to keep same" class="w-full px-5 py-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showEditModal = false" class="flex-1 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl">Cancel</button>
                    <button type="submit" class="flex-1 py-4 bg-slate-900 text-white font-bold rounded-2xl shadow-xl hover:bg-black transition">Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    <div x-show="showLogoutModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        
        <div x-show="showLogoutModal" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="relative bg-white rounded-[2.5rem] max-w-sm w-full p-10 shadow-2xl text-center border border-slate-100">
            
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-8">
                <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Sign Out?</h3>
            <p class="text-slate-500 mb-10">Are you sure you want to log out of your Rent N' Ride account?</p>
            
            <div class="flex flex-col gap-3">
                <a href="auth.php?action=logout" class="w-full py-4 bg-red-500 text-white font-bold rounded-2xl hover:bg-red-600 transition shadow-lg shadow-red-500/20">
                    Yes, Log Me Out
                </a>
                <button @click="showLogoutModal = false" class="w-full py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition">
                    Cancel
                </button>
                </div>
            </div>
    </div>

</body>
</html>