<?php
// Include the database connection
include 'db_connection.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];

    // Handle file upload
    $targetDir = "uploads/";
    $imageName = basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $imageName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        // Insert data into the database
        $stmt = $conn->prepare("INSERT INTO add_ons (name, image_path, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $targetFile, $price);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Add-on added successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color: red;'>Image upload failed.</p>";
    }
}
?>

<!-- HTML FORM -->
<h2>Add New Add-on</h2>
<form action="add_add_ons.php" method="POST" enctype="multipart/form-data">
    <label for="name">Add-on Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label for="price">Price (₱):</label><br>
    <input type="number" step="0.01" name="price" required><br><br>

    <label for="image">Image:</label><br>
    <input type="file" name="image" accept="image/*" required><br><br>

    <button type="submit">Add Add-on</button>
</form>
