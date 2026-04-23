<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php'; // Make sure session_start() is called here

// Determine if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles.css">
    <title>NqobileQ - Health & Home Solutions</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="NqobileQ - Professional pharmacy services, home renovation, interior décor, and landscaping. Your one-stop shop for health and home.">
    <meta name="keywords" content="pharmacy, home renovation, interior design, landscaping, lawn care, medication delivery">
    <meta name="author" content="NqobileQ">
</head>
<body>

   <!-- Header Section Code -->
<header style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 9%;">
    <a href="#" class="logo">NQOBILE<span>Q</span></a>
    
    <div class='bx bx-menu' id="menu-icon"></div>
    
    <div class="nav-container" style="display: flex; align-items: center; gap: 2rem;">
        <ul class="navbar">
            <li><a href="#home">Home</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#plans">Pricing</a></li>
            <li><a href="#review">Review</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        
        <!-- FIXED: Auth section with working onclick -->
        <div id="auth-section" class="auth-section">
    <div class="top-btn">
        <?php if ($is_logged_in): ?>
            <span>Welcome, <?php echo htmlspecialchars($user_name); ?></span>
            <a href="logout.php" class="auth-btn">Logout</a>
        <?php else: ?>
            <!-- FIXED: Added proper onclick with event.preventDefault() and correct closing parenthesis -->
            <a href="#" class="auth-btn" onclick="event.preventDefault(); document.getElementById('authModal').style.display='block'; document.body.classList.add('modal-open'); return false;">Login / Register</a>
        <?php endif; ?>
    </div>
</div>
        
        <!-- FIXED: Book Now button with working onclick -->
        <div class="top-btn">
            <a href="#" class="nav-btn" onclick="event.preventDefault(); document.getElementById('joinUsModal').style.display='block'; document.body.classList.add('modal-open'); return false;">Book Now</a>
        </div>
    </div>
</header>
  
    <!-- Service Booking Modal -->
  <!-- Service Booking Modal -->
<div id="joinUsModal" class="modal">
    <div class="modal-content">
        <!-- FIXED: Added onclick to close button -->
        <span class="close" onclick="document.getElementById('joinUsModal').style.display='none'; document.body.classList.remove('modal-open');">&times;</span>
        <h2>Book a Service</h2>
        <form id="joinUsForm" action="submit_booking.php" method="POST">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
            </div>

            <div class="form-group">
                <label for="service-type">Service Required:</label>
<select id="service-type" name="service_type" required>
    <option value="">Please select a service</option>
    <option value="pharmacy">Pharmacy Services</option>
    <option value="renovation">Home Renovation</option>
    <option value="interior">Interior Décor</option>
    <option value="landscaping">Landscaping & Lawn Care</option>
    <option value="multiple">Multiple Services</option>
</select>
            </div>

            <div class="form-group">
                <label for="service-date">Preferred Date:</label>
                <input type="date" id="service-date" name="preferred_date" required>
            </div>

            <div class="form-group">
                <label for="message">Additional Details:</label>
                <textarea id="message" name="message" rows="3" placeholder="Tell us more about your requirements..."></textarea>
            </div>

            <div class="button-group">
                <button type="submit" class="submit-btn">Book Now</button>
                <a href="https://wa.me/+27782280408" target="_blank" class="submit-btn whatsapp-btn">WhatsApp Us</a>
            </div>
        </form>
    </div>
