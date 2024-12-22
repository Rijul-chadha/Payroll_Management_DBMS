<?php
function connectToDatabase()
{
    $host = "oracle.scs.ryerson.ca";
    $port = "1521";
    $sid = "orcl";
    $username = "kjshah";
    $password = "07267000";

    $connection_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
    $conn = oci_connect($username, $password, $connection_string);

    if (!$conn) {
        $e = oci_error();
        die("Connection failed: " . htmlentities($e['message']));
    }
    return $conn;
}

function createSchema($conn)
{
    // List of SQL queries
    $queries = [
        "CREATE TABLE Employer(
            employer_id INT,
            PRIMARY KEY(employer_id),
            name VARCHAR2(20) NOT NULL,
            username VARCHAR2(20) NOT NULL,
            password VARCHAR2(20) NOT NULL
        )",
        "CREATE TABLE Deduction_Ind(
            deduction_ind_id INT,
            PRIMARY KEY (deduction_ind_id),
            employer_id INT NOT NULL,
            FOREIGN KEY (employer_id) REFERENCES Employer(employer_id) ON DELETE CASCADE,
            deduction_type VARCHAR2(20) NOT NULL,
            deduction_percent NUMBER(3, 2) NOT NULL,
            CHECK(deduction_percent >= 0)
        )",
        "CREATE TABLE Pay_Period_Ind(
            payroll_gen_date DATE,
            PRIMARY KEY(payroll_gen_date),
            employer_id INT NOT NULL,
            FOREIGN KEY (employer_id) REFERENCES Employer(employer_id) ON DELETE CASCADE,
            pay_period INT NOT NULL,
            pay_start_date DATE NOT NULL,
            pay_end_date DATE NOT NULL,
            payment_date DATE NOT NULL,
            total_pay_hours NUMBER(5,2) NOT NULL,
            CHECK(total_pay_hours  >= 0)
        )",
        "CREATE TABLE Employee(
            employee_id INT,
            PRIMARY KEY (employee_id),
            employer_id INT NOT NULL,
            FOREIGN KEY (employer_id) REFERENCES Employer(employer_id) ON DELETE CASCADE,
            name VARCHAR2(20) NOT NULL,
            sin_num INT NOT NULL,
            address VARCHAR2(80) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE,
            username VARCHAR2(20) NOT NULL,
            password VARCHAR2(20) NOT NULL,
            bank VARCHAR2(20) NOT NULL,
            account_number INT NOT NULL,
            hourly_rate NUMBER(5, 2) NOT NULL,
            CHECK(hourly_rate  >= 0)
        )",
        "CREATE TABLE Payroll(
            employee_id REFERENCES Employee(employee_id) ON DELETE CASCADE,
            payroll_gen_date REFERENCES Pay_Period_Ind(payroll_gen_date) ON DELETE CASCADE,
            PRIMARY KEY(employee_id, payroll_gen_date),
            employer_id INT NOT NULL,
            FOREIGN KEY (employer_id) REFERENCES Employer(employer_id) ON DELETE CASCADE,
            gross_inc NUMBER(8, 2) NOT NULL,
            overtime_hours NUMBER(5,2) DEFAULT 0,
            total_deduction_amount NUMBER(8,2) DEFAULT 0,
            total_leave_amount NUMBER(8,2) DEFAULT 0,
            bonus NUMBER(8, 2) DEFAULT 0,
            CHECK(gross_inc >= 0),
            CHECK(overtime_hours >= 0),
            CHECK(total_deduction_amount >= 0),
            CHECK(total_leave_amount  >= 0),
            CHECK(bonus >= 0)
        )",
        "CREATE TABLE Deduction(
            employee_id REFERENCES Employee(employee_id) ON DELETE CASCADE,
            deduction_ind_id REFERENCES Deduction_Ind(deduction_ind_id) ON DELETE CASCADE,
            payroll_gen_date REFERENCES Pay_Period_Ind(payroll_gen_date) ON DELETE CASCADE,
            PRIMARY KEY(employee_id, deduction_ind_id, payroll_gen_date),
            deduction_amount NUMBER(8,2) DEFAULT 0,
            CHECK(deduction_amount >= 0)
        )",
        "CREATE TABLE Leave (
            leave_id INT,
            employee_id REFERENCES Employee(employee_id) ON DELETE CASCADE,
            deduction_ind_id REFERENCES Deduction_Ind(deduction_ind_id) ON DELETE CASCADE,
            PRIMARY KEY(leave_id, employee_id, deduction_ind_id),
            leave_hours NUMBER(5,2) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            CHECK(leave_hours >= 0)
        )",
        "CREATE SEQUENCE employer_seq_id START WITH 0 INCREMENT BY 1 MINVALUE 0",
        "CREATE SEQUENCE employee_seq_id START WITH 0 INCREMENT BY 1 MINVALUE 0",
        "CREATE SEQUENCE deduction_ind_seq_id START WITH 0 INCREMENT BY 1 MINVALUE 0",
        "CREATE SEQUENCE leave_seq_id START WITH 0 INCREMENT BY 1 MINVALUE 0",
        "CREATE VIEW EMPLOYEE_BANK_INFO AS SELECT NAME, EMPLOYEE_ID, BANK, ACCOUNT_NUMBER FROM EMPLOYEE",
        "CREATE VIEW SECOND_HALF_YEAR AS SELECT PAYROLL_GEN_DATE, PAY_PERIOD, PAY_START_DATE, PAY_END_DATE, PAYMENT_DATE FROM PAY_PERIOD_IND WHERE PAY_START_DATE >= TO_DATE('0107', 'DDMM')",
        "CREATE VIEW HIGH_INCOME AS SELECT EMPLOYEE_ID, GROSS_INC FROM PAYROLL WHERE GROSS_INC > 3000"
    ];

    foreach ($queries as $query) {
        try {
            executeQuery($conn, $query);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    return "Schema created successfully.";
}

function dropSchema($conn)
{
    // List of DROP queries
    $queries = [
        "DROP TABLE Deduction",
        "DROP TABLE Payroll",
        "DROP TABLE Leave",
        "DROP TABLE Deduction_Ind",
        "DROP TABLE Pay_Period_Ind",
        "DROP TABLE Employee",
        "DROP TABLE Employer",
        "DROP SEQUENCE Deduction_Ind_Seq_Id",
        "DROP SEQUENCE Employee_Seq_Id",
        "DROP SEQUENCE Leave_Seq_Id",
        "DROP SEQUENCE Employer_Seq_Id",
        "DROP VIEW EMPLOYEE_BANK_INFO",
        "DROP VIEW HIGH_INCOME",
        "DROP VIEW SECOND_HALF_YEAR"
    ];

    foreach ($queries as $query) {
        try {
            executeQuery($conn, $query);
        } catch (Exception $e) {
            // Return the error message if any query fails
            return "Error: " . $e->getMessage();
        }
    }

    return "Schema dropped successfully.";
}

function insertData($conn)
{
    // Array of INSERT queries
    $queries = [
        "INSERT INTO EMPLOYER(EMPLOYER_ID, NAME, USERNAME, PASSWORD) VALUES (employer_seq_id.nextval, 'Andy Jones', 'andy_jones', 'andyjones123')",
        "INSERT INTO EMPLOYER(EMPLOYER_ID, NAME, USERNAME, PASSWORD) VALUES (employer_seq_id.nextval, 'Emma Smith', 'emma_smith', 'emmasmith123')",

        "INSERT INTO EMPLOYEE(EMPLOYEE_ID, EMPLOYER_ID, NAME, SIN_NUM, ADDRESS, START_DATE, END_DATE, USERNAME, PASSWORD, BANK, ACCOUNT_NUMBER, HOURLY_RATE) 
        VALUES (employee_seq_id.nextval, 1, 'James Parker', '123456789', '50 Pinewood Blvd, Toronto, ON M1R 2F4, Canada', TO_DATE('20200516', 'YYYYMMDD'), NULL, 'jamesparker', 'jamesparker123', 'RBC', 234123, 35.00)",
        "INSERT INTO EMPLOYEE(EMPLOYEE_ID, EMPLOYER_ID, NAME, SIN_NUM, ADDRESS, START_DATE, END_DATE, USERNAME, PASSWORD, BANK, ACCOUNT_NUMBER, HOURLY_RATE) 
        VALUES (employee_seq_id.nextval, 1, 'Chase Samson', '789456123', '80 Brickwood Blvd, Toronto, ON M1P 2T5, Canada', TO_DATE('20240218', 'YYYYMMDD'), NULL, 'chasesamson', 'chasesamson123', 'BMO', 456789, 40.00)",

        "INSERT INTO PAY_PERIOD_IND(PAYROLL_GEN_DATE, EMPLOYER_ID, PAY_PERIOD, PAY_START_DATE, PAY_END_DATE, PAYMENT_DATE, TOTAL_PAY_HOURS) 
        VALUES (TO_DATE('20241015', 'YYYYMMDD'), 1, 1, TO_DATE('20241001', 'YYYYMMDD'), TO_DATE('20241014', 'YYYYMMDD'), TO_DATE('20241018', 'YYYYMMDD'), 80)",
        "INSERT INTO PAY_PERIOD_IND(PAYROLL_GEN_DATE, EMPLOYER_ID, PAY_PERIOD, PAY_START_DATE, PAY_END_DATE, PAYMENT_DATE, TOTAL_PAY_HOURS) 
        VALUES (TO_DATE('20241031', 'YYYYMMDD'), 1, 2, TO_DATE('20241015', 'YYYYMMDD'), TO_DATE('20241030', 'YYYYMMDD'), TO_DATE('20241103', 'YYYYMMDD'), 90)",

        "INSERT INTO DEDUCTION_IND(DEDUCTION_IND_ID, EMPLOYER_ID, DEDUCTION_TYPE, DEDUCTION_PERCENT) 
        VALUES (deduction_ind_seq_id.nextval, 1, 'TAX', 0.05)",
        "INSERT INTO DEDUCTION_IND(DEDUCTION_IND_ID, EMPLOYER_ID, DEDUCTION_TYPE, DEDUCTION_PERCENT) 
        VALUES (deduction_ind_seq_id.nextval, 1, 'SICK_LEAVE', 1.00)",

        "INSERT INTO DEDUCTION(EMPLOYEE_ID, DEDUCTION_IND_ID, PAYROLL_GEN_DATE, DEDUCTION_AMOUNT) 
        VALUES (1, 1, TO_DATE('20241015', 'YYYYMMDD'), DEFAULT)",
        "INSERT INTO DEDUCTION(EMPLOYEE_ID, DEDUCTION_IND_ID, PAYROLL_GEN_DATE, DEDUCTION_AMOUNT) 
        VALUES (2, 1, TO_DATE('20241015', 'YYYYMMDD'), DEFAULT)",

        "INSERT INTO LEAVE(LEAVE_ID, EMPLOYEE_ID, DEDUCTION_IND_ID, LEAVE_HOURS, START_DATE, END_DATE) 
        VALUES (leave_seq_id.nextval, 1, 2, 7, TO_DATE('20241013', 'YYYYMMDD'), TO_DATE('20241013', 'YYYYMMDD'))",
        "INSERT INTO LEAVE(LEAVE_ID, EMPLOYEE_ID, DEDUCTION_IND_ID, LEAVE_HOURS, START_DATE, END_DATE) 
        VALUES (leave_seq_id.nextval, 2, 2, 3.5, TO_DATE('20241014', 'YYYYMMDD'), TO_DATE('20241014', 'YYYYMMDD'))",

        "INSERT INTO PAYROLL(EMPLOYEE_ID, PAYROLL_GEN_DATE, EMPLOYER_ID, GROSS_INC, OVERTIME_HOURS, TOTAL_DEDUCTION_AMOUNT, TOTAL_LEAVE_AMOUNT, BONUS) 
        VALUES (1, TO_DATE('20241015', 'YYYYMMDD'), 2, 2800.00, DEFAULT, DEFAULT, DEFAULT, DEFAULT)",
        "INSERT INTO PAYROLL(EMPLOYEE_ID, PAYROLL_GEN_DATE, EMPLOYER_ID, GROSS_INC, OVERTIME_HOURS, TOTAL_DEDUCTION_AMOUNT, TOTAL_LEAVE_AMOUNT, BONUS) 
        VALUES (2, TO_DATE('20241031', 'YYYYMMDD'), 2, 3150.00, DEFAULT, DEFAULT, DEFAULT, DEFAULT)",

        "INSERT INTO PAYROLL(EMPLOYEE_ID, PAYROLL_GEN_DATE, EMPLOYER_ID, GROSS_INC, OVERTIME_HOURS, TOTAL_DEDUCTION_AMOUNT, TOTAL_LEAVE_AMOUNT, BONUS) 
        VALUES (2, TO_DATE('20241015', 'YYYYMMDD'), 2, 3200.00, 5, DEFAULT, DEFAULT, DEFAULT)"
    ];

    foreach ($queries as $query) {
        try {
            executeQuery($conn, $query);
        } catch (Exception $e) {
            // Return the error message if any query fails
            return "Error: " . $e->getMessage();
        }
    }

    return "Data inserted successfully.";
}

function queries($conn) {
  $queries = [
    "SELECT emp.name AS employee_name, e.name AS employer_name, di.deduction_percent, d.deduction_amount
     FROM Employee emp
     JOIN Employer e ON emp.employer_id = e.employer_id
     JOIN Deduction d ON emp.employee_id = d.employee_id
     JOIN Deduction_Ind di ON d.deduction_ind_id = di.deduction_ind_id
     JOIN Pay_Period_Ind pp ON d.payroll_gen_date = pp.payroll_gen_date
     WHERE pp.pay_period = 1",

    "SELECT e.name AS employer_name, SUM(p.gross_inc) AS total_payroll
     FROM Employer e
     JOIN Payroll p ON e.employer_id = p.employer_id
     GROUP BY e.name",

    "SELECT e.name AS employer_name, emp.name AS employee_name, SUM(l.leave_hours) AS total_leave_hours
     FROM Employer e
     JOIN Employee emp ON e.employer_id = emp.employer_id
     JOIN Leave l ON emp.employee_id = l.employee_id
     WHERE EXTRACT(YEAR FROM l.start_date) = EXTRACT(YEAR FROM SYSDATE)
     GROUP BY e.name, emp.name",

    "SELECT DISTINCT emp.name AS employee_name, e.name AS employer_name, pp.pay_period, pp.pay_start_date, pp.pay_end_date, pp.total_pay_hours, SUM(l.leave_hours) AS total_leave_hours
     FROM Employee emp
     JOIN Employer e ON emp.employer_id = e.employer_id
     JOIN Payroll p ON emp.employee_id = p.employee_id
     JOIN Pay_Period_Ind pp ON p.payroll_gen_date = pp.payroll_gen_date
     LEFT JOIN Leave l ON emp.employee_id = l.employee_id
     GROUP BY emp.name, e.name, pp.pay_period, pp.pay_start_date, pp.pay_end_date, pp.total_pay_hours",

    "SELECT e.name AS Employee_Name, l.leave_hours AS Leave_Hours 
     FROM Employee e 
     JOIN Leave l ON e.employee_id = l.employee_id 
     WHERE l.leave_hours > 5 
     ORDER BY l.leave_hours DESC",

    "SELECT em.name AS Employer_Name, SUM(p.total_deduction_amount) AS Total_Deductions
     FROM Employer em
     JOIN Payroll p ON em.employer_id = p.employer_id
     GROUP BY em.name
     ORDER BY Total_Deductions DESC",

    "DELETE FROM Deduction d
     WHERE d.employee_id IN (
         SELECT emp.employee_id
         FROM Employee emp
         WHERE emp.end_date IS NOT NULL AND emp.end_date < ADD_MONTHS(SYSDATE, -12)
     )",

    "UPDATE Payroll p SET p.total_deduction_amount = (SELECT SUM(d.deduction_amount)
     FROM Deduction d
     WHERE d.employee_id = p.employee_id AND d.payroll_gen_date = p.payroll_gen_date)
     WHERE EXISTS (
         SELECT 1
         FROM Deduction d
         WHERE d.employee_id = p.employee_id AND d.payroll_gen_date = p.payroll_gen_date
     )",

    "UPDATE Payroll SET bonus = 1000 where employee_id = 2 AND payroll_gen_date = TO_DATE('31102024', 'DDMMYYYY')",

    "SELECT emp.employee_id, emp.name, p.payroll_gen_date
     FROM Employee emp
     JOIN Payroll p ON emp.employee_id = p.employee_id
     WHERE p.overtime_hours > 0
     AND NOT EXISTS (
         SELECT 1
         FROM Leave l
         WHERE l.employee_id = emp.employee_id
         AND l.start_date <= p.payroll_gen_date
         AND l.end_date >= p.payroll_gen_date
     )",

    "SELECT employee_id
     FROM Employee
     MINUS
     SELECT employee_id
     FROM Deduction WHERE deduction_ind_id = 2",

    "SELECT emp.employer_id, e.name, COUNT(DISTINCT p.payroll_gen_date) AS num_pay_periods
     FROM Employee emp
     JOIN Payroll p ON emp.employee_id = p.employee_id
     JOIN Employer e ON emp.employer_id = e.employer_id
     GROUP BY emp.employer_id, e.name
     HAVING COUNT(DISTINCT p.payroll_gen_date) > 1",

    "SELECT emp.employee_id, emp.name, p.payroll_gen_date, p.overtime_hours, p.bonus
     FROM Employee emp
     JOIN Payroll p ON emp.employee_id = p.employee_id
     WHERE p.overtime_hours > 0
     UNION
     SELECT emp.employee_id, emp.name, p.payroll_gen_date, p.overtime_hours, p.bonus
     FROM Employee emp
     JOIN Payroll p ON emp.employee_id = p.employee_id
     WHERE p.bonus > 0",

    "SELECT emp.employer_id, e.name, AVG(p.gross_inc) AS avg_gross_income
     FROM Employee emp
     JOIN Payroll p ON emp.employee_id = p.employee_id
     JOIN Employer e ON emp.employer_id = e.employer_id
     GROUP BY emp.employer_id, e.name
     HAVING AVG(p.gross_inc) >= (SELECT AVG(gross_inc) FROM Payroll)"
  ];

  // Loop through the queries and execute them
  foreach ($queries as $query) {
    executeSelectQuery($conn, $query);
  }

}

