// Scroll effect for navbar
window.addEventListener('scroll', function() {
  const navbar = document.getElementById('navbar');
  if (window.scrollY > 50) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});



// Mobile Menu Toggle Function
function toggleMenu() {
  const navLinks = document.querySelector('.nav-links');
  const menuToggle = document.querySelector('.menu-toggle');
  
  // Toggle menu visibility
  navLinks.classList.toggle('active');
  
  // Toggle hamburger/close icon
  const icon = menuToggle.querySelector('i');
  if (navLinks.classList.contains('active')) {
    icon.classList.remove('fa-utensils');
    icon.classList.add('fa-xmark');
    icon.style.transform = 'rotate(180deg)';
  } else {
    icon.classList.remove('fa-xmark');
    icon.classList.add('fa-utensils');
    icon.style.transform = 'rotate(0deg)';
  }
}

// Close menu when clicking on a nav link (optional)
document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', () => {
    if (window.innerWidth <= 768) { // Only for mobile
      toggleMenu();
    }
  });
});








// Scroll To Top Button
const scrollBtn = document.getElementById("scrollTopBtn");
window.onscroll = function () {
  if (
    document.body.scrollTop > 300 ||
    document.documentElement.scrollTop > 300
  ) {
    scrollBtn.style.display = "block";
  } else {
    scrollBtn.style.display = "none";
  }
};
scrollBtn.onclick = function () {
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// WhatsApp link
const whatsappNumber = "2349064296917"; // Replace with your WhatsApp number (no '+' sign)
const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
  message
)}`;
window.open(whatsappURL, "_blank");
