<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

// Check if we have session data first (just placed order)
if (isset($_SESSION['last_order'])) {
    echo json_encode([
        'success' => true, 
        'data' => $_SESSION['last_order']
    ]);
    exit();
}

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'coffee_shop');
if ($conn->connect_error) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed'
    ]);
    exit();
}
$conn->set_charset('utf8mb4');

// Try to get order by order_id passed in URL (e.g. ORD-20250101-1234)
$order_id_param = $_GET['order_id'] ?? '';
if (!empty($order_id_param)) {
    // Extract the numeric DB id from the end of the order ID string
    $parts = explode('-', $order_id_param);
    $db_id = intval(end($parts));
    $sql = "SELECT * FROM orders WHERE id = $db_id LIMIT 1";
} else {
    // Fall back to most recent order
    $sql = "SELECT * FROM orders ORDER BY id DESC LIMIT 1";
}

$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'No orders found. Please place an order first.'
    ]);
    $conn->close();
    exit();
}

$order = $result->fetch_assoc();

// Price list
$prices = [
    'Espresso' => 11000, 'Cappuccino' => 14000, 'Latte' => 11500,
    'Mocha' => 15000, 'Americano' => 12500, 'Flat White' => 13500,
    'Green Tea' => 7000, 'Chai Latte' => 8000, 'Black Tea' => 6000,
    'Herbal Tea' => 9000, 'Hot Chocolate' => 9000,
    'Croissant' => 4500, 'Bread' => 4500, 'Chocolate Eclaire' => 5000,
    'Muffin' => 5000, 'Cookie' => 3500, 'Cake Slice' => 8000,
    'Classic' => 12000, 'Chocolate' => 12000, 'Banana' => 12000,
    'Oatmeal' => 12000, 'Buttermilk' => 12000, 'Belgian' => 10000,
    'Blueberry' => 10000, 'Cinnamon' => 11500, 'Strawberry' => 11500,
    'Cheese' => 11500, 'Veggie' => 11500
];

// Build items array
$items_ordered = [];
$subtotal = 0;

// Process Coffee
if (!empty($order['coffee'])) {
    $coffeeItems = array_filter(array_map('trim', explode(',', $order['coffee'])));
    foreach ($coffeeItems as $item) {
        $price = $prices[$item] ?? 0;
        $items_ordered[] = [
            'name' => $item,
            'price' => $price,
            'category' => 'Coffee'
        ];
        $subtotal += $price;
    }
}

// Process Tea
if (!empty($order['tea'])) {
    $teaItems = array_filter(array_map('trim', explode(',', $order['tea'])));
    foreach ($teaItems as $item) {
        $price = $prices[$item] ?? 0;
        $items_ordered[] = [
            'name' => $item,
            'price' => $price,
            'category' => 'Tea'
        ];
        $subtotal += $price;
    }
}

// Process Pastry
if (!empty($order['pastry'])) {
    $pastryItems = array_filter(array_map('trim', explode(',', $order['pastry'])));
    foreach ($pastryItems as $item) {
        $price = $prices[$item] ?? 0;
        $items_ordered[] = [
            'name' => $item,
            'price' => $price,
            'category' => 'Pastry'
        ];
        $subtotal += $price;
    }
}

// Process Pancakes
if (!empty($order['pancake'])) {
    $pancakeItems = array_filter(array_map('trim', explode(',', $order['pancake'])));
    foreach ($pancakeItems as $item) {
        $price = $prices[$item] ?? 0;
        $items_ordered[] = [
            'name' => $item . ' Pancake',
            'price' => $price,
            'category' => 'Pancake'
        ];
        $subtotal += $price;
    }
}

// Process Waffles
if (!empty($order['waffle'])) {
    $waffleItems = array_filter(array_map('trim', explode(',', $order['waffle'])));
    foreach ($waffleItems as $item) {
        $price = $prices[$item] ?? 0;
        $items_ordered[] = [
            'name' => $item . ' Waffles',
            'price' => $price,
            'category' => 'Waffle'
        ];
        $subtotal += $price;
    }
}

// Process Toast
if (!empty($order['toast'])) {
    $toastItems = array_filter(array_map('trim', explode(',', $order['toast'])));
    foreach ($toastItems as $item) {
        $price = $prices[$item] ?? 0;
        $items_ordered[] = [
            'name' => $item . ' Toast',
            'price' => $price,
            'category' => 'Toast'
        ];
        $subtotal += $price;
    }
}

// Calculate totals
$quantity = (int)$order['quantity'];
$subtotal = $subtotal * $quantity;
$delivery_fee = ($order['delivery_method'] === 'delivery') ? 3000 : 0;
$tax = round($subtotal * 0.18);
$total = $subtotal + $delivery_fee + $tax;

// Generate order ID
$order_id = 'ORD-' . date('Ymd', strtotime($order['order_time'])) . '-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT);

// Prepare response
$response = [
    'success' => true,
    'data' => [
        'order_id' => $order_id,
        'db_id' => (int)$order['id'],
        'name' => $order['name'],
        'email' => $order['email'],
        'phone' => $order['phone'],
        'pickup_time' => $order['pickup_time'],
        'items' => $items_ordered,
        'quantity' => $quantity,
        'subtotal' => $subtotal,
        'delivery_fee' => $delivery_fee,
        'tax' => $tax,
        'total' => $total,
        'delivery' => $order['delivery_method'],
        'address' => $order['address'] ?? '',
        'payment' => $order['payment_method'],
        'order_time' => $order['order_time'],
        'notes' => $order['notes'] ?? ''
    ]
];

echo json_encode($response);
$conn->close();
?>