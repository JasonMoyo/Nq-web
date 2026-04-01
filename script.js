// ================================
// MENU TOGGLE
// ================================
let menu = document.querySelector('#menu-icon');
let navbar = document.querySelector('.navbar');

if (menu) {
    menu.onclick = () => {
        menu.classList.toggle('bx-x');
        navbar.classList.toggle('active');
    }
}

window.onscroll = () => {
    if (menu) {
        menu.classList.remove('bx-x');
        navbar.classList.remove('active');
    }
}

// ================================
// PACKAGE BUTTONS (Join Now)
// ================================
function setupPackageButtons() {
    console.log('Setting up package buttons...');
    
    const joinNowModal = document.getElementById('joinNowModal');
    const selectedPlanInput = document.getElementById('selected-plan');
    
    if (!joinNowModal) {
        console.error('joinNowModal not found!');
        return;
    }
    if (!selectedPlanInput) {
        console.error('selected-plan input not found!');
        return;
    }
    
    document.querySelectorAll('.join-btn').forEach((btn) => {
        // Remove existing listener and add new one
        btn.removeEventListener('click', handlePackageClick);
        btn.addEventListener('click', handlePackageClick);
    });
}

function handlePackageClick(e) {
    console.log('✅ PACKAGE BUTTON CLICKED!');
    e.preventDefault();
    e.stopPropagation();
    
    const plan = this.getAttribute('data-plan');
    console.log('Plan selected:', plan);
    
    const joinNowModal = document.getElementById('joinNowModal');
    const selectedPlanInput = document.getElementById('selected-plan');
    const joinUsModal = document.getElementById('joinUsModal');
    
    // Close service modal if open
    if (joinUsModal) {
        joinUsModal.style.display = 'none';
    }
    
    if (plan && joinNowModal && selectedPlanInput) {
        selectedPlanInput.value = plan;
        joinNowModal.style.display = 'block';
        document.body.classList.add('modal-open');
        console.log('✅ Package modal opened with plan:', plan);
    } else {
        console.error('Missing something:', {plan, joinNowModal, selectedPlanInput});
    }
}

// ================================
// BOOKING BUTTONS (Book Now)
// ================================
function setupBookingButtons() {
    console.log('Setting up booking buttons...');
    
    document.querySelectorAll('.nav-btn, .btn:not(.auth-btn):not(.join-btn):not(.service-btn):not(.navbar a), .book-now-btn').forEach(btn => {
        // Only apply to buttons that should open the booking modal
        if (btn.classList.contains('navbar a') || btn.closest('.navbar')) {
            return; // Skip navigation links
        }
        
        btn.removeEventListener('click', handleBookingClick);
        btn.addEventListener('click', handleBookingClick);
    });
}



