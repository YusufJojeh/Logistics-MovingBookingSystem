// ===================================
// LOGISTICS & MOVING BOOKING SYSTEM
// Modern Professional Frontend JavaScript 2025
// ===================================

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Logistics & Moving Booking System - Modern Professional Frontend Loaded');
    
    // Initialize all components
    initDesignSystem();
    initAnimations();
    initNavigation();
    initTables();
    initForms();
    initModals();
    initToastSystem();
    initKPICards();
    initFAQAccordion();
    initPerformance();
    initAccessibility();
    initEnhancedComponents();
});

// ===================================
// DESIGN SYSTEM INITIALIZATION
// ===================================
function initDesignSystem() {
    // Add CSS custom properties for dynamic theming
    const root = document.documentElement;
    
    // Set theme colors - Sapphire Drift
    root.style.setProperty('--primary', '#4F86C6');
    root.style.setProperty('--primary-dark', '#3D6B9E');
    root.style.setProperty('--secondary', '#7BA7D9');
    root.style.setProperty('--accent', '#A8C6F0');
    
    // Add smooth scrolling
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Initialize glassmorphism effects
    initGlassmorphism();
    
    // Initialize scroll effects
    initScrollEffects();
}

function initGlassmorphism() {
    // Add glassmorphism effect to cards
    const cards = document.querySelectorAll('.glass-card, .stat-glass, .service-3d-card, .feature-glass-card, .testimonial-glass-card, .kpi-card');
    
    cards.forEach(card => {
        // Add subtle animation on load
        card.style.opacity = '0';
        card.style.transform = 'translateY(40px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
        
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-12px) scale(1.02)';
            this.style.boxShadow = '0 35px 60px -15px rgba(0, 0, 0, 0.3)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 12px 40px rgba(0, 0, 0, 0.15)';
        });
    });
}

function initScrollEffects() {
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar-glass');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
}

// ===================================
// ENHANCED ANIMATION SYSTEM
// ===================================
function initAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe all cards and sections
    document.querySelectorAll('.glass-card, .stat-glass, .service-3d-card, .feature-glass-card, .testimonial-glass-card, .kpi-card, section').forEach(el => {
        observer.observe(el);
    });
    
    // Add staggered animations
    const staggeredElements = document.querySelectorAll('.timeline-step, .service-3d-card, .kpi-card');
    staggeredElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.15}s`;
    });
    
    // Add pulse animation to important elements
    const pulseElements = document.querySelectorAll('.btn-primary, .kpi-icon');
    pulseElements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            this.classList.add('pulse');
        });
        
        el.addEventListener('mouseleave', function() {
            this.classList.remove('pulse');
        });
    });
}

// ===================================
// ENHANCED NAVIGATION SYSTEM
// ===================================
function initNavigation() {
    // Smooth scrolling for anchor links
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
    
    // Active navigation highlighting
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');
    
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
    
    // Mobile menu enhancements
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
        
        // Close mobile menu when clicking on a link
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navbarCollapse.classList.remove('show');
            });
        });
    }
}

// ===================================
// ENHANCED TABLE SYSTEM
// ===================================
function initTables() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        // Add sorting functionality
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const columnIndex = Array.from(header.parentElement.children).indexOf(header);
                sortTable(table, columnIndex);
            });
            
            // Add sort indicator
            header.innerHTML += ' <i class="bi bi-arrow-down-up"></i>';
        });
        
        // Add hover effects
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
                this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelector(`th:nth-child(${columnIndex + 1})`);
    
    // Toggle sort direction
    const isAscending = header.classList.contains('sort-asc');
    
    // Remove sort classes from all headers
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // Add sort class to current header
    header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();
        
        // Try to parse as numbers first
        const aNum = parseFloat(aValue);
        const bNum = parseFloat(bValue);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? bNum - aNum : aNum - bNum;
        }
        
        // Otherwise sort as strings
        return isAscending ? 
            bValue.localeCompare(aValue) : 
            aValue.localeCompare(bValue);
    });
    
    // Reorder rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Update sort indicator
    const icon = header.querySelector('i');
    icon.className = isAscending ? 'bi bi-arrow-down' : 'bi bi-arrow-up';
}

// ===================================
// ENHANCED FORM SYSTEM
// ===================================
function initForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Add validation
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearFieldError(input));
        });
        
        // Add loading states
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                submitBtn.classList.add('loading');
            }
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
    if (type === 'email' && value && !isValidEmail(value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    // Password validation
    if (type === 'password' && value && value.length < 6) {
        showFieldError(field, 'Password must be at least 6 characters');
        return false;
    }
    
    return true;
}

function showFieldError(field, message) {
    // Remove existing error
    clearFieldError(field);
    
    // Add error styling
    field.classList.add('is-invalid');
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    errorDiv.style.color = '#C67C7C';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
    
    // Show toast notification
    showToast(message, 'error');
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// ===================================
// ENHANCED MODAL SYSTEM
// ===================================
function initModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        // Add entrance animation
        modal.addEventListener('show.bs.modal', function() {
            const modalContent = this.querySelector('.modal-content');
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                modalContent.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                modalContent.style.opacity = '1';
                modalContent.style.transform = 'scale(1)';
            }, 10);
        });
        
        // Add exit animation
        modal.addEventListener('hide.bs.modal', function() {
            const modalContent = this.querySelector('.modal-content');
            modalContent.style.transform = 'scale(0.8)';
            modalContent.style.opacity = '0';
        });
        
        // Add backdrop blur effect
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.style.backdropFilter = 'blur(10px)';
            backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        }
    });
}

// ===================================
// TOAST NOTIFICATION SYSTEM
// ===================================
function initToastSystem() {
    // Create toast container if it doesn't exist
    if (!document.querySelector('.toast-container')) {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
}

function showToast(message, type = 'info', duration = 5000) {
    const toastContainer = document.querySelector('.toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    
    const icons = {
        success: 'bi-check-circle',
        error: 'bi-exclamation-triangle',
        warning: 'bi-exclamation-triangle',
        info: 'bi-info-circle'
    };
    
    toast.innerHTML = `
        <div class="toast-header">
            <i class="bi ${icons[type] || icons.info} me-2"></i>
            <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, duration);
    
    // Manual close
    const closeBtn = toast.querySelector('.btn-close');
    closeBtn.addEventListener('click', () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    });
}

