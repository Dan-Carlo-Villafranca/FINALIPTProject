<?php
require_once 'auth.php'; 
check_login(); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch Available Vehicles
$stmt_cars = $pdo->prepare("SELECT * FROM tbl_cars WHERE status = 'Available' ORDER BY id DESC");
$stmt_cars->execute();
$cars = $stmt_cars->fetchAll();

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta charset="UTF-8">
    <title>Rent N' Ride | Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-sidebar { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
        
        /* Custom scrollbar for categories if they overflow on mobile */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-50" x-data="{ 
    showLogoutModal: false, 
    selectedCar: null, 
    search: '', 
    filter: 'All' 
}">
<form id="rentalForm" action="rental_process.php" method="POST" style="display: none;">
    <input type="hidden" name="car_id" x-bind:value="selectedCar ? selectedCar.id : ''">
    <input type="hidden" name="start_date" id="form_start_date">
    <input type="hidden" name="end_date" id="form_end_date">
</form>

    <div class="flex min-h-screen">
        <aside class="w-64 glass-sidebar text-white flex flex-col fixed h-full z-20">
            <div class="p-8">
                <h1 class="text-2xl font-bold tracking-tighter text-indigo-400">Rent N' Ride</h1>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="customerdashboard.php" class="flex items-center space-x-3 p-3 bg-indigo-600 text-white shadow-lg rounded-xl transition">
                    <i class="fa-solid fa-car-side w-5"></i> <span>Browse Cars</span>
                </a>
                <a href="my_rentals.php" class="flex items-center space-x-3 p-3 text-slate-400 hover:bg-slate-800 rounded-xl transition">
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
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?php echo $_SESSION['username']; ?></p>
                        <p class="text-xs text-slate-500">Customer</p>
                    </div>
                </div>
                <button @click.prevent="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">

            <?php if (isset($_GET['success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-2xl flex items-center shadow-sm">
                <i class="fa-solid fa-circle-check mr-2"></i>
                Rental request submitted successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-2xl flex items-center shadow-sm">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                Something went wrong. Please try again.
            </div>
        <?php endif; ?>

            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800">Available Vehicles</h2>
                    <p class="text-slate-500">Choose your ride for today, <?php echo $_SESSION['username']; ?>!</p>
                </div>

                <div class="relative w-full md:w-96 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input 
                        type="text" 
                        x-model="search"
                        placeholder="Search model, brand, or type..." 
                        class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-[2rem] focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm shadow-slate-200/50 text-sm"
                    >
                </div>
            </header>

            <div class="flex items-center gap-3 mb-10 overflow-x-auto pb-2 hide-scrollbar">
                <template x-for="cat in ['All', 'Business', 'Family', 'Camping', 'Luxury']">
                    <button 
                        @click="filter = cat"
                        :class="filter === cat ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100 border-indigo-600' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl text-xs font-bold uppercase tracking-widest border transition-all active:scale-95 whitespace-nowrap"
                        x-text="cat"
                    ></button>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($cars as $car): ?>
                <div 
                    x-show="
                        (filter === 'All' || '<?php echo strtolower($car['type']); ?>'.includes(filter.toLowerCase())) &&
                        ('<?php echo strtolower($car['model']); ?>'.includes(search.toLowerCase()) || '<?php echo strtolower($car['type']); ?>'.includes(search.toLowerCase()))
                    "
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col group hover:translate-y-[-4px] transition-all duration-300"
                >
                    <div class="h-48 overflow-hidden bg-slate-100 relative">
                        <?php if (!empty($car['image'])): ?>
                            <img src="Cars/<?php echo $car['image']; ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-indigo-50">
                                <i class="fa-solid fa-car text-indigo-200 text-5xl"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute top-4 right-4">
                            <span class="px-4 py-1.5 bg-white/90 backdrop-blur-sm rounded-full text-[10px] font-black uppercase text-indigo-600 shadow-sm border border-indigo-50">
                                <?php echo htmlspecialchars($car['type']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($car['model']); ?></h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-indigo-600 font-bold text-lg">₱<?php echo number_format($car['daily_rate'], 2); ?></span>
                                <span class="text-xs text-slate-400 uppercase font-bold tracking-tighter">/ day</span>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-auto">
                            <button 
                                @click="selectedCar = <?php echo htmlspecialchars(json_encode($car)); ?>"
                                class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-2xl transition-all active:scale-95 text-sm"
                            >
                                Details
                            </button>
                            <button
                                @click="selectedCar = <?php echo htmlspecialchars(json_encode($car)); ?>"
                                class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-100 active:scale-95 text-sm"
                                >
                                Rent
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div x-cloak x-show="search !== '' && $el.parentElement.querySelectorAll('.grid > div:not([style*=\'display: none\'])').length === 0" 
                 class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300">
                    <i class="fa-solid fa-car-on text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">No vehicles found</h3>
                <p class="text-slate-500 max-w-xs">We couldn't find any cars matching your search. Try a different keyword!</p>
            </div>
        </main>
    </div>

    <div x-show="selectedCar" class="fixed inset-0 z-[60] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
        <div x-show="selectedCar" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md" @click="selectedCar = null"></div>

        <div x-show="selectedCar" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-[2.5rem] max-w-lg w-full overflow-hidden shadow-2xl">
            <div class="h-64 bg-slate-200 relative">
                <template x-if="selectedCar && selectedCar.image">
                    <img :src="'Cars/' + selectedCar.image" class="w-full h-full object-cover">
                </template>
                <template x-if="selectedCar && !selectedCar.image">
                    <div class="w-full h-full flex items-center justify-center bg-indigo-50">
                        <i class="fa-solid fa-car text-indigo-200 text-6xl"></i>
                    </div>
                </template>
                <button @click="selectedCar = null" class="absolute top-4 right-4 bg-black/20 hover:bg-black/40 text-white h-10 w-10 rounded-full flex items-center justify-center transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="bg-slate-50 rounded-2xl p-6 mb-8 text-left">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Pick-up Date</label>
                        <input type="date" id="start_date_input" class="w-full p-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Return Date</label>
                        <input type="date" id="end_date_input" class="w-full p-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <p class="text-slate-400 text-sm font-bold uppercase mb-1">Rental Rate</p>
                <p class="text-3xl font-black text-indigo-600">₱<span x-text="selectedCar ? Number(selectedCar.daily_rate).toLocaleString() : ''"></span></p>
                <p class="text-xs text-slate-400 font-bold uppercase mt-1">per day</p>
            </div>

            <div class="flex flex-col gap-3">
                <button @click="
                    const start = document.getElementById('start_date_input').value;
                    const end = document.getElementById('end_date_input').value;
                    
                    if(!start || !end) {
                        alert('Please select your rental dates first.');
                        return;
                    }
                    
                    // Populate hidden form and submit
                    document.getElementById('form_start_date').value = start;
                    document.getElementById('form_end_date').value = end;
                    document.getElementById('rentalForm').submit();
                " class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition shadow-lg shadow-indigo-100">
                    Confirm & Rent Now
                </button>
                <button @click="selectedCar = null" class="w-full py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-2xl transition">
                    Back to Browsing
                </button>
            </div>
        </div>
    </div>

    <div x-show="showLogoutModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-cloak>
        <div x-show="showLogoutModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
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