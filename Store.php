<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jowaki Store - Professional Security Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Modern CSS Variables */
        :root {
            /* Color Palette */
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --secondary: #64748b;
            --accent: #10b981;
            --accent-dark: #059669;
            --success: #22c55e;
            --warning: #f59e0b;
            --error: #ef4444;
            
            /* Neutral Colors */
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            
            /* Typography */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --font-size-4xl: 2.25rem;
            
            /* Spacing */
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            --space-10: 2.5rem;
            --space-12: 3rem;
            --space-16: 4rem;
            --space-20: 5rem;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            
            /* Border Radius */
            --radius-sm: 0.25rem;
            --radius-md: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
            --radius-2xl: 1rem;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition-fast: 150ms ease;
            --transition-base: 250ms ease;
            --transition-slow: 350ms ease;
            
            /* Layout */
            --header-height: 80px;
            --sidebar-width: 320px;
            --container-max-width: 1400px;
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background: linear-gradient(135deg, var(--white) 0%, var(--gray-50) 100%);
            color: var(--gray-800);
            line-height: 1.6;
            font-size: var(--font-size-base);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--gray-200);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        header.scrolled {
            box-shadow: var(--shadow-lg);
        }

        .header-content {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: var(--space-8);
            padding: var(--space-4) var(--space-6);
            min-height: var(--header-height);
            max-width: var(--container-max-width);
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            text-decoration: none;
            transition: var(--transition-base);
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo-img {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            transition: var(--transition-base);
        }

        .logo:hover .logo-img {
            box-shadow: var(--shadow-lg);
            transform: rotate(5deg);
        }

        .logo-text {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--gray-800);
            letter-spacing: -0.025em;
            line-height: 1.2;
        }

        .main-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            text-decoration: none;
            color: var(--gray-600);
            font-weight: 500;
            font-size: var(--font-size-sm);
            padding: var(--space-3) var(--space-5);
            border-radius: var(--radius-full);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            white-space: nowrap;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: var(--radius-full);
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }

        .nav-link:hover::before {
            opacity: 0.1;
            transform: scale(1);
        }

        .nav-link:hover {
            color: var(--primary);
            transform: translateY(-2px);
        }

        .shop-link {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: var(--white);
            font-weight: 600;
            box-shadow: var(--shadow-md);
        }

        .shop-link::before {
            display: none;
        }

        .shop-link:hover {
            background: linear-gradient(135deg, var(--accent-dark), var(--accent));
            color: var(--white);
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        .profile-link {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            font-weight: 600;
            box-shadow: var(--shadow-md);
        }

        .profile-link::before {
            display: none;
        }

        .profile-link:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        /* Main Content */
        .store-main {
            padding-top: calc(var(--header-height) + var(--space-8));
            min-height: 100vh;
        }

        .store-container {
            max-width: var(--container-max-width);
            margin: 0 auto;
            padding: 0 var(--space-6);
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            gap: var(--space-8);
        }

        /* Sidebar Filter */
        .category-filter {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: var(--space-6);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            height: fit-content;
            position: sticky;
            top: calc(var(--header-height) + var(--space-4));
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-6);
        }

        .filter-header h3 {
            font-size: var(--font-size-xl);
            font-weight: 700;
            color: var(--gray-800);
        }

        .filter-close {
            display: none;
            background: none;
            border: none;
            color: var(--gray-500);
            font-size: var(--font-size-lg);
            cursor: pointer;
            padding: var(--space-2);
            border-radius: var(--radius-md);
            transition: var(--transition-base);
        }

        .filter-close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .filter-search {
            margin-bottom: var(--space-6);
        }



        .filter-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
            margin-bottom: var(--space-6);
        }

        .filter-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-4);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            background: var(--white);
            color: var(--gray-700);
            font-weight: 500;
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
        }

        .filter-btn:hover {
            border-color: var(--primary);
            background: var(--gray-50);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .filter-icon {
            font-size: var(--font-size-lg);
            margin-right: var(--space-3);
        }

        .filter-text {
            flex: 1;
        }

        .filter-count {
            background: var(--gray-200);
            color: var(--gray-700);
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }

        .filter-btn.active .filter-count {
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
        }

        /* Price Filter */
        .price-filter {
            border-top: 1px solid var(--gray-200);
            padding-top: var(--space-6);
        }

        .price-filter h4 {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: var(--space-4);
        }

        .price-range {
            margin-bottom: var(--space-4);
        }

        .price-slider {
            width: 100%;
            height: 6px;
            border-radius: var(--radius-full);
            background: var(--gray-200);
            outline: none;
            -webkit-appearance: none;
            margin-bottom: var(--space-3);
        }

        .price-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            cursor: pointer;
            box-shadow: var(--shadow-md);
        }

        .price-inputs {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .price-input {
            flex: 1;
            padding: var(--space-2) var(--space-3);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            text-align: center;
        }

        .price-input:focus {
            border-color: var(--primary);
            outline: none;
        }

        /* Products Section */
        .products-section {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--space-8);
            gap: var(--space-6);
        }

        .header-left h2 {
            font-size: var(--font-size-3xl);
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: var(--space-2);
            background: linear-gradient(135deg, var(--gray-900), var(--gray-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            color: var(--gray-600);
            font-size: var(--font-size-lg);
            margin-bottom: var(--space-4);
        }

        .results-info {
            color: var(--gray-500);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .sort-dropdown select {
            padding: var(--space-3) var(--space-4);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-xl);
            background: var(--white);
            color: var(--gray-800);
            font-size: var(--font-size-sm);
            font-weight: 500;
            cursor: pointer;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        .sort-dropdown select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1), var(--shadow-md);
        }

        .view-options {
            display: flex;
            gap: var(--space-2);
        }

        .view-btn {
            padding: var(--space-3) var(--space-4);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-xl);
            background: var(--white);
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
        }

        .view-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .view-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .view-btn:active {
            transform: translateY(0);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--space-6);
        }

        .product-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-2xl);
            border-color: var(--primary);
        }

        .product-image {
            position: relative;
            height: 240px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badges {
            position: absolute;
            top: var(--space-4);
            left: var(--space-4);
            display: flex;
            gap: var(--space-2);
        }

        .product-badge {
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .product-badge.sale {
            background: linear-gradient(135deg, var(--error), #dc2626);
            color: var(--white);
        }

        .product-badge.new {
            background: linear-gradient(135deg, var(--success), var(--accent));
            color: var(--white);
        }

        .product-badge.hot {
            background: linear-gradient(135deg, var(--warning), #f97316);
            color: var(--white);
        }

        .product-content {
            padding: var(--space-6);
        }

        .product-category {
            color: var(--primary);
            font-size: var(--font-size-sm);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: var(--space-2);
        }

        .product-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--space-3);
            line-height: 1.3;
        }

        .product-description {
            color: var(--gray-600);
            font-size: var(--font-size-sm);
            line-height: 1.5;
            margin-bottom: var(--space-4);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-4);
        }

        .product-price {
            display: flex;
            align-items: baseline;
            gap: var(--space-2);
        }

        .current-price {
            font-size: var(--font-size-xl);
            font-weight: 800;
            color: var(--gray-900);
        }

        .original-price {
            font-size: var(--font-size-sm);
            color: var(--gray-500);
            text-decoration: line-through;
        }

        .product-actions {
            display: flex;
            gap: var(--space-3);
        }

        .btn-primary {
            flex: 1;
            padding: var(--space-4) var(--space-6);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: var(--radius-xl);
            font-weight: 600;
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            box-shadow: var(--shadow-md);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            padding: var(--space-3) var(--space-5);
            background: var(--white);
            color: var(--gray-700);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-xl);
            font-weight: 600;
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary:active {
            transform: translateY(0);
        }

        /* Loading State */
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-20);
            color: var(--gray-500);
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--gray-200);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: var(--space-4);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Mobile Filter Toggle */
        .mobile-filter-toggle {
            display: none;
        }

        .filter-toggle-btn {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-4) var(--space-6);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: var(--radius-xl);
            font-weight: 600;
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-md);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filter-toggle-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        .filter-toggle-btn:active {
            transform: translateY(-1px);
        }

        /* WhatsApp Float */
        .whatsapp-float {
            position: fixed;
            bottom: var(--space-6);
            right: var(--space-6);
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: var(--white);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            text-align: center;
            font-size: var(--font-size-xl);
            box-shadow: var(--shadow-xl);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .whatsapp-float:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-2xl);
        }

        /* Floating Cart */
        .floating-cart-button {
            position: fixed;
            bottom: var(--space-6);
            left: var(--space-6);
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: var(--white);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            text-align: center;
            font-size: var(--font-size-xl);
            box-shadow: var(--shadow-xl);
            z-index: 1000;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .floating-cart-button:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-2xl);
        }

        .floating-cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--error);
            color: var(--white);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xs);
            font-weight: 700;
            border: 2px solid var(--white);
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 1;
            visibility: visible;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: var(--shadow-2xl);
            transform: scale(1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal.hidden .modal-content {
            transform: scale(0.9);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-6);
            padding-bottom: var(--space-4);
            border-bottom: 1px solid var(--gray-200);
        }

        .modal-header h2 {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--gray-900);
        }

        .btn-close {
            background: none;
            border: none;
            font-size: var(--font-size-xl);
            color: var(--gray-500);
            cursor: pointer;
            padding: var(--space-2);
            border-radius: var(--radius-md);
            transition: var(--transition-base);
        }

        .btn-close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .store-container {
                grid-template-columns: 1fr;
                gap: var(--space-6);
            }

            .mobile-filter-toggle {
                display: block;
                margin-bottom: var(--space-6);
            }

            .category-filter {
                position: fixed;
                top: 0;
                left: -100%;
                width: 320px;
                height: 100vh;
                z-index: 1500;
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                overflow-y: auto;
            }

            .category-filter.active {
                left: 0;
            }

            .filter-close {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .store-container {
                padding: 0 var(--space-4);
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: var(--space-4);
            }

            .section-header {
                flex-direction: column;
                gap: var(--space-4);
            }

            .header-right {
                width: 100%;
                justify-content: space-between;
            }

            .whatsapp-float,
            .floating-cart-button {
                width: 56px;
                height: 56px;
                font-size: var(--font-size-lg);
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-content {
                padding: var(--space-4);
            }

            .product-actions {
                flex-direction: column;
            }

            .whatsapp-float,
            .floating-cart-button {
                width: 52px;
                height: 52px;
                font-size: var(--font-size-base);
            }
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Notification */
        .notification {
            position: fixed;
            top: var(--space-6);
            right: var(--space-6);
            padding: var(--space-4) var(--space-6);
            border-radius: var(--radius-lg);
            color: var(--white);
            font-weight: 600;
            z-index: 3000;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-xl);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: linear-gradient(135deg, var(--success), var(--accent));
        }

        .notification.error {
            background: linear-gradient(135deg, var(--error), #dc2626);
        }

        .notification.warning {
            background: linear-gradient(135deg, var(--warning), #f97316);
        }
    </style>
</head>
<body>
    <?php include 'store_header.php'; ?>

    <!-- Main Content -->
    <main class="store-main">
        <div class="store-container">
            <!-- Mobile Filter Toggle -->
            <div class="mobile-filter-toggle">
                <button class="filter-toggle-btn" onclick="toggleMobileFilters()">
                    <i class="fas fa-filter"></i>
                    <span>Filters</span>
                </button>
            </div>

            <!-- Category Filter -->
            <aside class="category-filter" id="categoryFilter">
                <div class="filter-header">
                    <h3>Categories</h3>
                    <button class="filter-close" onclick="toggleMobileFilters()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                

                
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterProducts('all')">
                        <span class="filter-icon">🛍️</span>
                        <span class="filter-text">All Products</span>
                        <span class="filter-count" id="count-all">0</span>
                    </button>
                    <button class="filter-btn" onclick="filterProducts('fencing')">
                        <span class="filter-icon">⚡</span>
                        <span class="filter-text">Electric Fencing</span>
                        <span class="filter-count" id="count-fencing">0</span>
                    </button>
                    <button class="filter-btn" onclick="filterProducts('alarms')">
                        <span class="filter-icon">🚨</span>
                        <span class="filter-text">Alarm Systems</span>
                        <span class="filter-count" id="count-alarms">0</span>
                    </button>
                    <button class="filter-btn" onclick="filterProducts('cctv')">
                        <span class="filter-icon">📹</span>
                        <span class="filter-text">CCTV Systems</span>
                        <span class="filter-count" id="count-cctv">0</span>
                    </button>
                    <button class="filter-btn" onclick="filterProducts('gates')">
                        <span class="filter-icon">🚪</span>
                        <span class="filter-text">Automated Gates</span>
                        <span class="filter-count" id="count-gates">0</span>
                    </button>
                    <button class="filter-btn" onclick="filterProducts('razor')">
                        <span class="filter-icon">🔪</span>
                        <span class="filter-text">Razor Wire</span>
                        <span class="filter-count" id="count-razor">0</span>
                    </button>
                </div>
                
                <!-- Price Filter -->
                <div class="price-filter">
                    <h4>Price Range</h4>
                    <div class="price-range">
                        <input type="range" id="priceMin" min="0" max="100000" value="0" class="price-slider">
                        <input type="range" id="priceMax" min="0" max="100000" value="100000" class="price-slider">
                    </div>
                    <div class="price-inputs">
                        <input type="number" id="minPrice" placeholder="Min" class="price-input">
                        <span>-</span>
                        <input type="number" id="maxPrice" placeholder="Max" class="price-input">
                    </div>
                </div>
            </aside>

            <!-- Products Section -->
            <section class="products-section">
                <div class="section-header">
                    <div class="header-left">
                        <h2>Our Products</h2>
                        <p class="section-subtitle">Professional security equipment and solutions</p>
                        <div class="results-info">
                            <span id="productsCount">0</span> products found
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="sort-dropdown">
                            <select id="sortSelect" onchange="sortProducts()">
                                <option value="featured">Featured</option>
                                <option value="name">Name A-Z</option>
                                <option value="name-desc">Name Z-A</option>
                                <option value="price-low">Price Low to High</option>
                                <option value="price-high">Price High to Low</option>
                                <option value="newest">Newest First</option>
                            </select>
                        </div>
                        <div class="view-options">
                            <button class="view-btn active" onclick="setView('grid')" title="Grid View">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn" onclick="setView('list')" title="List View">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div class="loading-state" id="loadingState">
                    <div class="loading-spinner"></div>
                    <p>Loading products...</p>
                </div>

                <!-- Products Grid -->
                <div class="products-grid" id="productsGrid">
                    <!-- Products will be loaded here -->
                </div>


            </section>
        </div>
    </main>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/0721442248?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20inquire%20about%20your%20products." class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Floating Cart Button -->
    <div id="floatingCartButton" class="floating-cart-button" onclick="window.location.href='cart.php'" style="display: none;">
        <i class="fas fa-shopping-cart"></i>
        <span id="floatingCartCount" class="floating-cart-count" style="display: none;">0</span>
    </div>

    <!-- Product Detail Modal -->
    <div id="productDetailModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productDetailTitle">Product Details</h2>
                <button onclick="hideProductDetail()" class="btn-close">✕</button>
            </div>
            <div id="productDetailContent">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script src="store.js"></script>
    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile filter toggle
        function toggleMobileFilters() {
            const filter = document.getElementById('categoryFilter');
            filter.classList.toggle('active');
        }

        // Close mobile filters when clicking outside
        document.addEventListener('click', (e) => {
            const filter = document.getElementById('categoryFilter');
            const toggleBtn = document.querySelector('.filter-toggle-btn');
            
            if (!filter.contains(e.target) && !toggleBtn.contains(e.target)) {
                filter.classList.remove('active');
            }
        });

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>