<?php
session_start();
require_once '../config/database.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Filtering
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($search) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'name':
        $query .= " ORDER BY name ASC";
        break;
    default:
        $query .= " ORDER BY created_at DESC";
}

// Get total count for pagination
$count_query = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
$stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total = mysqli_fetch_assoc($result)['total'];
$total_pages = ceil($total / $per_page);

// Add pagination to query
$query .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

// Get products
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['category'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - E-Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .filters {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    select,
    input[type="text"] {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 2rem;
    }

    .pagination a {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-decoration: none;
        color: #333;
    }

    .pagination a.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="products-header">
            <h1>Our Products</h1>
            <div class="filters">
                <div class="filter-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category">
                        <option value="">All</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"
                            <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to
                            High</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High
                            to Low</option>
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                    </select>
                </div>
            </div>
        </div>

        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search products..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn">Search</button>
        </form>

        <div class="product-grid">
            <?php while ($product = mysqli_fetch_assoc($products)): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                <button class="btn add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>"
                class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    // Update URL when filters change
    document.getElementById('category').addEventListener('change', function() {
        updateUrl();
    });

    document.getElementById('sort').addEventListener('change', function() {
        updateUrl();
    });

    function updateUrl() {
        const category = document.getElementById('category').value;
        const sort = document.getElementById('sort').value;
        const search = new URLSearchParams(window.location.search).get('search') || '';
        window.location.href =
            `?category=${encodeURIComponent(category)}&search=${encodeURIComponent(search)}&sort=${sort}`;
    }
    </script>
</body>

</html>