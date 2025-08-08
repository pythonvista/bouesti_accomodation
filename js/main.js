// Main JavaScript for BOUESTI Housing System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initImageCarousel();
    initPropertyFilter();
    initFormValidation();
    initMobileMenu();
    initTooltips();
});

// Image Carousel for Property Details
function initImageCarousel() {
    const carousels = document.querySelectorAll('.image-carousel');
    
    carousels.forEach(carousel => {
        const images = carousel.querySelectorAll('.carousel-image');
        const prevBtn = carousel.querySelector('.carousel-nav.prev');
        const nextBtn = carousel.querySelector('.carousel-nav.next');
        let currentIndex = 0;
        
        if (images.length <= 1) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return;
        }
        
        function showImage(index) {
            images.forEach((img, i) => {
                img.style.display = i === index ? 'block' : 'none';
            });
        }
        
        function nextImage() {
            currentIndex = (currentIndex + 1) % images.length;
            showImage(currentIndex);
        }
        
        function prevImage() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(currentIndex);
        }
        
        if (prevBtn) prevBtn.addEventListener('click', prevImage);
        if (nextBtn) nextBtn.addEventListener('click', nextImage);
        
        // Auto-advance carousel
        setInterval(nextImage, 5000);
        
        // Show first image initially
        showImage(0);
    });
}

// Property Filtering
function initPropertyFilter() {
    const searchInput = document.getElementById('property-search');
    const typeFilter = document.getElementById('property-type');
    const maxRentFilter = document.getElementById('max-rent');
    const propertyCards = document.querySelectorAll('.property-card');
    
    function filterProperties() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const selectedType = typeFilter ? typeFilter.value : '';
        const maxRent = maxRentFilter ? parseFloat(maxRentFilter.value) : Infinity;
        
        propertyCards.forEach(card => {
            const title = card.querySelector('.property-title')?.textContent.toLowerCase() || '';
            const address = card.querySelector('.property-address')?.textContent.toLowerCase() || '';
            const type = card.dataset.type || '';
            const rent = parseFloat(card.dataset.rent) || 0;
            
            const matchesSearch = !searchTerm || title.includes(searchTerm) || address.includes(searchTerm);
            const matchesType = !selectedType || type === selectedType;
            const matchesRent = rent <= maxRent;
            
            if (matchesSearch && matchesType && matchesRent) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        updatePropertyCount();
    }
    
    function updatePropertyCount() {
        const visibleCards = document.querySelectorAll('.property-card[style*="block"], .property-card:not([style*="none"])');
        const countElement = document.getElementById('property-count');
        if (countElement) {
            countElement.textContent = visibleCards.length;
        }
    }
    
    if (searchInput) searchInput.addEventListener('input', filterProperties);
    if (typeFilter) typeFilter.addEventListener('change', filterProperties);
    if (maxRentFilter) maxRentFilter.addEventListener('input', filterProperties);
    
    // Initial count
    updatePropertyCount();
}

// Form Validation
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearFieldError(input));
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    clearFieldError(field);
    
    // Required field validation
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation
    if (field.name === 'phone' && value) {
        const phoneRegex = /^[0-9]{10,11}$/;
        const cleanPhone = value.replace(/[^0-9]/g, '');
        if (!phoneRegex.test(cleanPhone)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    // Password validation
    if (type === 'password' && value) {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters long');
            return false;
        }
    }
    
    // Confirm password validation
    if (field.name === 'confirm_password' && value) {
        const passwordField = field.form.querySelector('input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            showFieldError(field, 'Passwords do not match');
            return false;
        }
    }
    
    return true;
}

function showFieldError(field, message) {
    field.classList.add('border-red-500');
    
    let errorElement = field.parentNode.querySelector('.field-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'field-error text-red-500 text-sm mt-1';
        field.parentNode.appendChild(errorElement);
    }
    errorElement.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('border-red-500');
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

// Mobile Menu Toggle
function initMobileMenu() {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Tooltips
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = e.target.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip absolute bg-gray-900 text-white text-sm px-2 py-1 rounded z-50';
    tooltip.textContent = tooltipText;
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    
    e.target.tooltip = tooltip;
}

function hideTooltip(e) {
    if (e.target.tooltip) {
        e.target.tooltip.remove();
        e.target.tooltip = null;
    }
}

// AJAX Functions
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error('Request failed'));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        if (data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    });
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Loading States
function showLoading(element) {
    element.disabled = true;
    element.dataset.originalText = element.textContent;
    element.innerHTML = '<span class="spinner"></span> Loading...';
}

function hideLoading(element) {
    element.disabled = false;
    element.textContent = element.dataset.originalText;
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-black' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// File Upload Preview
function initFileUploadPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const previewContainer = document.getElementById(this.dataset.preview);
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            Array.from(this.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-20 h-20 object-cover rounded mr-2 mb-2';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    });
}

// Initialize file upload preview
document.addEventListener('DOMContentLoaded', initFileUploadPreview);
