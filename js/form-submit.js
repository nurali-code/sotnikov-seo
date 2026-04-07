document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    const openFormBtn = document.querySelector('.contacts__onmob .contacts__btn');
    
    // Mobile form functionality
    if (openFormBtn && contactForm) {
        openFormBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check screen width
            if (window.innerWidth <= 901) {
                // Toggle form visibility
                if (contactForm.classList.contains('--active')) {
                    // Hide form
                    contactForm.classList.remove('--active');
                } else {
                    // Show form
                    contactForm.classList.add('--active');
                    
                    // Scroll to form only when opening
                    setTimeout(() => {
                        contactForm.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    }, 100);
                }
            } else {
                // For desktop, just scroll to form
                contactForm.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
        });
        
        // Close form when clicking outside (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 901 && 
                contactForm.classList.contains('--active') &&
                !contactForm.contains(e.target) && 
                !openFormBtn.contains(e.target)) {
                contactForm.classList.remove('--active');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 901) {
                contactForm.classList.remove('--active');
            }
        });
    }
    
    if (!contactForm) {
        console.error('Contact form not found');
        return;
    }

    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form elements
        const submitBtn = contactForm.querySelector('.contacts__btn--submit');
        const originalText = submitBtn.textContent;
        
        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка...';
        
        // Create FormData object
        const formData = new FormData(contactForm);
        
        // Add current page URL to form data
        formData.append('page_url', window.location.href);
        
        // Send form data via fetch
        fetch('send.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show success message
                showMessage('Сообщение успешно отправлено! <br> Мы свяжемся с вами в ближайшее время.', 'success');
                // Reset form
                contactForm.reset();
            } else {
                // Show error message
                showMessage(data.message || 'Произошла ошибка при отправке. <br> Попробуйте еще раз.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Произошла ошибка при отправке. <br> Попробуйте еще раз.', 'error');
        })
        .finally(() => {
            // Restore submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // Function to show messages
    function showMessage(text, type) {
        // Remove existing messages
        const existingMessage = document.querySelector('.form-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Create message element
        const messageDiv = document.createElement('div');
        messageDiv.className = `form-message form-message--${type}`;
        messageDiv.innerHTML = text;
        
        // Style the message with fixed positioning at top right
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            font-size: 14px;
            line-height: 1.4;
            z-index: 1000;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            ${type === 'success' 
                ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' 
                : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
            }
        `;
        
        // Add to body
        document.body.appendChild(messageDiv);
        
        // Auto-remove message after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.style.opacity = '0';
                messageDiv.style.transform = 'translateX(100%)';
                messageDiv.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Phone number validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Remove all non-digit characters except +, (, ), -
            let value = e.target.value.replace(/[^\d\+\(\)\-\s]/g, '');
            e.target.value = value;
        });
    }

    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function(e) {
            const value = e.target.value.trim();
            if (value && !isValidEmail(value)) {
                e.target.setCustomValidity('Пожалуйста, введите корректный email адрес');
            } else {
                e.target.setCustomValidity('');
            }
        });
    }

    // Email validation helper
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
