<?php require_once __DIR__ . '/../../../core/helpers/url.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('digital_menu'); ?> - <?php echo htmlspecialchars($tenant['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --accent: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Battambang', 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            padding-bottom: 50px;
        }

        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .tagline {
            opacity: 0.9;
            font-weight: 300;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .category-section {
            margin-bottom: 40px;
        }

        .category-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            color: var(--primary-dark);
        }

        .category-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(to right, #e2e8f0, transparent);
            margin-left: 15px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background-color: #f1f5f9;
        }

        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .product-description {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .badge {
            background: #fef3c7;
            color: #d97706;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .no-results {
            text-align: center;
            padding: 80px 0;
            color: var(--text-light);
        }

        /* Improved Image Container */
        .product-image-wrapper {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
            background-color: #f1f5f9;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        /* Price Tag Styling */
        .price-tag {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1.1rem;
        }

        /* Category Header refinement */
        .category-title {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 25px;
            padding-left: 15px;
            border-left: 5px solid var(--primary);
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .category-title::after {
            display: none;
        }

        /* Placeholder styling */
        .no-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #94a3b8;
        }

        .no-image-placeholder i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            opacity: 0.5;
        }


        @media (max-width: 640px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }

        .search-bar {
            margin-top: -30px;
            margin-bottom: 30px;
            position: sticky;
            top: 20px;
            z-index: 10;
        }

        .search-input {
            width: 100%;
            padding: 15px 25px;
            border-radius: 99px;
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            font-size: 1rem;
            outline: none;
        }

        /* Cart UI */
        .cart-floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 70px;
            height: 70px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.5);
            cursor: pointer;
            z-index: 1000;
            transition: transform 0.3s;
        }

        .cart-floating-btn:hover {
            transform: scale(1.1);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            border: 2px solid white;
        }

        .cart-drawer {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 450px;
            height: 100%;
            background: white;
            z-index: 2000;
            box-shadow: -10px 0 30px rgba(0,0,0,0.1);
            transition: right 0.4s ease;
            display: flex;
            flex-direction: column;
        }

        .cart-drawer.open {
            right: 0;
        }

        .cart-header {
            padding: 25px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
        }

        .cart-footer {
            padding: 25px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .cart-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .cart-item-img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: var(--primary);
            font-weight: 600;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 99px;
        }

        .qty-btn {
            cursor: pointer;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            font-size: 0.8rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .btn-order {
            width: 100%;
            padding: 18px;
            border-radius: 18px;
            background: var(--primary);
            color: white;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            margin-top: 15px;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .cart-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1500;
            display: none;
            backdrop-filter: blur(4px);
        }

        .cart-overlay.open {
            display: block;
        }

        .add-to-cart-btn {
            background: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .add-to-cart-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .checkout-form {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            outline: none;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        @keyframes shake {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.1) rotate(5deg); }
            50% { transform: scale(1.1) rotate(-5deg); }
            75% { transform: scale(1.1) rotate(5deg); }
        }

        .shake {
            animation: shake 0.4s ease-in-out;
        }


    </style>
</head>
<body>

<header>
    <?php if (!empty($settings['receipt_logo_path'])): ?>
        <img src="<?php echo htmlspecialchars($settings['receipt_logo_path']); ?>" alt="Logo" style="height: 60px; margin-bottom: 15px; border-radius: 12px; background: white; padding: 5px;">
    <?php endif; ?>
    <div class="logo"><?php echo htmlspecialchars($tenant['name']); ?></div>
    <div class="tagline"><?php echo __('explore_menu_msg'); ?></div>
</header>

<div class="container">
    <div class="search-bar">
        <input type="text" id="searchInput" class="search-input" placeholder="<?php echo __('search_food_drinks'); ?>" onkeyup="filterMenu()">
    </div>

    <?php if (empty($categories)): ?>
        <div class="no-results">
            <i class="fas fa-utensils fa-3x mb-3"></i>
            <p><?php echo __('no_items_found'); ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $categoryName => $items): ?>
            <div class="category-section" id="cat-<?php echo md5($categoryName); ?>">
                <h2 class="category-title"><?php echo htmlspecialchars($categoryName); ?></h2>
                <div class="products-grid">
                    <?php foreach ($items as $product): ?>
                        <div class="product-card" data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>">
                            <div class="product-image-wrapper">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars(mc_url('uploads/products/' . $product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-utensils"></i>
                                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;"><?php echo __('freshly_prepared'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <p class="product-description"><?php echo htmlspecialchars($product['description'] ?: __('product_desc_fallback', ['default' => 'Tailored to perfection with the finest ingredients.'])); ?></p>
                                <div class="product-footer">
                                    <span class="price-tag">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['status'] !== 'active'): ?>
                                        <span class="badge" style="background:#fee2e2; color:#ef4444; border: 1px solid #fecaca;"><?php echo __('sold_out'); ?></span>
                                    <?php else: ?>
                                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo htmlspecialchars(json_encode([
                                            'id' => $product['id'],
                                            'name' => $product['name'],
                                            'price' => (float)$product['price'],
                                            'image' => $product['image'] ? mc_url('uploads/products/' . $product['image']) : null
                                        ])); ?>)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="cart-floating-btn" id="cartBtn" onclick="toggleCart()">
    <i class="fas fa-shopping-basket"></i>
    <div class="cart-badge" id="cartCount">0</div>
</div>

<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>

