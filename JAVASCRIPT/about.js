// JavaScript for mobile menu toggle
function toggleMenu() {
  const navLinks = document.querySelector(".nav-links");
  const menuToggle = document.querySelector(".menu-toggle");
  const barsIcon = menuToggle.querySelector(".fa-bars");
  const xIcon = menuToggle.querySelector(".fa-xmark");

  navLinks.classList.toggle("active");

  if (navLinks.classList.contains("active")) {
    barsIcon.style.display = "none";
    xIcon.style.display = "block";
  } else {
    barsIcon.style.display = "block";
    xIcon.style.display = "none";
  }
}

// JavaScript for scroll reveal animations
function revealOnScroll() {
  const reveals = document.querySelectorAll(
    ".reveal, .reveal-left, .reveal-right, .reveal-scale"
  );

  reveals.forEach((element) => {
    const windowHeight = window.innerHeight;
    const elementTop = element.getBoundingClientRect().top;
    const elementVisible = 150;

    if (elementTop < windowHeight - elementVisible) {
      element.classList.add("active");
    }
  });
}

window.addEventListener("scroll", revealOnScroll);
window.addEventListener("load", revealOnScroll);

// JavaScript for scroll to top button
const scrollTopBtn = document.getElementById("scrollTopBtn");

window.addEventListener("scroll", () => {
  if (window.pageYOffset > 300) {
    scrollTopBtn.style.display = "block";
  } else {
    scrollTopBtn.style.display = "none";
  }
});

scrollTopBtn.addEventListener("click", () => {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

// JavaScript for CEO image modal
const ceoImage = document.getElementById("ceoImage");
const modal = document.getElementById("ceoModal");
const modalImage = document.getElementById("modalImage");
const closeBtn = document.querySelector(".close");

ceoImage.addEventListener("click", () => {
  modal.style.display = "block";
  modalImage.src = ceoImage.src;
});

closeBtn.addEventListener("click", () => {
  modal.style.display = "none";
});

window.addEventListener("click", (event) => {
  if (event.target === modal) {
    modal.style.display = "none";
  }
});

// JavaScript for navbar scroll effect
window.addEventListener("scroll", () => {
  const navbar = document.querySelector(".navbar");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});
