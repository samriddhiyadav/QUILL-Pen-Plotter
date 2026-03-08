<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Clear redirect URL if requested
if (isset($_GET['clear_redirect']) && isset($_SESSION['redirect_url'])) {
    unset($_SESSION['redirect_url']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /src/auth/auth.php");
    exit;
}

// Initialize variables
$errors = [];
$success = [];
$name = $email = '';
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login process
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $errors[] = "Please fill in all fields";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Determine redirect URL based on role
                $redirectUrl = BASE_URL . '/src/viewer/index.php';
                if ($user['role'] === 'admin') {
                    $redirectUrl = BASE_URL . '/src/admin/dashboard.php';
                } elseif ($user['role'] === 'author') {
                    $redirectUrl = BASE_URL . '/src/author/dashboard.php';
                }

                // Redirect directly to the appropriate dashboard
                header("Location: $redirectUrl");
                exit;
            } else {
                $errors[] = "Invalid email or password";
            }
        }
    } elseif (isset($_POST['register'])) {
        // Registration process
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = isset($_POST['role']) ? $_POST['role'] : 'viewer';

        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $errors[] = "Please fill in all fields";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Passwords don't match";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already registered";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $password_hash, $role])) {
                    $user_id = $pdo->lastInsertId();
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;

                    // Set success message
                    $success[] = "Registration successful! Welcome to Quill, $name!";
                    $_SESSION['success'] = $success;

                    // Store redirect URL in session
                    $_SESSION['redirect_url'] = BASE_URL . '/src/viewer/index.php';
                    if ($role === 'admin') {
                        $_SESSION['redirect_url'] = BASE_URL . '/src/admin/dashboard.php';
                    } elseif ($role === 'author') {
                        $_SESSION['redirect_url'] = BASE_URL . '/src/author/dashboard.php';
                    }

                    // Stay on page to show success message
                    $activeTab = 'register';
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            }
        }

        if (!empty($errors)) {
            $activeTab = 'register';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication | Quill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --rich-black: rgb(13, 11, 11);
            --warm-brown: #5D4037;
            --soft-beige: #D7CCC8;
            --vanilla-cream: #E8D8C4;
            --warm-gold: #C9A66B;
            --soft-ivory: #F9F7F4;
            --text-dark: #1A1A1A;
            --text-light: #F5F5F5;
            --gold-gradient: linear-gradient(135deg, #C9A66B 0%, #E8D8C4 100%);

            --jungle-green: #27AE60;
            /* Success alerts */
            --wine-berry: #9B1B30;
            /* Error alerts */
            --walnut: #773F1A;
            /* Warning alerts */
            --black-pearl: #1E272E;
            /* Info alerts */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lora', serif;
            background-color: var(--soft-ivory);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden;
            background-color: var(--soft-ivory);
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .auth-container {
            background-color: white;
            border-radius: 50px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(201, 166, 107, 0.2);
            max-height: 95vh;
            overflow-y: auto;
        }

        .auth-header {
            background-color: var(--rich-black);
            color: var(--vanilla-cream);
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .auth-header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }

        .auth-header p {
            font-weight: 300;
            opacity: 0.9;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .logo-icon {
            color: var(--warm-gold);
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            letter-spacing: 1px;
        }

        .logo-text:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 1px;
            background: var(--gold-gradient);
            top: 90px;
            left: 50%;
            transform: translateX(-50%);
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid rgba(201, 166, 107, 0.2);
        }

        .tab {
            flex: 1;
            text-align: center;
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            color: var(--text-dark);
            opacity: 0.7;
        }

        .tab.active {
            opacity: 1;
            color: var(--warm-gold);
        }

        .tab.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--warm-gold);
        }

        .tab-content {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid rgba(201, 166, 107, 0.3);
            border-radius: 8px;
            font-family: 'Lora', serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--soft-ivory);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--warm-gold);
            box-shadow: 0 0 0 2px rgba(201, 166, 107, 0.2);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: var(--warm-gold);
            color: var(--rich-black);
        }

        .btn-primary:hover {
            background-color: var(--warm-brown);
            color: var(--vanilla-cream);
            transform: translateY(-2px);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #666;
            font-size: 0.9rem;
        }

        .divider:before,
        .divider:after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(201, 166, 107, 0.2);
        }

        .divider:before {
            margin-right: 1rem;
        }

        .divider:after {
            margin-left: 1rem;
        }

        .social-login {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem;
            border-radius: 8px;
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .social-btn i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .google-btn {
            color: #DB4437;
        }

        .facebook-btn {
            color: #4267B2;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .auth-footer a {
            color: var(--warm-gold);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 45px;
            /* Adjusted to align better with input */
            transform: none;
            cursor: pointer;
            color: var(--warm-brown);
            opacity: 0.6;
            z-index: 2;
        }

        .password-toggle:hover {
            opacity: 1;
        }

        @media (max-width: 576px) {
            .auth-container {
                border-radius: 12px;
            }

            .auth-header h1 {
                font-size: 1.8rem;
            }

            .tab {
                padding: 1rem;
                font-size: 0.9rem;
            }

            .tab-content {
                padding: 1.5rem;
            }

            .social-login {
                grid-template-columns: 1fr;
            }
        }

        /* Custom scrollbar for the container */
        .auth-container::-webkit-scrollbar {
            width: 8px;
        }

        .auth-container::-webkit-scrollbar-track {
            background: rgba(201, 166, 107, 0.1);
            border-radius: 4px;
        }

        .auth-container::-webkit-scrollbar-thumb {
            background: rgba(201, 166, 107, 0.4);
            border-radius: 4px;
        }

        .auth-container::-webkit-scrollbar-thumb:hover {
            background: rgba(201, 166, 107, 0.6);
        }

        .alert-message {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-left: 1rem;
            margin-right: 1rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            border-left: 4px solid;
        }

        .error-message {
            color: var(--wine-berry);
            background-color: rgba(155, 27, 48, 0.1);
            border-left-color: var(--wine-berry);
            margin-left: 1rem;
            margin-right: 1rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .success-message {
            color: var(--jungle-green);
            background-color: rgba(39, 174, 96, 0.1);
            border-left-color: var(--jungle-green);
            margin-left: 1rem;
            margin-right: 1rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .warning-message {
            color: var(--walnut);
            background-color: rgba(119, 63, 26, 0.1);
            border-left-color: var(--walnut);
            margin-left: 1rem;
            margin-right: 1rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .info-message {
            color: var(--black-pearl);
            background-color: rgba(30, 39, 46, 0.1);
            border-left-color: var(--black-pearl);
            margin-left: 1rem;
            margin-right: 1rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .alert-message ul {
            list-style-position: inside;
            margin-top: 0.5rem;
            margin-left: 1rem;
            margin-right: 1rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        /* Scrollbar modifications */
        html {
            overflow: hidden;
        }

        /* Role selection styles */
        .role-selection {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .role-option {
            flex: 1;
            text-align: center;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option label {
            display: block;
            padding: 0.5rem;
            border: 1px solid rgba(201, 166, 107, 0.3);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.8rem;
        }

        .role-option input[type="radio"]:checked+label {
            background-color: var(--warm-gold);
            color: var(--rich-black);
            border-color: var(--warm-gold);
        }

        .role-option label:hover {
            border-color: var(--warm-gold);
        }

        .role-admin label:hover {
            background-color: rgba(155, 27, 48, 0.1);
            border-color: var(--wine-berry);
        }

        .role-admin input[type="radio"]:checked+label {
            background-color: var(--warm-gold);
            color: var(--rich-black);
        }

        .role-admin input[type="radio"]:checked+label:hover {
            background-color: var(--wine-berry);
            color: white;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">
                <i class="fas fa-feather-alt logo-icon"></i>
                <span class="logo-text">Quill</span>
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to your account or create a new one</p>
        </div>

        <div class="tabs">
            <div class="tab <?php echo $activeTab === 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">Login
            </div>
            <div class="tab <?php echo $activeTab === 'register' ? 'active' : ''; ?>" onclick="switchTab('register')">
                Register</div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert-message error-message">
                <?php if (count($errors) === 1): ?>
                    <?php echo $errors[0]; ?>
                <?php else: ?>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-message success-message">
                <?php if (is_array($success)): ?>
                    <ul>
                        <?php foreach ($success as $msg): ?>
                            <li><?php echo $msg; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <?php echo $success; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div id="login-tab" class="tab-content" style="<?php echo $activeTab !== 'login' ? 'display: none;' : ''; ?>">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" class="form-control" required>
                    <span class="password-toggle" onclick="togglePassword('login-password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <button type="submit" name="login" class="btn btn-primary">Sign In</button>

                <div class="auth-footer">
                    Don't have an account? <a href="?tab=register">Sign up</a>
                </div>
            </form>
        </div>

        <div id="register-tab" class="tab-content"
            style="<?php echo $activeTab !== 'register' ? 'display: none;' : ''; ?>">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Account Type</label>
                    <div class="role-selection">
                        <div class="role-option">
                            <input type="radio" id="role-viewer" name="role" value="viewer" checked>
                            <label for="role-viewer">Viewer</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="role-author" name="role" value="author">
                            <label for="role-author">Author</label>
                        </div>
                        <div class="role-option role-admin">
                            <input type="radio" id="role-admin" name="role" value="admin">
                            <label for="role-admin">Admin</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="register-name">Full Name</label>
                    <input type="text" id="register-name" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="password" class="form-control" required>
                    <span class="password-toggle" onclick="togglePassword('register-password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <div class="form-group">
                    <label for="register-confirm-password">Confirm Password</label>
                    <input type="password" id="register-confirm-password" name="confirm_password" class="form-control"
                        required>
                    <span class="password-toggle" onclick="togglePassword('register-confirm-password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <button type="submit" name="register" class="btn btn-primary">Create Account</button>

                <div class="auth-footer">
                    Already have an account? <a href="?tab=login">Sign in</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.getElementById('login-tab').style.display = tabName === 'login' ? 'block' : 'none';
            document.getElementById('register-tab').style.display = tabName === 'register' ? 'block' : 'none';

            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            document.querySelector(`.tab:nth-child(${tabName === 'login' ? 1 : 2})`).classList.add('active');

            // Update URL without reload
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Check URL for tab parameter on page load
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');

            if (tabParam === 'register') {
                switchTab('register');
            }
        });

        // Handle delayed redirect if there's a success message from registration
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage && <?php echo isset($_SESSION['redirect_url']) ? 'true' : 'false'; ?>) {
                setTimeout(function() {
                    window.location.href = '<?php echo isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : ''; ?>';
                }, 1000); // 1 second delay
                
                // Clear the redirect URL from session
                fetch('?clear_redirect=1', {method: 'GET'});
            }
        });
    </script>
</body>

</html>