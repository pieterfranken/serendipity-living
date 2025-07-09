/**
 * Serendipity Living Theme JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Navbar scroll effect
    const navbar = document.getElementById('mainNav');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }
    
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
    
    // Contact modal villa name population
    const contactModal = document.getElementById('contact-modal');
    if (contactModal) {
        contactModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const villaName = button.getAttribute('data-villa');
            if (villaName) {
                const messageField = contactModal.querySelector('#contact_message');
                if (messageField && !messageField.value) {
                    messageField.value = `I'm interested in ${villaName}. Please contact me with more information.`;
                }
            }
        });
    }
    
    // Form submissions - only handle contact forms, not filter forms
    document.querySelectorAll('form.contact-form, form.villa-inquiry-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Basic form validation
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (isValid) {
                // Show success message (in a real app, this would submit to server)
                alert('Thank you for your inquiry! We will contact you soon.');
                form.reset();

                // Close modal if form is in a modal
                const modal = form.closest('.modal');
                if (modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            }
        });
    });
    
    // Image lazy loading
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Villa filter form auto-submit
    const filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('select, input');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Auto-submit form when filters change
                // filterForm.submit();
            });
        });
    }
    
});

// Utility functions
window.SerendipityTheme = {
    
    // Show loading state
    showLoading: function(element) {
        element.classList.add('loading');
        element.disabled = true;
    },
    
    // Hide loading state
    hideLoading: function(element) {
        element.classList.remove('loading');
        element.disabled = false;
    },
    
    // Show notification
    showNotification: function(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
};
