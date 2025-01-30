

<?php
// Start the session
session_start();
?>
<?php
require_once('database.php');



class Login {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function handleLogin() {
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $_SESSION['username'] = $username;
            

            // Use prepared statements to prevent SQL injection
            $query = "SELECT * FROM login WHERE username = :username AND password = :password";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                // Login successful, redirect to the dashboard or index page
                header("Location: index.php");
                exit();
            } else {
                // Login failed, redirect to an error page

                
                echo("Incorrect password and/or username. Please ");
                echo "<a href='login.html' target='_blank'>try again</a>";
                
                
                
                exit();
            }

            
           
        }
    }
}

// Instantiate and process the login
$login = new Login();
$login->handleLogin();



?>