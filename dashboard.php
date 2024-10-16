<?php
session_start();
include 'config.php';
include 'ajax.php';

// Check if the user is logged in; if not, redirect them to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Pagination Variables
$limit = 10; // Users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search Filter Variables
$roleFilter = isset($_POST['role']) ? $_POST['role'] : '';
$statusFilter = isset($_POST['status']) ? $_POST['status'] : '';

// Build Query with Filters
$query = "SELECT * FROM users WHERE is_delete = 0";
$params = [];
$types = '';
if ($roleFilter != '') {
  $query .= " AND role = ?";
  $types .= 'i'; // 'role' should be an integer (assuming)
  $params[] = $roleFilter;
}
if ($statusFilter != '') {
  $query .= " AND status = ?";
  $types .= 'i'; // 'status' should be an integer
  $params[] = $statusFilter;
}

$query .= " LIMIT ?, ?";
$types .= 'ii'; // for $offset and $limit
$params[] = $offset;
$params[] = $limit;

$stmt = $conn->prepare($query);

// Bind parameters dynamically
$stmt->bind_param($types, ...$params);

$stmt->execute();
$user_data = $stmt->get_result();

$totalQuery = "SELECT COUNT(*) as count FROM users WHERE is_delete = 0";
$totalResult = $conn->query($totalQuery);
$totalUsers = $totalResult->fetch_assoc()['count'];
$totalPages = ceil($totalUsers / $limit);

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
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-out {
            animation: fadeOut 0.5s ease-in;
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .d-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .gap-2 {
        gap: 0.5rem; /* Add spacing between buttons */
    }

    .btn {
        min-width: 100px; /* Ensure the buttons are the same size */
    }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-success">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#">Welcome, <?php echo htmlspecialchars($name_main['username']); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
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
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col">
                <select name="role" class="form-select">
                    <option value="">Select Role</option>
                    <option value="0" <?php echo ($roleFilter == '0') ? 'selected' : ''; ?>>Admin</option>
                    <option value="1" <?php echo ($roleFilter == '1') ? 'selected' : ''; ?>>Staff</option>
                    <option value="2" <?php echo ($roleFilter == '2') ? 'selected' : ''; ?>>Customer</option>
                </select>
            </div>
            <div class="col">
                <select name="status" class="form-select">
                    <option value="">Select Status</option>
                    <option value="1" <?php echo ($statusFilter == '1') ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($statusFilter == '0') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
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
            <tr id="user_<?php echo $user['id']; ?>" class="fade-in">
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
                    <?php 
                    if ($user['role'] == 1) { 
                        echo "Staff"; 
                    } elseif ($user['role'] == 2) { 
                        echo "Customer"; 
                    } elseif ($user['role'] == 0) { 
                        echo "Admin"; 
                    } 
                    ?>
                </td>
                <td>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($_SESSION['role'] == 0): ?>
            <?php if ($user['status'] == 1): ?>
                <button type="button" class="btn btn-danger btn-sm" onclick="updateStatus(0, <?php echo $user['id']; ?>)">Deactivate</button>
            <?php else: ?>
                <button type="button" class="btn btn-success btn-sm" onclick="updateStatus(1, <?php echo $user['id']; ?>)">Activate</button>
            <?php endif; ?>
            <button type="button" onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal(<?php echo $user['id']; ?>)">Assign Role</button>
        <?php endif; ?>
        <?php if ($_SESSION['email'] == $user['email'] || $_SESSION['role'] == 0): ?>
            <a class="btn btn-info btn-sm" href="./register.php?id=<?php echo $user['id']; ?>">Edit</a>
        <?php endif; ?>
    </div>
</td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $roleFilter; ?>&status=<?php echo $statusFilter; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
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
                    <option value="0">Admin</option>
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
                    $('#user_' + userId).addClass('fade-out').delay(500).queue(function() {
                        $(this).remove();
                        alert('User deleted successfully.');
                    });
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
