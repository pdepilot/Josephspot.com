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

// Close menu when clicking on a nav link (optional)
document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", () => {
    if (window.innerWidth <= 768) {
      // Only for mobile
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
      description:
        "Filled With Snails, Stockfishes, Dryfishes, Lots of Protein",
      price: 2500,
      category: "soups",
      image: "../images/image20.jpg",
    },
    {
      id: 2,
      title: "Ofe Nsala White Soup",
      description:
        "Thickened With Pure White Yam, Filled With Snails, And Lots of Proteins",
      price: 3500,
      category: "soups",
      image: "../images/nsala.jpeg",
    },
    {
      id: 3,
      title: "Egusi Soup",
      description: "Melon seed soup with assorted meat and fish",
      price: 2800,
      category: "soups",
      image: "../images/egusi.jpg",
    },
    {
      id: 4,
      title: "Ogbono Soup",
      description: "African mango seed soup with okra and assorted meat",
      price: 3000,
      category: "soups",
      image: "../images/okra.jpeg",
    },
    {
      id: 5,
      title: "Jollof Rice",
      description: "Classic Nigerian jollof rice with chicken and plantain",
      price: 3200,
      category: "main courses",
      image: "../images/jollof rice.jpg",
    },
    {
      id: 6,
      title: "Fried Rice",
      description: "Special fried rice with mixed vegetables and beef",
      price: 3500,
      category: "main courses",
      image: "../images/fried rice.jpg",
    },
    {
      id: 7,
      title: "Bitterleaf Soup",
      description: "Smooth pounded yam or Fufu with soup filled with Happiness",
      price: 4000,
      category: "main courses",
      image: "../images/ogbonno soup.jpg",
    },
    {
      id: 8,
      title: "Nkwobi",
      description: "Goat Meat Sauced with Igbo Traditional Spiced and ugba",
      price: 3800,
      category: "main courses",
      image: "../images/nkwobi.jpeg",
    },
    {
      id: 9,
      title: "Indomie Noodles",
      description: "Special indomie noodles with fried egg and sausages",
      price: 2500,
      category: "noodles",
      image: "../images/noodles.jpeg",
    },
    {
      id: 10,
      title: "Spaghetti Jollof",
      description: "Jollof spaghetti with chicken and vegetables",
      price: 2800,
      category: "noodles",
      image: "../images/spaghetti.jpg",
    },
    {
      id: 11,
      title: "Instant Noodles",
      description:
        "Quick noodles with vegetables and eggs mixed with fried plantain",
      price: 1800,
      category: "noodles",
      image: "../images/2021-09-06.webp",
    },
    {
      id: 12,
      title: "Yam Noodles",
      description: "Healthy hotdog noodles with stir-fry vegetables",
      price: 3000,
      category: "noodles",
      image: "../images/noodle and hotdog.jpg",
    },
    {
      id: 13,
      title: "palm-Wine",
      description: "Refreshing Igbo Palm-wine drink",
      price: 1500,
      category: "drinks",
      image: "../images/palm wine.jpg",
    },
    {
      id: 14,
      title: "Sprite",
      description: "Iced or Cold sprite drink",
      price: 1200,
      category: "drinks",
      image: "../images/images (10).jpeg",
    },
    {
      id: 15,
      title: "Fanta Orange ",
      description: "Iced or Cold Fanta drink",
      price: 1800,
      category: "drinks",
      image: "../images/images (8).jpeg",
    },
    {
      id: 16,
      title: "Coca Cola",
      description: "Iced or Cold Coca Cola drink",
      price: 1800,
      category: "drinks",
      image: "../images/images (9).jpeg",
    },
    {
      id: 17,
      title: "Malt",
      description: "Iced or Cold Choice of Malt drink",
      price: 1800,
      category: "drinks",
      image: "../images/malts.png",
    },
    {
      id: 18,
      title: "Bottled Water",
      description: "Iced or Cold Choice of bottled water",
      price: 1800,
      category: "drinks",
      image: "../images/images (11).jpeg",
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
  const closeCustomerDetails = document.querySelector(
    ".close-customer-details"
  );
  const customerDetailsForm = document.getElementById("customerDetailsForm");
  const backToCartBtn = document.getElementById("backToCart");
  const paymentOptions = document.querySelectorAll(
    'input[name="paymentMethod"]'
  );
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
  const receiptCustomerAddress = document.getElementById(
    "receiptCustomerAddress"
  );
  const receiptPaymentMethod = document.getElementById("receiptPaymentMethod");
  const receiptPaymentDetails = document.getElementById(
    "receiptPaymentDetails"
  );
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

  // Admin Credentials
  const ADMIN_CREDENTIALS = {
    username: "admin",
    password: "admin123", // Login password
    actionPassword: "secure123", // Different password for actions
  };

  // 3. Cart State
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let currentTab = "pending";
  let currentAction = null;
  let currentOrderId = null;

  // 4. Initialize App
  function init() {
    renderMenuItems();
    updateCartCount();
    setupEventListeners();
    setupAdminEventListeners();

    // Initialize with some sample orders if none exist
    initializeSampleOrders();

    // Setup empty cart prompt event listeners
    closeEmptyCartBtn.addEventListener("click", closeEmptyCartPrompt);
    browseMenuBtn.addEventListener("click", function () {
      closeEmptyCartPrompt();
      // Scroll to menu section
      document.getElementById("menu").scrollIntoView({ behavior: "smooth" });
    });
  }

  // Initialize sample orders for demonstration
  function initializeSampleOrders() {
    const orders = JSON.parse(localStorage.getItem("orders")) || [];
    if (orders.length === 0) {
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
              image:
                "https://images.unsplash.com/photo-1547592166-23ac45744acd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
            {
              id: 17,
              title: "Malt",
              price: 1800,
              quantity: 1,
              image:
                "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
          ],
          subtotal: 6800,
          deliveryFee: 1500,
          total: 8300,
          paymentMethod: "cod",
          date: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(), // 2 hours ago
          dateFormatted: new Date(
            Date.now() - 2 * 60 * 60 * 1000
          ).toLocaleDateString("en-US", {
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
              image:
                "https://images.unsplash.com/photo-1593219531316-511804d4b5e3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
            {
              id: 6,
              title: "Fried Rice",
              price: 3500,
              quantity: 1,
              image:
                "https://images.unsplash.com/photo-1630918037678-a8f7e0f9c9c6?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
            },
          ],
          subtotal: 6700,
          deliveryFee: 1500,
          total: 8200,
          paymentMethod: "bank",
          date: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(), // 1 day ago
          dateFormatted: new Date(
            Date.now() - 24 * 60 * 60 * 1000
          ).toLocaleDateString("en-US", {
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
    }
  }

  // 5. Render Menu Items
  function renderMenuItems(category = "all") {
    menuItemsContainer.innerHTML = "";

    const filteredItems =
      category === "all"
        ? menuItems
        : menuItems.filter((item) => item.category === category);

    if (filteredItems.length === 0) {
      menuItemsContainer.innerHTML =
        '<p class="no-items">No items in this category</p>';
      return;
    }

    filteredItems.forEach((item) => {
      const menuItemElement = document.createElement("div");
      menuItemElement.className = "menu-item";
      menuItemElement.innerHTML = `
                        <img src="${item.image}" alt="${
        item.title
      }" class="menu-item-img">
                        <div class="menu-item-content">
                            <h3 class="menu-item-title">${item.title}</h3>
                            <p class="menu-item-desc">${item.description}</p>
                            <div class="menu-item-footer">
                                <span class="menu-item-price">₦${item.price.toLocaleString()}</span>
                                <button class="add-to-cart" data-id="${
                                  item.id
                                }">Add to Cart</button>
                            </div>
                        </div>
                    `;
      menuItemsContainer.appendChild(menuItemElement);
    });
  }

  // 6. Setup Event Listeners
  function setupEventListeners() {
    // Category Buttons
    categoryButtons.forEach((button) => {
      button.addEventListener("click", function () {
        categoryButtons.forEach((btn) => btn.classList.remove("active"));
        this.classList.add("active");
        renderMenuItems(this.dataset.category);
      });
    });

    // Add to Cart
    menuItemsContainer.addEventListener("click", function (e) {
      if (e.target.classList.contains("add-to-cart")) {
        const itemId = parseInt(e.target.getAttribute("data-id"));
        addToCart(itemId);
      }
    });

    // Cart Icon - Modified to check for empty cart
    cartIcon.addEventListener("click", function () {
      if (cart.length === 0) {
        showEmptyCartPrompt();
      } else {
        openCheckoutModal();
      }
    });

    // Close Checkout Modal
    closeCheckout.addEventListener("click", closeCheckoutModal);

    // Clear Cart
    clearCartBtn.addEventListener("click", clearCart);

    // Proceed to Checkout
    proceedToCheckoutBtn.addEventListener("click", openCustomerDetails);

    // Close Customer Details
    closeCustomerDetails.addEventListener("click", closeCustomerDetailsModal);

    // Back to Cart
    backToCartBtn.addEventListener("click", function () {
      closeCustomerDetailsModal();
      openCheckoutModal();
    });

    // Payment Method Change
    paymentOptions.forEach((option) => {
      option.addEventListener("change", handlePaymentMethodChange);
    });

    // Copy Account Number
    copyAccountNumberBtn.addEventListener("click", copyAccountNumber);

    // Form Submission
    customerDetailsForm.addEventListener("submit", handleFormSubmission);

    // Receipt Actions
    printReceiptBtn.addEventListener("click", printReceipt);
    downloadReceiptBtn.addEventListener("click", downloadReceipt);
    shareWhatsAppBtn.addEventListener("click", shareViaWhatsApp);
    shareEmailBtn.addEventListener("click", shareViaEmail);
    closeReceiptBtn.addEventListener("click", closeReceiptModal);
  }

  // Setup Admin Event Listeners
  function setupAdminEventListeners() {
    // Admin login button
    adminLoginBtn.addEventListener("click", openAdminLogin);

    // Close admin login
    closeAdminLogin.addEventListener("click", closeAdminLoginModal);

    // Admin login form submission
    adminLoginForm.addEventListener("submit", handleAdminLogin);

    // Close admin dashboard
    closeAdminDashboard.addEventListener("click", closeAdminDashboardModal);

    // Admin logout button
    adminLogoutBtn.addEventListener("click", handleAdminLogout);

    // Dashboard tabs
    dashboardTabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        dashboardTabs.forEach((t) => t.classList.remove("active"));
        this.classList.add("active");
        currentTab = this.dataset.tab;
        updateOrdersTableTitle();
        loadAdminData();
      });
    });

    // Password modal events
    closeAdminPassword.addEventListener("click", closeAdminPasswordModal);
    cancelPasswordBtn.addEventListener("click", closeAdminPasswordModal);
    submitPasswordBtn.addEventListener("click", handlePasswordSubmit);
  }

  function updateOrdersTableTitle() {
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

  // Function to format payment method for display
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

    // Show toast notification
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
      cartItemsContainer.innerHTML =
        '<p class="empty-cart">Your cart is empty</p>';
      return;
    }

    cart.forEach((item) => {
      const cartItemElement = document.createElement("div");
      cartItemElement.className = "cart-item";
      cartItemElement.innerHTML = `
                        <img src="${item.image}" alt="${
        item.title
      }" class="cart-item-img">
                        <div class="cart-item-details">
                            <h4 class="cart-item-title">${item.title}</h4>
                            <p class="cart-item-price">₦${(
                              item.price * item.quantity
                            ).toLocaleString()}</p>
                        </div>
                        <div class="quantity-control">
                            <button class="quantity-btn minus" data-id="${
                              item.id
                            }">-</button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="quantity-btn plus" data-id="${
                              item.id
                            }">+</button>
                        </div>
                        <button class="remove-item" data-id="${item.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
      cartItemsContainer.appendChild(cartItemElement);
    });

    // Add event listeners to quantity controls
    document.querySelectorAll(".quantity-btn.minus").forEach((btn) => {
      btn.addEventListener("click", () =>
        updateQuantity(parseInt(btn.dataset.id), -1)
      );
    });

    document.querySelectorAll(".quantity-btn.plus").forEach((btn) => {
      btn.addEventListener("click", () =>
        updateQuantity(parseInt(btn.dataset.id), 1)
      );
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
    const subtotal = cart.reduce(
      (total, item) => total + item.price * item.quantity,
      0
    );
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

  // Admin Modal Functions
  function openAdminLogin() {
    adminLoginModal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }

  function closeAdminLoginModal() {
    adminLoginModal.style.display = "none";
    document.body.style.overflow = "auto";
  }

  function openAdminDashboard() {
    adminDashboard.style.display = "flex";
    document.body.style.overflow = "hidden";
    loadAdminData();
  }

  function closeAdminDashboardModal() {
    adminDashboard.style.display = "none";
    document.body.style.overflow = "auto";
  }

  function openAdminPasswordModal(action, orderId) {
    currentAction = action;
    currentOrderId = orderId;

    // Set appropriate prompt based on action
    if (action === "delete") {
      passwordPrompt.textContent =
        "Please enter your admin action password to delete this order";
    }

    adminPasswordModal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }

  function closeAdminPasswordModal() {
    adminPasswordModal.style.display = "none";
    document.body.style.overflow = "auto";
    adminActionPassword.value = "";
    currentAction = null;
    currentOrderId = null;
  }

  function handleAdminLogout() {
    closeAdminDashboardModal();
    // Clear any admin session data if needed
  }

  // 9. Payment Handling
  function handlePaymentMethodChange(e) {
    bankDetailsSection.style.display =
      e.target.value === "bank" ? "block" : "none";
  }

  function copyAccountNumber() {
    const accountNumber = document.getElementById("accountNumber").textContent;
    navigator.clipboard.writeText(accountNumber).then(() => {
      const originalText = copyAccountNumberBtn.textContent;
      copyAccountNumberBtn.textContent = "Copied!";
      setTimeout(() => (copyAccountNumberBtn.textContent = originalText), 2000);
    });
  }

  // 10. Form Handling - UPDATED TO SHOW PROCESSING STATE
  async function handleFormSubmission(e) {
    e.preventDefault();

    const formData = {
      fullName: document.getElementById("fullName").value,
      email: document.getElementById("email").value,
      phone: document.getElementById("phone").value,
      state: document.getElementById("state").value,
      address: document.getElementById("address").value,
      deliveryNotes: document.getElementById("deliveryNotes").value,
      paymentMethod: document.querySelector(
        'input[name="paymentMethod"]:checked'
      ).value,
      proofUpload: document.getElementById("proofUpload").files[0]
        ? "Uploaded"
        : "Not provided",
    };

    const subtotal = cart.reduce(
      (total, item) => total + item.price * item.quantity,
      0
    );
    const totalAmount = subtotal + 1500; // Include delivery fee

    // Show processing state
    const submitButton = document.querySelector('.btn-submit');
    const originalText = submitButton.textContent;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitButton.disabled = true;
    
    // Disable form inputs during processing
    const formInputs = customerDetailsForm.querySelectorAll('input, textarea, select, button');
    formInputs.forEach(input => {
      input.disabled = true;
    });

    try {
      // Process payment based on selected method
      if (formData.paymentMethod === 'paystack') {
        await loadPaystackScript();
        processPaystackPayment(formData, subtotal, totalAmount * 100); // Convert to kobo
        return; // Exit early as Paystack will handle the callback
      } else if (formData.paymentMethod === 'flutterwave') {
        await loadFlutterwaveScript();
        processFlutterwavePayment(formData, subtotal, totalAmount * 100); // Convert to kobo
        return; // Exit early as Flutterwave will handle the callback
      } else {
        // For COD and Bank Transfer, just save the order
        const order = saveOrder(formData, subtotal, totalAmount);
        
        // Clear cart
        cart = [];
        updateCart();
        
        // Show receipt - pass the order data instead of using cart
        openReceiptModal();
        generateReceipt(formData, order.items, subtotal, totalAmount);
      }
    } catch (error) {
      console.error('Payment processing error:', error);
      showToast('Payment processing failed. Please try again.', true);
    } finally {
      // Reset button state
      submitButton.textContent = originalText;
      submitButton.disabled = false;
      
      // Re-enable form inputs
      formInputs.forEach(input => {
        input.disabled = false;
      });
    }
  }

  function saveOrder(formData, subtotal, totalAmount) {
    const now = new Date();
    const order = {
      id: "GD" + Math.floor(10000 + Math.random() * 90000),
      customerName: formData.fullName,
      customerEmail: formData.email,
      customerPhone: formData.phone,
      customerAddress: formData.address,
      items: [...cart], // Save a copy of the cart items
      subtotal: subtotal,
      deliveryFee: 1500,
      total: totalAmount,
      paymentMethod: formData.paymentMethod,
      date: now.toISOString(),
      dateFormatted: now.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      }),
      status: "pending", // All new orders start as pending
    };

    // Save to localStorage
    const orders = JSON.parse(localStorage.getItem("orders")) || [];
    orders.push(order);
    localStorage.setItem("orders", JSON.stringify(orders));

    return order;
  }

  function handleAdminLogin(e) {
    e.preventDefault();

    const username = document.getElementById("adminUsername").value;
    const password = document.getElementById("adminPassword").value;

    if (
      username === ADMIN_CREDENTIALS.username &&
      password === ADMIN_CREDENTIALS.password
    ) {
      closeAdminLoginModal();
      openAdminDashboard();
    } else {
      alert("Invalid admin credentials");
    }
  }

  function handlePasswordSubmit() {
    const password = adminActionPassword.value;

    if (password === ADMIN_CREDENTIALS.actionPassword) {
      // Password is correct, perform the action
      if (currentAction === "delete") {
        deleteOrder(currentOrderId);
      }
      closeAdminPasswordModal();
    } else {
      alert("Incorrect action password. Action not authorized.");
    }
  }

  function loadAdminData() {
    // Get orders from localStorage
    const orders = JSON.parse(localStorage.getItem("orders")) || [];

    // Update stats
    totalOrdersCount.textContent = orders.length;

    // Calculate pending orders
    const pendingOrders = orders.filter((order) => order.status === "pending");
    pendingOrdersCount.textContent = pendingOrders.length;

    // Calculate total revenue from completed orders
    const completedOrders = orders.filter(
      (order) => order.status === "completed"
    );
    const revenue = completedOrders.reduce(
      (total, order) => total + order.total,
      0
    );
    totalRevenue.textContent = `₦${revenue.toLocaleString()}`;

    // Populate orders table based on current tab
    ordersTableBody.innerHTML = "";

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
    }

    if (filteredOrders.length === 0) {
      ordersTableBody.innerHTML = `
                        <tr>
                            <td colspan="8" style="text-align: center;">No orders found</td>
                        </tr>
                    `;
      return;
    }

    // Show orders in reverse chronological order (newest first)
    const sortedOrders = [...filteredOrders].reverse();

    sortedOrders.forEach((order) => {
      const row = document.createElement("tr");
      row.innerHTML = `
                        <td>${order.id}</td>
                        <td>${order.customerName}</td>
                        <td>${order.customerPhone}</td>
                        <td>₦${order.total.toLocaleString()}</td>
                        <td>${formatPaymentMethod(order.paymentMethod)}</td>
                        <td class="order-date">${
                          order.dateFormatted ||
                          new Date(order.date).toLocaleString()
                        }</td>
                        <td class="status-${order.status}">${
        order.status.charAt(0).toUpperCase() + order.status.slice(1)
      }</td>
                        <td>
                            ${
                              order.status === "pending"
                                ? `<button class="action-btn complete-order" data-id="${order.id}">Complete</button>
                                   <button class="delete-btn delete-order" data-id="${order.id}">Delete</button>`
                                : `<button class="action-btn view-order" data-id="${order.id}">View</button>
                                   <button class="delete-btn delete-order" data-id="${order.id}">Delete</button>`
                            }
                        </td>
                    `;
      ordersTableBody.appendChild(row);
    });

    // Add event listeners to action buttons
    document.querySelectorAll(".complete-order").forEach((btn) => {
      btn.addEventListener("click", function () {
        const orderId = this.getAttribute("data-id");
        completeOrder(orderId);
      });
    });

    document.querySelectorAll(".view-order").forEach((btn) => {
      btn.addEventListener("click", function () {
        const orderId = this.getAttribute("data-id");
        viewOrder(orderId);
      });
    });

    // Add event listeners to delete buttons
    document.querySelectorAll(".delete-order").forEach((btn) => {
      btn.addEventListener("click", function () {
        const orderId = this.getAttribute("data-id");
        openAdminPasswordModal("delete", orderId);
      });
    });
  }

  function completeOrder(orderId) {
    const orders = JSON.parse(localStorage.getItem("orders")) || [];
    const orderIndex = orders.findIndex((order) => order.id === orderId);

    if (orderIndex !== -1) {
      orders[orderIndex].status = "completed";
      localStorage.setItem("orders", JSON.stringify(orders));
      loadAdminData(); // Refresh the table

      // Show confirmation message
      alert(`Order ${orderId} has been marked as completed.`);
    }
  }

  function deleteOrder(orderId) {
    const orders = JSON.parse(localStorage.getItem("orders")) || [];
    const orderIndex = orders.findIndex((order) => order.id === orderId);

    if (orderIndex !== -1) {
      orders.splice(orderIndex, 1);
      localStorage.setItem("orders", JSON.stringify(orders));
      loadAdminData(); // Refresh the table

      // Show confirmation message
      alert(`Order ${orderId} has been deleted.`);
    }
  }

  function viewOrder(orderId) {
    const orders = JSON.parse(localStorage.getItem("orders")) || [];
    const order = orders.find((order) => order.id === orderId);

    if (order) {
      alert(`Order Details:\n
Order ID: ${order.id}
Customer: ${order.customerName}
Phone: ${order.customerPhone}
Email: ${order.customerEmail}
Address: ${order.customerAddress}
Total: ₦${order.total.toLocaleString()}
Payment Method: ${order.paymentMethod}
Status: ${order.status}
Date: ${order.dateFormatted || new Date(order.date).toLocaleString()}`);
    }
  }

  // 11. Receipt Generation - UPDATED TO ACCEPT ITEMS AND TOTALS AS PARAMETERS
  function generateReceipt(formData = {}, items = [], subtotal = 0, totalAmount = 0) {
    const orderId = "GD" + Math.floor(10000 + Math.random() * 90000);
    const now = new Date();

    // Format options for current date and time
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

    // Set receipt details with current time
    receiptOrderId.textContent = orderId;
    receiptDate.textContent = currentDateTime; // Shows exact generation time
    receiptQrCode.innerHTML = "";

    // Set customer details
    receiptCustomerName.textContent = formData.fullName || "Walk-in Customer";
    receiptCustomerEmail.textContent = formData.email || "Not provided";
    receiptCustomerPhone.textContent = formData.phone || "N/A";
    receiptCustomerState.textContent = formData.state || "Not specified";
    receiptCustomerAddress.textContent = formData.address || "In-store pickup";

    // Render items
    receiptItemsContainer.innerHTML = `
                <tr>
                    <th class="item-name">Item</th>
                    <th class="item-qty">Qty</th>
                    <th class="item-price">Price</th>
                </tr>
            `;

    // Calculate subtotal from provided items
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
      // Fallback if no items provided
      calculatedSubtotal = subtotal;
    }

    // Add delivery fee
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

    // Payment details
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
                            ${
                              formData.proofUpload === "Uploaded"
                                ? "<p><strong>Proof Uploaded:</strong> Yes</p>"
                                : ""
                            }
                        `;
          break;
        case "paystack":
          receiptPaymentMethod.textContent = "Paystack";
          paymentDetails = `
                            <p>Paid via Paystack payment gateway</p>
                            ${
                              formData.paymentReference
                                ? `<p><strong>Reference:</strong> ${formData.paymentReference}</p>`
                                : ""
                            }
                        `;
          break;
        case "flutterwave":
          receiptPaymentMethod.textContent = "Flutterwave";
          paymentDetails = `
                            <p>Paid via Flutterwave payment gateway</p>
                            ${
                              formData.paymentReference
                                ? `<p><strong>Reference:</strong> ${formData.paymentReference}</p>`
                                : ""
                            }
                        `;
          break;
        default:
          receiptPaymentMethod.textContent = "Unknown";
          paymentDetails = "<p>Payment method not specified</p>";
      }
      receiptPaymentDetails.innerHTML = paymentDetails;
    }

    // Generate QR code
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

  // 12. Receipt Actions
  function printReceipt() {
    const printContent = document.querySelector(".receipt-container").innerHTML;
    const originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    openReceiptModal();
  }

  function downloadReceipt() {
    const element = document
      .querySelector(".receipt-container")
      .cloneNode(true);
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

    // First try the universal WhatsApp API
    const whatsappUrl = `https://api.whatsapp.com/send?text=${encodedMessage}`;

    // Fallback for mobile devices
    const mobileUrl = `whatsapp://send?text=${encodedMessage}`;

    // Open the link
    window.open(whatsappUrl, "_blank");

    // Fallback mechanism in case the first attempt fails
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
      `mailto:?subject=${encodeURIComponent(
        `My Order #${orderId} from Joseph's Pot`
      )}&body=${encodeURIComponent(
        `Hi,\n\nHere's my order details:\nOrder ID: ${orderId}\nTotal: ${total}\n\nThank you!`
      )}`,
      "_blank"
    );
  }

  // 13. Payment Processors - UPDATED TO PASS ORDER DATA
  function processPaystackPayment(formData, subtotal, amount) {
    // Show processing state
    showProcessingState(true);
    
    const handler = PaystackPop.setup({
      key: "pk_test_26f8c2230ec7838bcf82ad3e199674e777ccfac0",
      email: formData.email,
      amount: amount,
      currency: "NGN",
      ref: "GD-" + Date.now(),
      callback: function (response) {
        formData.paymentReference = response.reference;
        
        // Save order and show receipt - pass the cart items before clearing
        const order = saveOrder(formData, subtotal, amount / 100);
        cart = [];
        updateCart();
        openReceiptModal();
        generateReceipt(formData, order.items, subtotal, amount / 100);
        
        // Hide processing state
        showProcessingState(false);
      },
      onClose: function () {
        alert("Payment window closed");
        // Hide processing state
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
    // Show processing state
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
        
        // Save order and show receipt - pass the cart items before clearing
        const order = saveOrder(formData, subtotal, amount / 100);
        cart = [];
        updateCart();
        openReceiptModal();
        generateReceipt(formData, order.items, subtotal, amount / 100);
        
        // Hide processing state
        showProcessingState(false);
      },
      onclose: function () {
        alert("Payment window closed");
        // Hide processing state
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

  // NEW: Function to show/hide processing state
  function showProcessingState(show) {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const submitButton = document.querySelector('.btn-submit');
    const formInputs = customerDetailsForm.querySelectorAll('input, textarea, select, button');
    
    if (show) {
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
      submitButton.disabled = true;
      
      // Disable all form inputs
      formInputs.forEach(input => {
        input.disabled = true;
        input.style.opacity = '0.7';
      });
      
      // Style payment options to show processing
      paymentOptions.forEach(option => {
        option.style.opacity = '0.7';
        option.style.pointerEvents = 'none';
      });
    } else {
      submitButton.innerHTML = 'Submit Order';
      submitButton.disabled = false;
      
      // Re-enable all form inputs
      formInputs.forEach(input => {
        input.disabled = false;
        input.style.opacity = '1';
      });
      
      // Reset payment options styling
      paymentOptions.forEach(option => {
        option.style.opacity = '1';
        option.style.pointerEvents = 'auto';
      });
    }
  }

  // 14. Helper Functions
  function animateCartIcon() {
    cartIcon.style.transform = "scale(1.2)";
    setTimeout(() => {
      cartIcon.style.transform = "scale(1)";
    }, 300);
  }

  // New Functions for Toast and Empty Cart Prompt
  function showToast(message, isError = false) {
    const toast = document.createElement("div");
    toast.className = isError ? "toast error" : "toast";
    toast.innerHTML = `
                    <i class="fas ${
                      isError ? "fa-exclamation-circle" : "fa-check-circle"
                    }"></i>
                    <span>${message}</span>
                `;

    toastContainer.appendChild(toast);

    // Remove toast after animation completes
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
  "Hello, I would like to place an order"
)}`;