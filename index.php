<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Finance Manager</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
</head>

<body>
    <?php
    require_once('financialController.php');

    $financialController = new FinancialController();

    // Process form data if submitted
    $formData = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_GET['edit'])) {
            $financialController->updateExpense($_GET['edit'], $_POST); // Process edit
            header("Location: index.php"); // Redirect after update
        } else {
            $financialController->financial_processor(); // Process new entry
        }
    }

    // Handle delete request
    if (isset($_GET['delete'])) {
        $financialController->deleteExpense($_GET['delete']);
        header("Location: index.php");
    }

    // Get data for editing if applicable
    $edit_data = null;
    if (isset($_GET['edit'])) {
        $edit_data = $financialController->getExpenseById($_GET['edit']);
    }

    // Retrieve all expenses and financial summary
    $expenses = $financialController->getAllExpenses();
    $summary = $financialController->getFinancialSummary();

    
    $dates = $financialController->getAllDates();
    $user_id = $financialController->getUserId();
    

    // Calculate category totals for pie chart data
    $expense_totals = [];
    $income_totals = [];
    foreach ($expenses as $expense) {
        $category = $expense['category'];
        if ($expense['type'] === 'expense') {
            $expense_totals[$category] = ($expense_totals[$category] ?? 0) + $expense['amount'];
        } elseif ($expense['type'] === 'income') {
            $income_totals[$category] = ($income_totals[$category] ?? 0) + $expense['amount'];
        }
    }

    

    // Calculate profits by day
    $profitsByDay = [];
    foreach ($expenses as $entry) {
        $date = $entry['date'];
        $amount = $entry['amount'];
        $type = $entry['type'];

        if (!isset($profitsByDay[$date])) {
            $profitsByDay[$date] = 0;
        }

        if ($type === 'income') {
            $profitsByDay[$date] += $amount;
        } elseif ($type === 'expense') {
            $profitsByDay[$date] -= $amount;
        }
    }

    // Ensure all dates in range are represented
    $allDates = [];

    
    if (!empty($profitsByDay)) {
        $startDate = new DateTime(min(array_keys($profitsByDay))); // Earliest date
        $endDate = new DateTime(max(array_keys($profitsByDay)));   // Latest date
        $interval = new DateInterval('P1D'); // Daily interval
    

    $dateRange = new DatePeriod($startDate, $interval, $endDate->add($interval)); // Include endDate
    foreach ($dateRange as $date) {
        $dateStr = $date->format('Y-m-d');
        $allDates[$dateStr] = $profitsByDay[$dateStr] ?? 0; // Fill missing dates with 0
    }
    }

    // Calculate cumulative balance over time
    $cumulativeBalance = [];
    $currentBalance = 0;
    foreach ($allDates as $date => $profit) {
        $currentBalance += $profit; // Add daily profit to the running balance
        $cumulativeBalance[$date] = $currentBalance;
    }

    // Convert cumulative balance data to JavaScript-friendly format
    $cumulativeBalanceLabels = json_encode(array_keys($cumulativeBalance));
    $cumulativeBalanceData = json_encode(array_values($cumulativeBalance));


    // Weekly and Monthly Aggregation
    $weeklyData = [];
    $weeklyLabels = [];
    foreach ($allDates as $date => $profit) {
        $week = (new DateTime($date))->format('Y-W'); // Year-week number
        $weeklyData[$week] = ($weeklyData[$week] ?? 0) + $profit;
    }
    $weeklyLabels = array_keys($weeklyData);

    $monthlyData = [];
    $monthlyLabels = [];
    foreach ($allDates as $date => $profit) {
        $month = (new DateTime($date))->format('Y-M'); // Year-month format
        $monthlyData[$month] = ($monthlyData[$month] ?? 0) + $profit;
    }
    $monthlyLabels = array_keys($monthlyData);

    $yearlyData = [];
    $yearlyLabels = [];
    foreach ($allDates as $date => $profit) {
        $year = (new DateTime($date))->format('Y'); // Year format
        $yearlyData[$year] = ($yearlyData[$year] ?? 0) + $profit;
    }
    $yearlyLabels = array_keys($yearlyData);

    $graphData = [
        'daily' => [
            'labels' => array_keys($allDates),
            'data' => array_values($allDates),
        ],
        'weekly' => [
            'labels' => $weeklyLabels, // Calculate weekly labels
            'data' => $weeklyData, // Aggregate profits into weekly totals
        ],
        'monthly' => [
            'labels' => $monthlyLabels, // Calculate monthly labels
            'data' => $monthlyData, // Aggregate profits into monthly totals
        ],
        'yearly' => [
            'labels' => $yearlyLabels, // Calculate monthly labels
            'data' => $yearlyData, // Aggregate profits into monthly totals
        ]
    ];


    // Convert PHP data to JavaScript-friendly format
    $expenseCategories = json_encode(array_keys($expense_totals));
    $expenseAmounts = json_encode(array_values($expense_totals));
    $incomeCategories = json_encode(array_keys($income_totals));
    $incomeAmounts = json_encode(array_values($income_totals));
