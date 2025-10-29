<?php
require 'config.php';
if (empty($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['add_record'])) {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $supplier = $_POST['supplier'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($name == '' or $category == '' or $price == '' or $stock == '' or $supplier == '' or !is_numeric($price) or !is_numeric($stock)) {
        header("Location: studentmanage.php?status=invalid_input");
        exit();
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, supplier, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $stock, $supplier, $description]);
        header("Location: index.php?status=added");
        exit;
    } catch (PDOException $e) {
        die("Failed to add record: " . $e->getMessage());
    }
}

if (isset($_POST['delete_record'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_POST['delete_record']]);
        header("Location: index.php?status=deleted");
        exit;
    } catch (PDOException $e) {
        die("Failed to delete record: " . $e->getMessage());
    }
}

if (isset($_POST['edit_record'])) {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $supplier = $_POST['supplier'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($name == '' or $category == '' or $price == '' or $stock == '' or $supplier == '' or !is_numeric($price) or !is_numeric($stock)) {
        header("Location: index.php?status=invalid_input");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock = ?, supplier = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $category, $price, $stock, $supplier, $description, $_POST['edit_record']]);
        header("Location: index.php?status=updated");
        exit;
    } catch (PDOException $e) {
        die("Failed to update record: " . $e->getMessage());
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Failed to pull records from the database");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product List</title>
</head>
<style>
html {font-family: Arial, Helvetica, sans-serif;}
.table-section {
    display:flex;
    justify-content:center;
}
.table-section table {
    width: 80%;
}
.addForm-section {
    display:flex;
    justify-content:center;
    /* border: 1px solid black; */
    margin-bottom: 2em;
    
}
.addForm {
    border: 1px solid black;
    border-radius: 10px;
    padding: 1em;
    width: 30%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.addForm label {
    display: inline-block;
    font-weight: bold;
    /* align-self: flex-start; */
    margin-bottom: 0.3em;
}
.addForm input, .addForm select {
    box-sizing: border-box;
    margin-bottom: 1.5em;
    width: 100%;
    padding: 0.5em;
}
.addForm button[type="submit"] {
    width: 100%;
    height: 2.5em;
    font-weight: bold;
    border-radius: 5px;
    border: 1px solid black;
    cursor: pointer;
}

.logoutBox {
    display: inline-block;
    position: absolute;
    z-index: 2;
    pointer-events: auto;
    margin-left: 50px;
}
.logoutBox button[type="submit"] {
    font-size: 1.5rem;
}

</style>
<body>
<div class="logoutBox">
    <form action="logout.php">
        <button type="submit">Log Out</button>
    </form>
</div>
<center><h1>Product List</h1></center>
<div class="addForm-section">
    <form method="POST" class="addForm">
        <input type="hidden" name="add_record">
        <label>Product Name</label><br><input type="text" name="name"required><br>
        <label>Category</label><br>
        <select name="category" required>
            <option disabled selected value>Please choose a category</option>
            <option value="Electronics">Electronics</option>
            <option value="Fashion">Fashion</option>
            <option value="Outdoor">Outdoor</option>
        </select>
        <label>Price</label><br><input type="number" name="price" step=".01" required><br>
        <label>Stock</label><br><input type="number" name="stock" required><br>
        <label>Supplier</label><br><input type="text" name="supplier" required><br>
        <label>Description</label><br><input type="text" name="description"><br>
        <button type="submit">Add Product</button>
    </form>
</div>

<?php if (isset($_GET['status'])): ?>
    <p>
        <?php
            if ($_GET['status'] === 'added') echo "<p style='color: green; text-align: center; font-weight: bold; font-size: 1.5em;'>Record added successfully.</p>";
            if ($_GET['status'] === 'deleted') echo "<p style='color: green; text-align: center; font-weight: bold; font-size: 1.5em;'>Record deleted successfully.</p>";
            if ($_GET['status'] === 'updated') echo "<p style='color: green; text-align: center; font-weight: bold; font-size: 1.5em;'>Record updated successfully.</p>";
            if ($_GET['status'] === 'invalid_input') echo "<p style='color: red; text-align: center; font-weight: bold; font-size: 1.5em;'>Invalid input. Please enter all fields correctly.</p>";
        ?>
    </p>
<?php endif; ?>

<div class="table-section">
    <table border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Supplier</th>
            <th>Description</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        <?php if (empty($products)): ?>
            <tr>
                <td colspan="9" style="text-align:center;">No records found</td>
            </tr>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <?php if (isset($_GET['edit']) && $_GET['edit'] == $p['id']): ?>
                    <tr>
                        <form method="POST">
                            <td><?php echo $p['id']; ?></td>
                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>"></td>
                            <td>
                                <select name="category" required>
                                    <option value="Electronics" <?php if ($p['category'] === 'Electronics') echo 'selected'; ?>>Electronics</option>
                                    <option value="Fashion" <?php if ($p['category'] === 'Fashion') echo 'selected'; ?>>Fashion</option>
                                    <option value="Outdoor" <?php if ($p['category'] === 'Outdoor') echo 'selected'; ?>>Outdoor</option>
                                </select>
                            </td>
                            <td>₱<input type="number" name="price" step=".01" value="<?php echo $p['price']; ?>"></td>
                            <td><input type="number" name="stock" value="<?php echo $p['stock']; ?>"></td>
                            <td><input type="text" name="supplier" value="<?php echo htmlspecialchars($p['supplier']); ?>"></td>
                            <td><input type="text" name="description" value="<?php echo htmlspecialchars($p['description']); ?>"></td>
                            <td><?php echo $p['created_at']; ?></td>
                            <td>
                                <input type="hidden" name="edit_record" value="<?php echo $p['id']; ?>">
                                <button type="submit">Save</button>
                                <a href="index.php">Cancel</a>
                            </td>
                        </form>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['category']); ?></td>
                        <td>₱<?php echo number_format($p['price'], 2); ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td><?php echo htmlspecialchars($p['supplier']); ?></td>
                        <td><?php echo htmlspecialchars($p['description']); ?></td>
                        <td><?php echo $p['created_at']; ?></td>
                        <td>
                            <form action="index.php?edit=<?php echo $p['id']; ?>" method="GET" style="display:inline;">
                                <input type="hidden" name="edit" value="<?php echo $p['id']; ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_record" value="<?php echo $p['id']; ?>">
                                <button type="submit" onclick="return confirm('Delete this record?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
