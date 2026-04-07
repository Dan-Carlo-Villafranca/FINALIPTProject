<?php
require_once 'auth.php'; 
check_login(); 
prevent_back_button(); 

$user_id = $_SESSION['user_id'];
$success_msg = isset($_GET['success']) ? "Profile updated successfully!" : "";
$error_msg = "";

// Fetch current user data for comparison and initial load
$stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // --- 1. HANDLE MAIN PROFILE UPDATE ---
        if (isset($_POST['update_profile'])) {
            $display_name = trim($_POST['display_name']);
            $first_name = trim($_POST['first_name']);
            $middle_name = trim($_POST['middle_name']);
            $last_name = trim($_POST['last_name']);
            $birthday = $_POST['birthday'];
            $email = trim($_POST['email']);
            $address = trim($_POST['address']);
            $contact = trim($_POST['contact_number']);
            $gender = $_POST['gender'];

            $image_sql = "";
            $params = [$display_name, $first_name, $middle_name, $last_name, $birthday, $email, $address, $contact, $gender];
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                // Remove old image if it exists to save storage
                if (!empty($user['profile_image']) && file_exists("Profile/" . $user['profile_image'])) {
                    unlink("Profile/" . $user['profile_image']);
                }

                $file_ext = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
                $image_name = "admin_" . $user_id . "_" . time() . "." . $file_ext;
                
                if (!is_dir('Profile/')) mkdir('Profile/', 0777, true);
                move_uploaded_file($_FILES["profile_image"]["tmp_name"], "Profile/" . $image_name);
                
                $image_sql = ", profile_image = ?";
                $params[] = $image_name;
            }

            $params[] = $user_id;
            $stmt = $pdo->prepare("UPDATE tbl_users SET display_name=?, first_name=?, middle_name=?, last_name=?, birthday=?, email=?, address=?, contact_number=?, gender=? $image_sql WHERE id=?");
            
            if ($stmt->execute($params)) {
                header("Location: admin_profile.php?success=1");
                exit();
            }
        }

        // --- 2. HANDLE ACCOUNT SECURITY UPDATE ---
        if (isset($_POST['update_account'])) {
            $new_username = trim($_POST['new_username']);
            $curr_pass = $_POST['current_password'];
            $new_pass = $_POST['new_password'];

            if (password_verify($curr_pass, $user['password'])) {
                $sql = "UPDATE tbl_users SET username = ?";
                $params = [$new_username];

                if (!empty($new_pass)) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($new_pass, PASSWORD_DEFAULT);
                }

                $sql .= " WHERE id = ?";
                $params[] = $user_id;

                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($params)) {
                    $_SESSION['username'] = $new_username;
                    header("Location: admin_profile.php?success=1");
                    exit();
                }
            } else {
                $error_msg = "Current password incorrect. Changes not saved.";
            }
        }
    } catch (PDOException $e) {
        $error_msg = "Database Error: " . $e->getMessage();
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent n' Ride | Admin Profile</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Assets/sidebar.css">
    <style>
        [x-cloak] { display: none !important; }
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .profile-input {
            width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #ffffff; transition: all 0.2s;
        }
        .profile-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
    </style>
</head>
<body class="bg-slate-50" x-data="{ showLogoutModal: false, showAccountModal: false }">

    <div class="flex min-h-screen">
        <aside class="w-64 glass-sidebar text-white flex flex-col fixed h-full">
            <div class="p-8">
                <h1 class="text-2xl font-bold tracking-tighter text-indigo-400">Rent N' Ride</h1>
                <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-2 py-1 rounded-md uppercase font-bold mt-2 inline-block italic">Admin Portal</span>
            </div>
            
            <nav class="flex-1 px-4 space-y-2">
                <a href="admindashboard.php" class="nav-link <?= $current_page == 'admindashboard.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high w-5"></i> <span>System Overview</span>
                </a>
                <a href="manage_staff.php" class="nav-link <?= $current_page == 'manage_staff.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users w-5"></i> <span>Manage Staff</span>
                </a>
                <a href="customer.php" class="nav-link <?= $current_page == 'customers.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users w-5"></i> <span>Customers Section</span>
                </a>
                <a href="inventory.php" class="nav-link <?= $current_page == 'inventory.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-car w-5"></i> <span>Full Inventory</span>
                </a>
                <a href="revenue.php" class="nav-link <?= $current_page == 'revenue.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice-dollar w-5"></i> <span>Revenue Reports</span>
                </a>
                <hr class="border-slate-800 my-4 mx-2">
                <a href="admin_profile.php" class="nav-link <?= $current_page == 'admin_profile.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-circle-user w-5"></i> <span>My Profile</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 mb-4 px-2">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white overflow-hidden">
                        <?php if(!empty($user['profile_image'])): ?>
                            <img src="Profile/<?= $user['profile_image'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold truncate w-32"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                        <p class="text-[10px] text-indigo-400 font tracking-wider">Administrator</p>
                    </div>
                </div>
                <button @click.prevent="showLogoutModal = true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition w-full text-left">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Log Out</span>
                </button>
            </div>
        </aside>

        <main class="flex-1 ml-64 p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-800">My Profile</h1>
                        <p class="text-slate-500">Manage your personal details</p>
                    </div>
                    <button @click="showAccountModal = true" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition flex items-center shadow-sm">
                        <i class="fa-solid fa-shield-halved mr-2 text-indigo-500"></i> Edit Account
                    </button>
                </div>

                <?php if($success_msg): ?>
                    <div class="mb-6 p-4 bg-green-50 text-green-600 rounded-2xl border border-green-100 flex items-center">
                        <i class="fa-solid fa-circle-check mr-2"></i> <?= $success_msg ?>
                    </div>
                <?php endif; ?>
                <?php if($error_msg): ?>
                    <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-2xl border border-red-100 flex items-center">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i> <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1">
                            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 text-center">
                                <div class="relative inline-block">
                                    <div class="w-32 h-32 bg-indigo-100 rounded-full mx-auto mb-4 overflow-hidden border-4 border-white shadow-lg flex items-center justify-center">
                                        <img id="preview" src="<?= !empty($user['profile_image']) ? 'Profile/'.$user['profile_image'] : '#' ?>" 
                                             class="w-full h-full object-cover <?= empty($user['profile_image']) ? 'hidden' : '' ?>">
                                        <?php if(empty($user['profile_image'])): ?>
                                            <div id="placeholder" class="text-indigo-500 text-4xl font-bold uppercase">
                                                <?= substr($user['username'], 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <label for="imgInput" class="absolute bottom-2 right-0 bg-white p-2 rounded-full shadow-md cursor-pointer border border-slate-100 hover:text-indigo-600">
                                        <i class="fa-solid fa-camera"></i>
                                        <input type="file" id="imgInput" name="profile_image" class="hidden" accept="image/*" onchange="previewImage(this)">
                                    </label>
                                </div>
                                <h2 class="text-xl font-bold text-slate-800 mt-2">
                                    <?= htmlspecialchars($user['display_name'] ?? $user['username']) ?></h2>
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-widest mt-1"><?= $user['role'] ?? 'Admin' ?></p>
                            </div>
                        </div>

                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Display Name</label>
                                        <input type="text" name="display_name" class="profile-input" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                                        <input type="email" name="email" class="profile-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                                    <input type="text" name="first_name" placeholder="First Name" class="profile-input" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                                    <input type="text" name="middle_name" placeholder="Middle Name" class="profile-input" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>">
                                    <input type="text" name="last_name" placeholder="Last Name" class="profile-input" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Birthday</label>
                                        <input type="date" name="birthday" class="profile-input" value="<?= $user['birthday'] ?? '' ?>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Gender</label>
                                        <select name="gender" class="profile-input">
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?= ($user['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= ($user['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Contact & Address</label>
                                    <input type="text" name="contact_number" placeholder="Phone Number" class="profile-input mb-4" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>">
                                    <textarea name="address" placeholder="Full Home Address" class="profile-input h-24 pt-3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button type="submit" name="update_profile" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                        Update Basic Info
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <div x-show="showAccountModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAccountModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-md w-full p-8 shadow-2xl" x-transition x-data="{ showPass: false, showNewPass: false }">
            <h3 class="text-2xl font-bold text-slate-800 mb-1">Account Security</h3>
            <p class="text-slate-500 text-sm mb-6">Manage your login credentials.</p>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Username</label>
                    <input type="text" name="new_username" class="profile-input bg-slate-50" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <hr class="my-4 border-slate-100">

                <div class="relative">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Current Password</label>
                    <input :type="showPass ? 'text' : 'password'" name="current_password" class="profile-input" placeholder="Required to save changes" required>
                    <button type="button" @click="showPass = !showPass" class="absolute right-4 top-9 text-slate-400">
                        <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>

                <div class="relative">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">New Password</label>
                    <input :type="showNewPass ? 'text' : 'password'" name="new_password" class="profile-input" placeholder="Leave blank to keep same">
                    <button type="button" @click="showNewPass = !showNewPass" class="absolute right-4 top-9 text-slate-400">
                        <i class="fa-solid" :class="showNewPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" name="update_account" class="flex-1 py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg">Save Account</button>
                    <button type="button" @click="showAccountModal = false" class="px-6 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-[2rem] max-w-sm w-full p-8 shadow-2xl text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 mb-6">
                <i class="fa-solid fa-arrow-right-from-bracket text-red-500 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Sign Out?</h3>
            <p class="text-slate-500 mb-8">Ready to end your session?</p>
            <div class="flex flex-col gap-3">
                <a href="auth.php?action=logout" class="w-full py-4 bg-red-500 text-white font-bold rounded-2xl">Log Me Out</a>
                <button @click="showLogoutModal = false" class="w-full py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const placeholder = document.getElementById('placeholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if(placeholder) placeholder.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>