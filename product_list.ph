<?php
// Example code for displaying products with delete link
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $row['product_name'] . '</td>';
    echo '<td>' . $row['product_price'] . '</td>';
    echo '<td>';
    // Delete link with confirmation prompt
    echo '<a href="delete_product.php?id=' . $row['product_id'] . '" onclick="return confirm(\'Are you sure you want to delete this product?\');" title="Delete Product"><i class="fas fa-trash"></i></a>';
    echo '</td>';
    echo '</tr>';
}
?>
