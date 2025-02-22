<?php
session_start();
require_once('database.php');

class UserAuth {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    // Handle Registration
    public function handleRegister() {
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
            $full_name = filter_input(INPUT_POST, "full_name", FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
            $account_type = filter_input(INPUT_POST, "account_type", FILTER_SANITIZE_SPECIAL_CHARS);
            $password = $_POST["password"];

            if (empty($full_name) || empty($email) || empty($account_type) || empty($password)) {
                echo "<script>alert('All fields are required.');</script>";
                return;
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $this->pdo->prepare("INSERT INTO accounts (account_type, password, full_name, email) VALUES (:account_type, :password, :full_name, :email)");
                $stmt->bindParam(":account_type", $account_type);
                $stmt->bindParam(":password", $hashedPassword);
                $stmt->bindParam(":full_name", $full_name);
                $stmt->bindParam(":email", $email);

                if ($stmt->execute()) {
                    echo "<script>alert('Registration Complete!');</script>";
                } else {
                    echo "<script>alert('Failed to register. Please try again.');</script>";
                }
            } catch (PDOException $e) {
                die("SQL Error: " . $e->getMessage());
            }
        }
    }

    // Handle Login
    public function handleLogin() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $query = "SELECT * FROM accounts WHERE username = :username";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                echo "<script>alert('Incorrect username or password.');</script>";
            }
        }
    }
}

$auth = new UserAuth();
$auth->handleRegister();
$auth->handleLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup Form</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <section class="wrapper">
        <div class="form signup">
            <header>Signup</header>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="text" name="full_name" placeholder="Full name" required>
                <input type="text" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="account_type" placeholder="Account Type" required>
                <div class="checkbox">
                    <input type="checkbox" id="signupCheck" required>
                    <label for="signupCheck">I accept all terms & conditions</label>
                </div>
                <input type="submit" name="register" value="Signup">
            </form>
        </div>

        <div class="form login">
            <header>Login</header>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="text" name="username" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <a href="#">Forgot password?</a>
                <input type="submit" name="login" value="Login">
            </form>
        </div>
    </section>

    <script>
        const wrapper = document.querySelector(".wrapper"),
              signupHeader = document.querySelector(".signup header"),
              loginHeader = document.querySelector(".login header");

        loginHeader.addEventListener("click", () => {
            wrapper.classList.add("active");
        });

        signupHeader.addEventListener("click", () => {
            wrapper.classList.remove("active");
        });
    </script>
</body>
</html>
