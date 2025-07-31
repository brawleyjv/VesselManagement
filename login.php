<?php
require_once 'config_sqlite.php';
require_once 'auth_functions.php';
require_once 'vessel_functions.php';

ensure_session();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (login_user($conn, $username, $password)) {
        // Login successful - check if this is demo user (admin)
        if ($username === 'admin') {
            // Set demo mode FIRST
            $_SESSION['is_demo_mode'] = true;
            
            // Set Test Vessel as active vessel for demo user
            $test_vessel_query = "SELECT VesselID FROM vessels WHERE VesselName = 'Test Vessel' LIMIT 1";
            $result = $conn->query($test_vessel_query);
            
            if ($result && $result->num_rows > 0) {
                $vessel = $result->fetch_assoc();
                // Use the unified vessel setting function
                set_active_vessel($vessel['VesselID'], 'Test Vessel');
                echo "DEBUG: Set Test Vessel ID " . $vessel['VesselID'] . " as active";
            } else {
                echo "DEBUG: Test Vessel not found in database";
            }
        }
        
        // Redirect to main page
        $redirect = $_GET['redirect'] ?? 'index.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $error_message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vessel Data Logger</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-form h2 {
            color: #1e3c72;
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-form h3 {
            color: #2a5298;
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 300;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 1rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
            transform: translateY(-2px);
        }

        .btn {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(42, 82, 152, 0.3);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .wave {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="rgba(255,255,255,0.1)"></path></svg>') repeat-x;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="wave"></div>
    <div class="login-container">
        <div class="login-form">
            <h2>🚢</h2>
            <h2>Vessel Data Logger</h2>
            <h3>Welcome Back</h3>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
