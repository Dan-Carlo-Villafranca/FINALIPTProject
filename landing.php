<?php
require_once 'auth.php'; // To access $pdo and database connection

// Fetch a maximum of 6 of the newest available cars for the showcase
$stmt = $pdo->query("SELECT * FROM tbl_cars WHERE status = 'Available' ORDER BY id DESC LIMIT 6");
$featured_cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent N' Ride | Premium Car Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero-gradient {
            background: radial-gradient(circle at top right, #4f46e5, #312e81);
        }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-white text-slate-900">

    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <span class="text-xl font-bold tracking-tighter text-slate-800">Rent N' Ride</span>
            </div>
            
            <div class="flex items-center gap-6">
                <a href="#fleet" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">Our Fleet</a>
                <a href="login.php" class="flex items-center justify-center w-10 h-10 rounded-full border border-slate-200 text-slate-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition shadow-sm" title="Login to your account">
                    <i class="fa-solid fa-user-lock"></i>
                </a>
            </div>
        </div>
    </nav>

    <section class="pt-32 pb-20 px-6">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold uppercase tracking-wider mb-6">Premium Rental Service</span>
                <h1 class="text-5xl lg:text-7xl font-bold text-slate-900 leading-tight mb-6">
                    Drive the <span class="text-indigo-600">Dream</span> Without the Debt.
                </h1>
                <p class="text-lg text-slate-500 mb-10 leading-relaxed">
                    Experience luxury and comfort with Rent N' Ride. From sleek sedans to rugged SUVs, we have the perfect vehicle for your next journey.
                </p>
                <div class="flex gap-4">
                    <a href="#fleet" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition">Browse Fleet</a>
                    <a href="login.php" class="px-8 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition">Get Started</a>
                </div>
            </div>
            <div class="relative">
                <div class="absolute -inset-4 bg-indigo-500/10 rounded-[3rem] blur-3xl"></div>
                <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=800" alt="Luxury Car" class="relative rounded-[2.5rem] shadow-2xl">
            </div>
        </div>
    </section>

    <section id="fleet" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900">Featured Vehicles</h2>
                    <p class="text-slate-500">Showing our latest arrivals (Max 6).</p>
                </div>
                <a href="login.php" class="text-indigo-600 font-bold hover:text-indigo-700 transition flex items-center gap-2">
                    View Full Inventory <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($featured_cars as $car): ?>
                <div class="bg-white rounded-[2rem] p-4 border border-slate-100 shadow-sm hover:shadow-xl transition group">
                    <div class="overflow-hidden rounded-[1.5rem] mb-6 aspect-video bg-slate-100 flex items-center justify-center">
                        <?php if(!empty($car['image'])): ?>
                            <img src="Cars/<?= $car['image'] ?>" alt="<?= htmlspecialchars($car['model']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php else: ?>
                            <i class="fa-solid fa-car text-4xl text-slate-300"></i>
                        <?php endif; ?>
                    </div>
                    <div class="px-2">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($car['model']) ?></h3>
                                <span class="text-sm text-slate-400 font-medium"><?= htmlspecialchars($car['type']) ?></span>
                            </div>
                            <div class="text-right">
                                <span class="block text-indigo-600 font-bold text-lg">₱<?= number_format($car['daily_rate'], 0) ?></span>
                                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Per Day</span>
                            </div>
                        </div>
                        <hr class="my-4 border-slate-50">
                        <a href="login.php" class="block w-full py-3 bg-slate-900 text-white text-center font-bold rounded-xl hover:bg-indigo-600 transition shadow-lg shadow-slate-200 hover:shadow-indigo-200">Book Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($featured_cars)): ?>
                    <div class="col-span-full py-10 text-center text-slate-400">
                        <i class="fa-solid fa-car-rear text-4xl mb-4 block"></i>
                        <p>Our fleet is currently out on the road. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="py-12 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-slate-400 text-sm font-medium">© 2026 Rent N' Ride Fleet Management System. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>