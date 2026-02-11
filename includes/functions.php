<?php
/**
 * Utility Functions
 * Dealka Marketplace
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Get user by ID
 */
function get_user($user_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get product by ID
 */
function get_product($product_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM products p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get product error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all approved products with pagination
 */
function get_products($page = 1, $limit = 12, $category = null, $keyword = null, $sort = 'newest') {
    global $pdo;

    try {
        $offset = ($page - 1) * $limit;
        $query = "SELECT p.*, u.username as seller_name FROM products p LEFT JOIN users u ON p.user_id = u.id WHERE p.status = 'approved' AND p.status != 'sold'";
        $params = [];
        
        if ($category) {
            $query .= " AND p.category = ?";
            $params[] = $category;
        }

        if (!empty($keyword)) {
            $query .= " AND (p.title LIKE ? OR p.description LIKE ? OR u.username LIKE ?)";
            $searchTerm = '%' . $keyword . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY p.price ASC, p.created_at DESC";
                break;
            case 'price_desc':
                $query .= " ORDER BY p.price DESC, p.created_at DESC";
                break;
            case 'oldest':
                $query .= " ORDER BY p.created_at ASC";
                break;
            default:
                $query .= " ORDER BY p.created_at DESC";
                break;
        }

        $query .= " LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Get products error: " . $e->getMessage());
        return [];
    }
}

/**
 * Count approved products with filters
 */
function count_products($category = null, $keyword = null) {
    global $pdo;

    try {
        $query = "SELECT COUNT(*) FROM products p LEFT JOIN users u ON p.user_id = u.id WHERE p.status = 'approved' AND p.status != 'sold'";
        $params = [];

        if ($category) {
            $query .= " AND p.category = ?";
            $params[] = $category;
        }

        if (!empty($keyword)) {
            $query .= " AND (p.title LIKE ? OR p.description LIKE ? OR u.username LIKE ?)";
            $searchTerm = '%' . $keyword . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Count products error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Basic marketplace stats for homepage
 */
function get_marketplace_stats() {
    global $pdo;

    try {
        $approvedProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'approved' AND status != 'sold'")->fetchColumn();
        $activeSellers = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM products WHERE status IN ('approved', 'pending', 'sold')")->fetchColumn();
        $successfulDeals = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();

        return [
            'approved_products' => $approvedProducts,
            'active_sellers' => $activeSellers,
            'successful_deals' => $successfulDeals
        ];
    } catch (Exception $e) {
        error_log("Get marketplace stats error: " . $e->getMessage());
        return [
            'approved_products' => 0,
            'active_sellers' => 0,
            'successful_deals' => 0
        ];
    }
}

/**
 * Get user's products
 */
function get_user_products($user_id, $page = 1, $limit = 12) {
    global $pdo;

    try {
        $offset = ($page - 1) * $limit;
        $stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Get user products error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get order by ID
 */
function get_order($order_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   p.title as product_title, p.price as product_price,
                   b.username as buyer_username, b.email as buyer_email,
                   s.username as seller_username,
                   sa.full_name, sa.phone, sa.address_text, sa.note
            FROM orders o
            LEFT JOIN products p ON o.product_id = p.id
            LEFT JOIN users b ON o.buyer_id = b.id
            LEFT JOIN users s ON o.seller_id = s.id
            LEFT JOIN shipping_addresses sa ON o.shipping_address_id = sa.id
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get order error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get user's orders
 */
function get_user_orders($user_id, $type = 'all') {
    global $pdo;

    try {
        if ($type === 'buying') {
            $query = "
                SELECT o.*, p.title as product_title, u.username as seller_username
                FROM orders o
                LEFT JOIN products p ON o.product_id = p.id
                LEFT JOIN users u ON o.seller_id = u.id
                WHERE o.buyer_id = ?
                ORDER BY o.created_at DESC
            ";
        } elseif ($type === 'selling') {
            $query = "
                SELECT o.*, p.title as product_title, u.username as buyer_username
                FROM orders o
                LEFT JOIN products p ON o.product_id = p.id
                LEFT JOIN users u ON o.buyer_id = u.id
                WHERE o.seller_id = ?
                ORDER BY o.created_at DESC
            ";
        } else {
            $query = "
                SELECT o.*, p.title as product_title, 
                       CASE WHEN o.buyer_id = ? THEN u.username ELSE u2.username END as other_username,
                       CASE WHEN o.buyer_id = ? THEN 'buying' ELSE 'selling' END as type
                FROM orders o
                LEFT JOIN products p ON o.product_id = p.id
                LEFT JOIN users u ON o.seller_id = u.id
                LEFT JOIN users u2 ON o.buyer_id = u2.id
                WHERE o.buyer_id = ? OR o.seller_id = ?
                ORDER BY o.created_at DESC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Get user orders error: " . $e->getMessage());
        return [];
    }
}

/**
 * Create order
 */
function create_order($buyer_id, $product_id, $shipping_address) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get product
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'approved' FOR UPDATE");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Product not found or not available'];
        }

        if ($product['user_id'] == $buyer_id) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Cannot buy your own product'];
        }

        // Calculate fees
        $fee = round($product['price'] * (SELLER_FEE_PERCENT / 100), 2);
        $net_amount = $product['price'] - $fee;

        // Generate order code
        $order_code = generate_order_code();

        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_code, buyer_id, seller_id, product_id, price, fee, net_amount, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([$order_code, $buyer_id, $product['user_id'], $product_id, $product['price'], $fee, $net_amount]);
        $order_id = $pdo->lastInsertId();

        // Create shipping address
        $stmt = $pdo->prepare("
            INSERT INTO shipping_addresses (order_id, full_name, phone, address_text, note)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $order_id,
            $shipping_address['full_name'],
            $shipping_address['phone'],
            $shipping_address['address_text'],
            $shipping_address['note'] ?? ''
        ]);

        $shipping_id = $pdo->lastInsertId();

        // Update order with shipping address ID
        $stmt = $pdo->prepare("UPDATE orders SET shipping_address_id = ? WHERE id = ?");
        $stmt->execute([$shipping_id, $order_id]);

        // Log action
        log_action($buyer_id, 'create_order', 'Order created: ' . $order_code, 'orders', $order_id);

        $pdo->commit();

        return ['success' => true, 'message' => 'Order created', 'order_id' => $order_id, 'order_code' => $order_code];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Create order error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Order creation failed'];
    }
}

/**
 * Get payment by order ID
 */
function get_payment($order_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
        $stmt->execute([$order_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get payment error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get pending payments for admin
 */
function get_pending_payments() {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT p.*, o.order_code, o.price, u.username, u.email
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.status = 'pending'
            ORDER BY p.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Get pending payments error: " . $e->getMessage());
        return [];
    }
}

/**
 * Approve payment
 */
function approve_payment($payment_id, $admin_id) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get payment
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? FOR UPDATE");
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch();

        if (!$payment || $payment['status'] !== 'pending') {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Payment not found or already processed'];
        }

        // Get order
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$payment['order_id']]);
        $order = $stmt->fetch();

        if (!$order) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Update payment status
        $stmt = $pdo->prepare("UPDATE payments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$payment_id]);

        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
        $stmt->execute([$payment['order_id']]);

        // Log action
        log_action($admin_id, 'approve_payment', 'Payment approved: ' . $payment['id'], 'payments', $payment_id);

        $pdo->commit();

        return ['success' => true, 'message' => 'Payment approved'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Approve payment error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Payment approval failed'];
    }
}

/**
 * Reject payment
 */
function reject_payment($payment_id, $reason, $admin_id) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get payment
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? FOR UPDATE");
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch();

        if (!$payment || $payment['status'] !== 'pending') {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Payment not found or already processed'];
        }

        // Update payment status
        $stmt = $pdo->prepare("UPDATE payments SET status = 'rejected', rejected_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $payment_id]);

        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$payment['order_id']]);

        // Log action
        log_action($admin_id, 'reject_payment', 'Payment rejected: ' . $payment['id'] . ' - Reason: ' . $reason, 'payments', $payment_id);

        $pdo->commit();

        return ['success' => true, 'message' => 'Payment rejected'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Reject payment error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Payment rejection failed'];
    }
}

/**
 * Get pending withdrawals for admin
 */
function get_pending_withdrawals() {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT w.*, u.username, u.email, u.phone
            FROM withdrawals w
            LEFT JOIN users u ON w.user_id = u.id
            WHERE w.status = 'pending'
            ORDER BY w.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Get pending withdrawals error: " . $e->getMessage());
        return [];
    }
}

