<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<section class="vh-100 gradient-custom">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card bg-dark text-white" style="border-radius: 1rem;">
          <div class="card-body p-5 text-center">

            <div class="mb-md-5 mt-md-4 pb-5">

              <h2 class="fw-bold mb-2 text-uppercase">Login</h2>
              <p class="text-white-50 mb-5">Please enter your login and password!</p>

              <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-3">
              <label class="form-label" for="username">Username</label>
                <div data-mdb-input-init class="form-outline form-white mb-4">
                  <input type="text" id="username" name="username" class="form-control form-control-lg" required>
                  
                </div>

                <div data-mdb-input-init class="form-outline form-white mb-4">
                <label class="form-label" for="password">Password</label>
                  <input type="password" id="password" name="password" class="form-control form-control-lg" required>
                  
                </div>

               

                <button data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>
              </form>

            </div>

            

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
require_once "config.php";

// Check if the form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Get the data from the POST form
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Prepare SQL statement to find the user with the entered username
    $sql = "SELECT id, username, password, urole FROM users WHERE username = ?";

    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_username);

        $param_username = $username;

        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);

            if(mysqli_stmt_num_rows($stmt) == 1){
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $urole);
                if(mysqli_stmt_fetch($stmt)){
                    if(password_verify($password, $hashed_password)){
                        session_start();

                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["urole"] = $urole;

                        if ($urole === "1") {
                            header("location: admin/index.php");
                        }
                        if ($urole === "2") {
                            header("location: technician/total.php");
                        } 
                        if ($urole === "3") {
                            header("location: users/index.php");
                        } 
                    } else{
                        $password_err = "รหัสผ่านที่คุณป้อนไม่ถูกต้อง";
                    }
                }
            } else{
                $username_err = "ไม่พบบัญชีผู้ใช้ดังกล่าว";
            }
        } else{
            echo "มีบางอย่างผิดพลาด กรุณาลองใหม่ภายหลัง";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
$password = $_POST["password"]; // รับรหัสผ่านจากฟอร์ม

// เข้ารหัสรหัสผ่านก่อนที่จะบันทึกลงในฐานข้อมูล
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

?>

</body>
</html>
