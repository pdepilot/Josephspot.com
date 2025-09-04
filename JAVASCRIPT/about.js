// Enhanced JavaScript with cosmic effects
document.addEventListener('DOMContentLoaded', function() {
  // Cosmic cursor effect
  const cursor = document.createElement('div');
  cursor.classList.add('cosmic-cursor');
  document.body.appendChild(cursor);
  
  document.addEventListener('mousemove', function(e) {
    cursor.style.left = e.clientX + 'px';
    cursor.style.top = e.clientY + 'px';
    
    // Create trailing particles
    if (Math.random() > 0.7) {
      const particle = document.createElement('div');
      particle.classList.add('cosmic-particle');
      particle.style.left = e.clientX + 'px';
      particle.style.top = e.clientY + 'px';
      particle.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 70%)`;
      document.body.appendChild(particle);
      
      // Remove particle after animation
      setTimeout(() => {
        particle.remove();
      }, 1000);
    }
  });
  
  // Add cosmic particles to header on load
  const header = document.querySelector('.header');
  for (let i = 0; i < 15; i++) {
    const particle = document.createElement('div');
    particle.classList.add('header-particle');
    particle.style.left = Math.random() * 100 + '%';
    particle.style.top = Math.random() * 100 + '%';
    particle.style.width = Math.random() * 10 + 5 + 'px';
    particle.style.height = particle.style.width;
    particle.style.animationDelay = Math.random() * 5 + 's';
    particle.style.animationDuration = Math.random() * 10 + 5 + 's';
    header.appendChild(particle);
  }
  
  // Enhanced scroll to top button
  const scrollBtn = document.getElementById('scrollTopBtn');
  
  window.onscroll = function() {
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
      scrollBtn.style.display = 'flex';
      scrollBtn.style.alignItems = 'center';
      scrollBtn.style.justifyContent = 'center';
    } else {
      scrollBtn.style.display = 'none';
    }
  };
  
  scrollBtn.onclick = function() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  };
  
  // Existing modal and other JS functionality remains the same
  const modal = document.getElementById("ceoModal");
  const modalImg = document.getElementById("modalImage");
  const ceoImg = document.getElementById("ceoImage");
  const closeBtn = document.getElementsByClassName("close")[0];

  ceoImg.onclick = function() {
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
  };

  closeBtn.onclick = function() {
    modal.style.display = "none";
  };

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  };
});

// Add this to your existing JS file



// Enhanced Navbar Scroll Effect
window.addEventListener('scroll', function() {
  const navbar = document.querySelector('.navbar');
  const scrollPosition = window.scrollY;
  
  if (scrollPosition > 100) {
    navbar.classList.add('scrolled');
    
    // Add cosmic pulse effect when scrolled
    navbar.style.animation = 'cosmicPulse 3s infinite alternate';
  } else {
    navbar.classList.remove('scrolled');
    navbar.style.animation = 'none';
  }
});



function toggleMenu() {
  const navLinks = document.querySelector('.nav-links');
  const menuToggle = document.querySelector('.menu-toggle');
  
  // Toggle menu visibility
  navLinks.classList.toggle('active');
  menuToggle.classList.toggle('active');
  
  // Toggle hamburger/X icons
  const bars = document.querySelector('.fa-bars');
  const xmark = document.querySelector('.fa-xmark');
  
  if (navLinks.classList.contains('active')) {
    bars.style.display = 'none';
    xmark.style.display = 'inline-block';
  } else {
    bars.style.display = 'inline-block';
    xmark.style.display = 'none';
  }
}












