<?php

require_once('database.php');

class FinancialModel {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    // Insert a new transaction
    public function dataUpload($data) {
        $sql = "INSERT INTO finances (amount, category, type, date, description, username) VALUES (:amount, LOWER(:category), :type, :date, :description, :username)";
        $stmt = $this->pdo->prepare($sql);
    
        // Debugging: Print the data being inserted
        echo "<pre>Data to insert: ";
        print_r($data);
        echo "</pre>";
    
        $stmt->bindValue(':amount', $data['amount']);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':date', $data['date']);
        $stmt->bindValue(':category', $data['category']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':username', $_SESSION['username']);
    
        $result = $stmt->execute();
    
        // Check if insert was successful
        if ($result) {
            echo "Data inserted successfully!";
        } else {
            echo "Data insert failed.";
            print_r($stmt->errorInfo()); // Output error details if the insert fails
        }
    
        return $result;
    }

    // Retrieve all transactions
    public function dataExtract() {
        $sql = "SELECT * FROM finances WHERE username = :username ORDER BY Date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':username', $_SESSION['username']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Retrieve all dates
    public function getDates() {
        $sql = "SELECT amount, type, date FROM finances WHERE username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':username', $_SESSION['username']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserId() {
        $sql = "SELECT 'user_id' FROM finances";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }

// // Output the dates as JSON
// header('Content-Type: application/json');
// echo json_encode(getAllDates());

    // Retrieve a single transaction by ID
    public function getExpenseById($id) {
        $sql = "SELECT * FROM finances WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update an existing transaction by ID
    public function updateExpense($id, $data) {
        $sql = "UPDATE finances SET amount = :amount, type = :type, date = :date, category = :category, description = :description WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':amount', $data['amount']);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':date', $data['date']);
        $stmt->bindValue(':category', $data['category']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Delete a transaction by ID
    public function deleteExpense($id) {
        $sql = "DELETE FROM finances WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        

        return $stmt->execute();
    }

    // Get total income
    public function getTotalIncome() {
        $sql = "SELECT SUM(amount) AS total_income FROM finances WHERE type = 'income' AND username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':username', $_SESSION['username']);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_income'] ?? 0;
    }

    // Get total expenses
    public function getTotalExpenses() {
        $sql = "SELECT SUM(amount) AS total_expenses FROM finances WHERE type = 'expense' AND username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':username', $_SESSION['username']);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_expenses'] ?? 0;
    }
}

?>
