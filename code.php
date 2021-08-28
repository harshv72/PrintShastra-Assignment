<!-- PHP code to establish connection
// with the localserver -->
<?php
  
// Username is root
$user = 'root';
$password = ''; 
  
// Database name is gfg
$database = 'smrusqsn_store'; 
  
// Server is localhost with
// port number 3308
$servername='localhost:3306';
$mysqli = new mysqli($servername, $user, 
                $password, $database);
  
// Checking for connections
if ($mysqli->connect_error) {
    die('Connect Error (' . 
    $mysqli->connect_errno . ') '. 
    $mysqli->connect_error);
}
  
// SQL query to get all table names from database
$sql = "SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='$database' ";
// print($sql);

$result = $mysqli->query($sql);
// while($rows=$result->fetch_assoc())
// {
//     print($rows["TABLE_NAME"]);
// }
$mysqli->close();
?>
<!-- HTML code to display data in tabular format -->
<!DOCTYPE html>
<html lang="en">
  
<head>
    
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PrintShastra</title>
    <!-- CSS FOR STYLING THE PAGE -->
    <style>
        table {
            margin: 0 auto;
            font-size: large;
            border: 1px solid black;
        }
  
        h1 {
            display: inline;
            font-size: xx-large;
            font-family: 'Gill Sans', 'Gill Sans MT', 
            ' Calibri', 'Trebuchet MS', 'sans-serif';
        }
  
        td {
            background-color: #E4F5D4;
            border: 1px solid black;
        }
  
        th,
        td {
            font-weight: bold;
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
  
        td {
            font-weight: lighter;
        }
    </style>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
  
<body>
    <section>
        <div style="text-align: center;">
            <h1 style="color:orange">Print</h1>
            <h1 style="color:blue">Shastra</h1>
            <h1> Assignment</h1>
        </div><br>
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <strong>Note: </strong> Select "Entire Database" option to export the Entire Database.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <!-- TABLE CONSTRUCTION-->
        <center>
        <form id="export" action="process.php" method="POST">
            <div class="row">
                <div class="col" style="text-align:right;line-height:50px;">
                    <label class="form-check-label" for="SelectTable">
                        Select Item to Export
                    </label>
                </div>
                <div class="col">
                    <select name="item" class="form-select" style="width=80px;height:50px" aria-label="Default select example">
                        <option disabled selected>-- Select --</option>
                        <option value="smrusqsn_store">Entire Database</option>
                        <!-- PHP CODE TO FETCH DATA FROM ROWS-->
                        <?php   // LOOP TILL END OF DATA 
                            while($rows=$result->fetch_assoc())
                            {
                                echo "<option value='". $rows["TABLE_NAME"] ."'>" .$rows["TABLE_NAME"] ."</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="col"></div>
            </div>
            <br><br>
            <div class="row">
                <div class="col"></div>
                <div class="col">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="export_format" id="pdf" value="pdf">
                        <label class="form-check-label" for="pdf">PDF</label>
                        </div>
                        <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="export_format" id="excel" value="excel">
                        <label class="form-check-label" for="excel">Excel</label>
                    </div>
                </div>
                <div class="col"></div>
            </div>
            <br><br>
            <input class="btn btn-danger" type="submit" value="Export">
        </form>
        </center> 
        <div id="paramvalue"></div>  
        
    </section>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
  
</html>