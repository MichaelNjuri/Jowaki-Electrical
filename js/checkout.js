// Checkout Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Set default selections
    selectDelivery('standard');
    selectPayment('mpesa');
});

// Delivery method selection
function selectDelivery(method) {
    document.querySelectorAll('.delivery-option').forEach(option => {
        option.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    document.querySelector(`input[value="${method}"]`).checked = true;
    
    // Update total based on delivery method
    updateTotal(method);
}

// Update total based on delivery method
function updateTotal(deliveryMethod) {
    const { subtotal, tax, standardDeliveryFee, expressDeliveryFee } = window.checkoutData;
    
    const deliveryFee = deliveryMethod === 'express' ? expressDeliveryFee : standardDeliveryFee;
    const total = subtotal + tax + deliveryFee;
    
    // Update delivery fee display
    document.querySelector('.summary-row:nth-child(3) span:last-child').textContent = `KSh ${deliveryFee.toFixed(2)}`;
    
    // Update total display
    document.querySelector('.summary-row.total span:last-child').textContent = `KSh ${total.toFixed(2)}`;
}

// Payment method selection
function selectPayment(method) {
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    document.querySelector(`input[value="${method}"]`).checked = true;
}

// Place order function
async function placeOrder() {
    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Show loading
    document.getElementById('loading').classList.add('show');
    document.querySelector('.place-order-btn').disabled = true;
    
    try {
        const { subtotal, tax, standardDeliveryFee, expressDeliveryFee, cart } = window.checkoutData;
        
        // Calculate delivery fee based on selected method
        const deliveryMethod = formData.get('delivery_method');
        const deliveryFee = deliveryMethod === 'express' ? expressDeliveryFee : standardDeliveryFee;
        
        // Recalculate total with correct delivery fee
        const total = subtotal + tax + deliveryFee;
        
        // Prepare order data
        const orderData = {
            customer_info: {
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                address: formData.get('address'),
                city: formData.get('city'),
                postalCode: formData.get('postalCode')
            },
            cart: cart,
            subtotal: subtotal,
            tax: tax,
            delivery_fee: deliveryFee,
            total: total,
            delivery_method: deliveryMethod,
            payment_method: formData.get('payment_method'),
            notes: formData.get('notes'),
            order_date: new Date().toISOString()
        };
        
        // Send order to server
        const response = await fetch('API/place_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Redirect to thank you page
            window.location.href = `thankyou.php?order_id=${result.order_id}`;
        } else {
            throw new Error(result.error || 'Failed to place order');
        }
        
    } catch (error) {
        console.error('Error placing order:', error);
        alert('Error placing order: ' + error.message);
        
        // Hide loading
        document.getElementById('loading').classList.remove('show');
        document.querySelector('.place-order-btn').disabled = false;
    }
}





