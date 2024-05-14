<!-- <!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Employee Time Clock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<form class=" d-flex justify-content-center" id="employeeForm">
    <label for="employee_name"><b>Select Employee:</b></label>
    <select name="employee_name123" id="employee_name123">
        <option value="">Select an Employee</option>
        <option value="abc">abc</option>
        <option value="xyz">xyz</option>
        <option value="pqr">pqr</option>
    </select>
</form>

<script>
document.getElementById('employee_name').addEventListener('change', function() {
    let employeeName = this.value;
    if (employeeName) {
        fetch('times.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'employee_name=' + encodeURIComponent(employeeName)
        })
    }
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html> -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Punching</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <h1 class="mt-5 mb-3">Employee Punching System</h1>
        <form id="punchForm">
            <div class="mb-3">
                <label for="employee_name" class="form-label"><b>Select Employee:</b></label>
                <select name="employee_name" id="employee_name" class="form-select">
                    <option value="">Select an Employee</option>
                    <option value="abc">ABC</option>
                    <option value="xyz">XYZ</option>
                    <option value="pqr">PQR</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary" id="checkInBtn">Check In</button>
            <button type="button" class="btn btn-danger" id="checkOutBtn">Check Out</button>
        </form>

       
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('punchForm');
            const checkInBtn = document.getElementById('checkInBtn');
            const checkOutBtn = document.getElementById('checkOutBtn');
            const recordsBody = document.getElementById('recordsBody');

            // Event listener for Check In button
            checkInBtn.addEventListener('click', function() {
                punchAction('checkIn');
            });

            // Event listener for Check Out button
            checkOutBtn.addEventListener('click', function() {
                punchAction('checkOut');
            });

            // Function to handle check-in or check-out action
            function punchAction(action) {
                const employeeName = form.employee_name.value;

                if (!employeeName) {
                    alert('Please select an employee.');
                    return;
                }

                fetch('times.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'employee_name=' + encodeURIComponent(employeeName) + '&action=' + action
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Refresh the records table
                        fetchRecords();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Function to fetch and display records
            function fetchRecords() {
                fetch('fetch_records.php')
                .then(response => response.json())
                .then(data => {
                    // Clear previous records
                    recordsBody.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(record => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${record.employee_name}</td>
                                <td>${record.time_in}</td>
                                <td>${record.time_out}</td>
                            `;
                            recordsBody.appendChild(row);
                        });
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td colspan="3" class="text-center">No records found</td>`;
                        recordsBody.appendChild(row);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Fetch and display records on page load
            fetchRecords();
        });
    </script>
</body>
</html>
<?php
include("dbconnect.php");

$sql = "SELECT employee_name, time_in, time_out FROM emp_times ";
$result = $conn->query($sql);

if (isset($_POST['employee_name'])) {
    // Sanitizing the input to make it safer
    $employeeName = ($_POST['employee_name']);

    // SQL to check if the employee has already checked in but not clocked out
    $sql = "SELECT * FROM emp_times WHERE employee_name = '{$employeeName}' AND time_out IS NULL ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // If already checked in, record the clock out time
        $row = $result->fetch_assoc();
        $updateSql = "UPDATE emp_times SET time_out = NOW() WHERE id = {$row['id']}";
        $conn->query($updateSql);
        echo "Check-out recorded for $employeeName.";
    } else {
        // If not yet clocked in, insert new clock in record
        $insertSql = "INSERT INTO emp_times (employee_name, time_in) VALUES ('{$employeeName}', NOW())";
        $conn->query($insertSql);
        echo "Check-in recorded for $employeeName.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        table, th, td {
            margin-left: 9.5vw;
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
        th, td {
            text-align: center;
        }
    </style>
</head>

<body>
    <h1 id="h1">-------Employee Time Record⏲</h1>
    <table>
        <tr>
            <th>Employee Name</th>
            <th>Time In</th>
            <th>Time Out</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["employee_name"] . "</td>";
                echo "<td>" . $row["time_in"] . "</td>";
                echo "<td>" . $row["time_out"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No records found</td></tr>";
        }
        ?>
    </table>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>