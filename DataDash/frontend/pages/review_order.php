<?php
require_once '../../backend/utils/session.php';
require_once '../../backend/include/database_config.php';

// Establish database connection using the configured credentials
$conn = new mysqli("localhost", "root", "", "datadash");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to retrieve cart items for a given user ID
function getCartItems($userId) {
$conn = new mysqli("localhost", "root", "", "datadash");

    $sql = "SELECT cp.product_id, p.name, p.price, p.image, cp.quantity
            FROM cart_product cp
            JOIN product p ON cp.product_id = p.product_id
            JOIN cart c ON cp.cart_id = c.cart_id
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }
    }
    return $cartItems;
}

function getAddressById($addressId) {
$conn = new mysqli("localhost", "root", "", "datadash");

    $sql = "SELECT * FROM addresses WHERE address_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $addressId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to retrieve a specific payment method by ID
function getPaymentMethodById($paymentMethodId) {
$conn = new mysqli("localhost", "root", "", "datadash");

    $sql = "SELECT * FROM payment_methods WHERE payment_method_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $paymentMethodId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to create a new order
function createOrder($userId, $selectedProducts, $selectedQuantities, $shippingAddressId, $paymentMethodId) {
$conn = new mysqli("localhost", "root", "", "datadash");

    $orderDate = date('Y-m-d H:i:s');
    $totalPrice = 0;

    // Calculate total price
    foreach ($selectedProducts as $productId) {
        $quantity = $selectedQuantities[$productId];
        $sql = "SELECT price FROM product WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $productPrice = $row['price'];
        $totalPrice += $productPrice * $quantity;
    }

    // Insert order into orders table
    $sql = "INSERT INTO orders (user_id, order_date, total_amount, status)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $statusp = 'processing';
    $stmt->bind_param("isds", $userId, $orderDate, $totalPrice, $statusp);
    $stmt->execute();

    // Get the order ID
    $orderId = $conn->insert_id;

    // Insert order details into ordered_item table
    foreach ($selectedProducts as $productId) {
        $quantity = $selectedQuantities[$productId];
        $sql = "INSERT INTO ordered_item (order_id, user_id, product_id, quantity, order_status, order_date)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $orderStatus = 'processing'; // Assuming 0 represents a new order
        $stmt->bind_param("iiisi", $orderId, $userId, $productId, $quantity, $orderStatus, $orderDate);
        $stmt->execute();

        // Update inventory for the specific product
        $sql = "UPDATE inventory SET quantity = quantity - ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $productId);
        $stmt->execute();
    }

    // Remove items from cart (for selected products)
    foreach ($selectedProducts as $productId) {
        $sql = "DELETE FROM cart_product WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id = ?) AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <title>Checkout</title>
    <style>
        /* Style for the checkout page */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .checkout-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .checkout-section {
            margin-bottom: 20px;
        }

        .checkout-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        .checkout-table th, .checkout-table td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .checkout-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .checkout-table img {
            max-width: 100px;
            height: auto;
            margin-right: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }



        .order-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .order-details p {
            margin: 5px 0;
        }

        .continue-shopping-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #337ab7;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .continue-shopping-btn:hover {
            background-color: #21618C;
        }

        /* Style for radio button labels */
        .form-group label {
            display: block;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .form-group label:hover {
            background-color: #f0f0f0;
        }

        /* Style for "Create New" buttons */
        .checkout-section button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: block;
            margin: 10px auto;
            width: fit-content;
        }

        .checkout-section button:hover {
            background-color: #0056b3;
        }

        .button-container {
            display: flex; /* Enable flexbox */
        justify-content: center; /* Center content horizontally */
        }

        .shop-button {
            display: inline-block;
            padding: 17px 40px;
            font-size: 16px;
            color: #fff;
            background-color: #009dff; /* Bootstrap primary color */
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .shop-button:hover {
            background-color: #0056b3; /* Darker shade for hover effect */
        }
    </style>
</head>
<body>
<body>
    <header>
        <div class="heading">
            <div class="left-heading">
                <div class="logo">
                    <a href="homepage.php">
                        <img src="../images/misc/DataDash.png" alt="Logo" width="105" height="500">
                    </a>
                </div>
                <div class="search-bar">
                    <form class="search-form">
                        <label>
                            <input type="search" name="search" placeholder="search...">
                        </label>
                        <input type="submit" name="submit-search" class ="search-button">
                    </form>
                </div>
            </div> <br>
            <div class="shop-button-container">
                <a href="shop.php" class="shop-button">Shop</a>
            </div>
            <div class="right-heading">
                <div class="login-status">
                    <?php if (sessionExists()): ?>
                        <div class="hello-message">
                            <span>Hello, <?php echo getSessionUsername(); ?></span>
                        </div>
                        <div class="icons">
                            <a href="account.php"><i class="fas fa-user-check fa-2x"></i>Account</a>
                            <a href="cart.php"><i class="fas fa-shopping-cart fa-2x"></i>Cart</a>
                            <a href="../../backend/utils/logout.php"><i class="fas fa-sign-out-alt fa-2x"></i>Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="login" title="login">
                            <a href="login_page.php"><i class="fas fa-sign-in-alt fa-2x"></i>Login</a>
                        </div>
                        <div class="register" title="register">
                            <a href="create_account.php"><i class="fas fa-user-times fa-2x"></i>Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="checkout-container">
            <h2>Checkout</h2>
            <?php
            if (sessionExists()) {
                $userId = getSessionUserId();
                $cartItems = getCartItems($userId);

                if (!empty($cartItems)) {
                    $selectedProductIds = [];
                    $selectedQuantities = [];
                    if (isset($_POST['selected_products'])) {
                        $selectedProductsArray = json_decode($_POST['selected_products'], true);
                        if (isset($selectedProductsArray) && !empty($selectedProductsArray)) {
                            foreach ($cartItems as $item) {
                                if (in_array($item['product_id'], $selectedProductsArray)) {
                                    $selectedProductIds[] = $item['product_id'];
                                    $selectedQuantities[$item['product_id']] = $item['quantity'];
                                }
                            }
                            ?>
                            <div class="checkout-section">
                                <h3>Your Order</h3>
                                <table class="checkout-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalPrice = 0;
                                        foreach ($selectedProductIds as $productId) {
                                            $product = array_filter($cartItems, function($item) use ($productId) {
                                                return $item['product_id'] === $productId;
                                            });
                                            $product = reset($product);
                                            if ($product) {
                                                $quantity = $selectedQuantities[$productId];
                                                $productTotal = $product['price'] * $quantity;
                                                $totalPrice += $productTotal;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <img src="../images/electronic_products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                                                        <?php echo $product['name']; ?>
                                                    </td>
                                                    <td><?php echo $quantity; ?></td>
                                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                    <td>$<?php echo number_format($productTotal, 2); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3">Subtotal:</th>
                                            <th>$<?php echo number_format($totalPrice, 2); ?></th>
                                        </tr>
                                        <tr>
                                            <th colspan="3">Shipping:</th>
                                            <th>$0.00 (Free)</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3">Total:</th>
                                            <th id="total-price">$<?php echo number_format($totalPrice, 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="checkout-section">
                                <?php
                                if (sessionExists()) {
                                    $userId = getSessionUserId();

                                    $shippingAddressId = $_POST['shipping_address_id'];
                                    $paymentMethodId = $_POST['payment_method_id'];

                                    $shippingAddress = getAddressById($shippingAddressId);
                                    $paymentMethod = getPaymentMethodById($paymentMethodId);
                                }
                                ?>
                            <h3>Shipping Address</h3>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="shipping-address-<?php echo $shippingAddress['address_id']; ?>">
                                        <?php echo $shippingAddress['street_address'] . ', ' . $shippingAddress['city'] . ', ' . $shippingAddress['state'] . ', ' . $shippingAddress['postal_code'] . ', ' . $shippingAddress['country']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-section">
                            <h3>Payment Information</h3>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="payment-method-<?php echo $paymentMethod['payment_method_id']; ?>">
                                        <?php echo $paymentMethod['method_type'] . ' (Ending in ' . substr($paymentMethod['card_number'], -4) . ')'; ?>
                                        <br>
                                        <?php echo 'CVV: ' . $paymentMethod['cvs_number'] . ', Expiration: ' . $paymentMethod['expiration_date']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php
                        // Check if the form has been submitted
                        if (isset($_POST['place_order'])) {
                            // Call the createOrder function after validating product selection
                            if (isset($_POST['selected_products']) && !empty($selectedProductIds)) {
                                createOrder($userId, $selectedProductIds, $selectedQuantities, $shippingAddressId, $paymentMethodId);
                                echo '<p class="success-message">Thank you for your order!</p>';
                            } else {
                                echo '<p>Invalid product selection.</p>';
                            }
                        } else {
                            // Display the order summary
                            ?>
                            <div class="button-container">
                              <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <input type="hidden" name="selected_products" value='<?php echo json_encode($selectedProductIds); ?>' />
                                <input type="hidden" name="shipping_address_id" value="<?php echo $shippingAddressId; ?>" />
                                <input type="hidden" name="payment_method_id" value="<?php echo $paymentMethodId; ?>" />
                                <button type="submit" name="place_order" id="place-order-button" style="
                                background-color: #009dff;
                                color: white;
                                padding: 10px 20px;
                                border: none;
                                border-radius: 5px;
                                cursor: pointer;
                                text-decoration: none;
                                transition: background-color 0.3s ease;
                              ">
                                Place Order
                              </button>
                              </form>
                            </div>
                            <?php
                        }
                        } else {
                            echo '<p>Invalid product selection.</p>';
                        }
                    } else {
                        echo '<p>Please select products for checkout.</p>';
                    }
                } else {
                    echo '<p>Your cart is empty.</p>';
                }
            } else {
                echo '<p>Please <a href="login_page.php">log in</a> to proceed to checkout.</p>';
            }
            ?>
        </div>
    </main>
    <footer>
        <div class="social-media">
            <br><br>
            <ul>
                <li><a href="#"><i class="fab fa-facebook fa-1.5x"></i>Facebook</a></li>
                <li><a href="#"><i class="fab fa-instagram fa-1.5x"></i>Instagram</a></li>
                <li><a href="#"><i class="fab fa-youtube fa-1.5x"></i>YouTube</a></li>
                <li><a href="#"><i class="fab fa-twitter fa-1.5x"></i>Twitter</a></li>
                <li><a href="#"><i class="fab fa-pinterest fa-1.5x"></i>Pinterest</a></li>
            </ul>
        </div>
        <div class="general-info">
            <div class="help">
                <h3>Help</h3>
                <ul>
                    <li><a href="faq.php">Frequently Asked Questions</a></li>
                    <li><a href="returns.php">Returns</a></li>
                    <li><a href="customer_service.php">Customer Service</a></li>
                </ul>
            </div>
            <div class="location">
                <p>123 Main Street, City, Country</p>
            </div>
            <div class="legal">
                <h3>Privacy & Legal</h3>
                <ul>
                    <li><a href="cookies_and_privacy.php">Cookies & Privacy</a></li>
                    <li><a href="terms_and_conditions.php">Terms & Conditions</a></li>
                </ul>
            </div>
        </div>
        2024 DataDash, All Rights Reserved.
    </footer>
    <script>
    $(document).ready(function() {
        $("#search-form").submit(function(event) {
            event.preventDefault();
            var searchTerm = $("#search-input").val();

            // Redirect to shop.php with search term as a query parameter
            window.location.href = "shop.php?search=" + searchTerm;
        });
    });
    </script>
</html>