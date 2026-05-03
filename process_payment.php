<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

// Get payment data from POST or JSON body
$input = file_get_contents('php://input');
$json_data = json_decode($input, true);

$payment_method = $_POST['method'] ?? $json_data['method'] ?? '';
$phone_number   = $_POST['phone']  ?? $json_data['phone']  ?? '';
$amount         = $_POST['amount'] ?? $json_data['amount'] ?? 0;

// Validate payment method
if (empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Payment method is required']);
    exit();
}

// Validate and normalize phone for mobile money
if ($payment_method !== 'Cash') {
    $phone_clean = preg_replace('/[\s\-]/', '', $phone_number);

    if (empty($phone_clean)) {
        echo json_encode(['success' => false, 'message' => 'Phone number is required for mobile money payment']);
        exit();
    }

    if (preg_match('/^0([0-9]{9})$/', $phone_clean, $matches)) {
        $phone_number = '+256' . $matches[1];
    } elseif (preg_match('/^256([0-9]{9})$/', $phone_clean, $matches)) {
        $phone_number = '+256' . $matches[1];
    } elseif (preg_match('/^\+256[0-9]{9}$/', $phone_clean)) {
        $phone_number = $phone_clean;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number. Use format: 07XXXXXXXX or +256XXXXXXXXX']);
        exit();
    }
}

// Connect to database
try {
    $conn = new mysqli('localhost', 'root', '', 'coffee_shop');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    // Get order ID from session OR fall back to most recent order in DB
    $order_db_id = $_SESSION['last_order']['db_id'] ?? null;

    if (!$order_db_id) {
        $latest = $conn->query("SELECT id FROM orders ORDER BY id DESC LIMIT 1");
        if (!$latest || $latest->num_rows === 0) {
            throw new Exception('No orders found. Please place an order first.');
        }
        $row = $latest->fetch_assoc();
        $order_db_id = (int)$row['id'];
    }

    // Update the order with payment info
    $payment_phone  = ($payment_method !== 'Cash' && $phone_number) ? $phone_number : null;
    $payment_status = 'pending';

    $sql  = "UPDATE orders SET payment_method = ?, payment_phone = ?, payment_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param("sssi", $payment_method, $payment_phone, $payment_status, $order_db_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update payment: ' . $stmt->error);
    }

    // Update session if it exists
    if (isset($_SESSION['last_order'])) {
        $_SESSION['last_order']['payment_method'] = $payment_method;
        $_SESSION['last_order']['payment_phone']  = $phone_number;
        $_SESSION['last_order']['payment_status'] = 'pending';
    }

    $stmt->close();
    $conn->close();

    // Build the order ID string
    $order_id_str = isset($_SESSION['last_order']['order_id'])
        ? $_SESSION['last_order']['order_id']
        : 'ORD-' . date('Ymd') . '-' . str_pad($order_db_id, 4, '0', STR_PAD_LEFT);

    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully',
        'data' => [
            'order_id' => $order_id_str,
            'db_id'    => $order_db_id,
            'method'   => $payment_method,
            'phone'    => $phone_number,
            'amount'   => $amount,
            'status'   => 'pending'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
