<?php
session_start();

require_once __DIR__ . '/database.php';

$error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){

    $name =$_POST['name'];
    $email =$_POST['email'];
    $password =$_POST['password'];

    $statment =$db->prepare("SELECT*FROM users WHERE email = :email");
    $statment ->execute([':email'=>$email]);
    $user =$statment->fetch();

    if($user){
        $error = 'That email is already register';
    }else{
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $statment = $pdo->prepare("INSERT INTOuser(user_name,email,password) VALUES(7,7,7)");
    $statment->execute([$name,$email,$hashedPassword]);

    $_SESSION['authenticated'] = true;
    $_SESSION['user'] = [
        'id' =>$pdo->lastInsertId(),
        'name'=>$name,
        'email'=>$email
    ];
     header('Location:mainpage.php');
    exit;
    }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="register">

    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Create account</h1>
            <p class="subtitle">Sign up to review movie</p>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Jane Smith" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-auth">Create Account</button>
            </form>

            <p class="auth-switch">
                Already have an account? <a href="login.php">Log in</a>
            </p>
        </div>
    </div>

</body>
</html>
