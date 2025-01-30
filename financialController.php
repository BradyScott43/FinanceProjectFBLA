<?php
require_once('financialModel.php');

class FinancialController {

    private $model;

    public function __construct() {
        $this->model = new FinancialModel();
    }

    public function financial_processor() {
        $data = [
            'amount' => $_POST['amount'] ?? '',
            'category' => $_POST['category'] ?? '',
            'type' => $_POST['type'] ?? '',
            'date' => $_POST['date'] ?? '',
            'description' => $_POST['description'] ?? '',
            'amount_error' => '',
            'category_error' => '',
            'type_error' => '',
            'date_error' => '',
            'description_error' => ''
        ];

        if (isset($_POST['submit']) && $_POST['submit'] == "Add Transaction") {
            if (empty($data['amount'])) $data['amount_error'] = "You did not set an amount";
            if (empty($data['category'])) $data['category_error'] = "You did not set a category type";
            if (empty($data['type'])) $data['type_error'] = "You did not set a type of transaction";
            if (empty($data['date'])) $data['date_error'] = "You did not set a date for the transaction";
            if (empty($data['amount_error']) && empty($data['category_error']) && empty($data['type_error']) && empty($data['date_error']) && empty($data['description_error'])) {
                $added = $this->model->dataUpload($data);
                
                if ($added) {
                    header("Location: index.php?success=1");
                    exit;
                } else {
                    echo "Error in saving data";
                }
            } else {
                echo "Validation errors: ";
                print_r($data); // Debug output to view errors
            }
        }

        return $data;
    }

    public function getAllExpenses() {
        return $this->model->dataExtract();
    }

    public function getExpenseById($id) {
        return $this->model->getExpenseById($id);
    }

    public function getAllDates() {
        return $this->model->getDates();
    }

    public function getUserId(){
        return $this->model->getUserId();
    }


    public function updateExpense($id, $data) {
        $updateData = [
            'amount' => $data['amount'],
            'category' => $data['category'],
            'type' => $data['type'],
            'date' => $data['date'],
            'description' => $data['description']
        ];
    
        // Update transaction with given ID
        return $this->model->updateExpense($id, $updateData);
    }
    
    public function deleteExpense($id) {
        return $this->model->deleteExpense($id);
    }

    public function getFinancialSummary() {
        $total_income = $this->model->getTotalIncome();
        $total_expenses = $this->model->getTotalExpenses();
        $net_balance = $total_income - $total_expenses;

        return [
            'total_income' => $total_income,
            'total_expenses' => $total_expenses,
            'net_balance' => $net_balance
        ];
    }

}

?>
