<?php
session_start();

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'user_db');

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch users grouped by user_type
$query = "SELECT * FROM user_form ORDER BY user_type ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Handle delete request
if (isset($_POST['delete_user'])) {
    $id = $_POST['delete_id'];
    $delete_query = "DELETE FROM user_form WHERE id = '$id'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "User deleted successfully!";
        header("Location: adminDashboard.php");
    } else {
        $_SESSION['message'] = "Error deleting user: " . mysqli_error($conn);
    }
}

// Handle update request
if (isset($_POST['update_user'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $email = $_POST['edit_email'];
    $password = $_POST['edit_password'];
    $user_type = $_POST['edit_user_type'];

    $update_query = "UPDATE user_form SET name = '$name', email = '$email', password = '$password', user_type = '$user_type' WHERE id = '$id'";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "User updated successfully!";
        header("Location: adminDashboard.php");
    } else {
        $_SESSION['message'] = "Error updating user: " . mysqli_error($conn);
    }
}
$car_query = "SELECT * FROM cars";
$house_query = "SELECT * FROM house";
$laptop_query = "SELECT * FROM laptop";

$car_result = mysqli_query($conn, $car_query);
$house_result = mysqli_query($conn, $house_query);
$laptop_result = mysqli_query($conn, $laptop_query);

// Handle delete product request
if (isset($_POST['delete_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_product_id']);
    $table = $_POST['delete_product_table'];
    $allowed_tables = ['cars', 'house', 'laptop'];
    if (!in_array($table, $allowed_tables)) {
        echo "Invalid table specified.";
    } else {
        $delete_query = "DELETE FROM `$table` WHERE `id` = '$id'";
        if (mysqli_query($conn, $delete_query)) {
            header("Location: adminDashboard.php");
            exit;
        } else {
            echo "Error deleting product: " . mysqli_error($conn);
        }
    }
}

// Handle update product request
if (isset($_POST['update_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['edit_product_id']);
    $table = mysqli_real_escape_string($conn, $_POST['edit_product_table']);
    $name = mysqli_real_escape_string($conn, $_POST['edit_product_name']);
    $price = mysqli_real_escape_string($conn, $_POST['edit_product_price']);
    $category = mysqli_real_escape_string($conn, $_POST['edit_product_category']);

    // Map table to correct name column
    $name_col = 'name';
    if ($table === 'cars') {
        $name_col = 'car_name';
    } elseif ($table === 'house') {
        $name_col = 'house_name';
    } elseif ($table === 'laptop') {
        $name_col = 'laptop_name';
    }

    $update_query = "UPDATE `$table` SET `$name_col` = '$name', `price` = '$price', `category` = '$category' WHERE `id` = '$id'";
    if (mysqli_query($conn, $update_query)) {
        header("Location: adminDashboard.php");
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/adminDashboard.css">
    <style>
       
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <a href="loginReg.php">Logout</a>
</header>

<div class="container">
    <h2>Users Management</h2>

    <!-- Display Flash Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Owner Table -->
    <h3>Owner Table</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Actions</th>
        </tr>
        <?php
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['user_type'] === 'owner') { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                    <td>
                        <button class="btn-edit" onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                        <button class="btn-delete" onclick="deleteUser(<?php echo $row['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php }
        }
        ?>
    </table>

    <!-- Renter Table -->
    <h3>Renter Table</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Actions</th>
        </tr>
        <?php
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['user_type'] === 'renter') { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                    <td>
                        <button class="btn-edit" onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                        <button class="btn-delete" onclick="deleteUser(<?php echo $row['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php }
        }
        ?>
    </table>
</div>

<!-- Edit Form -->
<div id="editForm" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeForm('editForm')">&times;</span>
        <h3>Edit User</h3>
        <form method="POST" action="">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-group">
                <label for="edit_name">Name</label>
                <input type="text" name="edit_name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Email</label>
                <input type="email" name="edit_email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label for="edit_password">Password</label>
                <input type="text" name="edit_password" id="edit_password" required>
            </div>
            <div class="form-group">
                <label for="edit_user_type">User Type</label>
                <select name="edit_user_type" id="edit_user_type" required>
                    <option value="owner">Owner</option>
                    <option value="renter">Renter</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" name="update_user" class="btn-update">Update</button>
                <button type="button" class="btn-cancel" onclick="closeForm('editForm')">Cancel</button>
            </div>
        </form>
    </div>
</div>


<!-- Delete Form -->
<div id="deleteForm" style="display: none;">
    <form method="POST" action="">
        <input type="hidden" name="delete_id" id="delete_id">
        <input type="hidden" name="delete_user" value="1">
    </form>
</div>


<div class="container2">
    <h2>Products Management</h2><br>

    <!-- Cars Table -->
    <h3>Cars</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Category</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php while ($car = mysqli_fetch_assoc($car_result)) { ?>
            <tr>
                <td><?php echo $car['id']; ?></td>
                <td><?php echo htmlspecialchars($car['car_name']); ?></td>
                <td><?php echo htmlspecialchars($car['price']); ?></td>
                <td><?php echo htmlspecialchars($car['category']); ?></td>
                <td><img src="../images/<?php echo $car['image']; ?>" alt="Car Image" class="product-image" ></td>

                <td>
                    <button class="btn-edit" onclick="editProduct(<?php echo $car['id']; ?>, 'cars', '<?php echo htmlspecialchars($car['car_name']); ?>', '<?php echo $car['price']; ?>', '<?php echo $car['category']; ?>')">Edit</button>
                    <button class="btn-delete" onclick="deleteProduct(<?php echo $car['id']; ?>, 'cars')">Delete</button>
                </td>
            </tr>
        <?php } ?>
    </table>
    
    <!-- Houses Table -->
    <h3>Houses</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Category</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php while ($house = mysqli_fetch_assoc($house_result)) { ?>
            <tr>
                <td><?php echo $house['id']; ?></td>
                <td><?php echo htmlspecialchars($house['house_name']); ?></td>
                <td><?php echo htmlspecialchars($house['price']); ?></td>
                <td><?php echo htmlspecialchars($house['category']); ?></td>
                <td><img src="../images/<?php echo $house['image']; ?>" alt="House Image" class="product-image" ></td>
                <td>
                    <button class="btn-edit" onclick="editProduct(<?php echo $house['id']; ?>, 'house', '<?php echo htmlspecialchars($house['house_name']); ?>', '<?php echo $house['price']; ?>', '<?php echo $house['category']; ?>')">Edit</button>
                    <button class="btn-delete" onclick="deleteProduct(<?php echo $house['id']; ?>, 'house')">Delete</button>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Laptops Table -->
    <h3>Laptops</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Category</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php while ($laptop = mysqli_fetch_assoc($laptop_result)) { ?>
            <tr>
                <td><?php echo $laptop['id']; ?></td>
                <td><?php echo htmlspecialchars($laptop['laptop_name']); ?></td>
                <td><?php echo htmlspecialchars($laptop['price']); ?></td>
                <td><?php echo htmlspecialchars($laptop['category']); ?></td>
                <td><img src="../images/<?php echo $laptop['image']; ?>" alt="Laptop Image" class="product-image" ></td>
                <td>
                    <button class="btn-edit" onclick="editProduct(<?php echo $laptop['id']; ?>, 'laptop', '<?php echo htmlspecialchars($laptop['laptop_name']); ?>', '<?php echo $laptop['price']; ?>', '<?php echo $laptop['category']; ?>')">Edit</button>
                    <button class="btn-delete" onclick="deleteProduct(<?php echo $laptop['id']; ?>, 'laptop')">Delete</button>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<!-- Edit Product Form -->
<div id="editProductForm">
    <h3>Edit Product</h3>
    <form method="POST" action="">
        <input type="hidden" name="edit_product_id" id="edit_product_id">
        <input type="hidden" name="edit_product_table" id="edit_product_table">
        <div class="form-group">
            <label for="edit_product_name">Name</label>
            <input type="text" name="edit_product_name" id="edit_product_name" required>
        </div>
        <div class="form-group">
            <label for="edit_product_price">Price</label>
            <input type="text" name="edit_product_price" id="edit_product_price" required>
        </div>
        <div class="form-group">
            <label for="edit_product_category">Category</label>
            <input type="text" name="edit_product_category" id="edit_product_category" required>
        </div>
        <div class="form-actions">
            <button type="submit" name="update_product" class="btn-update">Update</button>
            <button type="button" class="btn-cancel" onclick="closeForm()">Cancel</button>
        </div>
    </form>
</div>

<!-- Hidden form for product delete actions -->
<form id="productDeleteForm" method="POST" style="display:none;"></form>

<script>
    function editUser(user) {
    const editForm = document.getElementById('editForm');
    editForm.style.display = 'block'; // Show modal
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_password').value = user.password;
    document.getElementById('edit_user_type').value = user.user_type;
}
    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').querySelector('form').submit();
        }
    }

    function closeForm(formId) {
        document.getElementById(formId).style.display = 'none';
    }
    // Close the modal when clicking outside of it
window.onclick = function (event) {
    const modal = document.getElementById('editForm');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

function editProduct(id, table, name, price, category) {
    document.getElementById('editProductForm').style.display = 'block';
    document.getElementById('edit_product_id').value = id;
    document.getElementById('edit_product_table').value = table;
    document.getElementById('edit_product_name').value = name;
    document.getElementById('edit_product_price').value = price;
    document.getElementById('edit_product_category').value = category;
}

function deleteProduct(id, table) {
    if (confirm('Are you sure you want to delete this product?')) {
        const form = document.getElementById('productDeleteForm');
        form.innerHTML = '<input type="hidden" name="delete_product" value="1">' +
                         '<input type="hidden" name="delete_product_id" value="' + id + '">' +
                         '<input type="hidden" name="delete_product_table" value="' + table + '">';
        form.submit();
    }
}
</script>

</body>
</html>