</div>

    <!-- Home Section Code -->
    <section class="home" id="home">
        <div class="home-content" data-aos="zoom-in">
            <h3>Welcome to</h3>
            <h1>Nqobile<span>Q</span></h1>
            <h3><span class="multiple-text"></span></h3>

            <p>Your premier multi-disciplinary service provider dedicated to enhancing your quality of life. We seamlessly blend health and home by offering professional pharmacy services alongside expert home renovation, interior décor, and meticulous landscaping.</p>

            <!-- FIXED: Book Now button in home section -->
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="#" class="btn" onclick="event.preventDefault(); document.getElementById('joinUsModal').style.display='block'; document.body.classList.add('modal-open'); return false;">Book Now</a>
                <a href="https://wa.me/+27782280408" target="_blank" class="whatsapp-btn" style="background-color: #25D366; color: white; padding: 1rem 2.8rem; border-radius: 1rem; font-size: 1.6rem; display: inline-block;">WhatsApp Us</a>
            </div>
        </div>

        
    </section>

    <!-- Services Section Code -->
    <section class="services" id="services">
        <h2 class="heading" data-aos="zoom-in-down">Our <Span>Services</Span></h2>
        
        <div class="services-content" data-aos="zoom-in-up">
            <!-- Pharmacy Services -->
            <!-- In index.html, update the Pharmacy Services link -->
<!-- Find the Pharmacy Services section and update the href -->
<div class="row">
    <div class="service-img">
        <img src="assets/pharm.jpg" alt="Pharmacy Services">
        <div class="service-overlay">
            <!-- Use this path with ln/ -->
            <a href="/Docker-Webs-main/Phamarcy.html" class="service-btn">Learn More</a>
        </div>
    </div>
    <div class="service-info">
        <h4><a href="/Docker-Webs-main/Phamarcy.html" >Pharmacy Services</a></h4>
        <p>Professional medication management, prescription delivery, health consultations, and wellness advice from qualified pharmacists.</p>
        <div class="service-tags">
            <span class="tag">Prescriptions</span>
            <span class="tag">Free Delivery</span>
            <span class="tag">Consultation</span>
        </div>
    </div>
</div>

            <!-- Home Renovation -->
            <div class="row">
                <div class="service-img">
                    <img src="assets/home-renovation.jpg" alt="Home Renovation">
                    <div class="service-overlay">
                        <a href="renovation.html" class="service-btn">Learn More</a>
                    </div>
                </div>
                <div class="service-info">
                    <h4><a href="renovation.html">Home Renovation</a></h4>
                    <p>Expert kitchen remodeling, bathroom renovations, room additions, and complete home makeovers with quality craftsmanship.</p>
                    <div class="service-tags">
                        <span class="tag">Kitchens</span>
                        <span class="tag">Bathrooms</span>
                        <span class="tag">Full Home</span>
                    </div>
                </div>
            </div>

            <!-- Interior Décor -->
            <div class="row">
                <div class="service-img">
                    <img src="assets/interior-decor.jpg" alt="Interior Décor">
                    <div class="service-overlay">
                        <a href="interior.html" class="service-btn">Learn More</a>
                    </div>
                </div>
                <div class="service-info">
                    <h4><a href="interior.html">Interior Décor</a></h4>
                    <p>Transform your living spaces with professional interior design, space planning, color consultation, and furniture selection.</p>
                    <div class="service-tags">
                        <span class="tag">Design</span>
                        <span class="tag">Consultation</span>
                        <span class="tag">Furniture</span>
                    </div>
                </div>
            </div>

            <!-- Landscaping & Lawn Care -->
            <div class="row">
                <div class="service-img">
                    <img src="assets/Happy-Lawn-Mowing.jpg" alt="Landscaping">
                    <div class="service-overlay">
                        <a href="landscaping.html" class="service-btn">Learn More</a>
                    </div>
                </div>
                <div class="service-info">
                    <h4><a href="landscaping.html">Landscaping & Lawn Care</a></h4>
                    <p>Professional garden design, lawn maintenance, tree trimming, and complete outdoor space transformation.</p>
                    <div class="service-tags">
                        <span class="tag">Garden Design</span>
                        <span class="tag">Lawn Care</span>
                        <span class="tag">Maintenance</span>
                    </div>
                </div>
            </div>

    </section>

    <!-- About Section Code -->
    <section class="about" id="about">
        <div class="about-img" data-aos="zoom-in-down">
            <img src="assets/nq.jpeg" alt="NqobileQ Team">
        </div>

        <div class="about-content" data-aos="zoom-in-up">
            <h2 class="heading">Why Choose <span>Us?</span></h2>

            <p>NqobileQ is a premier, multi-disciplinary service provider dedicated to enhancing the quality of life and living spaces for our clients. We seamlessly blend health and home by offering professional pharmacy services alongside expert home renovation, interior décor, and meticulous landscaping services.</p>
            
            <p>Whether we are managing your medication needs, renovating your kitchen, or rejuvenating your garden, our team is committed to professionalism, high-quality craftsmanship, and unparalleled customer satisfaction.</p>
            
            <p>We aim to be your one-stop shop for creating a healthy, beautiful, and functional environment.</p>

            <!-- FIXED: Book Consultation button in About section -->
