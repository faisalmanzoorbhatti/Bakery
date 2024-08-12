<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: row;
            min-height: 100vh;
            background-color: #f2f2f2; 
        }
        .left-side {
            flex: 2.0; 
            background-image: url('images/img_0130.jpg');
            background-size: cover;
            background-position: center;
        }
        .right-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between; 
            padding: 20px;
            background-color: #626463; 
        }
        .header {
            background-color: #626463;
            color: white;
            text-align: center;
            padding: 10px;
            width: 100%;
        }
        .form-container {
            background-color: #ddd; 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .form-container input {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .btn {
            flex: 1;
            padding: 10px;
            margin: 0 5px;
            text-decoration: none;
            text-align: center;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn.register {
            background-color: #F79746;
        }
        .btn.forgot {
            background-color: #F79746;
        }
        .footer {
            text-align: right;
            color: #aaa;
            font-size: 12px;
            padding: 10px;
            background-color: #656565;
            width: 100%;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="left-side">
       
    </div>
    <div class="right-side">
        <div class="header">
            <h2>Welcome to Online Bay Bakery</h2>
        </div>
        <div class="form-container">
            <h3>Sign In</h3>
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <div class="btn-group">
                    <button type="submit" class="btn register">Login</button>
                    <a href="register.php" class="btn register">Register</a>
                </div>
            </form>
            <a href="forgot_password.php" class="btn forgot">Forgot Password</a>
        </div>
        <div class="footer">
            Powered by Virtual University of Pakistan
        </div>
    </div>
</body>
</html>
