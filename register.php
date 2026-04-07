<?php 
require_once 'auth.php'; 

$success = "";
$error = "";

$userReg = "";
$passReg = "";
$confirmPassReg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userReg = trim($_POST['username']);
    $passReg = $_POST['password'];
    $confirmPassReg = $_POST['confirm_password']; // New field

    // 1. Check if passwords match
    if ($passReg !== $confirmPassReg) {
        $error = "Passwords do not match!";
    } else {
        // 2. Check if username exists
        $check = $pdo->prepare("SELECT id FROM tbl_users WHERE username = ?");
        $check->execute([$userReg]);
        
        if ($check->rowCount() > 0) {
            $error = "Username already taken.";
        } else {
            // 3. Hash the password (This is the value that goes into the DB)
            $hashedPass = password_hash($passReg, PASSWORD_DEFAULT);
            
            $insert = $pdo->prepare("INSERT INTO tbl_users (username, password) VALUES (?, ?)");
            if ($insert->execute([$userReg, $hashedPass])) {
                // This sends the user to login.php and carries a "flag" in the URL
                header("location: login.php?registered=success"); 
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent N' Ride | Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- <script>
    // Check if PHP variable $success is true
    <?php if ($success): ?>
        Swal.fire({
            title: 'Account Created!',
            text: 'Registration was successful. Click OK to log in.',
            icon: 'success',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                // The redirect happens ONLY after the user clicks the button
                window.location.href = 'login.php';
            }
        });
    <?php endif; ?>
</script> -->

<body class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
    <div class="w-full max-w-4xl bg-white shadow-2xl rounded-[2rem] overflow-hidden flex flex-col md:row-reverse md:flex-row">
        
        <div class="md:w-1/2 bg-indigo-900 p-12 text-white flex flex-col justify-between" 
             style="background-image: linear-gradient(rgba(49, 46, 129, 0.85), rgba(49, 46, 129, 0.85)), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&q=80&w=1000'); background-size: cover;">
            <div>
                <h1 class="text-3xl font-bold tracking-tighter text-indigo-300 mb-2">Rent N' Ride</h1>
                <p class="text-indigo-100">Join our Management Team</p>
            </div>
            <p class="text-indigo-200 text-sm italic">"The best way to predict the future is to create it."</p>
        </div>

        <div class="md:w-1/2 p-12 bg-white">
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Create Account</h3>
            <p class="text-slate-400 mb-8">Set up your manager profile.</p>

            <?php if($error): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="mb-6 p-4 bg-green-50 text-green-600 text-sm rounded-2xl border border-green-100">
                    <i class="fa-solid fa-circle-check mr-2"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Username</label>
                    <input type="text" name="username" required value="<?= htmlspecialchars($userReg) ?>" class="w-full px-5 py-4 bg-slate-50 border-transparent focus:bg-white focus:ring-2 focus:ring-indigo-500 rounded-2xl outline-none transition">
                </div>
                
                <div class="relative"> <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Password</label>
                    <input type="password" name="password" id="password" required value="<?= htmlspecialchars($passReg) ?>" 
                        class="w-full px-5 py-4 bg-slate-50 border-transparent focus:bg-white focus:ring-2 focus:ring-indigo-500 rounded-2xl outline-none transition">
                    
                    <button type="button" onclick="togglePassword('password', 'eye1')" class="absolute right-5 top-10 text-slate-400 hover:text-indigo-600">
                        <i id="eye1" class="fa-solid fa-eye"></i>
                    </button>
                </div>

                <div class="relative"> 
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Confirm Password</label>
                    
                    <input type="password" name="confirm_password" id="confirm_password" required value="<?= htmlspecialchars($confirmPassReg) ?>" 
                        class="w-full px-5 py-4 bg-slate-50 border-transparent focus:bg-white focus:ring-2 focus:ring-indigo-500 rounded-2xl outline-none transition">
                    
                    <button type="button" onclick="togglePassword('confirm_password', 'eye2')" class="absolute right-5 top-10 text-slate-400 hover:text-indigo-600">
                        <i id="eye2" class="fa-solid fa-eye"></i>
                    </button>
                </div>

                <!-- <div> DEV NOTE: THIS IS JUST A REFERENCE OF THE OLD CONFIRM PASS SECTION
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="w-full px-5 py-4 bg-slate-50 border-transparent focus:bg-white focus:ring-2 focus:ring-indigo-500 rounded-2xl outline-none transition">
                </div> -->

                <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-xl transition">Register Account</button>
            </form>
            
          <p class="mt-8 text-center text-sm text-slate-500">
                Already have an account? 
                <a href="login.php" class="text-indigo-600 font-bold hover:underline">Log In</a>
            </p>

<script>
        function togglePassword(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>