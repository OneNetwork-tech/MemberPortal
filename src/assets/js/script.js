// assets/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const postalCodeInput = document.getElementById('postal_code');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    
    // Format personnummer as user types
    const personnummerInput = document.getElementById('personnummer');
    personnummerInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        
        if (value.length > 8) {
            if (value.length <= 12) {
                value = value.replace(/(\d{4})(\d{2})(\d{2})(\d+)/, '$1$2$3-$4');
            } else {
                value = value.substring(0, 12);
            }
        }
        e.target.value = value;
    });
    
    // Auto-fill address based on postal code
    postalCodeInput.addEventListener('blur', function() {
        const postalCode = postalCodeInput.value.trim();
        
        if (postalCode.length >= 5) {
            fetchCityFromPostalCode(postalCode);
        }
    });
    
    function fetchCityFromPostalCode(postalCode) {
        // Simulate API call - in production, use a real Swedish postal service API
        const postalCodeMap = {
            '11359': { city: 'Stockholm', state: 'Stockholm' },
            '11129': { city: 'Stockholm', state: 'Stockholm' },
            '21119': { city: 'Malmö', state: 'Skåne' },
            '41319': { city: 'Göteborg', state: 'Västra Götaland' }
        };
        
        if (postalCodeMap[postalCode]) {
            cityInput.value = postalCodeMap[postalCode].city;
            stateInput.value = postalCodeMap[postalCode].state;
        }
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
    
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        clearErrors();
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                showError(field, 'This field is required');
                isValid = false;
            }
        });
        
        // Validate email
        const email = document.getElementById('email');
        if (email.value && !isValidEmail(email.value)) {
            showError(email, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate personnummer
        const personnummer = document.getElementById('personnummer');
        if (personnummer.value && !isValidPersonnummer(personnummer.value)) {
            showError(personnummer, 'Please enter a valid Swedish personnummer');
            isValid = false;
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function isValidPersonnummer(personnummer) {
        // Basic Swedish personnummer format validation
        const pnrRegex = /^\d{6}-\d{4}$|^\d{8}-\d{4}$/;
        return pnrRegex.test(personnummer);
    }
    
    function showError(field, message) {
        field.style.borderColor = '#e32';
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.style.color = '#e32';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearErrors() {
        const errors = document.querySelectorAll('.form-error');
        errors.forEach(error => error.remove());
        
        const fields = form.querySelectorAll('input');
        fields.forEach(field => field.style.borderColor = '#ddd');
    }
});