<div style="display: flex; gap: 15px; flex-wrap: wrap;">
    <a href="#" class="btn" onclick="event.preventDefault(); document.getElementById('joinUsModal').style.display='block'; document.body.classList.add('modal-open'); return false;">Book Consultation</a>
    <a href="tel:+27782280408" class="phone-btn" style="background: var(--main-color); color: var(--bg-color); padding: 1rem 2.8rem; border-radius: 1rem; font-size: 1.6rem; display: inline-block;">📞 Call Us: +27782280408</a>
</div>
        </div>
    </section>

    <!-- Pricing/Packages Section Code -->
<section class="plans" id="plans">
    <h2 class="heading" data-aos="zoom-in-down">Our <span>Packages</span></h2>

    <div class="plans-content" data-aos="zoom-in-up">
        <!-- Basic Package -->
        <div class="box">
            <h3>ESSENTIAL</h3>
            <h2><span>R5,000</span></h2>
            <ul>
                <li>Pharmacy Consultation</li>
                <li>Prescription Delivery</li>
                <li>Basic Garden Maintenance</li>
                <li>Interior Design Consultation</li>
            </ul>
            <!-- FIXED: Package button with working onclick -->
            <a href="#" onclick="event.preventDefault(); event.stopPropagation(); document.getElementById('selected-plan').value='ESSENTIAL'; document.getElementById('joinNowModal').style.display='block'; document.body.classList.add('modal-open'); return false;" class="join-btn" data-plan="ESSENTIAL">
                Join Now
                <i class='bx bx-right-arrow-alt'></i>
            </a>
        </div>

        <!-- Pro Package -->
        <div class="box">
            <h3>COMPREHENSIVE</h3>
            <h2><span>R15,000</span></h2>
            <ul>
                <li>All Essential Services</li>
                <li>Room Renovation</li>
                <li>Full Interior Design Plan</li>
                <li>Complete Landscaping</li>
                <li>Priority Support</li>
            </ul>
            <!-- FIXED: Package button with working onclick -->
            <a href="#" onclick="event.preventDefault(); event.stopPropagation(); document.getElementById('selected-plan').value='COMPREHENSIVE'; document.getElementById('joinNowModal').style.display='block'; document.body.classList.add('modal-open'); return false;" class="join-btn" data-plan="COMPREHENSIVE">
                Join Now
                <i class='bx bx-right-arrow-alt'></i>
            </a>
        </div>

        <!-- Premium Package -->
        <div class="box">
            <h3>PREMIUM</h3>
            <h2><span>R30,000</span></h2>
            <ul>
                <li>All Comprehensive Services</li>
                <li>Full Home Renovation</li>
                <li>Premium Interior Design</li>
                <li>Complete Garden Makeover</li>
                <li>Monthly Medication Delivery</li>
                <li>24/7 Priority Support</li>
            </ul>
            <!-- FIXED: Package button with working onclick -->
            <a href="#" onclick="event.preventDefault(); event.stopPropagation(); document.getElementById('selected-plan').value='PREMIUM'; document.getElementById('joinNowModal').style.display='block'; document.body.classList.add('modal-open'); return false;" class="join-btn" data-plan="PREMIUM">
                Join Now
                <i class='bx bx-right-arrow-alt'></i>
            </a>
        </div>
    </div>