function view($conn, $table_name) {
    echo "Displaying table: " . htmlentities($table_name) . "<br>";
    $query = "SELECT * FROM $table_name";
    executeSelectQuery($conn, $query);

}

function update($conn, $table_name, $column_name, $new_value, $condition) {
    echo "Updated table " .  htmlentities($table_name) . "<br>";
    $query = "UPDATE $table_name SET $column_name = '$new_value' WHERE $condition";
    executeQuery($conn, $query);
    view($conn, $table_name);
}

function search($conn, $table_name, $column_name, $condition) {
   echo "Searching " . htmlentities($table_name) . "<br>";
   $query = "SELECT $column_name FROM $table_name WHERE $condition";
   executeSelectQuery($conn, $query);

}

function deleteRec($conn, $table_name, $condition){
   echo "Deleted record in table " . htmlentities($table_name) . "<br>";
   $query = "DELETE FROM $table_name WHERE $condition";
   executeQuery($conn, $query);
   view($conn, $table_name);
}

function executeSelectQuery($conn, $query)
{
    // Print and execute SELECT query
    echo "Executing SELECT Query: " . htmlentities($query) . "<br>";

    $stid = oci_parse($conn, $query);
    if (!$stid) {
        $e = oci_error($conn);
        die("Error parsing SQL: " . htmlentities($e['message']));
    }

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        die("Error executing SQL: " . htmlentities($e['message']));
    }

    // Start the HTML table
    echo "<table border='1' style='border-collapse: collapse; width: 100%; text-align: left;'>";
    
    // Initialize a flag to print the table header only once
    $headerPrinted = false;
    
    // Fetch rows
    while ($row = oci_fetch_assoc($stid)) {
        // Print the table header on the first iteration
        if (!$headerPrinted) {
            echo "<tr>";
            foreach (array_keys($row) as $columnName) {
                echo "<th>" . htmlspecialchars($columnName) . "</th>";
            }
            echo "</tr>";
            $headerPrinted = true;
        }
    
        // Print each row
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    
    // End the table
    echo "</table>";	
    echo "<br>";
    oci_free_statement($stid);
}

function executeQuery($conn, $query)
{

    echo "Executing Query: " . htmlentities($query) . "<br>";	
    $stid = oci_parse($conn, $query);
    if (!$stid) {
        $e = oci_error($conn);
        die("Error parsing SQL: " . htmlentities($e['message']));
    }
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        die("Error executing SQL: " . htmlentities($e['message']));
    }
    oci_free_statement($stid);
}


?>

