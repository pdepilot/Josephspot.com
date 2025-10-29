// Scroll effect for navbar
window.addEventListener("scroll", function () {
  const navbar = document.getElementById("navbar");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});

// Mobile Menu Toggle Function
function toggleMenu() {
  const navLinks = document.querySelector(".nav-links");
  const menuToggle = document.querySelector(".menu-toggle");

  // Toggle menu visibility
  navLinks.classList.toggle("active");

  // Toggle hamburger/close icon
  const icon = menuToggle.querySelector("i");
  if (navLinks.classList.contains("active")) {
    icon.classList.remove("fa-utensils");
    icon.classList.add("fa-xmark");
    icon.style.transform = "rotate(180deg)";
  } else {
    icon.classList.remove("fa-xmark");
    icon.classList.add("fa-utensils");
    icon.style.transform = "rotate(0deg)";
  }
}

// Close menu when clicking on a nav link
document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", () => {
    if (window.innerWidth <= 768) {
      toggleMenu();
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  // 1. Menu Data
  const menuItems = [
    {
      id: 1,
      title: "Ofe Owerri",
      description: "Filled With Snails, Stockfishes, Dryfishes, Lots of Protein",
      price: 2500,
      category: "soups",
      image: "./images/image20.jpg",
    },
    {
      id: 2,
      title: "Ofe Nsala White Soup",
      description: "Thickened With Pure White Yam, Filled With Snails, And Lots of Proteins",
      price: 3500,
      category: "soups",
      image: "./images/nsala.jpeg",
    },
    {
      id: 3,
      title: "Egusi Soup",
      description: "Melon seed soup with assorted meat and fish",
      price: 2800,
      category: "soups",
      image: "./images/egusi.jpg",
    },
    
    {
      id: 4,
      title: "Semo",
      description: "Smooth semovita wrap",
      price: 1500,
      category: "soups",
      image: "./images/semo.jpeg",
    },
    {
      id: 5,
      title: "Garri",
      description: "Smooth garri wrap",
      price: 1500,
      category: "soups",
      image: "./images/garri.JPG",
    },

    {
      id: 6,
      title: "Fufu",
      description: "Smooth fufu wrap",
      price: 1500,
      category: "soups",
      image: "./images/fufu.jpg",
    },

    {
      id: 7,
      title: "Poundo Yam",
      description: "Smooth poundo yam wrap",
      price: 3000,
      category: "soups",
      image: "./images/poundo.jpg",
    },

    {
      id: 8,
      title: "Oat Swallow",
      description: "Smooth oat swallow wrap",
      price: 3000,
      category: "soups",
      image: "./images/oat.jpg",
    },

    {
      id: 9,
      title: "Plaintain Flour Swallow",
      description: "Smooth plaintain flour swallow wrap",
      price: 3000,
      category: "soups",
      image: "./images/plantain.jpg",
    },






    {
      id: 5,
      title: "Jollof Rice",
      description: "Classic Nigerian jollof rice with chicken and plantain",
      price: 3200,
      category: "main courses",
      image: "./images/jollof rice.jpg",
    },
    {
      id: 6,
      title: "Fried Rice",
      description: "Special fried rice with mixed vegetables and beef",
      price: 3500,
      category: "main courses",
      image: "./images/fried rice.jpg",
    },
    {
      id: 7,
      title: "Bitterleaf Soup",
      description: "Smooth pounded yam or Fufu with soup filled with Happiness",
      price: 4000,
      category: "main courses",
      image: "./images/ogbonno soup.jpg",
    },
    {
      id: 8,
      title: "Nkwobi",
      description: "Goat Meat Sauced with Igbo Traditional Spiced and ugba",
      price: 3800,
      category: "main courses",
      image: "./images/nkwobi.jpeg",
    },
    {
      id: 9,
      title: "Chicken & Chips Max",
      description: "Crispy chicken served with golden fries",
      price: 15000,
      category: "noodles",
      image: "./images/IM42.jpg",
    },
    {
      id: 9,
      title: "Indomie Noodles",
      description: "Special indomie noodles with fried egg and sausages",
      price: 6000,
      category: "noodles",
      image: "./images/noodles.jpeg",
    },
    {
      id: 10,
      title: "Spaghetti",
      description: "Jollof spaghetti with chicken and vegetables",
      price: 7000,
      category: "noodles",
      image: "./images/spaghetti.jpg",
    },
    {
      id: 11,
      title: "Joe's Secret ",
      description: "Quick noodles with vegetables and eggs mixed with fried plantain",
      price: 25000,
      category: "noodles",
      image: "./images/2021-09-06.webp",
    },
    {
      id: 12,
      title: "Yam Noodles",
      description: "Healthy hotdog noodles with stir-fry vegetables",
      price: 3000,
      category: "noodles",
      image: "./images/noodle and hotdog.jpg",
    },
    {
      id: 13,
      title: "palm-Wine",
      description: "Refreshing Igbo Palm-wine drink",
      price: 10000,
      category: "drinks",
      image: "./images/IM30.jpg",
    },
    {
      id: 14,
      title: "Sprite",
      description: "Iced or Cold sprite drink",
      price: 1300,
      category: "drinks",
      image: "./images/images (10).jpeg",
    },
    {
      id: 15,
      title: "Fanta Orange ",
      description: "Iced or Cold Fanta drink",
      price: 1300,
      category: "drinks",
      image: "./images/images (8).jpeg",
    },
    {
      id: 16,
      title: "Coca Cola",
      description: "Iced or Cold Coca Cola drink",
      price: 1300,
      category: "drinks",
      image: "./images/images (9).jpeg",
    },
    {
      id: 17,
      title: "Malt,Fayrouz",
      description: "Iced or Cold Choice of Malt, Fayrouz drink",
      price: 1500,
      category: "drinks",
      image: "./images/malts.png",
    },
    {
      id: 18,
      title: "Small Bottled Water",
      description: "Iced or Cold Choice of bottled water",
      price: 500,
      category: "drinks",
      image: "./images/images (11).jpeg",
    },
    {
      id: 19,
      title: "Big Eva Bottled Water",
      description: "Iced or Cold Choice of bottled water",
      price: 2000,
      category: "drinks",
      image: "./images/IM43.png",
    },
    {
      id: 20,
      title: "Heineken, G.Stout, Despirado Beer",
      description: "Iced or Cold Heineken, G.Stout, Despirado Beer drink",
      price: 3500,
      category: "drinks",
      image: "./images/IM45.png",
    },
  ];

  // 2. DOM Elements
  const menuItemsContainer = document.getElementById("menuItems");
  const cartIcon = document.getElementById("cartIcon");
  const cartCount = document.querySelector(".cart-count");
  const categoryButtons = document.querySelectorAll(".category-btn");
  const checkoutModal = document.getElementById("checkoutModal");
  const closeCheckout = document.querySelector(".close-checkout");
  const cartItemsContainer = document.getElementById("cartItems");
  const subtotalElement = document.getElementById("subtotal");
  const totalAmountElement = document.getElementById("totalAmount");
  const clearCartBtn = document.getElementById("clearCart");
  const proceedToCheckoutBtn = document.getElementById("proceedToCheckout");
  const customerDetailsModal = document.getElementById("customerDetailsModal");
  const closeCustomerDetails = document.querySelector(".close-customer-details");
  const customerDetailsForm = document.getElementById("customerDetailsForm");
  const backToCartBtn = document.getElementById("backToCart");
  const paymentOptions = document.querySelectorAll('input[name="paymentMethod"]');
  const bankDetailsSection = document.getElementById("bankDetails");
  const copyAccountNumberBtn = document.getElementById("copyAccountNumber");
  const receiptModal = document.getElementById("receiptModal");
  const receiptItemsContainer = document.getElementById("receiptItems");
  const receiptSubtotal = document.getElementById("receiptSubtotal");
  const receiptTotal = document.getElementById("receiptTotal");
  const receiptCustomerName = document.getElementById("receiptCustomerName");
  const receiptCustomerEmail = document.getElementById("receiptCustomerEmail");
  const receiptCustomerPhone = document.getElementById("receiptCustomerPhone");
  const receiptCustomerState = document.getElementById("receiptCustomerState");
  const receiptCustomerAddress = document.getElementById("receiptCustomerAddress");
  const receiptPaymentMethod = document.getElementById("receiptPaymentMethod");
  const receiptPaymentDetails = document.getElementById("receiptPaymentDetails");
  const receiptOrderId = document.getElementById("receiptOrderId");
  const receiptDate = document.getElementById("receiptDate");
  const receiptQrCode = document.getElementById("receiptQrCode");
  const printReceiptBtn = document.getElementById("printReceipt");
  const downloadReceiptBtn = document.getElementById("downloadReceipt");
  const shareWhatsAppBtn = document.getElementById("shareWhatsApp");
  const shareEmailBtn = document.getElementById("shareEmail");
  const closeReceiptBtn = document.getElementById("closeReceipt");
  // Admin Elements
  const adminLoginBtn = document.getElementById("adminLoginBtn");
  const adminLoginModal = document.getElementById("adminLoginModal");
  const closeAdminLogin = document.querySelector(".close-admin-login");
  const adminLoginForm = document.getElementById("adminLoginForm");
  const adminDashboard = document.getElementById("adminDashboard");
  const closeAdminDashboard = document.querySelector(".close-admin-dashboard");
  const totalOrdersCount = document.getElementById("totalOrdersCount");
  const pendingOrdersCount = document.getElementById("pendingOrdersCount");
  const totalRevenue = document.getElementById("totalRevenue");
  const ordersTableBody = document.getElementById("ordersTableBody");
  const adminLogoutBtn = document.getElementById("adminLogout");
  const dashboardTabs = document.querySelectorAll(".dashboard-tab");
  const ordersTableTitle = document.getElementById("ordersTableTitle");
  // Password Modal Elements
  const adminPasswordModal = document.getElementById("adminPasswordModal");
  const closeAdminPassword = document.querySelector(".close-admin-password");
  const adminActionPassword = document.getElementById("adminActionPassword");
  const cancelPasswordBtn = document.getElementById("cancelPassword");
  const submitPasswordBtn = document.getElementById("submitPassword");
  const passwordPrompt = document.getElementById("passwordPrompt");
  // Toast and Empty Cart Elements
  const toastContainer = document.getElementById("toastContainer");
  const emptyCartPrompt = document.getElementById("emptyCartPrompt");
  const closeEmptyCartBtn = document.getElementById("closeEmptyCart");
  const browseMenuBtn = document.getElementById("browseMenu");
  // Proof of Payment Modal Elements
  const proofViewModal = document.getElementById("proofViewModal");
  const closeProofView = document.querySelector(".close-proof-view");
  const proofImage = document.getElementById("proofImage");

  // Admin Credentials
  const ADMIN_CREDENTIALS = {
    username: "admin",
    password: "admin123",
    actionPassword: "secure123",
  };

  // 3. Cart State
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let currentTab = "pending";
  let currentAction = null;
  let currentOrderId = null;

  // 4. Initialize App
  function init() {
    console.log("Initializing app");
    renderMenuItems();
    updateCartCount();
    setupEventListeners();
    setupAdminEventListeners();
    initializeSampleOrders();
    closeEmptyCartBtn.addEventListener("click", closeEmptyCartPrompt);
    browseMenuBtn.addEventListener("click", function () {
      closeEmptyCartPrompt();
      document.getElementById("menu").scrollIntoView({ behavior: "smooth" });
    });
  }

  // Initialize sample orders
  function initializeSampleOrders() {
    console.log("Checking for sample orders");
    let orders = JSON.parse(localStorage.getItem("orders"));
    if (!Array.isArray(orders) || orders.length === 0) {
      console.log("No orders found, initializing sample orders");
      const sampleOrders = [
        {
          id: "GD10001",
          customerName: "Chinwe Okoro",
          customerEmail: "chinwe.okoro@example.com",
          customerPhone: "08031234567",
          customerAddress: "12 Nnamdi Azikiwe Road, Enugu",
          items: [
            {
              id: 1,
              title: "Ofe Owerri",
              price: 2500,
              quantity: 2,
              image: "https://images.unsplash.com/photo-1547592166-23ac45744acd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
            {
              id: 17,
              title: "Malt",
              price: 1800,
              quantity: 1,
              image: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
          ],
          subtotal: 6800,
          deliveryFee: 1500,
          total: 8300,
          paymentMethod: "cod",
          date: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
          dateFormatted: new Date(Date.now() - 2 * 60 * 60 * 1000).toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          }),
          status: "pending",
        },
        {
          id: "GD10002",
          customerName: "Emeka Nwankwo",
          customerEmail: "emeka.nwankwo@example.com",
          customerPhone: "08029876543",
          customerAddress: "45 Okigwe Road, Owerri",
          items: [
            {
              id: 5,
              title: "Jollof Rice",
              price: 3200,
              quantity: 1,
              image: "https://images.unsplash.com/photo-1593219531316-511804d4b5e3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
            {
              id: 6,
              title: "Fried Rice",
              price: 3500,
              quantity: 1,
              image: "https://images.unsplash.com/photo-1630918037678-a8f7e0f9c9c6?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
          ],
          subtotal: 6700,
          deliveryFee: 1500,
          total: 8200,
          paymentMethod: "bank",
          proofOfPayment: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAB9SURBVFhH7ZLRDcAgCEO9/6Un8Gi0oBvoBrqBAaQ7KULM3b2V3F0hF0gBXACXgAswAFwAF8AFcAFcABfABXABXAAXwAVwAVwAF8AFcAFcABfABXABXAAXwAVwAVwAF8AFcAFcABfABXABXAAXwAVwAVwAF8AFcAFcABfABXABXAAXwAb8AB2vA0E8RAAAAAElFTkSuQmCC",
          date: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
          dateFormatted: new Date(Date.now() - 24 * 60 * 60 * 1000).toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          }),
          status: "completed",
        },
      ];
      localStorage.setItem("orders", JSON.stringify(sampleOrders));
      orders = sampleOrders;
    }
    console.log("Orders initialized:", orders);
  }

  // 5. Render Menu Items
  function renderMenuItems(category = "all") {
    menuItemsContainer.innerHTML = "";
    const filteredItems = category === "all" ? menuItems : menuItems.filter((item) => item.category === category);

    if (filteredItems.length === 0) {
      menuItemsContainer.innerHTML = '<p class="no-items">No items in this category</p>';
      return;
    }

    filteredItems.forEach((item) => {
      const menuItemElement = document.createElement("div");
      menuItemElement.className = "menu-item";
      menuItemElement.innerHTML = `
        <img src="${item.image}" alt="${item.title}" class="menu-item-img">
        <div class="menu-item-content">
          <h3 class="menu-item-title">${item.title}</h3>
          <p class="menu-item-desc">${item.description}</p>
          <div class="menu-item-footer">
            <span class="menu-item-price">₦${item.price.toLocaleString()}</span>
            <button class="add-to-cart" data-id="${item.id}">Add to Cart</button>
          </div>
        </div>
      `;
      menuItemsContainer.appendChild(menuItemElement);
    });
  }

  // 6. Setup Event Listeners
  function setupEventListeners() {
    categoryButtons.forEach((button) => {
      button.addEventListener("click", function () {
        categoryButtons.forEach((btn) => btn.classList.remove("active"));
        this.classList.add("active");
        renderMenuItems(this.dataset.category);
      });
    });

    menuItemsContainer.addEventListener("click", function (e) {
      if (e.target.classList.contains("add-to-cart")) {
        const itemId = parseInt(e.target.getAttribute("data-id"));
        addToCart(itemId);
      }
    });

    cartIcon.addEventListener("click", function () {
      if (cart.length === 0) {
        showEmptyCartPrompt();
      } else {
        openCheckoutModal();
      }
    });

    closeCheckout.addEventListener("click", closeCheckoutModal);
    clearCartBtn.addEventListener("click", clearCart);
    proceedToCheckoutBtn.addEventListener("click", openCustomerDetails);
    closeCustomerDetails.addEventListener("click", closeCustomerDetailsModal);
    backToCartBtn.addEventListener("click", function () {
      closeCustomerDetailsModal();
      openCheckoutModal();
    });
    paymentOptions.forEach((option) => {
      option.addEventListener("change", handlePaymentMethodChange);
    });
    copyAccountNumberBtn.addEventListener("click", copyAccountNumber);
    customerDetailsForm.addEventListener("submit", handleFormSubmission);
    printReceiptBtn.addEventListener("click", printReceipt);
    downloadReceiptBtn.addEventListener("click", downloadReceipt);
    shareWhatsAppBtn.addEventListener("click", shareViaWhatsApp);
    shareEmailBtn.addEventListener("click", shareViaEmail);
    closeReceiptBtn.addEventListener("click", closeReceiptModal);
  }

  // Setup Admin Event Listeners
  function setupAdminEventListeners() {
    console.log("Setting up admin event listeners");
    adminLoginBtn.addEventListener("click", openAdminLogin);
    closeAdminLogin.addEventListener("click", closeAdminLoginModal);
    adminLoginForm.addEventListener("submit", handleAdminLogin);
    closeAdminDashboard.addEventListener("click", closeAdminDashboardModal);
    adminLogoutBtn.addEventListener("click", handleAdminLogout);

    dashboardTabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        console.log("Tab clicked:", this.dataset.tab);
        dashboardTabs.forEach((t) => t.classList.remove("active"));
        this.classList.add("active");
        currentTab = this.dataset.tab;
        updateOrdersTableTitle();
        loadAdminData();
      });
    });

    closeAdminPassword.addEventListener("click", closeAdminPasswordModal);
    cancelPasswordBtn.addEventListener("click", closeAdminPasswordModal);
    submitPasswordBtn.addEventListener("click", handlePasswordSubmit);

    if (closeProofView) {
      closeProofView.addEventListener("click", closeProofViewModal);
    } else {
      console.error("closeProofView element not found");
    }

    if (ordersTableBody) {
      ordersTableBody.addEventListener("click", function (e) {
        if (e.target.classList.contains("proof-thumbnail")) {
          console.log("Thumbnail clicked, order ID:", e.target.dataset.id);
          const orderId = e.target.dataset.id;
          const orders = JSON.parse(localStorage.getItem("orders")) || [];
          const order = orders.find((o) => o.id === orderId);
          if (order && order.proofOfPayment) {
            console.log("Opening modal with image:", order.proofOfPayment);
            openProofViewModal(order.proofOfPayment);
          } else {
            console.warn("No proof of payment found for order:", orderId);
            showToast("No proof of payment available for this order.", true);
          }
        }
      });
    } else {
      console.error("ordersTableBody not found");
    }
  }

  function updateOrdersTableTitle() {
    console.log("Updating table title for tab:", currentTab);
    switch (currentTab) {
      case "pending":
        ordersTableTitle.textContent = "Pending Orders";
        break;
      case "completed":
        ordersTableTitle.textContent = "Completed Orders";
        break;
      case "all":
        ordersTableTitle.textContent = "All Orders";
        break;
    }
  }

  function formatPaymentMethod(method) {
    switch (method) {
      case "cod":
        return '<span class="payment-badge payment-cod">Cash on Delivery</span>';
      case "bank":
        return '<span class="payment-badge payment-bank">Bank Transfer</span>';
      case "paystack":
        return '<span class="payment-badge payment-paystack">Paystack</span>';
      case "flutterwave":
        return '<span class="payment-badge payment-flutterwave">Flutterwave</span>';
      default:
        return '<span class="payment-badge">' + method + "</span>";
    }
  }

  // 7. Cart Functions
  function addToCart(itemId) {
    const existingItem = cart.find((item) => item.id === itemId);

    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      const menuItem = menuItems.find((item) => item.id === itemId);
      if (menuItem) {
        cart.push({
          ...menuItem,
          quantity: 1,
        });
      }
    }

    updateCart();
    animateCartIcon();

    const addedItem = menuItems.find((item) => item.id === itemId);
    if (addedItem) {
      showToast(`Added ${addedItem.title} to cart`);
    }
  }

  function updateCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    renderCartItems();
    calculateTotals();
  }

  function updateCartCount() {
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCount.textContent = totalItems;
    cartCount.style.display = totalItems > 0 ? "flex" : "none";
  }

  function renderCartItems() {
    cartItemsContainer.innerHTML = "";

    if (cart.length === 0) {
      cartItemsContainer.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
      return;
    }

    cart.forEach((item) => {
      const cartItemElement = document.createElement("div");
      cartItemElement.className = "cart-item";
      cartItemElement.innerHTML = `
        <img src="${item.image}" alt="${item.title}" class="cart-item-img">
        <div class="cart-item-details">
          <h4 class="cart-item-title">${item.title}</h4>
          <p class="cart-item-price">₦${(item.price * item.quantity).toLocaleString()}</p>
        </div>
        <div class="quantity-control">
          <button class="quantity-btn minus" data-id="${item.id}">-</button>
          <span class="quantity">${item.quantity}</span>
          <button class="quantity-btn plus" data-id="${item.id}">+</button>
        </div>
        <button class="remove-item" data-id="${item.id}">
          <i class="fas fa-trash"></i>
        </button>
      `;
      cartItemsContainer.appendChild(cartItemElement);
    });

    document.querySelectorAll(".quantity-btn.minus").forEach((btn) => {
      btn.addEventListener("click", () => updateQuantity(parseInt(btn.dataset.id), -1));
    });

    document.querySelectorAll(".quantity-btn.plus").forEach((btn) => {
      btn.addEventListener("click", () => updateQuantity(parseInt(btn.dataset.id), 1));
    });

    document.querySelectorAll(".remove-item").forEach((btn) => {
      btn.addEventListener("click", () => removeItem(parseInt(btn.dataset.id)));
    });
  }

  function updateQuantity(itemId, change) {
    const itemIndex = cart.findIndex((item) => item.id === itemId);
    if (itemIndex !== -1) {
      cart[itemIndex].quantity += change;
      if (cart[itemIndex].quantity <= 0) {
        cart.splice(itemIndex, 1);
      }
      updateCart();
    }
  }

  function removeItem(itemId) {
    cart = cart.filter((item) => item.id !== itemId);
    updateCart();
  }

  function clearCart() {
    cart = [];
    updateCart();
    closeCheckoutModal();
  }

  function calculateTotals() {
    const subtotal = cart.reduce((total, item) => total + item.price * item.quantity, 0);
    const deliveryFee = 1500;
    const total = subtotal + deliveryFee;

    subtotalElement.textContent = `₦${subtotal.toLocaleString()}`;
    totalAmountElement.textContent = `₦${total.toLocaleString()}`;
  }

  // 8. Modal Functions
  function openCheckoutModal() {
    checkoutModal.style.display = "flex";
    document.body.style.overflow = "hidden";
    renderCartItems();
    calculateTotals();
  }

  function closeCheckoutModal() {
    checkoutModal.style.display = "none";
    document.body.style.overflow = "auto";
  }

  function openCustomerDetails() {
    if (cart.length === 0) return;
    checkoutModal.style.display = "none";
    customerDetailsModal.style.display = "flex";
  }

  function closeCustomerDetailsModal() {
    customerDetailsModal.style.display = "none";
    document.body.style.overflow = "auto";
  }

  function openReceiptModal() {
    customerDetailsModal.style.display = "none";
    receiptModal.style.display = "flex";
  }

  function closeReceiptModal() {
    receiptModal.style.display = "none";
    document.body.style.overflow = "auto";
  }

  function openAdminLogin() {
    adminLoginModal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }

  function closeAdminLoginModal() {
    adminLoginModal.style.display = "none";
    document.body.style.overflow = "auto";
  }

  function openAdminDashboard() {
    adminLoginModal.style.display = "none";
    adminDashboard.style.display = "flex";
    document.body.style.overflow = "hidden";
    loadAdminData();
  }

  function closeAdminDashboardModal() {
    adminDashboard.style.display = "none";
    document.body.style.overflow = "auto";
  }

  function openAdminPasswordModal(action, orderId) {
    console.log(`Opening password modal for action: ${action}, order: ${orderId}`);
    currentAction = action;
    currentOrderId = orderId;

    if (action === "delete") {
      passwordPrompt.textContent = "Please enter your admin password to delete this order";
    }

    adminPasswordModal.style.display = "flex";
    document.body.style.overflow = "hidden";

    setTimeout(() => {
      adminActionPassword.focus();
    }, 100);
  }

  function closeAdminPasswordModal() {
    adminPasswordModal.style.display = "none";
    document.body.style.overflow = "auto";
    adminActionPassword.value = "";
    currentAction = null;
    currentOrderId = null;
  }

  function openProofViewModal(imageSrc) {
    if (proofViewModal && proofImage) {
      console.log("Setting proof image src:", imageSrc);
      proofImage.src = imageSrc;
      proofViewModal.style.display = "flex";
      document.body.style.overflow = "hidden";
    } else {
      console.error("proofViewModal or proofImage not found");
      showToast("Error: Unable to display proof of payment.", true);
    }
  }

  function closeProofViewModal() {
    if (proofViewModal && proofImage) {
      proofViewModal.style.display = "none";
      proofImage.src = "";
      document.body.style.overflow = "auto";
    } else {
      console.error("proofViewModal or proofImage not found");
    }
  }

  function handleAdminLogout() {
    closeAdminDashboardModal();
  }

  // 9. Payment Handling
  function handlePaymentMethodChange(e) {
    bankDetailsSection.style.display = e.target.value === "bank" ? "block" : "none";
    const proofUploadInput = document.getElementById("proofUpload");
    if (e.target.value === "bank") {
      proofUploadInput.required = true;
    } else {
      proofUploadInput.required = false;
    }
  }

  function copyAccountNumber() {
    const accountNumber = document.getElementById("accountNumber").textContent;
    navigator.clipboard.writeText(accountNumber).then(() => {
      const originalText = copyAccountNumberBtn.textContent;
      copyAccountNumberBtn.textContent = "Copied!";
      setTimeout(() => (copyAccountNumberBtn.textContent = originalText), 2000);
    });
  }

  // 10. Form Handling
  async function handleFormSubmission(e) {
    e.preventDefault();

    const formData = {
      fullName: document.getElementById("fullName").value,
      email: document.getElementById("email").value,
      phone: document.getElementById("phone").value,
      state: document.getElementById("state").value,
      address: document.getElementById("address").value,
      deliveryNotes: document.getElementById("deliveryNotes").value,
      paymentMethod: document.querySelector('input[name="paymentMethod"]:checked').value,
      proofUpload: document.getElementById("proofUpload").files[0] ? "Uploaded" : "Not provided",
    };

    const subtotal = cart.reduce((total, item) => total + item.price * item.quantity, 0);
    const totalAmount = subtotal + 1500;

    if (formData.paymentMethod === "bank" && !document.getElementById("proofUpload").files[0]) {
      showToast("Please upload proof of payment for bank transfer.", true);
      return;
    }

    const submitButton = document.querySelector('.btn-submit');
    const originalText = submitButton.textContent;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitButton.disabled = true;

    const formInputs = customerDetailsForm.querySelectorAll('input, textarea, select, button');
    formInputs.forEach(input => {
      input.disabled = true;
    });

    try {
      if (formData.paymentMethod === 'paystack') {
        await loadPaystackScript();
        processPaystackPayment(formData, subtotal, totalAmount * 100);
        return;
      } else if (formData.paymentMethod === 'flutterwave') {
        await loadFlutterwaveScript();
        processFlutterwavePayment(formData, subtotal, totalAmount * 100);
        return;
      } else {
        const order = await saveOrder(formData, subtotal, totalAmount, "pending");
        if (!order) {
          throw new Error("Order creation failed");
        }
        cart = [];
        updateCart();
        openReceiptModal();
        generateReceipt(formData, order.items, subtotal, totalAmount);
      }
    } catch (error) {
      console.error('Payment processing error:', error);
      showToast('Payment processing failed. Please try again.', true);
    } finally {
      submitButton.textContent = originalText;
      submitButton.disabled = false;
      formInputs.forEach(input => {
        input.disabled = false;
      });
    }
  }

  function readFileAsBase64(file) {
    return new Promise((resolve, reject) => {
      if (!file || !file.type.startsWith("image/")) {
        reject(new Error("Invalid file type. Please upload an image (PNG, JPEG)."));
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        reject(new Error("Image size exceeds 2MB limit."));
        return;
      }
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = () => reject(new Error("Failed to read file"));
      reader.readAsDataURL(file);
    });
  }

  async function saveOrder(formData, subtotal, totalAmount, status = "pending") {
    const now = new Date();
    let proofOfPayment = null;

    if (formData.paymentMethod === "bank" && document.getElementById("proofUpload").files[0]) {
      try {
        const file = document.getElementById("proofUpload").files[0];
        proofOfPayment = await readFileAsBase64(file);
      } catch (error) {
        console.error("Error reading proof of payment:", error);
        showToast(error.message, true);
        return null;
      }
    }

    const order = {
      id: "GD" + Math.floor(10000 + Math.random() * 90000),
      customerName: formData.fullName,
      customerEmail: formData.email,
      customerPhone: formData.phone,
      customerAddress: formData.address,
      items: [...cart],
      subtotal: subtotal,
      deliveryFee: 1500,
      total: totalAmount,
      paymentMethod: formData.paymentMethod,
      proofOfPayment: proofOfPayment,
      date: now.toISOString(),
      dateFormatted: now.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      }),
      status: status,
    };

    const orders = JSON.parse(localStorage.getItem("orders")) || [];
    orders.push(order);
    localStorage.setItem("orders", JSON.stringify(orders));

    return order;
  }

  function handleAdminLogin(e) {
    e.preventDefault();

    const username = document.getElementById("adminUsername").value;
    const password = document.getElementById("adminPassword").value;

    if (username === ADMIN_CREDENTIALS.username && password === ADMIN_CREDENTIALS.password) {
      closeAdminLoginModal();
      openAdminDashboard();
      showToast("Admin login successful!");
    } else {
      showToast("Invalid admin credentials", true);
    }
  }

  function handlePasswordSubmit() {
    console.log("Handling password submit");
    const password = adminActionPassword.value;

    if (password === ADMIN_CREDENTIALS.actionPassword) {
      if (currentAction === "delete" && currentOrderId) {
        deleteOrder(currentOrderId);
        showToast(`Order ${currentOrderId} has been deleted.`);
      }
      closeAdminPasswordModal();
    } else {
      showToast("Incorrect action password. Action not authorized.", true);
      adminActionPassword.value = "";
    }
  }

  function handleOrderAction(action, orderId) {
    console.log(`Handling action: ${action} for order: ${orderId}`);
    let orders = JSON.parse(localStorage.getItem("orders")) || [];
    const orderIndex = orders.findIndex((order) => order.id === orderId);

    if (orderIndex !== -1) {
      if (action === "complete") {
        orders[orderIndex].status = "completed";
        localStorage.setItem("orders", JSON.stringify(orders));
        showToast(`Order ${orderId} marked as completed.`);
        loadAdminData();
      } else if (action === "delete") {
        openAdminPasswordModal("delete", orderId);
      } else if (action === "view") {
        viewOrder(orderId);
      }
    } else {
      showToast(`Order ${orderId} not found.`, true);
    }
  }

  function deleteOrder(orderId) {
    console.log(`Deleting order: ${orderId}`);
    let orders = JSON.parse(localStorage.getItem("orders")) || [];
    orders = orders.filter((order) => order.id !== orderId);
    localStorage.setItem("orders", JSON.stringify(orders));
    showToast(`Order ${orderId} has been deleted.`);
    loadAdminData();
  }

  function viewOrder(orderId) {
    const orders = JSON.parse(localStorage.getItem("orders")) || [];
    const order = orders.find((order) => order.id === orderId);

    if (order) {
      let orderDetails = `Order Details:\n\n`;
      orderDetails += `Order ID: ${order.id}\n`;
      orderDetails += `Customer: ${order.customerName}\n`;
      orderDetails += `Phone: ${order.customerPhone}\n`;
      orderDetails += `Email: ${order.customerEmail}\n`;
      orderDetails += `Address: ${order.customerAddress}\n\n`;
      orderDetails += `Items:\n`;
      order.items.forEach(item => {
        orderDetails += `- ${item.title} x${item.quantity} - ₦${(item.price * item.quantity).toLocaleString()}\n`;
      });
      orderDetails += `\nSubtotal: ₦${order.subtotal.toLocaleString()}\n`;
      orderDetails += `Delivery Fee: ₦${order.deliveryFee.toLocaleString()}\n`;
      orderDetails += `Total: ₦${order.total.toLocaleString()}\n\n`;
      orderDetails += `Payment Method: ${order.paymentMethod}\n`;
      orderDetails += `Proof of Payment: ${order.proofOfPayment ? "Uploaded" : "Not provided"}\n`;
      orderDetails += `Status: ${order.status}\n`;
      orderDetails += `Date: ${order.dateFormatted || new Date(order.date).toLocaleString()}`;

      alert(orderDetails);
    }
  }

  function loadAdminData() {
    console.log("Loading admin data for tab:", currentTab);
    let orders = JSON.parse(localStorage.getItem("orders"));
    if (!Array.isArray(orders)) {
      console.warn("Orders in localStorage is not an array, resetting to empty array");
      orders = [];
      localStorage.setItem("orders", JSON.stringify(orders));
    }
    console.log("All orders:", orders);

    totalOrdersCount.textContent = orders.length;
    const pendingOrders = orders.filter((order) => order.status === "pending");
    pendingOrdersCount.textContent = pendingOrders.length;
    const revenue = orders
      .filter((order) => order.status === "completed")
      .reduce((total, order) => total + (order.total || 0), 0);
    totalRevenue.textContent = `₦${revenue.toLocaleString()}`;

    if (ordersTableBody) {
      ordersTableBody.innerHTML = "";
    } else {
      console.error("ordersTableBody not found in DOM");
      showToast("Error: Orders table not found.", true);
      return;
    }

    let filteredOrders = [];
    switch (currentTab) {
      case "pending":
        filteredOrders = orders.filter((order) => order.status === "pending");
        break;
      case "completed":
        filteredOrders = orders.filter((order) => order.status === "completed");
        break;
      case "all":
        filteredOrders = orders;
        break;
      default:
        filteredOrders = orders;
    }
    console.log("Filtered orders:", filteredOrders);

    if (filteredOrders.length === 0) {
      ordersTableBody.innerHTML = `
        <tr>
          <td colspan="9" style="text-align: center; padding: 20px;">No orders found</td>
        </tr>
      `;
      return;
    }

    const sortedOrders = [...filteredOrders].sort(
      (a, b) => new Date(b.date) - new Date(a.date)
    );

    sortedOrders.forEach((order) => {
      const row = document.createElement("tr");
      const proofCell = order.paymentMethod === "bank" && order.proofOfPayment
        ? `<img src="${order.proofOfPayment}" class="proof-thumbnail" data-id="${order.id}" alt="Proof of Payment" />`
        : "N/A";
      row.innerHTML = `
        <td>${order.id}</td>
        <td>${order.customerName}</td>
        <td>${order.customerPhone}</td>
        <td>₦${(order.total || 0).toLocaleString()}</td>
        <td>${formatPaymentMethod(order.paymentMethod)}</td>
        <td class="order-date">${order.dateFormatted || new Date(order.date).toLocaleString()}</td>
        <td class="status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</td>
        <td>${proofCell}</td>
        <td>
          ${
            order.status === "pending"
              ? `<button class="action-btn complete-order" data-id="${order.id}">Complete</button>
                 <button class="action-btn delete-order" data-id="${order.id}" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Delete</button>`
              : `<button class="action-btn view-order" data-id="${order.id}">View</button>
                 <button class="action-btn delete-order" data-id="${order.id}" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Delete</button>`
          }
        </td>
      `;
      ordersTableBody.appendChild(row);
    });

    attachOrderActionListeners();
  }

  function attachOrderActionListeners() {
    console.log("Attaching order action listeners");
    const oldButtons = document.querySelectorAll(".complete-order, .delete-order, .view-order");
    oldButtons.forEach(btn => btn.replaceWith(btn.cloneNode(true)));

    const completeButtons = document.querySelectorAll(".complete-order");
    const deleteButtons = document.querySelectorAll(".delete-order");
    const viewButtons = document.querySelectorAll(".view-order");

    completeButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        console.log(`Complete button clicked for order: ${btn.dataset.id}`);
        handleOrderAction("complete", btn.dataset.id);
      });
    });

    deleteButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        console.log(`Delete button clicked for order: ${btn.dataset.id}`);
        handleOrderAction("delete", btn.dataset.id);
      });
    });

    viewButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        console.log(`View button clicked for order: ${btn.dataset.id}`);
        handleOrderAction("view", btn.dataset.id);
      });
    });
  }

  function generateReceipt(formData = {}, items = [], subtotal = 0, totalAmount = 0) {
    const orderId = "GD" + Math.floor(10000 + Math.random() * 90000);
    const now = new Date();

    const dateOptions = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    };

    const timeOptions = {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: true,
    };

    const currentDate = now.toLocaleDateString("en-US", dateOptions);
    const currentTime = now.toLocaleTimeString("en-US", timeOptions);
    const currentDateTime = `${currentDate} at ${currentTime}`;

    receiptOrderId.textContent = orderId;
    receiptDate.textContent = currentDateTime;
    receiptQrCode.innerHTML = "";

    receiptCustomerName.textContent = formData.fullName || "Walk-in Customer";
    receiptCustomerEmail.textContent = formData.email || "Not provided";
    receiptCustomerPhone.textContent = formData.phone || "N/A";
    receiptCustomerState.textContent = formData.state || "Not specified";
    receiptCustomerAddress.textContent = formData.address || "In-store pickup";

    receiptItemsContainer.innerHTML = `
      <tr>
        <th class="item-name">Item</th>
        <th class="item-qty">Qty</th>
        <th class="item-price">Price</th>
      </tr>
    `;

    let calculatedSubtotal = 0;
    if (items.length > 0) {
      items.forEach((item) => {
        const itemTotal = item.price * item.quantity;
        calculatedSubtotal += itemTotal;
        receiptItemsContainer.innerHTML += `
          <tr>
            <td>${item.title}</td>
            <td class="item-qty">${item.quantity}</td>
            <td class="item-price">₦${itemTotal.toLocaleString()}</td>
          </tr>
        `;
      });
    } else {
      calculatedSubtotal = subtotal;
    }

    const deliveryFee = 1500;
    receiptItemsContainer.innerHTML += `
      <tr>
        <td colspan="2">Delivery Fee</td>
        <td class="item-price">₦${deliveryFee.toLocaleString()}</td>
      </tr>
    `;

    const total = calculatedSubtotal + deliveryFee;
    receiptSubtotal.textContent = `₦${calculatedSubtotal.toLocaleString()}`;
    receiptTotal.textContent = `₦${total.toLocaleString()}`;

    if (formData.paymentMethod) {
      let paymentDetails = "";
      switch (formData.paymentMethod) {
        case "cod":
          receiptPaymentMethod.textContent = "Cash on Delivery";
          paymentDetails = "<p>Payment to be collected upon delivery</p>";
          break;
        case "bank":
          receiptPaymentMethod.textContent = "Bank Transfer";
          paymentDetails = `
            <p><strong>Bank Name:</strong> Zenith Bank</p>
            <p><strong>Account Name:</strong> Joseph's Pot Ltd</p>
            <p><strong>Account Number:</strong> 1012345678</p>
            ${formData.proofUpload === "Uploaded" ? "<p><strong>Proof Uploaded:</strong> Yes</p>" : ""}
          `;
          break;
        case "paystack":
          receiptPaymentMethod.textContent = "Paystack";
          paymentDetails = `
            <p>Paid via Paystack payment gateway</p>
            ${formData.paymentReference ? `<p><strong>Reference:</strong> ${formData.paymentReference}</p>` : ""}
          `;
          break;
        case "flutterwave":
          receiptPaymentMethod.textContent = "Flutterwave";
          paymentDetails = `
            <p>Paid via Flutterwave payment gateway</p>
            ${formData.paymentReference ? `<p><strong>Reference:</strong> ${formData.paymentReference}</p>` : ""}
          `;
          break;
        default:
          receiptPaymentMethod.textContent = "Unknown";
          paymentDetails = "<p>Payment method not specified</p>";
      }
      receiptPaymentDetails.innerHTML = paymentDetails;
    }

    try {
      const qrCodeElement = document.createElement("canvas");
      receiptQrCode.appendChild(qrCodeElement);
      QRCode.toCanvas(
        qrCodeElement,
        `Order ID: ${orderId}\nDate: ${currentDateTime}\nTotal: ₦${total.toLocaleString()}`,
        {
          width: 150,
          margin: 2,
          color: { dark: "#292f36", light: "#ffffff" },
          errorCorrectionLevel: "H",
        }
      );
    } catch (error) {
      console.error("QR code generation failed:", error);
    }
  }

  function printReceipt() {
    const printContent = document.querySelector(".receipt-container").innerHTML;
    const originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    openReceiptModal();
  }

  function downloadReceipt() {
    const element = document.querySelector(".receipt-container").cloneNode(true);
    const actions = element.querySelector(".receipt-actions");
    if (actions) actions.remove();

    const opt = {
      margin: 10,
      filename: `receipt_${receiptOrderId.textContent}.pdf`,
      image: { type: "jpeg", quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
    };

    html2pdf().from(element).set(opt).save();
  }

  function shareViaWhatsApp() {
    const orderId = receiptOrderId.textContent;
    const total = receiptTotal.textContent;
    const message = `My order from Joseph's Pot - Order #${orderId} - Total: ${total}`;
    const encodedMessage = encodeURIComponent(message);

    const whatsappUrl = `https://api.whatsapp.com/send?text=${encodedMessage}`;
    const mobileUrl = `whatsapp://send?text=${encodedMessage}`;

    window.open(whatsappUrl, "_blank");

    setTimeout(() => {
      if (!document.hidden) {
        window.location.href = mobileUrl;
      }
    }, 500);
  }

  function shareViaEmail() {
    const orderId = receiptOrderId.textContent;
    const total = receiptTotal.textContent;
    window.open(
      `mailto:?subject=${encodeURIComponent(`My Order #${orderId} from Joseph's Pot`)}&body=${encodeURIComponent(
        `Hi,\n\nHere's my order details:\nOrder ID: ${orderId}\nTotal: ${total}\n\nThank you!`
      )}`,
      "_blank"
    );
  }

  function processPaystackPayment(formData, subtotal, amount) {
    showProcessingState(true);

    const handler = PaystackPop.setup({
      key: "pk_test_26f8c2230ec7838bcf82ad3e199674e777ccfac0",
      email: formData.email,
      amount: amount,
      currency: "NGN",
      ref: "GD-" + Date.now(),
      callback: function (response) {
        formData.paymentReference = response.reference;
        const order = saveOrder(formData, subtotal, amount / 100, "completed");
        cart = [];
        updateCart();
        openReceiptModal();
        generateReceipt(formData, order.items, subtotal, amount / 100);
        showProcessingState(false);
      },
      onClose: function () {
        alert("Payment window closed");
        showProcessingState(false);
      },
    });
    handler.openIframe();
  }

  function loadPaystackScript() {
    return new Promise((resolve) => {
      if (window.PaystackPop) return resolve();
      const script = document.createElement("script");
      script.src = "https://js.paystack.co/v1/inline.js";
      script.onload = resolve;
      document.head.appendChild(script);
    });
  }

  function processFlutterwavePayment(formData, subtotal, amount) {
    showProcessingState(true);

    FlutterwaveCheckout({
      public_key: "FLWPUBK_TEST-598a8b4cadcb2c02ca9b177034b11e16-X",
      tx_ref: "GD-" + Date.now(),
      amount: amount / 100,
      currency: "NGN",
      payment_options: "card,mobilemoney,ussd",
      customer: {
        email: formData.email,
        phone_number: formData.phone,
        name: formData.fullName,
      },
      callback: function (response) {
        formData.paymentReference = response.tx_ref;
        const order = saveOrder(formData, subtotal, amount / 100, "completed");
        cart = [];
        updateCart();
        openReceiptModal();
        generateReceipt(formData, order.items, subtotal, amount / 100);
        showProcessingState(false);
      },
      onclose: function () {
        alert("Payment window closed");
        showProcessingState(false);
      },
      customizations: {
        title: "Joseph's Pot",
        description: "Payment for your delicious order",
        logo: "https://via.placeholder.com/100x100?text=JP",
      },
    });
  }

  function loadFlutterwaveScript() {
    return new Promise((resolve) => {
      if (window.FlutterwaveCheckout) return resolve();
      const script = document.createElement("script");
      script.src = "https://checkout.flutterwave.com/v3.js";
      script.onload = resolve;
      document.head.appendChild(script);
    });
  }

  function showProcessingState(show) {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const submitButton = document.querySelector('.btn-submit');
    const formInputs = customerDetailsForm.querySelectorAll('input, textarea, select, button');

    if (show) {
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
      submitButton.disabled = true;
      formInputs.forEach(input => {
        input.disabled = true;
        input.style.opacity = '0.7';
      });
      paymentOptions.forEach(option => {
        option.style.opacity = '0.7';
        option.style.pointerEvents = 'none';
      });
    } else {
      submitButton.innerHTML = 'Submit Order';
      submitButton.disabled = false;
      formInputs.forEach(input => {
        input.disabled = false;
        input.style.opacity = '1';
      });
      paymentOptions.forEach(option => {
        option.style.opacity = '1';
        option.style.pointerEvents = 'auto';
      });
    }
  }

  function animateCartIcon() {
    cartIcon.style.transform = "scale(1.2)";
    setTimeout(() => {
      cartIcon.style.transform = "scale(1)";
    }, 300);
  }

  function showToast(message, isError = false) {
    const toast = document.createElement("div");
    toast.className = isError ? "toast error" : "toast";
    toast.innerHTML = `
      <i class="fas ${isError ? "fa-exclamation-circle" : "fa-check-circle"}"></i>
      <span>${message}</span>
    `;
    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.remove();
    }, 3000);
  }

  function showEmptyCartPrompt() {
    emptyCartPrompt.style.display = "flex";
    document.body.style.overflow = "hidden";
  }

  function closeEmptyCartPrompt() {
    emptyCartPrompt.style.display = "none";
    document.body.style.overflow = "auto";
  }

  // 15. Initialize the App
  init();
});

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
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// WhatsApp link
const whatsappNumber = "2349064296917";
const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent("Hello, I would like to place an order")}`;