// ===================================
// KPI CARDS SYSTEM
// ===================================
function initKPICards() {
    const kpiCards = document.querySelectorAll('.kpi-card');
    
    kpiCards.forEach(card => {
        const numberElement = card.querySelector('.kpi-number');
        if (numberElement) {
            const finalNumber = parseInt(numberElement.textContent.replace(/[^0-9]/g, ''));
            animateNumber(numberElement, 0, finalNumber, 2000);
        }
        
        // Add click effect
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'translateY(-8px)';
            }, 150);
        });
    });
}

function animateNumber(element, start, end, duration) {
    const startTime = performance.now();
    const startValue = start;
    const change = end - start;
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentValue = Math.floor(startValue + change * easeOutQuart);
        
        element.textContent = currentValue.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    }
    
    requestAnimationFrame(updateNumber);
}

// ===================================
// FAQ ACCORDION SYSTEM
// ===================================
function initFAQAccordion() {
    const accordionItems = document.querySelectorAll('.faq-accordion .accordion-item');
    
    accordionItems.forEach(item => {
        const button = item.querySelector('.accordion-button');
        const body = item.querySelector('.accordion-body');
        
        if (button && body) {
            button.addEventListener('click', function() {
                // Add animation to body
                if (body.style.maxHeight) {
                    body.style.maxHeight = null;
                } else {
                    body.style.maxHeight = body.scrollHeight + 'px';
                }
                
                // Add icon rotation
                const icon = this.querySelector('i');
                if (icon) {
                    icon.style.transform = body.style.maxHeight ? 'rotate(180deg)' : 'rotate(0deg)';
                }
            });
        }
    });
}

// ===================================
// PERFORMANCE OPTIMIZATION
// ===================================
function initPerformance() {
    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Debounce scroll events
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            // Handle scroll-based operations
        }, 100);
    });
    
    // Preload critical resources
    const criticalResources = [
        '../assets/css/style.css',
        '../assets/js/main.js'
    ];
    
    criticalResources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = resource;
        link.as = resource.endsWith('.css') ? 'style' : 'script';
        document.head.appendChild(link);
    });
}

