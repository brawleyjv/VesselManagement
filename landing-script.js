// Landing Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Update pricing based on quantity
    const quantitySelect = document.getElementById('license-quantity');
    if (quantitySelect) {
        quantitySelect.addEventListener('change', function() {
            updatePayPalButton();
        });
    }

    // Initialize PayPal button
    initializePayPal();
});

function updatePayPalButton() {
    const quantity = parseInt(document.getElementById('license-quantity').value);
    const prices = {
        1: 299,
        2: 549,
        5: 1199,
        10: 2099
    };
    
    const amount = prices[quantity] || 299;
    
    // Clear existing PayPal button
    document.getElementById('paypal-button-container').innerHTML = '';
    
    // Reinitialize PayPal with new amount
    initializePayPal(amount, quantity);
}

function initializePayPal(amount = 299, quantity = 1) {
    // Check if PayPal SDK is loaded
    if (typeof paypal === 'undefined') {
        console.log('PayPal SDK not loaded yet');
        return;
    }

    paypal.Buttons({
        createOrder: function(data, actions) {
            const customerName = document.getElementById('customer-name').value;
            const customerEmail = document.getElementById('customer-email').value;
            const companyName = document.getElementById('company-name').value;

            if (!customerName || !customerEmail) {
                alert('Please fill in your name and email address before proceeding.');
                return Promise.reject('Missing required fields');
            }

            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: amount.toString()
                    },
                    description: `Vessel Data Logger License (${quantity} license${quantity > 1 ? 's' : ''})`,
                    custom_id: `VDL-${Date.now()}`,
                    soft_descriptor: 'VesselDataLogger'
                }],
                application_context: {
                    shipping_preference: 'NO_SHIPPING'
                }
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Show success message
                showPaymentSuccess(details);
                
                // Send order details to our license generation system
                generateLicense(details);
            });
        },
        onError: function(err) {
            console.error('PayPal Error:', err);
            alert('Payment failed. Please try again or contact support.');
        },
        onCancel: function(data) {
            console.log('Payment cancelled by user');
        }
    }).render('#paypal-button-container');
}

function showPaymentSuccess(details) {
    const container = document.getElementById('paypal-button-container');
    container.innerHTML = `
        <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; text-align: center;">
            <h3>🎉 Payment Successful!</h3>
            <p><strong>Transaction ID:</strong> ${details.id}</p>
            <p>Your license key will be sent to your email address within 5 minutes.</p>
            <p>You can now download the software and use your license key during installation.</p>
            <div style="margin-top: 20px;">
                <a href="download.html" class="btn btn-primary">Download Software</a>
            </div>
        </div>
    `;
}

function generateLicense(paypalDetails) {
    const customerName = document.getElementById('customer-name').value;
    const customerEmail = document.getElementById('customer-email').value;
    const companyName = document.getElementById('company-name').value;
    const quantity = parseInt(document.getElementById('license-quantity').value);

    // API endpoint - use local for development, remote for production
    const apiUrl = window.location.hostname === 'localhost' 
        ? '/api/generate-license.php' 
        : 'https://logicdock.org/api/generate-license.php';
    
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            customer_name: customerName,
            customer_email: customerEmail,
            company_name: companyName,
            quantity: quantity,
            paypal_transaction_id: paypalDetails.id,
            paypal_payer_id: paypalDetails.payer.payer_id,
            amount_paid: paypalDetails.purchase_units[0].amount.value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('License generated successfully:', data.license_keys);
        } else {
            console.error('License generation failed:', data.error);
        }
    })
    .catch(error => {
        console.error('Error generating license:', error);
    });
}

// Form validation
function validateForm() {
    const name = document.getElementById('customer-name').value.trim();
    const email = document.getElementById('customer-email').value.trim();
    
    if (!name) {
        alert('Please enter your full name.');
        return false;
    }
    
    if (!email || !isValidEmail(email)) {
        alert('Please enter a valid email address.');
        return false;
    }
    
    return true;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Add loading states and animations
function addLoadingState(button) {
    button.innerHTML = '<span class="spinner"></span> Processing...';
    button.disabled = true;
}

function removeLoadingState(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
}
