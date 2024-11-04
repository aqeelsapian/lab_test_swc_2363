<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "employee_payment");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from form
$name = $_POST['name'];
$staff_id = $_POST['staff_id'];
$month = $_POST['month'];
$position = $_POST['position'];
$deduction = $_POST['deduction'];

// Define salary based on position
$salary = 0;
switch ($position) {
    case 'Clerk 1':
        $salary = 1700;
        break;
    case 'Clerk 2':
        $salary = 2000;
        break;
    case 'Driver':
        $salary = 2500;
        break;
    case 'Temp staff':
        $salary = 1200;
        break;
}

// Calculate deductions
$kwsp = $salary * 0.12;
$socso = $salary * 0.05;

// Determine which deduction to apply
$final_kwsp = ($deduction === 'kwsp') ? $kwsp : 0;
$final_socso = ($deduction === 'socso') ? $socso : 0;
$net_payment = $salary - $final_kwsp - $final_socso;

// Insert employee data if not already exists
$sql = "INSERT INTO employees (name, staff_id, position) 
        VALUES ('$name', '$staff_id', '$position')
        ON DUPLICATE KEY UPDATE name='$name', position='$position'";
if (!$conn->query($sql)) {
    die("Error inserting employee data: " . $conn->error);
}

// Retrieve employee ID
$employee_id = $conn->insert_id;
if ($employee_id == 0) {
    // Employee already exists, fetch their ID
    $result = $conn->query("SELECT id FROM employees WHERE staff_id='$staff_id'");
    $row = $result->fetch_assoc();
    $employee_id = $row['id'];
}

// Insert payment data
$date_of_payment = date("Y-m-d");
$sql = "INSERT INTO payments (employee_id, month, salary, kwsp, socso, net_payment, date_of_payment)
        VALUES ('$employee_id', '$month', '$salary', '$final_kwsp', '$final_socso', '$net_payment', '$date_of_payment')";

if ($conn->query($sql) === TRUE) {
    // Include the CSS styling
    echo '<link rel="stylesheet" href="payslip-style.css">';
    
    // Payslip content with styling applied
    echo "<div class='payslip'>";
    echo "<h2>Payslip</h2>";
    echo "<p><span>Employee Name:</span> $name</p>";
    echo "<p><span>Staff ID:</span> $staff_id</p>";
    echo "<p><span>Position:</span> $position</p>";
    echo "<p><span>Month:</span> $month</p>";
    echo "<p><span>Salary:</span> RM $salary</p>";
    echo "<p><span>KWSP Deduction:</span> RM $final_kwsp</p>";
    echo "<p><span>SOCSO Deduction:</span> RM $final_socso</p>";
    echo "<p><span>Net Payment:</span> RM $net_payment</p>";
    echo "<p><span>Date of Payment:</span> $date_of_payment</p>";
    echo "<button onclick='window.print()'>Print</button>";
    echo "</div>";
} else {
    echo "Error inserting payment data: " . $conn->error;
}

$conn->close();
?>