<div class="cart-drawer" id="cartDrawer">
    <div class="cart-header">
        <h3 style="font-weight: 800;"><?php echo __('your_cart'); ?></h3>
        <button class="qty-btn" onclick="toggleCart()"><i class="fas fa-times"></i></button>
    </div>
    <div class="cart-items" id="cartItemsList">
        <!-- Items will be injected here -->
    </div>
    <div class="cart-footer">
        <div class="checkout-form">
            <div class="form-group">
                <label><?php echo __('table_number_location'); ?></label>
                <input type="text" id="tableNumber" class="form-control" placeholder="<?php echo __('table_number_placeholder'); ?>">
            </div>
            <div class="form-group">
                <label><?php echo __('your_name_optional'); ?></label>
                <input type="text" id="cartCustomerName" class="form-control" placeholder="<?php echo __('order_name_placeholder'); ?>">
            </div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-weight: 800; font-size: 1.2rem;">
            <span><?php echo __('total'); ?></span>
            <span id="cartTotalText">$0.00</span>
        </div>
        <button class="btn-order" id="orderSubmitBtn" onclick="placeOrder()"><?php echo __('place_order_now'); ?></button>
    </div>
</div>


<script>
    function filterMenu() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        const sections = document.querySelectorAll('.category-section');

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(filter)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Hide sections if all products inside are hidden
        sections.forEach(section => {
            const visibleCards = section.querySelectorAll('.product-card[style="display: flex;"]');
            if (visibleCards.length === 0 && filter !== '') {
                section.style.display = 'none';
            } else {
                section.style.display = 'block';
            }
        });
    }

    // Cart Logic
    let cart = JSON.parse(localStorage.getItem('myMenuCart')) || [];

    function updateCartUI() {
        const cartItemsList = document.getElementById('cartItemsList');
        const cartCount = document.getElementById('cartCount');
        const cartTotalText = document.getElementById('cartTotalText');
        const cartBtn = document.getElementById('cartBtn');

        // Update count badge
        const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.innerText = totalQty;
        cartBtn.style.display = totalQty > 0 ? 'flex' : 'none';

        // Clear and rebuild list
        cartItemsList.innerHTML = '';
        let total = 0;

        if (cart.length === 0) {
            cartItemsList.innerHTML = `
                <div style="text-align: center; padding: 40px 0; color: #94a3b8;">
                    <i class="fas fa-shopping-basket fa-3x" style="margin-bottom: 15px; opacity: 0.3;"></i>
                    <p><?php echo __('cart_empty_msg'); ?></p>
                </div>
            `;
        }

        cart.forEach((item, index) => {
            total += item.price * item.quantity;
            cartItemsList.innerHTML += `
                <div class="cart-item">
                    ${item.image ? `<img src="${item.image}" class="cart-item-img">` : `<div class="cart-item-img" style="background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:#94a3b8;"><i class="fas fa-utensils"></i></div>`}
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">$${item.price.toFixed(2)}</div>
                    </div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="changeQty(${index}, -1)"><i class="fas fa-minus"></i></button>
                        <span style="font-weight:700;">${item.quantity}</span>
                        <button class="qty-btn" onclick="changeQty(${index}, 1)"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            `;
        });

        cartTotalText.innerText = `$${total.toFixed(2)}`;
        localStorage.setItem('myMenuCart', JSON.stringify(cart));
    }

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        updateCartUI();
        
        // Visual feedback
        const btn = document.getElementById('cartBtn');
        btn.classList.add('shake');
        setTimeout(() => btn.classList.remove('shake'), 400);
        
        // Open drawer on first add
        if (cart.length === 1) toggleCart(true);
    }

    function changeQty(index, delta) {
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        updateCartUI();
    }

    function toggleCart(forceOpen = null) {
        const drawer = document.getElementById('cartDrawer');
        const overlay = document.getElementById('cartOverlay');
        
        if (forceOpen === true) {
            drawer.classList.add('open');
            overlay.classList.add('open');
        } else if (forceOpen === false) {
            drawer.classList.remove('open');
            overlay.classList.remove('open');
        } else {
            drawer.classList.toggle('open');
            overlay.classList.toggle('open');
        }
    }

    async function placeOrder() {
        if (cart.length === 0) return;

        const tableNumber = document.getElementById('tableNumber').value;
        const customerName = document.getElementById('cartCustomerName').value;

        if (!tableNumber) {
            alert('<?php echo __('enter_table_msg'); ?>');
            return;
        }

        const btn = document.getElementById('orderSubmitBtn');
        btn.disabled = true;
        btn.innerText = '<?php echo __('submitting'); ?>';

        try {
            const response = await fetch('<?php echo mc_url(($tenant['subdomain'] ?? '') . '/pos/menu/place_order', true); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    items: cart,
                    table_number: tableNumber,
                    customer_name: customerName
                })
            });

            const result = await response.json();

            if (result.success) {
                alert('<?php echo __('order_success_msg'); ?>');
                cart = [];
                updateCartUI();
                toggleCart(false);
                document.getElementById('tableNumber').value = '';
                document.getElementById('cartCustomerName').value = '';
            } else {
                alert('<?php echo __('order_failed_msg'); ?>' + result.message);
            }
        } catch (error) {
            alert('<?php echo __('something_wrong_msg'); ?>');
        } finally {
            btn.disabled = false;
            btn.innerText = '<?php echo __('place_order_now'); ?>';
        }
    }

    // Initialize
    updateCartUI();

</script>

</body>
</html>
