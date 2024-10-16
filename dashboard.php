<?php
session_start();
include 'config.php';
include 'ajax.php';

// Check if the user is logged in, if
// not then redirect them to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_data = $conn->query("SELECT * FROM users where is_delete = 0 ");
// print_r($_SESSION);
$query = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $query->bind_param("s", $_SESSION['email']);
    $query->execute();
    $result = $query->get_result(); 
    if ($result) {
        $name_main = $result->fetch_assoc(); 
        
    }
    // print_r($name_main);
// print_r("SELECT * FROM users where is_delete = 0 ");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport"
  content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>

<body>
    <nav class="navbar navbar-expand-sm navbar-light bg-success">
        <div class="container">
            <a class="navbar-brand" href="#" style="font-weight:bold; color:white;"><?php echo $name_main['username'] ; ?></a>
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavId">
                <ul class="navbar-nav m-auto mt-2 mt-lg-0">
                </ul>
                <form class="d-flex my-2 my-lg-0">
                    <a href="./logout.php" class="btn btn-light my-2 my-sm-0"
                      type="submit" style="font-weight:bolder;color:green;">
                        logout</a>
                </form>
            </div>
        </div>
    </nav>

    <div>
    </div>
    <div>
    <table class="table">
  <thead>
                 
    <tr>
      <th scope="col">#</th>
      <th scope="col">name</th>
      <th scope="col">email</th>
      <th scope="col">mobile no</th>
      <th scope="col">address</th>
      <th scope="col">status</th>
      <th scope="col">role</th>
      <th scope="col">actions</th>
    </tr>

    
  </thead>
  <tbody>
  <?php
  foreach($user_data as $user){ 
        ?>  
    <tr id="user_<?php echo $user['id']; ?>">
      <th scope="row"> <?php echo $user['id']; ?></th>
      <td> <?php echo $user['username']; ?></td>
      <td> <?php echo $user['email']; ?></td>
      <td> <?php echo $user['mobile_no']; ?></td>
      <td> <?php echo $user['address']; ?></td>
      <td><?php if($user['status'] == 1){ ?>
          Active
        <?php }else{ ?>
          Inactive
        <?php } 
        if($_SESSION['role']==0){?><br>
      <form>
      <input type="radio" name="status" value="1" onclick="updateStatus(this.value,<?php echo $user['id']; ?>)"> Active
      <input type="radio" name="status" value="0" onclick="updateStatus(this.value,<?php echo $user['id']; ?>)"> Inactive
     </form>
        <?php }?>
    </td>
      <?php if($user['role'] == 1){ ?>
          <td>Staff</td>
        <?php }else if($user['role'] == 2){ ?>
            <td>Customer</td>
        <?php }else{  ?>
            <td>Admin</td>
        <?php } 
        
      if($_SESSION['role']==0){?>
      <td><button type="button" onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-danger">Delete</button><br>
      <button type="button" class="btn btn-primary" onclick="openModal(<?php echo $user['id']; ?>)">Assign role</button></td>
      <?php }?>
      
      <?php if($_SESSION['email']==$user['email'] || $_SESSION['role']==0){?>
      <td><a type="button" class="btn btn-info" href="./register.php?id=<?php echo $user['id']; ?>">Edit</a></td>
      <?php }?>
    </tr>
    <?php } ?>
    
  </tbody>
</table>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Assign role</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <input type="hidden" name="user_id" id="user_id" value="" />
      <select name="role" id="role">
      <option value=""></option>
        <option value="1">staff</option>
        <option value="2">customer</option>
        <option value="3">admin</option>
        </select>     
     </div>
       
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" onclick="update_role()" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
</body>

</html>
<!-- Include jQuery from CDN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


<script>

function updateStatus(status,user_id) {
  $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: { status: status,
              user_id:user_id
             },
            success: function() {
              location.reload();
            }
        });
}


function update_role(){
  var role = $("#role").val();
  var user_id=$("#user_id").val();
  // alert(role);
  // alert(user_id);
  $.ajax({
            url: 'ajax.php', 
            type: 'POST',
            data: { role: role,
              user_id:user_id
             },
            success: function(response) {
              
              alert('role added successfully.');
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