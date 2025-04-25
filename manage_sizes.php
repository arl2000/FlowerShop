<h2>Existing Bouquet Sizes</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price (₱)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($size = mysqli_fetch_assoc($sizes_result)): ?>
            <tr>
                <td><?= $size['id'] ?></td>
                <td><?= htmlspecialchars($size['name']) ?></td>
                <td><?= number_format($size['price'], 2) ?></td>
                <td>
                    <a href="edit_size.php?id=<?= $size['id'] ?>">Edit</a>
                    <a href="delete_size.php?id=<?= $size['id'] ?>" onclick="return confirm('Delete this size?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>