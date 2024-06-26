<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5,minimum-scale=1.0">
    <script src="https://kit.fontawesome.com/d0ce752c6a.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="../css/style.css">
    <?php require_once '../../backend/utils/session.php'; ?>

    <title>Document</title>
    <style>
        /* ... (Your existing styles) ... */
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .product {
            width: 24%;
            margin-bottom: 20px;
        }

        .product img {
            max-width: 100%;
            height: auto;
            width: 275px; /* Set the same width and height */
            height: 275px;
            object-fit: contain; /* Maintain aspect ratio and fit within the container */
        }

        .featured-products .product-grid .product:first-child,
        .new-products .product-grid .product:first-child {
            margin-left: 3%;
        }
        .shop-button-container {
        text-align: center; /* Center the button horizontally */
        margin-top: 10px; /* Add some space above the button */
}

        .shop-button {
            display: inline-block;
            padding: 10px 40px;
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
</head>
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
                    <form id="search-form" method="GET" action="shop.php">
                        <label>
                            <input type="search" name="search" id="search-input" placeholder="search...">
                        </label>
                        <input type="submit" value="Search">
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
        <section class="banner">
            <div class="swiper">
                <div class="swiper-wrapper"></div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </section>
        <section class="featured-products">
            <h2>Featured Products</h2>
            <!-- Product grid -->
            <div class="product-grid">
                <?php
                $conn = new mysqli("localhost", "root", "", "datadash");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                } $featuredProducts = $conn->query("SELECT * FROM product ORDER BY RAND() LIMIT 4");
                foreach ($featuredProducts as $product) {
                    echo '<div class="product">';
                    echo '<a href="product_details.php?id=' . $product['product_id'] . '">';
                    echo '<img src="../images/electronic_products/' . $product['image'] . '" alt="' . $product['name'] . '">';
                    echo '<div class="product-details">';
                    echo '<h3 style="color: #000;">' . $product['name'] . '</h3>';
                    echo '<p style="color: #000;">$' . $product['price'] . '</p>';
                    echo '</a>';
                    if (sessionExists()) {
                         echo '<form action="../../backend/utils/add_to_cart.php" method="post">';
                         echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';
                         echo '<input type="hidden" name="quantity" value="1">';
                         echo '<button type="submit" class="add-to-cart">Add to Cart</button>';
                         echo '</form>';
                    } else {
                        echo '<a href="login_page.php" class="add-to-cart-link">Add to Cart</a>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                $conn->close();
                ?>
            </div>
        </section>
        <section class="new-products">
            <h2>New Products</h2>
            <!-- Product grid -->
            <div class="product-grid">
                <?php
                $conn = new mysqli("localhost", "root", "", "datadash");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $newProducts = $conn->query("SELECT * FROM product ORDER BY date_added DESC LIMIT 4");
                foreach ($newProducts as $product) {
                    echo '<div class="product">';
                    echo '<a href="product_details.php?id=' . $product['product_id'] . '">';
                    echo '<img src="../images/electronic_products/' . $product['image'] . '" alt="' . $product['name'] . '">';
                    echo '<div class="product-details">';
                    echo '<h3 style="color: #000;">' . $product['name'] . '</h3>';
                    echo '<p style="color: #000;">$' . $product['price'] . '</p>';
                    echo '</a>';
                    if (sessionExists()) {
                        echo '<form action="../../backend/utils/add_to_cart.php" method="post">';
                        echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';
                        echo '<input type="hidden" name="quantity" value="1">';
                        echo '<button type="submit" class="add-to-cart">Add to Cart</button>';
                        echo '</form>';
                    } else {
                        echo '<a href="login_page.php" class="add-to-cart-link">Add to Cart</a>';
                    }
                    echo '</div>';
                    echo '</div>';
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
        <ul>
                <h3>admin login</h3>     
                <li>  
                    <a href="../../admin/frontend/index.html">
                        <i class="fas fa-users-cog"></i>
                    </a>
                </li>
            </ul>
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
<script src = "../js/global.js" defer ></script>
<script src="../js/navbar.js"></script>
<script src="../js/slider.js"></script>
<script src = "../js/menu.js"></script>
<script src = "../js/banner.js"></script>
<script src = "../js/featured.js"></script>
<script src = "../js/newArrivals.js"></script>
<script src = "../js/starRatingSystem.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
</body>
</html>