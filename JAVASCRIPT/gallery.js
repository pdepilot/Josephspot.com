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


// Filter Gallery Items
const filterButtons = document.querySelectorAll(".filter-btn");
const galleryItems = document.querySelectorAll(".gallery-item");

filterButtons.forEach((btn) => {
  btn.addEventListener("click", () => {
    filterButtons.forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    const filter = btn.getAttribute("data-filter");
    galleryItems.forEach((item) => {
      const show = filter === "all" || item.classList.contains(filter);
      item.style.display = show ? "block" : "none";
    });
  });
});

// Lightbox Functionality
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const lightboxVideo = document.getElementById("lightbox-video");

document.querySelectorAll(".gallery-item img").forEach((img) => {
  img.addEventListener("click", () => {
    lightbox.style.display = "flex";
    lightboxImg.style.display = "block";
    lightboxVideo.style.display = "none";
    lightboxImg.src = img.src;
  });
});

document.querySelectorAll(".gallery-item video").forEach((vid) => {
  vid.addEventListener("click", () => {
    lightbox.style.display = "flex";
    lightboxImg.style.display = "none";
    lightboxVideo.style.display = "block";
    lightboxVideo.src = vid.currentSrc || vid.src;
  });
});

function closeLightbox() {
  lightbox.style.display = "none";
  lightboxImg.src = "";
  lightboxVideo.pause();
  lightboxVideo.src = "";
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
