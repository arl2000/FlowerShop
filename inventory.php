<?php
include 'navbar.php';
include 'db_connection.php';

// Function to get real-time inventory data
function getInventoryData($conn) {
    $inventory_query = "SELECT 
        p.product_id, 
        p.product_name, 
        p.stock_count,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.product_name";
    
    $result = mysqli_query($conn, $inventory_query);
    $inventory_data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $inventory_data[] = $row;
        }
    }
    
    return $inventory_data;
}

// Get initial inventory data
$inventory_data = getInventoryData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real-Time Inventory - Heavenly Bloom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff6f2;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            color: #d15e97;
            margin-top: 30px;
        }

        .inventory-section {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .inventory-title {
            color: #d15e97;
            font-size: 18px;
            font-weight: bold;
        }

        .refresh-btn {
            background-color: #d15e97;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .refresh-btn i {
            margin-right: 5px;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table th, .inventory-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #fcdde6;
        }

        .inventory-table th {
            background-color: #ffe8ec;
            color: #d15e97;
        }

        .inventory-table tr:hover {
            background-color: #fff6f2;
        }

        .stock-low {
            color: #ff6b6b;
            font-weight: bold;
        }

        .stock-medium {
            color: #ffa94d;
        }

        .stock-good {
            color: #51cf66;
        }

        .export-btn {
            background-color: #4dabf7;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            margin-left: 10px;
        }

        .export-btn i {
            margin-right: 5px;
        }

        .action-buttons {
            display: flex;
            align-items: center;
        }

        .filter-section {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-item {
            display: flex;
            align-items: center;
        }

        .filter-item label {
            margin-right: 8px;
            color: #555;
        }

        .filter-item select, .filter-item input {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #fcdde6;
        }

        .search-box {
            flex-grow: 1;
            max-width: 300px;
        }

        .search-box input {
            width: 100%;
        }

        @media (max-width: 600px) {
            .inventory-table {
                display: block;
                overflow-x: auto;
            }

            .filter-section {
                flex-direction: column;
            }

            .search-box {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<h2><i class="fas fa-boxes"></i> Real-Time Inventory Management</h2>

<div class="inventory-section">
    <div class="inventory-header">
        <div class="inventory-title">
            <i class="fas fa-boxes"></i> Product Inventory
        </div>
        <div class="action-buttons">
            <button class="refresh-btn" id="refreshInventory">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="export-btn" id="exportInventory">
                <i class="fas fa-file-export"></i> Export
            </button>
        </div>
    </div>

    <div class="filter-section">
        <div class="filter-item">
            <label for="categoryFilter">Category:</label>
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <?php
                // Get unique categories
                $categories_query = "SELECT DISTINCT c.name FROM categories c 
                                   JOIN products p ON c.id = p.category_id 
                                   ORDER BY c.name";
                $categories_result = mysqli_query($conn, $categories_query);
                while ($category = mysqli_fetch_assoc($categories_result)) {
                    echo "<option value='" . htmlspecialchars($category['name']) . "'>" . 
                         htmlspecialchars($category['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="filter-item">
            <label for="stockFilter">Stock Level:</label>
            <select id="stockFilter">
                <option value="">All Levels</option>
                <option value="low">Low Stock (≤5)</option>
                <option value="medium">Medium Stock (6-10)</option>
                <option value="good">Good Stock (>10)</option>
            </select>
        </div>
        <div class="filter-item search-box">
            <input type="text" id="searchInventory" placeholder="Search products...">
        </div>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Stock Count</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="inventoryTableBody">
            <?php foreach ($inventory_data as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_id']) ?></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                    <td class="<?= $item['stock_count'] <= 5 ? 'stock-low' : ($item['stock_count'] <= 10 ? 'stock-medium' : 'stock-good') ?>">
                        <?= htmlspecialchars($item['stock_count']) ?>
                    </td>
                    <td>
                        <?php if ($item['stock_count'] <= 5): ?>
                            <span class="stock-low">Low Stock</span>
                        <?php elseif ($item['stock_count'] <= 10): ?>
                            <span class="stock-medium">Medium Stock</span>
                        <?php else: ?>
                            <span class="stock-good">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= $item['product_id'] ?>" title="Edit Product">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- JavaScript for real-time inventory updates -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to refresh inventory data
        function refreshInventory() {
            fetch('get_inventory.php')
                .then(response => response.json())
                .then(data => {
                    updateInventoryTable(data);
                })
                .catch(error => console.error('Error fetching inventory data:', error));
        }
        
        // Function to update the inventory table with filtered data
        function updateInventoryTable(data) {
            const tableBody = document.getElementById('inventoryTableBody');
            const categoryFilter = document.getElementById('categoryFilter').value;
            const stockFilter = document.getElementById('stockFilter').value;
            const searchTerm = document.getElementById('searchInventory').value.toLowerCase();
            
            // Filter data based on selected filters
            let filteredData = data.filter(item => {
                // Category filter
                if (categoryFilter && item.category_name !== categoryFilter) {
                    return false;
                }
                
                // Stock level filter
                if (stockFilter) {
                    const stockCount = parseInt(item.stock_count);
                    if (stockFilter === 'low' && stockCount > 5) return false;
                    if (stockFilter === 'medium' && (stockCount <= 5 || stockCount > 10)) return false;
                    if (stockFilter === 'good' && stockCount <= 10) return false;
                }
                
                // Search term filter
                if (searchTerm) {
                    const productName = item.product_name.toLowerCase();
                    const categoryName = (item.category_name || '').toLowerCase();
                    if (!productName.includes(searchTerm) && !categoryName.includes(searchTerm)) {
                        return false;
                    }
                }
                
                return true;
            });
            
            // Clear table
            tableBody.innerHTML = '';
            
            // Add filtered rows
            filteredData.forEach(item => {
                let stockClass = 'stock-good';
                let stockStatus = 'In Stock';
                
                if (item.stock_count <= 5) {
                    stockClass = 'stock-low';
                    stockStatus = 'Low Stock';
                } else if (item.stock_count <= 10) {
                    stockClass = 'stock-medium';
                    stockStatus = 'Medium Stock';
                }
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.product_id}</td>
                    <td>${item.product_name}</td>
                    <td>${item.category_name || 'Uncategorized'}</td>
                    <td class="${stockClass}">${item.stock_count}</td>
                    <td><span class="${stockClass}">${stockStatus}</span></td>
                    <td>
                        <a href="edit_product.php?id=${item.product_id}" title="Edit Product">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }
        
        // Refresh inventory when button is clicked
        document.getElementById('refreshInventory').addEventListener('click', refreshInventory);
        
        // Apply filters when changed
        document.getElementById('categoryFilter').addEventListener('change', function() {
            fetch('get_inventory.php')
                .then(response => response.json())
                .then(data => updateInventoryTable(data))
                .catch(error => console.error('Error fetching inventory data:', error));
        });
        
        document.getElementById('stockFilter').addEventListener('change', function() {
            fetch('get_inventory.php')
                .then(response => response.json())
                .then(data => updateInventoryTable(data))
                .catch(error => console.error('Error fetching inventory data:', error));
        });
        
        // Search as user types
        document.getElementById('searchInventory').addEventListener('input', function() {
            fetch('get_inventory.php')
                .then(response => response.json())
                .then(data => updateInventoryTable(data))
                .catch(error => console.error('Error fetching inventory data:', error));
        });
        
        // Export inventory data
        document.getElementById('exportInventory').addEventListener('click', function() {
            fetch('get_inventory.php')
                .then(response => response.json())
                .then(data => {
                    // Create CSV content
                    let csvContent = "data:text/csv;charset=utf-8,";
                    csvContent += "Product ID,Product Name,Category,Stock Count,Status\n";
                    
                    data.forEach(item => {
                        let stockStatus = 'In Stock';
                        if (item.stock_count <= 5) {
                            stockStatus = 'Low Stock';
                        } else if (item.stock_count <= 10) {
                            stockStatus = 'Medium Stock';
                        }
                        
                        const row = [
                            item.product_id,
                            item.product_name,
                            item.category_name || 'Uncategorized',
                            item.stock_count,
                            stockStatus
                        ];
                        
                        csvContent += row.join(",") + "\n";
                    });
                    
                    // Create download link
                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", "inventory_" + new Date().toISOString().slice(0,10) + ".csv");
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                })
                .catch(error => console.error('Error exporting inventory data:', error));
        });
        
        // Auto-refresh inventory every 30 seconds
        setInterval(refreshInventory, 30000);
    });
</script>

</body>
</html> 