<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Collect form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$pickup = $_POST['pickup'] ?? '';

$coffee = isset($_POST['coffee']) ? $_POST['coffee'] : [];
$tea = isset($_POST['tea']) ? $_POST['tea'] : [];
$pastry = isset($_POST['pastry']) ? $_POST['pastry'] : [];
$pancake = isset($_POST['pancake']) ? $_POST['pancake'] : [];
$waffle = isset($_POST['waffle']) ? $_POST['waffle'] : [];
$toast = isset($_POST['toast']) ? $_POST['toast'] : [];

$notes = $_POST['Message'] ?? '';
$quantity = (int)($_POST['Quantity'] ?? 1);
$delivery = $_POST['delivery'] ?? 'pickup';
$address = $_POST['address'] ?? '';
$payment = $_POST['payment'] ?? 'cash';
$order_time = date('Y-m-d H:i:s');

// PRICE LIST Matching my menu exactly
$prices = [
    // Coffee
    'Espresso' => 11000,
    'Cappuccino' => 14000,
    'Latte' => 11500,
    'Mocha' => 15000,
    'Americano' => 12500,
    'Flat White' => 13500,
    
    // Tea
    'Green Tea' => 7000,
    'Chai Latte' => 8000,
    'Black Tea' => 6000,
    'Herbal Tea' => 9000,
    'Hot Chocolate' => 9000,
    
    // Pastries
    'Croissant' => 4500,
    'Bread' => 4500,
    'Chocolate Eclaire' => 5000,
    'Muffin' => 5000,
    'Cookie' => 3500,
    'Cake Slice' => 8000,
    
    // Pancakes (all same price)
    'Classic' => 12000,
    'Chocolate' => 12000,
    'Banana' => 12000,
    'Oatmeal' => 12000,
    'Buttermilk' => 12000,
    
    // Waffles (all same price)
    'Classic' => 10000,
    'Belgian' => 10000,
    'Chocolate' => 10000,
    'Blueberry' => 10000,
    
    // Toast
    'Cinnamon' => 11500,
    'Strawberry' => 11500,
    'Cheese' => 11500,
    'Veggie' => 11500
];

// Calculate total
$items_ordered = [];
$subtotal = 0;

// Process Coffee items
foreach ($coffee as $item) {
    $price = $prices[$item] ?? 0;
    $items_ordered[] = ['name' => $item, 'price' => $price, 'category' => 'Coffee'];
    $subtotal += $price;
}

// Process Tea items
foreach ($tea as $item) {
    $price = $prices[$item] ?? 0;
    $items_ordered[] = ['name' => $item, 'price' => $price, 'category' => 'Tea'];
    $subtotal += $price;
}

// Process Pastry items
foreach ($pastry as $item) {
    $price = $prices[$item] ?? 0;
    $items_ordered[] = ['name' => $item, 'price' => $price, 'category' => 'Pastry'];
    $subtotal += $price;
}

// Process Pancake items
foreach ($pancake as $item) {
    $price = $prices[$item] ?? 0;
    $items_ordered[] = ['name' => $item . ' Pancake', 'price' => $price, 'category' => 'Pancake'];
    $subtotal += $price;
}

// Process Waffle items
foreach ($waffle as $item) {
    $price = $prices[$item] ?? 0;
    $items_ordered[] = ['name' => $item . ' Waffles', 'price' => $price, 'category' => 'Waffle'];
    $subtotal += $price;
}

// Process Toast items
foreach ($toast as $item) {
    $price = $prices[$item] ?? 0;
    $items_ordered[] = ['name' => $item . ' Toast', 'price' => $price, 'category' => 'Toast'];
    $subtotal += $price;
}

// Validate: at least one item must be selected
if (empty($items_ordered)) {
    die("❌ Please select at least one item to order!");
}

// Calculate totals
$subtotal = $subtotal * $quantity;
$delivery_fee = ($delivery === 'delivery') ? 3000 : 0;
$tax = $subtotal * 0.18; // 18% VAT
$total = $subtotal + $delivery_fee + $tax;

// Generate unique order ID
$order_id = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);

// Connect to DB
$conn = new mysqli('localhost', 'root', '', 'coffee_shop');
if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Prepare items for database
$coffee_str = implode(", ", $coffee);
$tea_str = implode(", ", $tea);
$pastry_str = implode(", ", $pastry);
$pancake_str = implode(", ", $pancake);
$waffle_str = implode(", ", $waffle);
$toast_str = implode(", ", $toast);

// Insert into orders table
$sql = "INSERT INTO orders 
(name, email, phone, pickup_time, coffee, tea, pastry, pancake, waffle, toast, notes, quantity, delivery_method, address, payment_method, order_time)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "sssssssssssissss",
    $name, $email, $phone, $pickup,
    $coffee_str, $tea_str, $pastry_str, $pancake_str, $waffle_str, $toast_str,
    $notes, $quantity, $delivery, $address, $payment, $order_time
);

if ($stmt->execute()) {
    $order_db_id = $stmt->insert_id;
    
    // Store order details in session for receipt
    $_SESSION['last_order'] = [
        'order_id' => $order_id,
        'db_id' => $order_db_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'pickup_time' => $pickup,
        'items' => $items_ordered,
        'quantity' => $quantity,
        'subtotal' => $subtotal,
        'delivery_fee' => $delivery_fee,
        'tax' => $tax,
        'total' => $total,
        'delivery' => $delivery,
        'address' => $address,
        'payment' => $payment,
        'order_time' => $order_time,
        'notes' => $notes
    ];
    
    // Redirect to HTML receipt page (which fetches data via JavaScript)
    header("Location: payment.html?total=$total&subtotal=$subtotal&delivery=$delivery_fee&vat=$tax");
    exit();
    
} else {
    echo "❌ Error inserting order: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>