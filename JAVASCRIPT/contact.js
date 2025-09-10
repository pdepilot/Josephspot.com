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
// Initialize EmailJS
(function () {
  emailjs.init("4mVvhX_iatqJbQ5iy");
})();
// DOM Content Loaded
document.addEventListener("DOMContentLoaded", function () {
  // Set current year in footer
  document.getElementById("year").textContent = new Date().getFullYear();
  // Contact form submission
  document
    .getElementById("contactForm")
    .addEventListener("submit", function (event) {
      event.preventDefault();

      const submitBtn = document.getElementById("submit-btn");
      const statusMessage = document.getElementById("status-message");

      // Validate reCAPTCHA
      const recaptchaResponse = grecaptcha.getResponse();
      if (!recaptchaResponse) {
        statusMessage.textContent =
          "Please complete the reCAPTCHA verification.";
        statusMessage.className = "status-message error";
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = "Sending...";

      // Prepare form data
      const formData = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        phone: document.getElementById("phone").value,
        subject: document.getElementById("subject").value,
        message: document.getElementById("message").value,
        "g-recaptcha-response": recaptchaResponse,
      };

      // Send email using EmailJS
      emailjs
        .send("service_te9k19v", "template_b4ywg0q", formData)
        .then(
          function (response) {
            statusMessage.textContent =
              "Thank you! Your message has been sent successfully. We will get back to you soon without 24 hours.";
            statusMessage.className = "status-message success";
            document.getElementById("contactForm").reset();
            grecaptcha.reset();
          },
          function (error) {
            statusMessage.textContent =
              "Oops! Something went wrong. Please try again later.";
            statusMessage.className = "status-message error";
            console.error("EmailJS Error:", error);
          }
        )
        .finally(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = "Send Message";
        });
    });
});


// WhatsApp link
const whatsappNumber = "2349064296917"; // Replace with your WhatsApp number (no '+' sign)
const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
  message
)}`;
window.open(whatsappURL, "_blank");
