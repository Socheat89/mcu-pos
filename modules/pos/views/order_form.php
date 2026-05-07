<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$subdomain = Tenant::getCurrent()['subdomain'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('pos_create_order'); ?></title>
    <link href="<?php echo $urlPrefix; ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Inter', 'Segoe UI', sans-serif !important;
        }
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            color: #333;
            padding: 40px 50px;
            border-radius: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .header h1 {
            margin: 0;
            font-size: 2.8em;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header p {
            margin: 10px 0 0;
            color: #666;
            font-size: 1.1em;
            font-weight: 400;
        }
        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            max-width: none;
            margin: 0;
        }
        .nav-brand {
            font-size: 1.8em;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 25px;
        }
        .nav-links li a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
        }
        .nav-links li a:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateY(-2px);
        }
        .btn {
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s, color 0.3s;
            font-size: 14px;
        }
        .btn:hover {
            background: #f0f0f0;
        }
        .btn-primary {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: #fff;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-container button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-container button:hover {
            background: #0056b3;
        }
        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: right;
        }
        .total-section h3 {
            margin: 0;
            color: #333;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .product-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }
        .product-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        .product-item:hover {
            background: #f8f9fa;
        }
        .order-items {
            margin-top: 20px;
        }
        .order-items table {
            margin-top: 10px;
        }
        .quantity-input {
            width: 60px;
        }
        .main-content {
            display: grid;
            grid-template-columns: 2.5fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        .products-section {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .products-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            max-height: 650px;
            overflow-y: auto;
            padding-right: 10px;
        }
        .products-grid::-webkit-scrollbar {
            width: 6px;
        }
        .products-grid::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
        }
        .products-grid::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.3);
            border-radius: 3px;
        }
        .products-grid::-webkit-scrollbar-thumb:hover {
            background: rgba(102, 126, 234, 0.5);
        }
        .product-card {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
        }
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: rgba(102, 126, 234, 0.3);
        }
        .product-card:hover::before {
            transform: scaleX(1);
        }
        .product-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }
        .product-card.selected::before {
            transform: scaleX(1);
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        .product-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        .product-name {
            font-weight: 600;
            margin-bottom: 10px;
            color: #2d3748;
            font-size: 16px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-price {
            font-size: 18px;
            color: #38a169;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .product-price::before {
            content: '$';
            font-size: 14px;
            color: #68d391;
        }
        .product-stock {
            color: #718096;
            font-size: 13px;
            font-weight: 500;
            padding: 4px 8px;
            background: rgba(113, 128, 150, 0.1);
            border-radius: 6px;
            display: inline-block;
        }
        .product-stock.low-stock {
            color: #e53e3e;
            background: rgba(229, 62, 62, 0.1);
        }
        .search-box {
            margin-bottom: 25px;
            position: relative;
        }
        .search-box input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            font-size: 16px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            outline: none;
        }
        .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: rgba(255,255,255,1);
        }
        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #a0aec0;
            z-index: 1;
        }
        .cart-section {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        .cart-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        .cart-section h2 {
            margin: 0 0 20px 0;
            font-size: 1.5em;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
        }
        .cart-section h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.5em;
            font-weight: 600;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-name {
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        .cart-item-price {
            color: #28a745;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            background: #007bff;
            color: #fff;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .quantity-btn:hover {
            background: #0056b3;
        }
        .quantity-input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
        }
        .remove-btn {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        .remove-btn:hover {
            background: #c82333;
        }
        .cart-total {
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 20px;
            margin-top: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
        }
        .cart-total .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 0;
            color: #2d3748;
        }
        .cart-total .total-row span:last-child {
            color: #38a169;
        }
        .customer-select {
            margin-bottom: 20px;
        }
        .customer-select select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .customer-select select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .empty-cart {
            text-align: center;
            color: #a0aec0;
            padding: 60px 20px;
            font-size: 16px;
        }
        .empty-cart::before {
            content: '🛒';
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            margin-bottom: 10px;
            background: rgba(255,255,255,0.8);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }
        .cart-item:hover {
            background: rgba(255,255,255,0.9);
            transform: translateX(5px);
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        .cart-item-price {
            color: #718096;
            font-size: 14px;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 15px;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        .quantity-btn:hover {
            background: #5a67d8;
            transform: scale(1.1);
        }
        .quantity-input {
            width: 50px;
            padding: 6px;
            text-align: center;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            background: white;
        }
        .remove-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        .remove-btn:hover {
            background: #c53030;
            transform: scale(1.1);
        }
        .payment-options {
            margin: 20px 0;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .payment-options h3 {
            color: #2d3748;
            font-size: 1.3em;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .payment-options label {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            cursor: pointer;
            color: #4a5568;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .payment-options label:hover {
            background: rgba(255,255,255,0.2);
        }
        .payment-options input[type="radio"] {
            margin-right: 12px;
            accent-color: #667eea;
        }
        .checkout-btn {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: #fff;
            border: none;
            padding: 18px 20px;
            width: 100%;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .checkout-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #38a169, #2f855a);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
        }
        .checkout-btn:disabled {
            background: #a0aec0;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        .checkout-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .empty-cart {
            text-align: center;
            color: #666;
            padding: 40px 20px;
        }
        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0;
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            max-width: none;
            margin: 0;
        }
        .nav-brand {
            font-size: 1.5em;
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 5px;
        }
        .nav-menu li {
            position: relative;
        }
        .nav-menu li a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: block;
        }
        .nav-menu li a:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .nav-menu .dropdown {
            position: relative;
        }
        .nav-menu .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            min-width: 180px;
            z-index: 1001;
        }
        .nav-menu .dropdown:hover .dropdown-content {
            display: block;
        }
        .nav-menu .dropdown-content a {
            padding: 12px 16px;
            border-radius: 0;
            margin: 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .nav-menu .dropdown-content a:last-child {
            border-bottom: none;
        }
        .nav-menu .dropdown-content a:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }
        .hamburger span {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }
        .hamburger.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }
        .nav-menu.active {
            display: flex;
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.3);
            border-top: none;
            padding: 20px;
            gap: 10px;
        }
        .nav-menu.active .dropdown-content {
            position: static;
            display: block;
            box-shadow: none;
            border: none;
            background: transparent;
            margin-top: 10px;
        }
        .nav-menu.active .dropdown-content a {
            padding: 10px 20px;
        }
        @media (max-width: 1024px) {
            .main-content { grid-template-columns: 1fr; }
            .cart-section { position: static; }
            .container { padding: 20px; }
            .header { padding: 25px 20px; }
            .header h1 { font-size: 2em; }
            .products-section, .cart-section { padding: 20px; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
        }

        @media (max-width: 768px) {
            .navbar .container { padding: 10px 15px; }
            .nav-brand { font-size: 1.3em; }
            .nav-menu { display: none; }
            .hamburger { display: flex; }
            .container { padding: 15px; }
            .header { padding: 20px 15px; }
            .header h1 { font-size: 1.8em; }
            .products-section, .cart-section { padding: 15px; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
            .product-card { padding: 15px; }
            .product-name { font-size: 1em; }
            .product-price { font-size: 1.1em; }
            .cart-section h2 { font-size: 1.5em; }
            .cart-item { padding: 15px; }
            .cart-item-name { font-size: 1em; }
            .checkout-btn { padding: 15px; font-size: 1.1em; }
        }
            .product-name { font-size: 1em; }
            .product-price { font-size: 1.1em; }
            .cart-section h2 { font-size: 1.5em; }
            .cart-item { padding: 15px; }
            .cart-item-name { font-size: 1em; }
            .checkout-btn { padding: 15px; font-size: 1.1em; }
        }

        @media (max-width: 480px) {
            .navbar .container { flex-direction: row; justify-content: space-between; gap: 10px; }
            .nav-menu { display: none; }
            .hamburger { display: flex; }
            .header h1 { font-size: 1.5em; }
            .search-box input { padding: 12px; font-size: 1em; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
            .product-card { padding: 12px; }
            .product-image { height: 120px; }
            .product-name { font-size: 0.9em; }
            .product-price { font-size: 1em; }
            .product-stock { font-size: 0.8em; }
            .cart-section h2 { font-size: 1.3em; }
            .cart-item { padding: 12px; flex-direction: column; gap: 10px; }
            .cart-item-info { text-align: center; }
            .quantity-controls { justify-content: center; }
            .quantity-input { width: 60px; }
            .checkout-btn { padding: 12px; font-size: 1em; }
            .payment-options { text-align: center; }
            .payment-options label { display: block; margin: 8px 0; }
        }

        /* Beautiful Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fadeIn 0.3s ease-out;
        }
        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease-out;
            border: 1px solid rgba(255,255,255,0.2);
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 1.5em;
            font-weight: 600;
        }
        .modal-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 0.9em;
        }
        .modal-body {
            padding: 30px;
            text-align: center;
        }
        .modal-body .icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        .modal-body .message {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        .modal-footer {
            padding: 20px 30px 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            min-width: 100px;
        }
        .modal-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .modal-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .modal-btn-secondary {
            background: #6c757d;
            color: white;
        }
        .modal-btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: scale(0.9) translateY(-20px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes slideOut {
            from { transform: scale(1) translateY(0); opacity: 1; }
            to { transform: scale(0.9) translateY(-20px); opacity: 0; }
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'orders'; include __DIR__ . '/partials/navbar.php'; ?>
    <div class="container">
        <div class="header">
            <h1><?php echo __('order_creation'); ?></h1>
            <p><?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?></p>
        </div>

        <div class="main-content">
            <div class="products-section">
                <div class="search-box">
                    <input type="text" id="productSearch" placeholder="<?php echo __('search_products'); ?>" onkeyup="filterProducts()">
                </div>

                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock_quantity']; ?>)">
                            <img src="<?php echo $product['image'] ? htmlspecialchars(mc_url('uploads/products/' . $product['image'])) : htmlspecialchars(mc_url('public/images/no-image.svg')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-stock"><?php echo __('stock'); ?>: <?php echo $product['stock_quantity']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="cart-section">
                <h2><?php echo __('items_in_cart'); ?></h2>

                <div class="customer-select">
                    <select id="customerSelect" name="customer_id">
                        <option value=""><?php echo __('walk_in_customer'); ?></option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <form id="orderForm" method="POST" action="<?php echo mc_url($subdomain . '/pos/orders/create'); ?>">
                    <div id="cartItems" class="empty-cart">
                        <?php echo __('cart_empty_msg'); ?>
                    </div>

                    <div class="cart-total" id="cartTotal" style="display: none;">
                        <div class="total-row">
                            <span><?php echo __('total'); ?>:</span>
                            <span id="totalAmount">$0.00</span>
                        </div>
                    </div>

                    <div class="payment-options" id="paymentOptions" style="display: none;">
                        <h3><?php echo __('payment_method'); ?></h3>
                        <label><input type="radio" name="payment_method" value="cash" checked> <?php echo __('cash'); ?></label>
                        <label><input type="radio" name="payment_method" value="qr"> <?php echo __('qr_payment', ['default' => 'QR Payment']); ?></label>
                        <label><input type="radio" name="payment_method" value="card"> <?php echo __('card'); ?></label>
                    </div>

                    <input type="hidden" name="order_status" id="orderStatus" value="completed">

                    <button type="submit" class="checkout-btn" id="checkoutBtn" disabled><?php echo __('complete_order'); ?></button>
                    <button type="button" class="btn" id="saveDraftBtn" disabled><?php echo __('save_as_draft'); ?></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        let allProducts = <?php echo json_encode($products); ?>;
        const PRODUCT_IMAGE_BASE = '<?php echo mc_url('uploads/products/'); ?>';
        const PRODUCT_FALLBACK_IMAGE = '<?php echo mc_url('public/images/no-image.svg'); ?>';

        function filterProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const productsGrid = document.getElementById('productsGrid');

            productsGrid.innerHTML = '';

            allProducts.forEach(product => {
                if (product.name.toLowerCase().includes(searchTerm) || product.sku.toLowerCase().includes(searchTerm)) {
                    const productCard = document.createElement('div');
                    productCard.className = 'product-card';
                    productCard.onclick = () => addToCart(product.id, product.name, product.price, product.stock_quantity);
                    const imageSrc = product.image ? (PRODUCT_IMAGE_BASE + product.image) : PRODUCT_FALLBACK_IMAGE;
                    productCard.innerHTML = `
                        <img src="${imageSrc}" alt="${product.name}" class="product-image">
                        <div class="product-name">${product.name}</div>
                        <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
                        <div class="product-stock">Stock: ${product.stock_quantity}</div>
                    `;
                    productsGrid.appendChild(productCard);
                }
            });
        }

        function addToCart(productId, name, price, stock) {
            const existing = cart.find(item => item.product_id == productId);
            if (existing) {
                if (existing.quantity < stock) {
                    existing.quantity++;
                } else {
                    showModal('warning', 'Stock Warning', 'Not enough stock available for this product!', '📦');
                    return;
                }
            } else {
                if (stock > 0) {
                    cart.push({ product_id: productId, name: name, price: parseFloat(price), quantity: 1, stock: stock });
                } else {
                    showModal('error', 'Out of Stock', 'This product is currently out of stock!', '🚫');
                    return;
                }
            }
            updateCartDisplay();
        }

        function updateQuantity(productId, quantity) {
            const item = cart.find(item => item.product_id == productId);
            if (item) {
                quantity = parseInt(quantity);
                if (quantity <= 0) {
                    removeFromCart(productId);
                } else if (quantity <= item.stock) {
                    item.quantity = quantity;
                    updateCartDisplay();
                } else {
                    showModal('warning', 'Stock Warning', 'Not enough stock available for this product!', '📦');
                    updateCartDisplay();
                }
            }
        }

        function removeFromCart(productId) {
            cart = cart.filter(item => item.product_id != productId);
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const paymentOptions = document.getElementById('paymentOptions');
            const checkoutBtn = document.getElementById('checkoutBtn');

            if (cart.length === 0) {
                cartItems.innerHTML = '<div class="empty-cart">No items in cart</div>';
                cartTotal.style.display = 'none';
                paymentOptions.style.display = 'none';
                checkoutBtn.disabled = true;
                return;
            }

            cartItems.innerHTML = '';
            let total = 0;

            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                cartItems.innerHTML += `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">$${item.price.toFixed(2)} each</div>
                        </div>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="${item.stock}" onchange="updateQuantity(${item.product_id}, this.value)">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
                            <button type="button" class="remove-btn" onclick="removeFromCart(${item.product_id})">×</button>
                        </div>
                        <div style="font-weight: bold; color: #28a745;">$${itemTotal.toFixed(2)}</div>
                    </div>
                `;
            });

            // Add hidden inputs for form submission
            const form = document.getElementById('orderForm');
            form.innerHTML = form.innerHTML.replace(/<input type="hidden".*?>/g, ''); // Remove existing
            cart.forEach(item => {
                form.innerHTML += `<input type="hidden" name="items[${item.product_id}][product_id]" value="${item.product_id}">`;
                form.innerHTML += `<input type="hidden" name="items[${item.product_id}][quantity]" value="${item.quantity}">`;
            });

            document.getElementById('totalAmount').textContent = `$${total.toFixed(2)}`;
            cartTotal.style.display = 'block';
            paymentOptions.style.display = 'block';
            checkoutBtn.disabled = !(cart.length > 0);
            saveDraftBtn.disabled = !(cart.length > 0);
        }

        // Set up save draft button
        document.getElementById('saveDraftBtn').onclick = () => {
            document.getElementById('orderStatus').value = 'pending';
            document.getElementById('orderForm').submit();
        };

        function toggleMenu() {
            const hamburger = document.querySelector('.hamburger');
            const navMenu = document.querySelector('.nav-menu');
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        }

        // Beautiful Modal Functions
        function showModal(type, title, message, icon = '⚠️') {
            // Prefer shared POSUI modal for consistent design
            if (window.POSUI && typeof window.POSUI.alert === 'function') {
                var mapped = 'info';
                if (type === 'error') mapped = 'danger';
                if (type === 'warning') mapped = 'warning';
                if (type === 'success') mapped = 'success';
                window.POSUI.alert({
                    type: mapped,
                    title: title,
                    message: message,
                    okText: 'OK'
                });
                return;
            }

            // Remove existing modal if any
            const existingModal = document.querySelector('.modal-overlay');
            if (existingModal) {
                existingModal.remove();
            }

            // Set colors based on type
            let headerGradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            let btnClass = 'modal-btn-primary';

            switch(type) {
                case 'success':
                    headerGradient = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                    if (!icon || icon === '⚠️') icon = '✅';
                    break;
                case 'error':
                    headerGradient = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
                    if (!icon || icon === '⚠️') icon = '❌';
                    break;
                case 'warning':
                    headerGradient = 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)';
                    if (!icon || icon === '⚠️') icon = '⚠️';
                    break;
                case 'info':
                    headerGradient = 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)';
                    if (!icon || icon === '⚠️') icon = 'ℹ️';
                    break;
            }

            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header" style="background: ${headerGradient}">
                        <h3>${title}</h3>
                    </div>
                    <div class="modal-body">
                        <div class="icon">${icon}</div>
                        <div class="message">${message}</div>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn ${btnClass}" onclick="closeModal()">OK</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        function closeModal() {
            const modal = document.querySelector('.modal-overlay');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease-out';
                modal.querySelector('.modal-content').style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>