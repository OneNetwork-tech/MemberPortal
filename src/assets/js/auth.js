// Authentication related JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    initAuthForms();
    initPasswordToggle();
    initAutoAddress();
    initFormValidation();
});

// Initialize auth forms
function initAuthForms() {
    const forms = document.querySelectorAll('.auth-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Password visibility toggle
function initPasswordToggle() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const passwordInput = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Show password');
            }
        });
    });
}

// Auto-address lookup based on postal code
function initAutoAddress() {
    const postalCodeInput = document.getElementById('postal_code');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    
    if (postalCodeInput && cityInput) {
        postalCodeInput.addEventListener('blur', function() {
            const postalCode = this.value.trim();
            
            if (postalCode.length === 5 && /^\d+$/.test(postalCode)) {
                fetchCityFromPostalCode(postalCode, cityInput, stateInput);
            }
        });
    }
}

function fetchCityFromPostalCode(postalCode, cityInput, stateInput) {
    // Swedish postal code to city mapping
    const postalCodeMap = {
        '11359': { city: 'Stockholm', state: 'Stockholm' },
        '11129': { city: 'Stockholm', state: 'Stockholm' },
        '10005': { city: 'Stockholm', state: 'Stockholm' },
        '10044': { city: 'Stockholm', state: 'Stockholm' },
        '21119': { city: 'Malmö', state: 'Skåne' },
        '41319': { city: 'Göteborg', state: 'Västra Götaland' },
        '58102': { city: 'Linköping', state: 'Östergötland' },
        '75229': { city: 'Uppsala', state: 'Uppsala' },
        '85178': { city: 'Sundsvall', state: 'Västernorrland' },
        '97234': { city: 'Luleå', state: 'Norrbotten' },
        '90325': { city: 'Umeå', state: 'Västerbotten' },
        '65225': { city: 'Karlstad', state: 'Värmland' },
        '79171': { city: 'Falun', state: 'Dalarna' },
        '55111': { city: 'Jönköping', state: 'Jönköping' },
        '39234': { city: 'Kalmar', state: 'Kalmar' },
        '37104': { city: 'Karlskrona', state: 'Blekinge' },
        '83145': { city: 'Östersund', state: 'Jämtland' },
        '96133': { city: 'Boden', state: 'Norrbotten' },
        '94185': { city: 'Piteå', state: 'Norrbotten' },
        '93134': { city: 'Skellefteå', state: 'Västerbotten' }
    };
    
    const addressInfo = postalCodeMap[postalCode];
    
    if (addressInfo) {
        if (cityInput && !cityInput.value) {
            cityInput.value = addressInfo.city;
        }
        if (stateInput && !stateInput.value) {
            stateInput.value = addressInfo.state;
        }
        
        showAutoFillMessage('Address information filled automatically');
    }
}

function showAutoFillMessage(message) {
    // Remove existing message
    const existingMessage = document.querySelector('.auto-fill-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'auto-fill-message';
    messageDiv.style.cssText = `
        background: #d1fae5;
        color: #065f46;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        border: 1px solid #a7f3d0;
    `;
    messageDiv.textContent = message;
    
    const postalCodeGroup = document.getElementById('postal_code').closest('.form-group');
    postalCodeGroup.appendChild(messageDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Special validation for personnummer
    const personnummerInput = form.querySelector('#personnummer');
    if (personnummerInput && personnummerInput.value) {
        if (!isValidPersonnummer(personnummerInput.value)) {
            showFieldError(personnummerInput, 'Please enter a valid Swedish personnummer');
            isValid = false;
        }
    }
    
    // Special validation for email
    const emailInput = form.querySelector('#email');
    if (emailInput && emailInput.value) {
        if (!isValidEmail(emailInput.value)) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        }
    }
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (field.type === 'email' && value && !isValidEmail(value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.style.borderColor = '#dc2626';
    field.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.cssText = `
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    `;
    
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        ${message}
    `;
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.style.borderColor = '';
    field.style.boxShadow = '';
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
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

// Format personnummer as user types
const personnummerInput = document.getElementById('personnummer');
if (personnummerInput) {
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
}

// Add loading state to submit buttons
document.addEventListener('submit', function(e) {
    const submitButton = e.target.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            Processing...
        `;
    }
});