</section>

    <!-- Review Section Code -->
    <section class="review" id="review">
        <div class="review-box" data-aos="zoom-in-down">
            <h2 class="heading">Client <span>Reviews</span></h2>

            <div class="wrapper" data-aos="zoom-in-up">
                <div class="review-item">
                <img src="assets/nkanyez.jpeg" alt="">
                <h2>Nkanyezi</h2>
                <div class="rating">
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                </div>
                    <p>"NqobileQ transformed my home completely! The renovation team was professional, and the pharmacy delivery service is a lifesaver for my elderly parents."</p>
                </div>

                <div class="review-item">
                <img src="assets/kb.jpeg" alt="">
                <h2>KB</h2>
                <div class="rating">
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    
                </div>
                    <p>"The landscaping service exceeded my expectations. My garden looks beautiful, and their maintenance team is always on time. Highly recommended!"</p>
                </div>

                <div class="review-item">
                <img src="assets/tjay.jpeg" alt="">
                <h2>Sanele</h2>
                <div class="rating">
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    <i class='bx bxs-star' id="Star"></i>
                    
                </div>
                    <p>"The interior design team understood my vision perfectly. They created a beautiful, functional space that my family loves. Professional and affordable!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <h2 class="heading">Contact <span>Us</span></h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto;">
            
            <div class="contact-info" style="background: var(--snd-bg-color); padding: 30px; border-radius: 15px; border: 1px solid var(--main-color);">
                <h3 style="font-size: 2.5rem; margin-bottom: 20px;">Get in Touch</h3>
                
                <div style="margin-bottom: 20px;">
                    <i class='bx bxs-phone' style="font-size: 2rem; color: var(--main-color); margin-right: 10px;"></i>
                    <span style="font-size: 1.8rem;">+27782280408</span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <i class='bx bxs-envelope' style="font-size: 2rem; color: var(--main-color); margin-right: 10px;"></i>
                    <span style="font-size: 1.8rem;">nqobileq.co@gmail.com</span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <i class='bx bxs-map' style="font-size: 2rem; color: var(--main-color); margin-right: 10px;"></i>
                    <span style="font-size: 1.8rem;">Serving all areas - Contact for availability</span>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <a href="https://wa.me/+27782280408" target="_blank" style="background: #25D366; color: white; padding: 12px 25px; border-radius: 8px; font-size: 1.6rem; display: inline-flex; align-items: center; gap: 8px;">
                        <i class='bx bxl-whatsapp'></i> WhatsApp
                    </a>
                    <a href="tel:+27782280408" style="background: var(--main-color); color: var(--bg-color); padding: 12px 25px; border-radius: 8px; font-size: 1.6rem; display: inline-flex; align-items: center; gap: 8px;">
                        <i class='bx bxs-phone-call'></i> Call Now
                    </a>
                </div>
            </div>
            
            <!-- Quick Contact Form -->
            <div style="background: var(--snd-bg-color); padding: 30px; border-radius: 15px; border: 1px solid var(--main-color);">
                <h3 style="font-size: 2.5rem; margin-bottom: 20px;">Quick Inquiry</h3>
                <form action="submit_inquiry.php" method="POST">
                    <input type="text" name="name" placeholder="Your Name" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: none; background: rgba(255,255,255,0.1); color: white;">
                    <input type="email" name="email" placeholder="Your Email" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: none; background: rgba(255,255,255,0.1); color: white;">
                    <input type="tel" name="phone" placeholder="Phone Number" style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: none; background: rgba(255,255,255,0.1); color: white;">
                    <textarea name="message" placeholder="Your Message" rows="3" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: none; background: rgba(255,255,255,0.1); color: white;"></textarea>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Testimonial Submission Section -->
    <section class="review" id="testimonial-form">
        <h2 class="heading">Share Your <span>Experience</span></h2>
        <form id="testimonialForm" action="submit_testimonial.php" method="POST" class="modal-content" style="background: var(--snd-bg-color); color: #fff; max-width: 600px; margin: 0 auto;">
            <label for="testi-name">Full Name:</label>
            <input type="text" id="testi-name" name="name" required>
            
            <label for="testi-email">Email:</label>
            <input type="email" id="testi-email" name="email" required>
            
            <label for="testi-message">Your Experience:</label>
            <textarea id="testi-message" name="message" rows="4" required></textarea>
            
            <label for="testi-rating">Rating:</label>
            <select id="testi-rating" name="rating">
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
            </select>
            
            <button type="submit" class="submit-btn">Submit Review</button>
        </form>
    </section>

    <!-- Footer Section Code -->
    <footer class="footer">
        <div class="social">
            <a href="https://www.facebook.com/profile.php?id=61588428180925" target="_blank"><i class='bx bxl-facebook'></i></a>
            <a href="https://www.instagram.com/nqobileq_services_/" target="_blank"><i class='bx bxl-instagram'></i></a>
            <a href="https://wa.me/+27782280408" target="_blank"><i class='bx bxl-whatsapp'></i></a>
            <a href="mailto:nqobileq.co@gmail.com"><i class='bx bxl-gmail'></i></a>
        </div>

        <div style="text-align: center; margin: 20px 0; font-size: 1.6rem;">
            <p>📞 <a href="tel:+27782280408" style="color: var(--main-color);">+27782280408</a> | ✉️ <a href="mailto:nqobileq.co@gmail.com" style="color: var(--main-color);">nqobileq.co@gmail.com</a></p>
        </div>

        <p class="copyright">
            &copy; NqobileQ 2026 - All Rights Reserved | Health & Home Solutions
        </p>
    </footer>

    <!-- Package Booking Modal -->
    <!-- Package Booking Modal -->