function handleBookingClick(e) {
    console.log('📅 BOOKING BUTTON CLICKED');
    e.preventDefault();
    e.stopPropagation();
    
    const joinUsModal = document.getElementById('joinUsModal');
    const joinNowModal = document.getElementById('joinNowModal');
    const authModal = document.getElementById('authModal');
    
    if (joinNowModal) joinNowModal.style.display = 'none';
    if (authModal) authModal.style.display = 'none';
    
    if (joinUsModal) {
        joinUsModal.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

// ================================
// AUTH BUTTONS (Login/Register)
// ================================
function setupAuthButtons() {
    document.querySelectorAll('.auth-btn').forEach(btn => {
        // Skip if it's a logout button or has data-no-modal attribute
        if (btn.classList.contains('logout-btn') || btn.getAttribute('data-no-modal') === 'true') {
            return;
        }
        btn.removeEventListener('click', handleAuthClick);
        btn.addEventListener('click', handleAuthClick);
    });
}

function handleAuthClick(e) {
    console.log('🔐 AUTH BUTTON CLICKED');
    e.preventDefault();
    e.stopPropagation();
    
    const authModal = document.getElementById('authModal');
    const joinNowModal = document.getElementById('joinNowModal');
    const joinUsModal = document.getElementById('joinUsModal');
    
    if (joinNowModal) joinNowModal.style.display = 'none';
    if (joinUsModal) joinUsModal.style.display = 'none';
    
    if (authModal) {
        authModal.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

// ================================
// NAVIGATION LINKS
// ================================
// This function ensures navigation links work normally
function setupNavigationLinks() {
    // All navbar links should work normally
    document.querySelectorAll('.navbar a, .service-info h4 a, .service-overlay a, .footer a, .social a').forEach(link => {
        // Remove any previously attached handlers that might prevent default
        link.removeEventListener('click', preventDefaultHandler);
        // Let the browser handle these links normally
    });
}

// This is a dummy function to remove any existing handlers
function preventDefaultHandler(e) {
    // This function intentionally left empty to be removed
}

// ================================
// MODAL CLOSE HANDLING
// ================================
function setupModalCloseHandlers() {
    document.querySelectorAll('.close').forEach(btn => {
        btn.removeEventListener('click', handleCloseClick);
        btn.addEventListener('click', handleCloseClick);
    });
    
    window.removeEventListener('click', handleOutsideClick);
    window.addEventListener('click', handleOutsideClick);
}

function handleCloseClick(e) {
    e.preventDefault();
    e.stopPropagation();
    closeAllModals();
}

function handleOutsideClick(event) {
    if (event.target.classList.contains('modal')) {
        closeAllModals();
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
    document.body.classList.remove('modal-open');
}

// ================================
// CHECK LOGIN STATUS
// ================================
function checkLoginStatus() {
    fetch('check-login.php')
        .then(res => res.json())
        .then(data => {
            const authSection = document.getElementById('auth-section');
            
            if (authSection) {
                if (data.logged_in) {
                    authSection.innerHTML = `
                        <div class="user-info" style="display:flex;align-items:center;gap:15px;">
                            <span style="color: var(--main-color); font-size:1.6rem;">
                                <i class='bx bxs-user-circle' style="font-size:2rem;vertical-align:middle;"></i>
                                ${escapeHtml(data.user_name)}
                            </span>
                            <a href="logout.php" class="auth-btn logout-btn" data-no-modal="true" style="background:#f44336;color:white;border-color:#f44336;">Logout</a>
                        </div>
                    `;
                } else {
                    authSection.innerHTML = `
                        <div class="top-btn">
                            <a href="#" class="auth-btn">Login / Register</a>
                        </div>
                    `;
                }
            }
            
            // Re-setup all handlers after updating the DOM
            setTimeout(() => {
                setupAllHandlers();
            }, 100);
        })
        .catch(err => {
            console.error('Login check failed:', err);
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ================================
// SETUP ALL HANDLERS
// ================================
function setupAllHandlers() {
    console.log('Setting up all handlers...');
    setupPackageButtons();
    setupAuthButtons();
    setupBookingButtons();
    setupNavigationLinks(); // This ensures navigation links work
    setupModalCloseHandlers();
}

// ================================
// INITIALIZE
// ================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing');
    
    setupAllHandlers();
    checkLoginStatus();
    
    // Set min date for date inputs
    const dateInput = document.getElementById('service-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
    
    // Typed.js
    if (typeof Typed !== 'undefined') {
        new Typed('.multiple-text', {
            strings: ['Pharmacy Services', 'Home Renovation', 'Interior Décor', 'Landscaping', 'Health & Home'],
            typeSpeed: 50,
            backSpeed: 50,
            backDelay: 1000,
            loop: true,
        });
    }
    
    // AOS
    if (window.AOS) {
        AOS.init({
            offset: 300,
            duration: 1400,
        });
    }
});

window.addEventListener('load', function() {
    console.log('Window loaded - reattaching handlers');
    setupAllHandlers();
});

// ================================
// HELPER FUNCTIONS FOR AUTH MODAL TABS
// ================================
// These functions need to be global for onclick attributes
window.showLoginForm = function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    
    if (loginForm) loginForm.style.display = 'block';
    if (registerForm) registerForm.style.display = 'none';
    if (loginTab) loginTab.style.borderBottomColor = 'var(--main-color)';
    if (registerTab) registerTab.style.borderBottomColor = 'transparent';
};

window.showRegisterForm = function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    
    if (loginForm) loginForm.style.display = 'none';
    if (registerForm) registerForm.style.display = 'block';
    if (registerTab) registerTab.style.borderBottomColor = 'var(--main-color)';
    if (loginTab) loginTab.style.borderBottomColor = 'transparent';
};