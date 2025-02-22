# Centsible

Centsible is a web application that helps users track their income, expenses, and overall financial health. 
The application provides intuitive graphs, summary statistics, and an easy-to-use interface for managing financial transactions.

---

## Features

- **Add, Edit, and Delete Transactions**: Seamlessly manage your income and expenses.
- **Visual Insights**: Interactive pie charts and line graphs for analyzing financial trends by category and time period (daily, weekly, monthly, yearly).
- **Financial Summary**: Real-time display of total income, expenses, and net balance.
- **Filtering Options**: View specific data by year or month.
- **Cumulative Balance**: Track your balance growth over time.

---

## Installation

### Prerequisites
- A web server (e.g.,UniServer ,Apache, Nginx) with PHP support.
- Database (e.g., MySQL) for storing transaction data.
- A modern web browser to access the app.

### Steps
1. Download the files

2. Place the project files in your web server directory.

3. Import the database using the provided SQL file (finance.sql).

4. Update the database connection settings in database.php. 

5. Open the application in your browser.

Usage
Adding a Transaction:

1. Enter the amount, category, type (income/expense), date, and description in the form.
2. Click the "Add Transaction" button.

Editing a Transaction:

1. Click "Edit" next to the transaction.
2. Modify the details and save the changes.

Deleting a Transaction:

1. Click "Delete" next to the transaction and confirm the action.

Viewing Financial Insights:

Chart Graph:
    > Use the charts and filters to analyze your financial data by categories and time periods.
    > Click the income or expense button to switch between income and expense categories.
Line Graph:
    > Click the daily, weekly, monthly, or yearly button to see the corasponding income per period.
    > Click the balance over time button to see your balance over time.


Technologies Used
Frontend: HTML, CSS, JavaScript
Backend: PHP
Data Visualization: Chart.js
Database: phpMyAdmin, MySQL (or any other compatible database)
