<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5,minimum-scale=1.0">
    <script src="https://kit.fontawesome.com/d0ce752c6a.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/style.css">
    <?php require_once '../../backend/utils/session.php'; ?>

    <title>Shop - DataDash</title>
    <style>
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin-left: 50px;
        }

        .product {
            width: 24%;
            margin-bottom: 20px;
        }

        .product img {
            max-width: 100%;
            height: auto;
            width: 275px;
            height: 275px;
            object-fit: contain;
        }

        .product a {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .product a:hover {
            text-decoration: underline;
        }

        .shop-button {
            display: inline-block;
            padding: 15px 20px;
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

        .add-to-cart {
            background-color: #0ad4f8;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        .add-to-cart:hover {
            background-color: #07eaff;
        }

    </style>
    <header>
        <div class="heading">
            <div class="left-heading">
                <div class="logo">
                    <a href="homepage.php">
                        <img src="../images/misc/DataDash.png" alt="Logo" width="85" height="500">
                    </a>
                </div>
                <div class="search-bar">
                    <form class="search-form">
                        <label>
                            <input type="search" name="search" placeholder="search...">
                        </label>
                        <input type="submit" name="submit-search" class="search-button">
                    </form>
                </div>
            </div>
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
</head>
<body>
    <main>
        <section class="shop-products">
            <h2>All Products</h2>
            <div class="filter-sort">
                <label>Filter by:</label>
                <select>
                    <option value="">All Categories</option>
                    <option value="Smartphones">Smartphones</option>
                    <option value="Tablets">Tablets</option>
                    <option value="Headphones">Headphones</option>
                    <option value="Laptops">Laptops</option>
                    <option value="Smartwatches">Smartwatches</option>
                    <option value="Cameras">Cameras</option>
                    <option value="Earbuds">Earbuds</option>
                    <option value="Televisions">Televisions</option>
                    <option value="Gaming Consoles">Gaming Consoles</option>
                    <option value="Smart Speakers">Smart Speakers</option>
                    <option value="Chargers">Chargers</option>
                    <option value="Keyboards">Keyboards</option>
                    <option value="Computer Mice">Computer Mice</option>
                    <option value="Storage Devices">Storage Devices</option>
                    <option value="Virtual Reality">Virtual Reality</option>
                </select>
                <label>Sort by:</label>
                <select>
                    <option value="">Default</option>
                    <option value="price-asc">Price (Low to High)</option>
                    <option value="price-desc">Price (High to Low)</option>
                    <option value="rating">Rating</option>
                </select>
            </div>
            <div class="product-grid">
                <?php
                $conn = new mysqli("localhost", "root", "", "datadash");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $products = $conn->query("SELECT * FROM product");
                foreach ($products as $product) {
                    echo '<div class="product">';
                    echo '<a href="product_details.php?id=' . $product['product_id'] . '">';
                    echo '<img src="../images/electronic_products/' . $product['image'] . '" alt="' . $product['name'] . '">';
                    echo '<h3>' . $product['name'] . '</h3>';
                    echo '<p>$' . $product['price'] . '</p>';
                    echo '</a>';
                    if (sessionExists()) {
                        echo '<form action="../../backend/utils/add_to_cart.php" method="post">';
                        echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';
                        echo '<input type="hidden" name="quantity" value="1">';
                        echo '<button type="submit" class="add-to-cart">Add to Cart</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                }
                $conn->close();
                ?>
            </div>
        </section>
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
    <script src="../js/navbar.js"></script>
</body>
</html>