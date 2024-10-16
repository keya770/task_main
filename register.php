<?php
include 'config.php';

$message = "";
$toastClass = "";
$errors = []; // Array to hold validation errors

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $mobile_no = trim($_POST['mobile_no']);
    $address = trim($_POST['address']);
    $dob = trim($_POST['dob']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Get the confirm password

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = "User Name is required.";
    }
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if (empty($mobile_no)) {
        $errors['mobile_no'] = "Mobile No is required.";
    }
    if (empty($address)) {
        $errors['address'] = "Address is required.";
    }
    if (empty($dob)) {
        $errors['dob'] = "Date of Birth is required.";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // If there are no validation errors, proceed
    if (empty($errors)) {
        // Check if email already exists
        $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();

        if ($checkEmailStmt->num_rows > 0) {
            $errors['email'] = "Email ID already exists.";
        } else {
            // Insert user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, mobile_no, address, dob) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, password_hash($password, PASSWORD_DEFAULT), $mobile_no, $address, $dob); // Use password_hash for security

            if ($stmt->execute()) {
                $message = "Account created successfully.";
                $toastClass = "#28a745"; // Success color
            } else {
                $errors['general'] = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

// Edit user if ID is present in the URL
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }
}

// Update user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $mobile_no = trim($_POST['mobile_no']);
    $address = trim($_POST['address']);
    $dob = trim($_POST['dob']);

    // Sanitize the inputs
    $name = $conn->real_escape_string($name);
    $email = $conn->real_escape_string($email);
    $mobile_no = $conn->real_escape_string($mobile_no);
    $address = $conn->real_escape_string($address);
    $dob = $conn->real_escape_string($dob);

    // Update user in the database
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile_no = ?, address = ?, dob = ? WHERE id = ?");
    $stmt->bind_param("ssissi", $name, $email, $mobile_no, $address, $dob, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // Redirect to the user list
        exit();
    } else {
        $errors['general'] = "Error updating user: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Registration</title>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 500px;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #80bdff;
        }

        .form-header {
            background-color: #28a745;
            color: white;
            border-radius: 5px 5px 0 0;
            padding: 15px;
        }

        .form-button {
            font-weight: 600;
        }

        .error-message {
            color: #dc3545; /* Bootstrap danger color */
            font-size: 0.875em;
        }
    </style>
</head>

<body>
    <div class="container p-5">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: <?php echo $toastClass; ?>;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" class="form-control mt-3" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="form-header text-center">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2"></i>
                <h5 class="p-2">
                    <?php 
                    // Check if 'id' is present in the URL parameters
                    if (isset($_GET['id']) && !empty($_GET['id'])) {
                        echo 'Update Your Account';
                    } else {
                        echo 'Create Your Account';
                    }
                    ?>
                </h5>
            </div>
            <input type="hidden" name="id" value="<?php echo isset($user['id']) ? $user['id'] : ''; ?>">
            <div class="mb-3">
                <label for="username">User Name</label>
                <input type="text" name="username" id="username" value="<?php echo isset($user['username']) ? $user['username'] : ''; ?>" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <div class="error-message"><?php echo $errors['username']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="mobile_no">Mobile No</label>
                <input type="text" name="mobile_no" id="mobile_no" value="<?php echo isset($user['mobile_no']) ? $user['mobile_no'] : ''; ?>" class="form-control <?php echo isset($errors['mobile_no']) ? 'is-invalid' : ''; ?>" required>
                <?php if (isset($errors['mobile_no'])): ?>
                    <div class="error-message"><?php echo $errors['mobile_no']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="2" required class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>"><?php echo isset($user['address']) ? $user['address'] : ''; ?></textarea>
                <?php if (isset($errors['address'])): ?>
                    <div class="error-message"><?php echo $errors['address']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="dob">Date of Birth</label>
                <input type="date" name="dob" id="dob" value="<?php echo isset($user['dob']) ? $user['dob'] : ''; ?>" class="form-control <?php echo isset($errors['dob']) ? 'is-invalid' : ''; ?>" required>
                <?php if (isset($errors['dob'])): ?>
                    <div class="error-message"><?php echo $errors['dob']; ?></div>
                <?php endif; ?>
            </div>
            <?php if (isset($_GET['id'])): ?>

                <?php else: ?>
                    <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error-message"><?php echo $errors['confirm_password']; ?></div>
                <?php endif; ?>
            </div>
                <?php endif; ?>
           
            <div class="mb-3 text-center">
                <?php if (isset($_GET['id'])): ?>
                    <button name="update" type="submit" class="btn btn-success form-button">Update User</button>
                <?php else: ?>
                    <button name="create" type="submit" class="btn btn-success form-button">Create Account</button>
                <?php endif; ?>
            </div>
            <div class="mb-3 text-center">
                <p class="text-muted">Already have an Account? <a href="./login.php" style="text-decoration: none; color: #28a745;">Login</a></p>
            </div>
        </form>
    </div>

    <script>
        let toastElList = [].slice.call(document.querySelectorAll('.toast'));
        let toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>

</html>
