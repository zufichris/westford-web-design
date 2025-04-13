<?php
require_once __DIR__ . '/../config/database.php';

// Categories for products
$categories = [
    'Electronics',
    'Clothing',
    'Home & Kitchen',
    'Books',
    'Sports',
    'Beauty',
    'Toys',
    'Automotive',
    'Garden',
    'Office'
];

// Product name components
$productNames = [
    'Premium', 'Deluxe', 'Professional', 'Ultimate', 'Basic', 'Standard', 'Advanced',
    'Smart', 'Wireless', 'Portable', 'Compact', 'Lightweight', 'Heavy Duty',
    'Eco-Friendly', 'Sustainable', 'Organic', 'Natural', 'Classic', 'Modern'
];

$productTypes = [
    'Laptop', 'Smartphone', 'Headphones', 'Watch', 'Camera', 'Speaker', 'Tablet',
    'Shirt', 'Jeans', 'Dress', 'Shoes', 'Jacket', 'Sweater', 'Hat',
    'Blender', 'Coffee Maker', 'Toaster', 'Microwave', 'Cookware', 'Utensils',
    'Novel', 'Textbook', 'Magazine', 'Notebook', 'Pen', 'Desk', 'Chair',
    'Basketball', 'Football', 'Tennis Racket', 'Yoga Mat', 'Dumbbells', 'Bicycle',
    'Shampoo', 'Conditioner', 'Lotion', 'Perfume', 'Makeup', 'Skincare',
    'Board Game', 'Puzzle', 'Action Figure', 'Doll', 'Building Blocks', 'Remote Control Car',
    'Tire', 'Battery', 'Oil', 'Filter', 'Brake Pads', 'Car Wash Kit',
    'Plant', 'Fertilizer', 'Garden Tools', 'Outdoor Furniture', 'Grill', 'Sprinkler',
    'Printer', 'Scanner', 'Paper', 'Ink', 'Stapler', 'File Cabinet'
];

// Generate and insert 50 random products
for ($i = 0; $i < 50; $i++) {
    // Generate random product name
    $name = $productNames[array_rand($productNames)] . ' ' . $productTypes[array_rand($productTypes)];
    
    // Generate random price between 10 and 1000
    $price = mt_rand(1000, 100000) / 100;
    
    // Generate random stock quantity
    $stock = mt_rand(0, 100);
    
    // Select random category
    $category = $categories[array_rand($categories)];
    
    // Generate random description
    $description = "This " . strtolower($name) . " is a high-quality product that offers excellent value for money. ";
    $description .= "It features premium materials and is designed for durability and performance. ";
    $description .= "Perfect for everyday use or special occasions.";
    
    // Generate random image URL (using placeholder images)
    $image_url = "https://picsum.photos/400/400?random=" . $i;
    
    // Prepare and execute the insert query
    $query = "INSERT INTO products (name, description, price, image_url, stock_quantity, category) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssdsis", $name, $description, $price, $image_url, $stock, $category);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Added product: $name\n";
    } else {
        echo "Error adding product: " . mysqli_error($conn) . "\n";
    }
}

echo "Finished adding 50 random products!\n";
?>