<div id="joinNowModal" class="modal">
    <div class="modal-content">
        <!-- FIXED: Added onclick to close button -->
        <span class="close" onclick="document.getElementById('joinNowModal').style.display='none'; document.body.classList.remove('modal-open');">&times;</span>
        <h2>Book Your Package</h2>
        <form id="joinNowForm" action="submit_package.php" method="POST">
            <label for="member-name">Full Name:</label>
            <input type="text" id="member-name" name="name" required>

            <label for="member-email">Email:</label>
            <input type="email" id="member-email" name="email" required>

            <label for="member-phone">Phone Number:</label>
            <input type="tel" id="member-phone" name="phone" required>

            <label for="selected-plan">Selected Package:</label>
            <input type="text" id="selected-plan" name="package_name" readonly>

            <button type="submit" class="submit-btn">Confirm Booking</button>
        </form>
    </div>
</div>

    <!-- Login/Register Modal -->
    <div id="authModal" class="modal">
        <div class="modal-content">
            <!-- FIXED: Added onclick to close button -->
            <span class="close" onclick="document.getElementById('authModal').style.display='none'; document.body.classList.remove('modal-open');">&times;</span>
            <h2>Login or Register</h2>
            
            <form id="authForm" action="login.php" method="POST">
                <h3 style="color: var(--main-color); margin-bottom: 15px;">Login</h3>
                <label for="auth-email">Email:</label>
                <input type="email" id="auth-email" name="email" required>
                
                <label for="auth-password">Password:</label>
                <input type="password" id="auth-password" name="password" required>
                
                <button type="submit" class="submit-btn">Login</button>
            </form>
            
            <hr style="margin: 20px 0; border-color: rgba(255,255,255,0.1);">
            
            <form id="registerForm" action="register.php" method="POST">
                <h3 style="color: var(--main-color); margin-bottom: 15px;">Register</h3>
                <label>Full Name:</label>
                <input type="text" name="full_name" required>
                
                <label>Email:</label>
                <input type="email" name="email" required>
                
                <label>Phone:</label>
                <input type="tel" name="phone" required>
                
                <label>Password:</label>
                <input type="password" name="password" required>
                
                <button type="submit" class="submit-btn">Register</button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script src="script.js"></script>
    
    <script>
        AOS.init({
            offset: 300,
            duration: 1400,
        });
        
        
        // FIXED: Added window.onclick to close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        };
        
        // FIXED: Added ESC key to close modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(function(modal) {
                    if (modal.style.display === 'block') {
                        modal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                    }
                });
            }
        });
    </script>
</body>
</html>