// ===================================
// ACCESSIBILITY ENHANCEMENTS
// ===================================
function initAccessibility() {
    // Add skip to main content link
    if (!document.querySelector('.skip-link')) {
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link';
        skipLink.textContent = 'Skip to main content';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10000;
        `;
        document.body.insertBefore(skipLink, document.body.firstChild);
    }
    
    // Add focus indicators
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
    
    // Add ARIA labels
    const buttons = document.querySelectorAll('button:not([aria-label])');
    buttons.forEach(button => {
        if (button.textContent.trim()) {
            button.setAttribute('aria-label', button.textContent.trim());
        }
    });
    
    // Add live regions for dynamic content
    const liveRegion = document.createElement('div');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.style.cssText = 'position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;';
    document.body.appendChild(liveRegion);
}

// ===================================
// ENHANCED COMPONENTS
// ===================================
function initEnhancedComponents() {
    // Enhanced KPI Cards with real-time updates
    initEnhancedKPICards();
    
    // Enhanced search functionality
    initEnhancedSearch();
    
    // Enhanced data tables
    initEnhancedDataTables();
    
    // Enhanced form components
    initEnhancedFormComponents();
}

function initEnhancedKPICards() {
    const kpiCards = document.querySelectorAll('.kpi-card');
    
    kpiCards.forEach(card => {
        // Add real-time update simulation
        setInterval(() => {
            const numberElement = card.querySelector('.kpi-number');
            if (numberElement && Math.random() > 0.95) { // 5% chance to update
                const currentValue = parseInt(numberElement.textContent.replace(/[^0-9]/g, ''));
                const newValue = currentValue + Math.floor(Math.random() * 10);
                animateNumber(numberElement, currentValue, newValue, 1000);
            }
        }, 5000);
        
        // Add tooltip functionality
        const tooltip = card.getAttribute('data-tooltip');
        if (tooltip) {
            card.addEventListener('mouseenter', function() {
                showTooltip(this, tooltip);
            });
            
            card.addEventListener('mouseleave', function() {
                hideTooltip();
            });
        }
    });
}

function initEnhancedSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
        
        // Add search suggestions
        input.addEventListener('focus', function() {
            showSearchSuggestions(this);
        });
    });
}

function initEnhancedDataTables() {
    const tables = document.querySelectorAll('.enhanced-table');
    
    tables.forEach(table => {
        // Add pagination
        addPagination(table);
        
        // Add search functionality
        addTableSearch(table);
        
        // Add export functionality
        addTableExport(table);
    });
}

function initEnhancedFormComponents() {
    // Enhanced file upload
    const fileInputs = document.querySelectorAll('.file-upload');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFileUpload(this);
        });
    });
    
    // Enhanced date picker
    const dateInputs = document.querySelectorAll('.date-picker');
    dateInputs.forEach(input => {
        input.addEventListener('focus', function() {
            showDatePicker(this);
        });
    });
}

// ===================================
// UTILITY FUNCTIONS
// ===================================
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

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

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ===================================
// RESPONSIVE HANDLING
// ===================================
function handleResize() {
    const width = window.innerWidth;
    
    // Adjust animations based on screen size
    if (width < 768) {
        document.body.classList.add('mobile');
        document.body.classList.remove('desktop');
    } else {
        document.body.classList.add('desktop');
        document.body.classList.remove('mobile');
    }
    
    // Adjust table responsiveness
    const tables = document.querySelectorAll('.table-responsive');
    tables.forEach(table => {
        if (width < 768) {
            table.style.overflowX = 'auto';
        } else {
            table.style.overflowX = 'visible';
        }
    });
}

// Initialize resize handler
window.addEventListener('resize', debounce(handleResize, 250));
handleResize();

// ===================================
// GLOBAL EXPORTS
// ===================================
window.LogisticsSystem = {
    showToast,
    animateNumber,
    validateForm,
    isValidEmail,
    debounce,
    throttle
};