?>

    
    <header class="header">
            <h1>Personal Finance Manager</h1>
    </header>



    <div class="container">
            <div class="balance-section">
                <h2>Current Balance: <span id="balance">$<?php echo number_format($summary['net_balance'], 2); ?></span></h2>
                <a href="help-page.html" target="_blank" class="helppage">Help Page</a>
                <div class="logout">
                    <?php echo "Welcome " . $_SESSION["username"] ?>
                    <a href="login.html" target="_blank">logout</a>
                    
                </div>  
                <div style="clear: both;"></div>
            </div>
        

        <hr>
        <br>
        
        <div class="form-section">
            <h3><?php echo $edit_data ? "Edit Transaction" : "Add Transaction"; ?></h3>
            <form method="post" action="index.php<?php echo $edit_data ? '?edit=' . $edit_data['id'] : ''; ?>">
                <input type="number" id="amount" step="0.01" name="amount" placeholder="Amount" value="<?php echo $edit_data['amount'] ?? ''; ?>" required>
                <input type="text" id="category" name="category" placeholder="Category" value="<?php echo $edit_data['category'] ?? ''; ?>" required>
                <input type="text" id="description" name="description" placeholder="Description" value="<?php echo $edit_data['description'] ?? ''; ?>">
                <div class="date-n-type">
                    <select id="type" name="type" required>
                        <option value="income" <?php echo (isset($edit_data['type']) && $edit_data['type'] == 'income') ? 'selected' : ''; ?>>Income</option>
                        <option value="expense" <?php echo (isset($edit_data['type']) && $edit_data['type'] == 'expense') ? 'selected' : ''; ?>>Expense</option>
                    </select>
                    <input type="date" id="date" name="date" value="<?php echo $edit_data['date'] ?? ''; ?>" required>
                </div>
                <input type="submit" style="padding: 3px; margin: 4px 0; width: 100%; background-color: #228B22; color: white;" name="<?php echo $edit_data ? 'update' : 'submit'; ?>" value="<?php echo $edit_data ? 'Update Transaction' : 'Add Transaction'; ?>">
                <div style="clear: both;"></div>
            </form>
        </div>

        <br>
        <hr>
        <br>
        <main>
            <section class="section1"  >
                <h3>Expenses and Income by Category</h3>
                <div class="button" style="text-align: center">
                    <button id="toggleChartButton" onclick="toggleChart()">Switch to Income Chart</button>
                </div>
                <div class="chart-section" style="height:70%; width: 100%; padding: 10px; text-align: center; position: flex; flex: 1;">
                    <canvas id="categoryPieChart" ></canvas>
                </div>

                <div class="select-buttons">
                    <label for="monthSelect" style="width: 45%">Select Month:</label>
                    <select id="monthSelect">
                        <option value="defaultValue">All Months</option>
                        <?php
                        foreach (range(1, 12) as $month) {
                            $monthName = DateTime::createFromFormat('!m', $month)->format('F');
                            echo "<option value='$month'>$monthName</option>";
                        }
                        ?>
                    </select>

                    <div class="select-buttons">
                        <label for="yearSelect" style="width: 45%;">Select Year:&nbsp;&nbsp;&nbsp;</label>
                        <select id="yearSelect">
                            <option value="">All Years</option>
                            <?php
                            $years = array_unique(array_map(function($expense) {
                                return (new DateTime($expense['date']))->format('Y');
                            }, $expenses));
                            foreach ($years as $year) {
                                echo "<option value='$year'>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </section>


            <section class="section2">
            <h3>Balance Over Time and Profit During a Time Period</h3>
                <div class="chart-button">
                    <button onclick="updateToBalanceChart()">Balance Over Time</button>
                    <button onclick="updateLineChart('daily')">Daily</button>
                    <button onclick="updateLineChart('weekly')">Weekly</button>
                    <button onclick="updateLineChart('monthly')">Monthly</button>
                    <button onclick="updateLineChart('yearly')">Yearly</button>
                </div>

                <div class="chart-section" style="position: flex; height:100%; width:100%; padding: 10px;">
                    <canvas id="linechart"></canvas>
                </div>
            </section>
        </main>
        

        <br>
        <hr>
        <br>

        <div class="transactions-section" id="transactions">
            <h3>Transactions</h3>
            <input type="text" id="searchInput" placeholder="search" onkeyup="searchItems()">
            <table>
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <script src="js/search.js"></script>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['amount']); ?></td>
                            <td><?php echo htmlspecialchars($expense['category']); ?></td>
                            <td><?php echo htmlspecialchars($expense['type']); ?></td>
                            <td><?php echo htmlspecialchars($expense['date']); ?></td>
                            <td><?php echo htmlspecialchars($expense['description']); ?></td>
                            <td>
                                <a href="?edit=<?php echo $expense['id']; ?>">Edit</a> |
                                <a href="?delete=<?php echo $expense['id']; ?>" onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

        <br>
        <hr>
        <br>

            <div class="summary-section">
            <h3>Financial Summary</h3>
            <p>Total Income: $<?php echo number_format($summary['total_income'], 2); ?></p>
            <p>Total Expenses: $<?php echo number_format($summary['total_expenses'], 2); ?></p>
            <p>Net Balance: $<?php echo number_format($summary['net_balance'], 2); ?></p>
            </div>

        <script>
            // Category data for income and expenses
            const expenseCategories = <?php echo $expenseCategories; ?>;
            const expenseAmounts = <?php echo $expenseAmounts; ?>;
            const incomeCategories = <?php echo $incomeCategories; ?>;
            const incomeAmounts = <?php echo $incomeAmounts; ?>;
            const expenses = <?php echo json_encode($expenses); ?>;

            let isExpenseChart = true;

            // Chart setup
            const ctx = document.getElementById('categoryPieChart').getContext('2d');
            let categoryChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: expenseCategories,
                    datasets: [{
                        label: 'Spending by Category',
                        data: expenseAmounts,
                        backgroundColor: ['#228B22', '#696969', '#2E8B57', '#A9A9A9', '#556B2F', '#708090','#8FBC8F',"#555555","#778B44" ,"#D3D3D3","#B0C4DE"]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {

                                    console.log('Context object:', context); // Log the entire context object
                                    console.log('Raw value:', context.raw);   // Log the specific raw value
                                    console.log('Label:', context.label);    // Log the corresponding label
                                    console.log('Tooltip context:', context); // Log the context for debugging
                                    
                                    return context.label + ': $' + context.formattedValue;
                                    
                                   
                                    
                                    
                                }
                            }
                        }
                    }
                }
            });

            // Toggle chart data
            function toggleChart() {
                
                isExpenseChart = !isExpenseChart;
                categoryChart.data.labels = isExpenseChart ? expenseCategories : incomeCategories;
                categoryChart.data.datasets[0].data = isExpenseChart ? expenseAmounts : incomeAmounts;
                categoryChart.data.datasets[0].label = isExpenseChart ? 'Spending by Category' : 'Income by Category';
                categoryChart.update();

                
                
                document.getElementById('toggleChartButton').textContent = isExpenseChart ? 'Switch to Income Chart' : 'Switch to Expense Chart';
                document.getElementById("monthSelect").value = "defaultValue"
            }
        </script>
        <script>
            // Data for all intervals
            const graphData = {
                daily: {
                    labels: <?php echo json_encode($graphData['daily']['labels']); ?>,
                    data: <?php echo json_encode($graphData['daily']['data']); ?>
                },
                weekly: {
                    labels: <?php echo json_encode($graphData['weekly']['labels']); ?>,
                    data: <?php echo json_encode($graphData['weekly']['data']); ?>
                },
                monthly: {
                    labels: <?php echo json_encode($graphData['monthly']['labels']); ?>,
                    data: <?php echo json_encode($graphData['monthly']['data']); ?>
                },
                yearly: {
                    labels: <?php echo json_encode($graphData['yearly']['labels']); ?>,
                    data: <?php echo json_encode($graphData['yearly']['data']); ?>
                },
                balance: {
                    labels: <?php echo $cumulativeBalanceLabels; ?>,
                    data: <?php echo $cumulativeBalanceData; ?>
                }
            };

            // Default chart interval
            let currentInterval = 'daily'; // Default interval
            const ctxLine = document.getElementById('linechart').getContext('2d');

            let lineChart = new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: graphData[currentInterval].labels,
                    datasets: [{
                        label: 'Net Profit Over Time',
                        data: graphData[currentInterval].data,
                        borderColor: '#228B22',
                        backgroundColor: 'rgba(34, 139, 34, 0.2)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Date' },
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 10,
                            }
                        },
                        y: {
                            suggestedMin: -100,
                            suggestedMax: 500,
                            title: { display: true, text: 'Net Profit ($)' }
                        
                        }
                    }
                }
            });

            // Function to filter and update pie chart data
            // Function to filter and update pie chart data
            function updatePieChart() {
                const selectedMonth = document.getElementById('monthSelect').value;
                const selectedYear = document.getElementById('yearSelect').value;

                // Filter expenses or income based on the selected month and year
                let filteredData = expenses.filter(expense => {
                    const date = new Date(expense.date.replace(/-/g, '/')); // Replace dashes with slashes for consistent parsing
                    const month = date.getMonth() + 1; // JavaScript months are 0-indexed
                    const year = date.getFullYear();

                    console.log("Expense Date:", expense.date, "Month:", month, "Year:", year); // Debugging log
                    console.log("Selected Month:", selectedMonth);
                    console.log("expenseAmounts: ", expenseAmounts);
                    
                    
                    // Match selected month and year
                    return (!selectedMonth || month == selectedMonth) && 
                        (!selectedYear || year == selectedYear);
                    
                    
                });

                console.log("Filtered Data:", filteredData); // Debugging log

                // Recalculate totals by category
                const newExpenseTotals = {};
                const newIncomeTotals = {};
                filteredData.forEach(expense => {
                    const category = expense.category;

                    const amount = parseFloat(expense.amount);

                    // Debugging: Check the category and amount for each transaction
                    console.log("Category:", category, "Amount:", expense.amount, "Type:", expense.type);

                    if (expense.type === 'expense') {
                        newExpenseTotals[category] = parseFloat(newExpenseTotals[category] || 0) + parseFloat(expense.amount);
                    } else if (expense.type === 'income') {
                        newIncomeTotals[category] = parseFloat(newIncomeTotals[category] || 0) + parseFloat(expense.amount);
                    }
                });

                console.log("New Expense Totals:", newExpenseTotals); // Debugging log
                console.log("New Income Totals:", newIncomeTotals); // Debugging log


                // Update chart data
                if (selectedMonth == "defaultValue"){
                    categoryChart.data.labels = isExpenseChart ? expenseCategories : incomeCategories;
                    categoryChart.data.datasets[0].data = isExpenseChart ? expenseAmounts : incomeAmounts;
                    categoryChart.data.datasets[0].label = isExpenseChart ? 'Spending by Category' : 'Income by Category';
                    categoryChart.update();

                } else {
                    categoryChart.data.labels = isExpenseChart 
                        ? Object.keys(newExpenseTotals) 
                        : Object.keys(newIncomeTotals);

                    categoryChart.data.datasets[0].data = isExpenseChart 
                        ? Object.values(newExpenseTotals) 
                        : Object.values(newIncomeTotals);

                }
                

                
                categoryChart.update();
            }

            // Add event listeners for the dropdowns
            document.getElementById('monthSelect').addEventListener('change', updatePieChart);
            document.getElementById('yearSelect').addEventListener('change', updatePieChart);


            // Update chart based on selected interval
            function updateLineChart(interval) {
                currentInterval = interval;
                lineChart.data.labels = graphData[currentInterval].labels;
                lineChart.data.datasets[0].data = graphData[currentInterval].data;
                lineChart.data.datasets[0].label = 'Net Profit Over Time';
                lineChart.update();
            }
            function updateToBalanceChart() {
                lineChart.data.labels = graphData.balance.labels;
                lineChart.data.datasets[0].data = graphData.balance.data;
                lineChart.data.datasets[0].label = 'Cumulative Balance Over Time';
                lineChart.update();
            }
        </script>
        
    </div>
</body>
</html>