/**
 * Approve withdrawal
 */
function approve_withdrawal($withdrawal_id, $admin_id) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get withdrawal
        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ? FOR UPDATE");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch();

        if (!$withdrawal || $withdrawal['status'] !== 'pending') {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Withdrawal not found or already processed'];
        }

        // Update withdrawal status
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'completed' WHERE id = ?");
        $stmt->execute([$withdrawal_id]);

        // Deduct from pending_withdrawal
        $stmt = $pdo->prepare("UPDATE users SET pending_withdrawal = pending_withdrawal - ? WHERE id = ?");
        $stmt->execute([$withdrawal['amount'], $withdrawal['user_id']]);

        // Log action
        log_action($admin_id, 'approve_withdrawal', 'Withdrawal approved: ' . $withdrawal['id'] . ' - Amount: ' . $withdrawal['net_amount'], 'withdrawals', $withdrawal_id);

        $pdo->commit();

        return ['success' => true, 'message' => 'Withdrawal approved'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Approve withdrawal error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Withdrawal approval failed'];
    }
}

/**
 * Reject withdrawal
 */
function reject_withdrawal($withdrawal_id, $reason, $admin_id) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get withdrawal
        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ? FOR UPDATE");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch();

        if (!$withdrawal || $withdrawal['status'] !== 'pending') {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Withdrawal not found or already processed'];
        }

        // Update withdrawal status
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'rejected', rejected_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $withdrawal_id]);

        // Return amount to balance
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, pending_withdrawal = pending_withdrawal - ? WHERE id = ?");
        $stmt->execute([$withdrawal['amount'], $withdrawal['amount'], $withdrawal['user_id']]);

        // Log action
        log_action($admin_id, 'reject_withdrawal', 'Withdrawal rejected: ' . $withdrawal['id'] . ' - Reason: ' . $reason, 'withdrawals', $withdrawal_id);

        $pdo->commit();

        return ['success' => true, 'message' => 'Withdrawal rejected'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Reject withdrawal error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Withdrawal rejection failed'];
    }
}

/**
 * Get pending products for admin
 */
function get_pending_products() {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, u.email
            FROM products p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.status = 'pending'
            ORDER BY p.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Get pending products error: " . $e->getMessage());
        return [];
    }
}

/**
 * Approve product
 */
function approve_product($product_id, $admin_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
        $stmt->execute([$product_id]);

        log_action($admin_id, 'approve_product', 'Product approved: ' . $product_id, 'products', $product_id);

        return ['success' => true, 'message' => 'Product approved'];
    } catch (Exception $e) {
        error_log("Approve product error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Product approval failed'];
    }
}

/**
 * Reject product
 */
function reject_product($product_id, $reason, $admin_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("UPDATE products SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$product_id]);

        log_action($admin_id, 'reject_product', 'Product rejected: ' . $product_id . ' - Reason: ' . $reason, 'products', $product_id);

        return ['success' => true, 'message' => 'Product rejected'];
    } catch (Exception $e) {
        error_log("Reject product error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Product rejection failed'];
    }
}
?>
