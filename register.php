<?php
include 'config.php';

$message = "";
$toastClass = "";
print_r($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $mobile_no = $_POST['mobile_no'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $password = $_POST['password'];

    // Check if email already exists
    $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        $message = "Email ID already exists";
        $toastClass = "#007bff"; // Primary color
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, mobile_no ,address,dob) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $password,$mobile_no,$address,$dob);

        if ($stmt->execute()) {
            $message = "Account created successfully";
            $toastClass = "#28a745"; // Success color
        } else {
            $message = "Error: " . $stmt->error;
            $toastClass = "#dc3545"; // Danger color
        }

        $stmt->close();
    }

    // $checkEmailStmt->close();
    // $conn->close();
}

// edit
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['id'])) {
    // print_r($_GET);
    $id = $_GET['id'];
    $id = intval($id);

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id); // "i" means the parameter is an integer
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $name = $_POST['username'];
    $email = $_POST['email'];
    $mobile_no= $_POST['mobile_no'];
    $address= $_POST['address'];
    $dob= $_POST['dob'];

    // Sanitize the inputs
    $name = $conn->real_escape_string($name);
    $email = $conn->real_escape_string($email);
    $mobile_no = $conn->real_escape_string($mobile_no);
    $address = $conn->real_escape_string($address);
    $dob = $conn->real_escape_string($dob);

    // Update the user in the database
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?,mobile_no =?,address=?,dob = ? WHERE id = ?");
    $stmt->bind_param("ssissi", $name, $email,$mobile_no,$address,$dob, $id);

    if ($stmt->execute()) {
        echo "User updated successfully.";
        // Redirect or perform another action
        header("Location: dashboard.php"); // Redirect to the user list
        exit();
    } else {
        echo "Error updating user: " . $stmt->error;
    }
} else {
    echo "Invalid request method.";
}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Registration</title>
</head>

<body class="bg-light">
    <div class="container p-5 d-flex flex-column align-items-center">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white border-0" 
          role="alert" aria-live="assertive" aria-atomic="true"
                style="background-color: <?php echo $toastClass; ?>;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close
                    btn-close-white me-2 m-auto" 
                          data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" class="form-control mt-5 p-4"
            style="height:auto; width:380px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
            rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="row text-center">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2" style="color: green;"></i>
                <h5 class="p-4" style="font-weight: 700;">Create Your Account</h5>
            </div>
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            <div class="mb-2">
                <label for="username">User Name</label>
                <input type="text" name="username" id="username" value="<?php if(isset($user['username'])){echo $user['username'];}?>"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="email"> Email</label>
                <input type="text" name="email" id="email" value="<?php if(isset($user['email'])){echo $user['email'];}?>"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="mobile_no">Mobile No</label>
                <input type="text" name="mobile_no" id="mobile_no" value="<?php if(isset($user['mobile_no'])){echo $user['mobile_no'];}?>"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
            <label for="address">Address:</label><br>
            <textarea id="address" name="address" rows="2" cols="40" required><?php if(isset($user['address'])){echo $user['address'];}?></textarea>
            </div>
            <div class="mb-2 mt-2">
                <label for="dob"> Date of Birth</label>
                <input type="date" name="dob" id="dob" value="<?php if(isset($user['dob'])){echo $user['dob'];}?>"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="password">Password</label>
                <input type="text" name="password" id="password" value="<?php if(isset($user['password'])){echo $user['password'];}?>"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-3">
                    <?php
                    if (isset($_GET['id'])) {  ?>
                    <button name="update" type="submit">Update User</button>
                    <?php }else{ ?>
                    <button name="create" type="submit" 
                    class="btn btn-success
                    bg-success" style="font-weight: 600;">Create
                    Account</button>
                   <?php } ?>
            </div>
            <div class="mb-2 mt-4">
                <p class="text-center" style="font-weight: 600; 
                color: navy;">I have an Account <a href="./login.php"
                        style="text-decoration: none;">Login</a></p>
            </div>
        </form>
    </div>
    <script>
        let toastElList = [].slice.call(document.querySelectorAll('.toast'))
        let toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>

</html>
