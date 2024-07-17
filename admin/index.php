<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Dashboard</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title mb-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
                        <form action="logout.php" method="post">
                            <input type="submit" value="Logout" class="btn btn-danger">
                        </form>
                        <a href="crud.php" class="btn btn-primary mt-3">Manage Users</a>
                        <a href="total.php" class="btn btn-success mt-3">Calculate</a>
                        <a href="bill.php" class="btn btn-success mt-3">bill</a>
                        <a href="bill_back.php" class="btn btn-success mt-3">bill_back</a> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
