<?php
session_start();
include 'config.php';
include 'ajax.php';

// Check if the user is logged in; if not, redirect them to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_data = $conn->query("SELECT * FROM users WHERE is_delete = 0");
$query = $conn->prepare("SELECT username FROM users WHERE email = ?");
$query->bind_param("s", $_SESSION['email']);
$query->execute();
$result = $query->get_result();
if ($result) {
    $name_main = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light bg-success">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#">Welcome, <?php echo htmlspecialchars($name_main['username']); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Add more nav items here if needed -->
            </ul>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    User Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="./logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>


    <div class="container mt-4">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Mobile No</th>
                    <th scope="col">Address</th>
                    <th scope="col">Status</th>
                    <th scope="col">Role</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_data as $user): ?>
                  <tr id="user_<?php echo $user['id']; ?>">
    <th scope="row"><?php echo htmlspecialchars($user['id']); ?></th>
    <td><?php echo htmlspecialchars($user['username']); ?></td>
    <td><?php echo htmlspecialchars($user['email']); ?></td>
    <td><?php echo htmlspecialchars($user['mobile_no']); ?></td>
    <td><?php echo htmlspecialchars($user['address']); ?></td>
    <td>
        <span class="badge <?php echo $user['status'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
            <?php echo $user['status'] == 1 ? 'Active' : 'Inactive'; ?>
        </span>
    </td>
    <td>
        <?php if ($_SESSION['role'] == 0): ?>
            <?php if ($user['status'] == 1): ?>
                <button type="button" class="btn btn-danger" onclick="updateStatus(0, <?php echo $user['id']; ?>)">Deactivate</button>
            <?php else: ?>
                <button type="button" class="btn btn-success" onclick="updateStatus(1, <?php echo $user['id']; ?>)">Activate</button>
            <?php endif; ?>
            <button type="button" onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-danger">Delete</button>
            <button type="button" class="btn btn-primary" onclick="openModal(<?php echo $user['id']; ?>)">Assign Role</button>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($_SESSION['email'] == $user['email'] || $_SESSION['role'] == 0): ?>
            <a class="btn btn-info" href="./register.php?id=<?php echo $user['id']; ?>">Edit</a>
        <?php endif; ?>
    </td>
</tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Assign Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="user_id" value="" />
                    <select name="role" id="role" class="form-select">
                        <option value="">None</option>
                        <option value="1">Staff</option>
                        <option value="2">Customer</option>
                        <option value="3">Admin</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" onclick="update_role()" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery from CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        function updateStatus(status, user_id) {
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: { status: status, user_id: user_id },
                success: function() {
                    location.reload();
                }
            });
        }

        function update_role() {
            var role = $("#role").val();
            var user_id = $("#user_id").val();
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: { role: role, user_id: user_id },
                success: function(response) {
                    alert('Role added successfully.');
                    $('#myModal').modal('hide');
                    location.reload();
                }
            });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                $.ajax({
                    url: 'ajax.php',
                    type: 'POST',
                    data: { id: userId },
                    success: function(response) {
                        $('#user_' + userId).remove();
                        alert('User deleted successfully.');
                    }
                });
            }
        }

        function openModal(id) {
            $("#user_id").val(id);
            $('#myModal').modal('show');
        }
    </script>
</body>

</html>
