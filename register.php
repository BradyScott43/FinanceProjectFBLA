
<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
            <link rel="stylesheet" href="css/login.css">
            <title>Register to Personal Finance Manager</title>
        </head>
        <body>
            <header>
                <h3>Welcome to Personal Finance Manager!</h3>
            </header>
            <div class="container">
                <div class="row justify content center">
                    <div class="form_div" align="center">
                        <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?>"  method="post" >
                            <h3>Register to Personal Finance Manager</h3>
                            <input type="text" name="full_name" id="" class="form-control" placeholder="Enter Full Name" autocomplete="off"><br>
                            <input type="text" name="username" id="" class="form-control" placeholder="Enter Username" autocomplete="off"><br>
                            <input type="email" name="email" id="" class="form-control" placeholder="Enter Email" autocomplete="off"><br>
                            <input type="password" name="password" id="" class="form-control" placeholder="Enter Password" autocomplete="off"><br>
                            <input type="submit" value="register" class="btn btn-success">
                        </form>
                        <a href="login.html">back to login</a>
                    </div>


                </div>

            </div>
            
        </body>
    </html>
    
    
<?php
require_once('database.php');

    class Register {
        private $pdo;

        public function __construct() {
            try {
                $this->pdo = Database::connect();
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        public function handleRegister() {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                // Sanitize user inputs
                $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
                $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
                $full_name = filter_input(INPUT_POST, "full_name", FILTER_SANITIZE_SPECIAL_CHARS);
                $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS);

                // Validate inputs
                if (empty($username)) {
                    echo "<script type='text/javascript'>alert('Username is empty.');</script>";
                    return;
                }

                if (empty($password)) {
                    echo "<script type='text/javascript'>alert('Password is empty.');</script>";
                    return;
                }
                if (empty($full_name)) {
                    echo "<script type='text/javascript'>alert('Full name is empty.');</script>";
                    return;
                }
                if (empty($email)) {
                    echo "<script type='text/javascript'>alert('Email is empty.');</script>";
                    return;
                }

                try {
                    // Hash the password
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert into the database using a prepared statement
                    $stmt = $this->pdo->prepare("INSERT INTO login (username, password, full_name, email) VALUES (:username, :password, :full_name, :email)");
                    $stmt->bindParam(":username", $username);
                    $stmt->bindParam(":password", $password);
                    $stmt->bindParam(":full_name", $full_name);
                    $stmt->bindParam(":email", $email);

                    if ($stmt->execute()) {
                        echo "<script type='text/javascript'>alert('Registration Complete!');</script>";
                        
                    } else {
                        echo "<script type='text/javascript'>alert('Failed to register. Please try again.');</script>";
                    }
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
            }
        }
    }

// Instantiate the Register class and handle the registration
$register = new Register();
$register->handleRegister();
?>