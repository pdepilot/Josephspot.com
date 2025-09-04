// Scroll effect for navbar
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });
  
  // Mobile menu toggle function
  function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('active');
  }






  
// Scroll To Top Button
const scrollBtn = document.getElementById("scrollTopBtn");
window.onscroll = function () {
  if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
    scrollBtn.style.display = "block";
  } else {
    scrollBtn.style.display = "none";
  }
};
scrollBtn.onclick = function () {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};




// WhatsApp link
const whatsappNumber = "2349064296917"; // Replace with your WhatsApp number (no '+' sign)
const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
  message
)}`;
window.open(whatsappURL, "_blank");
