// JAVASCRIPT/contact.js
document.addEventListener("DOMContentLoaded", function () {
  // Initialize variables
  const whatsappNumber = "2348104344994";
  
  // Set current year in footer
  document.getElementById("year").textContent = new Date().getFullYear();

  // Initialize EmailJS
  emailjs.init("4mVvhX_iatqJbQ5iy");

  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

  // Mobile menu toggle
  window.toggleMenu = function() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('active');
  }

  // Close mobile menu when clicking on a link
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      const navLinks = document.querySelector('.nav-links');
      navLinks.classList.remove('active');
    });
  });

  // Scroll To Top Button
  const scrollBtn = document.getElementById("scrollTopBtn");
  window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
      scrollBtn.classList.add('show');
    } else {
      scrollBtn.classList.remove('show');
    }
  });

  scrollBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // WhatsApp functionality
  const whatsappButton = document.querySelector('.whatsapp-chat');
  whatsappButton.addEventListener('click', function(e) {
    e.preventDefault();
    const message = encodeURIComponent("Hello Joseph's Pot! I'd like to get more information about your restaurant.");
    const whatsappURL = `https://wa.me/${whatsappNumber}?text=${message}`;
    window.open(whatsappURL, '_blank');
  });

  // Form validation functions
  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function validatePhone(phone) {
    if (!phone) return true; // Phone is optional
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
  }

  function validateRequired(value) {
    return value.trim().length > 0;
  }

  function showValidationMessage(input, message, type) {
    const formGroup = input.closest('.form-group');
    const existingMsg = formGroup.querySelector('.validation-message');
    
    if (existingMsg) {
      existingMsg.remove();
    }

    const validationMsg = document.createElement('div');
    validationMsg.className = `validation-message ${type} show`;
    validationMsg.textContent = message;
    
    input.classList.remove('error', 'success');
    input.classList.add(type);
    
    formGroup.appendChild(validationMsg);
  }

  function clearValidationMessage(input) {
    const formGroup = input.closest('.form-group');
    const existingMsg = formGroup.querySelector('.validation-message');
    
    if (existingMsg) {
      existingMsg.remove();
    }
    
    input.classList.remove('error', 'success');
  }

  // Real-time form validation
  document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('blur', function() {
      if (this.name === 'email' && this.value) {
        if (!validateEmail(this.value)) {
          showValidationMessage(this, 'Please enter a valid email address', 'error');
        } else {
          showValidationMessage(this, 'Email looks good!', 'success');
        }
      }
      
      if (this.name === 'phone' && this.value) {
        if (!validatePhone(this.value)) {
          showValidationMessage(this, 'Please enter a valid phone number', 'error');
        } else {
          showValidationMessage(this, 'Phone number is valid', 'success');
        }
      }
      
      if (this.required && !validateRequired(this.value)) {
        showValidationMessage(this, 'This field is required', 'error');
      }
    });

    input.addEventListener('input', function() {
      clearValidationMessage(this);
    });
  });

  // Contact form submission
  const contactForm = document.getElementById("contactForm");
  const statusMessage = document.getElementById("status-message");
  const submitBtn = document.getElementById("submit-btn");

  contactForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    // Validate all required fields
    let isValid = true;
    const requiredFields = contactForm.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
      if (!validateRequired(field.value)) {
        showValidationMessage(field, 'This field is required', 'error');
        isValid = false;
      }
    });

    if (!isValid) {
      statusMessage.textContent = "Please fill in all required fields correctly.";
      statusMessage.className = "status-message error";
      return;
    }

    // Validate reCAPTCHA
    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
      statusMessage.textContent = "Please complete the reCAPTCHA verification.";
      statusMessage.className = "status-message error";
      return;
    }

    // Update button state
    submitBtn.disabled = true;
    submitBtn.textContent = "Sending...";
    submitBtn.classList.add('loading');

    // Prepare form data
    const formData = {
      name: document.getElementById("name").value.trim(),
      email: document.getElementById("email").value.trim(),
      phone: document.getElementById("phone").value.trim() || "Not provided",
      subject: document.getElementById("subject").value,
      message: document.getElementById("message").value.trim(),
      "g-recaptcha-response": recaptchaResponse,
      timestamp: new Date().toISOString()
    };

    try {
      // Send email using EmailJS
      const response = await emailjs.send(
        "service_te9k19v", 
        "template_b4ywg0q", 
        formData
      );

      if (response.status === 200) {
        // Success
        statusMessage.innerHTML = `
          <i class="fas fa-check-circle"></i> 
          <strong>Thank you, ${formData.name}!</strong><br>
          Your message has been sent successfully. We'll get back to you within 24 hours.
        `;
        statusMessage.className = "status-message success";
        
        // Reset form
        contactForm.reset();
        grecaptcha.reset();
        
        // Add visual feedback
        contactForm.style.opacity = '0.7';
        setTimeout(() => {
          contactForm.style.opacity = '1';
        }, 2000);
      } else {
        throw new Error('EmailJS returned non-200 status');
      }
    } catch (error) {
      console.error("EmailJS Error:", error);
      statusMessage.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Oops! Something went wrong.</strong><br>
        Please try again later or contact us directly at info@josephspot.com
      `;
      statusMessage.className = "status-message error";
    } finally {
      // Reset button state
      submitBtn.disabled = false;
      submitBtn.textContent = "Send Message";
      submitBtn.classList.remove('loading');
      
      // Auto-hide success message after 10 seconds
      if (statusMessage.classList.contains('success')) {
        setTimeout(() => {
          statusMessage.style.opacity = '0';
          setTimeout(() => {
            statusMessage.style.display = 'none';
            statusMessage.style.opacity = '1';
          }, 300);
        }, 10000);
      }
    }
  });

  // Initialize AOS
  AOS.init({
    duration: 800,
    once: true,
    offset: 100
  });

  // Initialize Google Map
  initMap();
});

// Google Maps initialization
function initMap() {
  // Owerri, Nigeria coordinates
  const owerriLocation = { lat: 5.4896, lng: 7.0330 };
  
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 15,
    center: owerriLocation,
    styles: [
      {
        featureType: "all",
        elementType: "geometry",
        stylers: [{ color: "#f5f5dc" }]
      },
      {
        featureType: "water",
        elementType: "geometry",
        stylers: [{ color: "#d2b48c" }]
      },
      {
        featureType: "road",
        elementType: "geometry",
        stylers: [{ color: "#8b4513" }, { lightness: 50 }]
      }
    ],
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false
  });

  // Custom marker icon
  const markerIcon = {
    url: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512'%3E%3Cpath fill='%238b4513' d='M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z'/%3E%3C/svg%3E",
    scaledSize: new google.maps.Size(40, 40),
    origin: new google.maps.Point(0, 0),
    anchor: new google.maps.Point(20, 40)
  };

  // Create marker
  const marker = new google.maps.Marker({
    position: owerriLocation,
    map: map,
    title: "Joseph's Pot Restaurant",
    icon: markerIcon,
    animation: google.maps.Animation.DROP
  });

  // Info window
  const infoWindow = new google.maps.InfoWindow({
    content: `
      <div style="padding: 10px;">
        <h3 style="margin: 0 0 5px 0; color: #8b4513;">Joseph's Pot</h3>
        <p style="margin: 0;">Ikenegbu Layout, Owerri<br>Imo State, Nigeria</p>
      </div>
    `
  });

  // Open info window on marker click
  marker.addListener('click', () => {
    infoWindow.open(map, marker);
  });

  // Add click listener to the map
  map.addListener('click', () => {
    infoWindow.close();
  });
}

// Handle Google Maps API error
window.gm_authFailure = function() {
  const mapElement = document.getElementById('map');
  if (mapElement) {
    mapElement.innerHTML = `
      <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f8f0e3; color: #8b4513; padding: 20px; text-align: center;">
        <div>
          <i class="fas fa-map-marker-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
          <p style="margin: 0;">Map temporarily unavailable.<br>We're located at Ikenegbu Layout, Owerri</p>
        </div>
      </div>
    `;
  }
};