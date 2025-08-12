document.addEventListener("DOMContentLoaded", function() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const productId = urlParams.get('id');

    if (productId) {
        loadProductDetails(productId);
    } else {
        displayError('Product not found!');
    }

    function loadProductDetails(productId) {
        // Simulated API call
        const product = storeProducts.find(p => String(p.id) === String(productId));
        if (!product) {
            displayError('Product not found!');
        }

        document.getElementById('productTitle').textContent = product.name;
        document.getElementById('breadcrumbProduct').textContent = product.name;

        const productDetailContent = `
            <div class="product-images">
                <img src="${product.image || 'placeholder.jpg'}" alt="${product.name}" style="max-width: 100%; height: auto;">
            </div>
            <div class="product-info">
                <h1>${product.name}</h1>
                <p>${product.description}</p>
                <div class="product-price">
                    ${product.discount_price ? `<span class="original-price">KSh ${product.price.toLocaleString()}</span>` : ''}
                    <span>KSh ${product.discount_price || product.price.toLocaleString()}</span>
                </div>
                <button class="btn btn-primary" onclick="addToCart(${product.id})">Add to Cart</button>
            </div>
        `;

        document.getElementById('productDetailContent').innerHTML = productDetailContent;
        document.getElementById('loadingState').style.display = 'none';
    }

    function displayError(message) {
        document.getElementById('loadingState').innerHTML = `<p>${message}</p>`;
    }
});
