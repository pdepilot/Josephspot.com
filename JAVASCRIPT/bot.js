
// === Chef Joseph AI Chatbot - Smart with Storage + Voice Input ===
// DOM Elements
const chatContainer = document.getElementById("aiChat-container");
const messagesDiv = document.getElementById("aiChat-messages");
const openBtn = document.getElementById("aiChat-open-btn");
const closeBtn = document.getElementById("aiChat-close");
const sendBtn = document.getElementById("aiChat-send-btn");
const userInput = document.getElementById("aiChat-user-input");
const voiceBtn = document.getElementById("aiChat-voice-btn");
const welcomeBox = document.getElementById("aiChat-welcome-options");
const liveBtn = document.getElementById("ai-start-live");
const waBtn = document.getElementById("ai-start-whatsapp");
const inputArea = document.getElementById("aiChat-input-area");

// Sounds
const openSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-game-notification-alert-1075.mp3");
const messageSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-positive-interface-echo-3157.mp3");
const successSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-achievement-bell-600.mp3");

// Chat state
let awaitingName = false;
let awaitingAddress = false;
let awaitingMealChoice = false;
let suggestionCount = 0;
let isAskingAboutPreviousOrder = false;
let isSuggestingItem = false;
let currentSuggestion = null;
let voiceRecognitionActive = false;

let customerName = localStorage.getItem("jp_customerName") || "";
let customerAddress = localStorage.getItem("jp_customerAddress") || "";
let cart = JSON.parse(localStorage.getItem("jp_cart") || "[]");
let currentMenuMessageId = null;
let lastOrderedItem = localStorage.getItem("jp_lastItem") || "";
let previousOrder = JSON.parse(localStorage.getItem("jp_previousOrder") || "[]");

/* Enhanced Menus */
const lunchMenu = [
  { id: 1, name: "Ofe Owerri", price: 25000, category: "soup" },
  { id: 2, name: "Nsala Soup", price: 20000, category: "soup" },
  { id: 3, name: "Onugbu Soup", price: 18000, category: "soup" },
  { id: 4, name: "Jollof Rice", price: 15000, category: "rice" },
  { id: 5, name: "Pepper Soup", price: 10000, category: "soup" },
  { id: 6, name: "Drinks", price: 5000, category: "drink" },
  { id: 7, name: "Nkwobi", price: 20000, category: "appetizer" },
  { id: 8, name: "Native Rice", price: 12000, category: "rice" },
  { id: 9, name: "Asun Rice", price: 18000, category: "rice" },
  { id: 10, name: "Coconut Rice", price: 15000, category: "rice" },
  { id: 11, name: "White Rice & Stew", price: 10000, category: "rice" },
  { id: 12, name: "Fried Rice", price: 15000, category: "rice" },
  { id: 13, name: "Matching Ground and Piom-piom", price: 12000, category: "special" },
  { id: 14, name: "Spaghetti", price: 8000, category: "pasta" },
  { id: 15, name: "Indomie", price: 7000, category: "noodles" },
  { id: 16, name: "Egusi Soup", price: 18000, category: "soup" },
  { id: 17, name: "Oha Soup", price: 20000, category: "soup" },
  { id: 18, name: "Bitter Soup", price: 18000, category: "soup" },
  { id: 19, name: "Abacha", price: 12000, category: "salad" },
  { id: 20, name: "Jitazzi", price: 15000, category: "special" },
  { id: 21, name: "Peppered Beef", price: 15000, category: "protein" },
  { id: 22, name: "Peppered Goat Meat", price: 18000, category: "protein" }
];

const breakfastMenu = [
  { id: "b1", name: "Akara & Pap", price: 5000, category: "breakfast" },
  { id: "b2", name: "Bread & Tea", price: 3500, category: "breakfast" },
  { id: "b3", name: "Noodles & Egg", price: 4500, category: "breakfast" },
  { id: "b4", name: "Nri Ututu", price: 6000, category: "breakfast" }
];

// Get all menu items for voice recognition
const allMenuItems = [...breakfastMenu, ...lunchMenu];

// Greeting by time
function getGreeting() {
  const hr = new Date().getHours();
  if (hr >= 5 && hr < 12) return "Good morning";
  if (hr >= 12 && hr < 17) return "Good afternoon";
  return "Good evening";
}

// Get time-based suggestion (different from what's already in cart)
function getTimeBasedSuggestion() {
  const hr = new Date().getHours();
  const menu = getTimeBasedMenu();
  
  // Filter out items already in cart
  const availableItems = menu.filter(item => 
    !cart.some(cartItem => cartItem.id === item.id)
  );
  
  if (availableItems.length === 0) {
    return null; // No items available to suggest
  }
  
  // Time-based suggestions
  if (hr >= 5 && hr < 11) {
    const breakfastItem = availableItems.find(item => item.category === "breakfast");
    return breakfastItem ? breakfastItem.name : availableItems[0].name;
  }
  
  if (hr >= 11 && hr < 16) {
    // Suggest something different for lunch
    const nonRiceItem = availableItems.find(item => item.category !== "rice");
    return nonRiceItem ? nonRiceItem.name : availableItems[0].name;
  }
  
  // Evening suggestion
  const eveningItem = availableItems.find(item => 
    item.category === "appetizer" || item.category === "protein"
  );
  return eveningItem ? eveningItem.name : availableItems[0].name;
}

// Get a suggestion that's different from the previous order
function getDifferentSuggestion(previousItemName) {
  const menu = getTimeBasedMenu();
  
  // Filter out the previous item and items already in cart
  const availableItems = menu.filter(item => 
    item.name !== previousItemName && 
    !cart.some(cartItem => cartItem.id === item.id)
  );
  
  if (availableItems.length === 0) {
    return null; // No items available to suggest
  }
  
  // Return a random item from available options
  const randomIndex = Math.floor(Math.random() * availableItems.length);
  return availableItems[randomIndex].name;
}

// Get time-based menu
function getTimeBasedMenu() {
  const hr = new Date().getHours();
  return (hr >= 5 && hr < 11) ? breakfastMenu : lunchMenu;
}

// On load, update popup greeting
window.onload = () => {
  setTimeout(() => {
    chatContainer.style.display = "flex";
    openSound.play();
    const g = getGreeting();
    const wText = document.getElementById("ai-welcome-text");
    if (wText) wText.innerText = `${g}! I'm Chef Joseph.`;
  }, 2000);
};

openBtn.onclick = () => { chatContainer.style.display = "flex"; openSound.play(); };
closeBtn.onclick = () => { chatContainer.style.display = "none"; };

liveBtn.onclick = () => {
  welcomeBox.style.display = "none";
  inputArea.style.display = "flex";
  const g = getGreeting();

  if (customerName) {
    if (lastOrderedItem) {
      appendBubble("bot", `${g}, ${customerName}! Welcome back.`);
      setTimeout(() => {
        appendBubble("bot", `Would you like to order ${lastOrderedItem} again?`, true);
        setTimeout(() => {
          appendQuickReplies(["Yes", "No"]);
          isAskingAboutPreviousOrder = true;
        }, 1500);
      }, 1000);
    } else {
      appendBubble("bot", `${g}, ${customerName}! Welcome back. Would you like to place an order?`);
      awaitingMealChoice = true;
    }
  } else {
    appendBubble("bot", `${g}! May I have your name please?`);
    awaitingName = true;
  }
};

// Send, Voice and Input
sendBtn.onclick = sendMessage;
userInput.addEventListener("keypress", e => {
  if (e.key === "Enter") sendMessage();
});

// Enhanced Voice to text with order recognition
if (voiceBtn) {
  voiceBtn.onclick = () => {
    if (!('webkitSpeechRecognition' in window)) {
      alert("Voice input not supported in this browser.");
      return;
    }
    
    if (voiceRecognitionActive) {
      return; 
    }
    
    voiceRecognitionActive = true;
    voiceBtn.style.backgroundColor = "#e44521"; 
    
    const recognition = new webkitSpeechRecognition();
    recognition.lang = "en-US";
    recognition.continuous = false;
    recognition.interimResults = false;
    
    recognition.onstart = () => {
      appendBubble("bot", "I'm listening... Speak now.", true);
    };
    
    recognition.onresult = event => {
      const transcript = event.results[0][0].transcript;
      userInput.value = transcript;
      
      // Process voice input for menu items
      processVoiceInput(transcript);
    };
    
    recognition.onerror = event => {
      console.error("Speech recognition error", event.error);
      voiceRecognitionActive = false;
      voiceBtn.style.backgroundColor = "";
      
      if (event.error === 'no-speech') {
        appendBubble("bot", "I didn't hear anything. Please try again.");
      } else {
        appendBubble("bot", "Error with voice recognition. Please try typing instead.");
      }
    };
    
    recognition.onend = () => {
      voiceRecognitionActive = false;
      voiceBtn.style.backgroundColor = "";
    };
    
    recognition.start();
  };
}

// Process voice input to detect menu items
function processVoiceInput(transcript) {
  const lowerTranscript = transcript.toLowerCase();
  
  // Check if the transcript contains any menu item
  const matchedItems = allMenuItems.filter(item => 
    lowerTranscript.includes(item.name.toLowerCase())
  );
  
  if (matchedItems.length > 0) {
    // If menu items detected, add them to cart
    matchedItems.forEach(item => {
      addToCart(item);
      appendBubble("bot", `Added ${item.name} to your order!`);
    });
    
    // Show updated menu
    setTimeout(() => {
      showMenu();
    }, 1000);
  } else {
    // If no menu items detected, process as regular message
    sendMessage();
  }
}

function sendMessage() {
  const text = userInput.value.trim();
  if (!text) return;
  appendBubble("user", text);
  userInput.value = "";

  if (isAskingAboutPreviousOrder) {
    handlePreviousOrderResponse(text);
    return;
  }

  if (isSuggestingItem) {
    handleSuggestionResponse(text);
    return;
  }

  if (awaitingName) {
    customerName = text;
    localStorage.setItem("jp_customerName", customerName);
    awaitingName = false;
    awaitingAddress = true;
    appendBubble("bot", `Thanks ${text}! Please enter your delivery address:`);
    return;
  }

  if (awaitingAddress) {
    customerAddress = text;
    localStorage.setItem("jp_customerAddress", customerAddress);
    awaitingAddress = false;
    const hr = new Date().getHours();
    let q = hr >= 5 && hr < 11 ? "Great! What would you like to eat for breakfast?" :
            hr >= 11 && hr < 16 ? "Great! What would you like to have for lunch?" :
            "Great! What would you like this evening?";
    appendBubble("bot", q);
    awaitingMealChoice = true;
    return;
  }

  if (awaitingMealChoice) {
    awaitingMealChoice = false;
    appendBubble("bot", "Sure! Here's our menu:");
    showMenu();
    return;
  }
  handleUnrecognizedInput(text);
}

// Handle inputs that don't match expected commands
function handleUnrecognizedInput(text) {
  const lowerText = text.toLowerCase();
  
  // Check if it's a greeting
  const greetings = ["hello", "hi", "hey", "good morning", "good afternoon", "good evening"];
  const isGreeting = greetings.some(greet => lowerText.includes(greet));
  
  if (isGreeting) {
    const g = getGreeting();
    appendBubble("bot", `${g}! How can I help you today?`);
    return;
  }
  // Check if it's a thank you
  const thanks = ["thank", "thanks", "appreciate"];
  const isThanks = thanks.some(thank => lowerText.includes(thank));
  
  if (isThanks) {
    appendBubble("bot", "You're welcome! Is there anything else I can help you with?");
    return;
  }
  
  // Check if it's a menu request
  const menuRequests = ["menu", "what do you have", "what can i order", "options"];
  const isMenuRequest = menuRequests.some(request => lowerText.includes(request));
  
  if (isMenuRequest) {
    appendBubble("bot", "Sure! Here's our menu:");
    showMenu();
    return;
  }
  
  // If none of the above, respond with voice error
  speakText("Sorry, I'm not trained to answer that question. Please ask about our menu or place an order.");
  appendBubble("bot", "Sorry, I'm not trained to answer that question. Please ask about our menu or place an order.");
}

// Text-to-speech function
function speakText(text) {
  if ('speechSynthesis' in window) {
    const speech = new SpeechSynthesisUtterance();
    speech.text = text;
    speech.volume = 1;
    speech.rate = 1;
    speech.pitch = 1;
    speech.lang = 'en-US';
    
    window.speechSynthesis.speak(speech);
  }
}

function handlePreviousOrderResponse(text) {
  isAskingAboutPreviousOrder = false;
  text = text.toLowerCase();
  
  if (text.includes('yes') || text.includes('yeah') || text.includes('sure')) {
    // Add previous order to cart
    if (previousOrder.length > 0) {
      previousOrder.forEach(item => {
        addToCart(item);
      });
      appendBubble("bot", "Thank you! I've added your previous order to the cart.");
      
      // Show menu with cart
      setTimeout(() => {
        showMenu();
        // Suggest something different from what was just added
        setTimeout(() => {
          const previousItemName = previousOrder[0].name;
          const suggestion = getDifferentSuggestion(previousItemName);
          
          if (suggestion) {
            suggestItem(suggestion);
          } else {
            appendBubble("bot", "Your cart looks great! Would you like to proceed to checkout?");
          }
        }, 1000);
      }, 1000);
    } else if (lastOrderedItem) {
      const menu = getTimeBasedMenu();
      const item = menu.find(i => i.name === lastOrderedItem);
      if (item) {
        addToCart(item);
        appendBubble("bot", "Thank you! I've added " + lastOrderedItem + " to your order.");
        
        // Show menu with cart
        setTimeout(() => {
          showMenu();
          // Suggest something different from what was just added
          setTimeout(() => {
            const suggestion = getDifferentSuggestion(lastOrderedItem);
            
            if (suggestion) {
              suggestItem(suggestion);
            } else {
              appendBubble("bot", "Your cart looks great! Would you like to proceed to checkout?");
            }
          }, 1000);
        }, 1000);
      }
    }
  } else {
    // User doesn't want previous order
    appendBubble("bot", "No problem! Let me show you our menu.");
    
    // Show menu
    setTimeout(() => {
      showMenu();
      // Suggest based on time of day
      setTimeout(() => {
        const suggestion = getTimeBasedSuggestion();
        if (suggestion) {
          suggestItem(suggestion);
        }
      }, 1000);
    }, 1000);
  }
}

function suggestItem(itemName) {
  const menu = getTimeBasedMenu();
  const item = menu.find(i => i.name === itemName);
  
  if (item) {
    appendBubble("bot", `Would you like to try our ${itemName}?`, true);
    setTimeout(() => {
      appendQuickReplies(["Yes, please add it", "No, suggest something else"]);
      isSuggestingItem = true;
      currentSuggestion = item;
    }, 1500);
  }
}

function handleSuggestionResponse(text) {
  isSuggestingItem = false;
  text = text.toLowerCase();
  
  if (text.includes('yes') || text.includes('add it')) {
    addToCart(currentSuggestion);
    appendBubble("bot", "Great! I've added " + currentSuggestion.name + " to your order.");
    suggestionCount = 0;
    
    // Show updated menu
    setTimeout(() => {
      showMenu();
      // Suggest another item different from what was just added
      setTimeout(() => {
        const suggestion = getDifferentSuggestion(currentSuggestion.name);
        if (suggestion) {
          suggestItem(suggestion);
        }
      }, 1000);
    }, 1000);
  } else {
    // User declined the suggestion
    suggestionCount++;
    
    if (suggestionCount >= 3) {
      appendBubble("bot", "I see you're not sure what you want. Let me redirect you to our full menu online.");
      setTimeout(() => {
        window.location.href = "order-online.html"; // Replace with your order online page
      }, 2000);
    } else {
      // Suggest another item different from the previous suggestion
      const suggestion = getDifferentSuggestion(currentSuggestion.name);
      if (suggestion) {
        suggestItem(suggestion);
      } else {
        appendBubble("bot", "I don't have any more suggestions. Your cart looks great! Would you like to proceed to checkout?");
      }
    }
  }
}

function appendQuickReplies(options) {
  const container = document.createElement("div");
  container.className = "quick-reply-buttons";
  
  options.forEach(option => {
    const button = document.createElement("button");
    button.className = "quick-reply-btn";
    button.textContent = option;
    button.onclick = () => {
      userInput.value = option;
      sendMessage();
    };
    container.appendChild(button);
  });
  
  messagesDiv.appendChild(container);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// SHOW MENU
function showMenu() {
  const hr = new Date().getHours();
  const items = (hr >= 5 && hr < 11) ? breakfastMenu : lunchMenu;

  if (currentMenuMessageId) {
    const prev = document.getElementById(currentMenuMessageId);
    if (prev) prev.remove();
  }

  const id = 'menu-' + Date.now();
  currentMenuMessageId = id;

  const container = document.createElement("div");
  container.className = "bot-bubble menu-container";
  container.id = id;

  const listDiv = document.createElement("div");
  listDiv.className = "menu-items";

  items.forEach(it => {
    const div = document.createElement("div");
    div.className = "menu-item";

    const nameSpan = document.createElement("span");
    nameSpan.textContent = `${it.name} - ₦${it.price.toLocaleString()}`;

    const controls = document.createElement("div");
    controls.className = "item-controls";

    const minusBtn = document.createElement("button");
    minusBtn.textContent = "-";
    minusBtn.className = "quantity-btn orange-btn";
    minusBtn.onclick = () => updateCart(it, -1);

    const qty = document.createElement("span");
    qty.className = "item-quantity";
    // Check if item is already in cart
    const cartItem = cart.find(c => c.id === it.id);
    qty.textContent = cartItem ? cartItem.quantity : 0;

    const plusBtn = document.createElement("button");
    plusBtn.textContent = "+";
    plusBtn.className = "quantity-btn orange-btn";
    plusBtn.onclick = () => updateCart(it, 1);

    controls.appendChild(minusBtn);
    controls.appendChild(qty);
    controls.appendChild(plusBtn);

    div.appendChild(nameSpan);
    div.appendChild(controls);
    listDiv.appendChild(div);
  });

  container.appendChild(listDiv);
  container.appendChild(createCartSummary());

  const action = document.createElement("div");
  action.className = "menu-actions";

  if (cart.length > 0) {
    const clearButton = document.createElement("button");
    clearButton.textContent = "Clear Order";
    clearButton.className = "clear-btn orange-btn";
    clearButton.onclick = clearCart;

    const waBtn2 = document.createElement("button");
    waBtn2.textContent = "Send via WhatsApp";
    waBtn2.className = "whatsapp-btn green-btn";
    waBtn2.onclick = sendViaWhatsApp;

    action.appendChild(clearButton);
    action.appendChild(waBtn2);
  }

  container.appendChild(action);
  messagesDiv.appendChild(container);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// Cart functions
function addToCart(item) {
  const found = cart.find(c => c.id === item.id);
  if (found) {
    found.quantity += 1;
  } else {
    cart.push({ id: item.id, name: item.name, price: item.price, quantity: 1 });
  }
  
  // Save to localStorage
  localStorage.setItem("jp_cart", JSON.stringify(cart));
  localStorage.setItem("jp_lastItem", item.name);
  
  // Update previous order
  previousOrder = [{ id: item.id, name: item.name, price: item.price, quantity: 1 }];
  localStorage.setItem("jp_previousOrder", JSON.stringify(previousOrder));
}

function updateCart(item, change) {
  const found = cart.find(c => c.id === item.id);
  if (found) {
    found.quantity += change;
    if (found.quantity <= 0) {
      cart = cart.filter(c => c.id !== item.id);
    }
  } else if (change > 0) {
    cart.push({ id: item.id, name: item.name, price: item.price, quantity: 1 });
  }
  
  // Save to localStorage
  localStorage.setItem("jp_cart", JSON.stringify(cart));
  localStorage.setItem("jp_lastItem", item.name);
  
  // Update previous order
  if (change > 0) {
    previousOrder = [{ id: item.id, name: item.name, price: item.price, quantity: 1 }];
    localStorage.setItem("jp_previousOrder", JSON.stringify(previousOrder));
  }
  
  showMenu();
}

function clearCart() {
  cart = [];
  localStorage.setItem("jp_cart", JSON.stringify(cart));
  showMenu();
  appendBubble("bot", "Cart cleared.");
}

function createCartSummary() {
  const wrap = document.createElement("div");
  wrap.className = "cart-summary";
  const title = document.createElement("h4");
  title.textContent = "Your Order:";
  wrap.appendChild(title);
  if (cart.length === 0) {
    wrap.appendChild(document.createTextNode("Your cart is empty"));
    return wrap;
  }
  const ul = document.createElement("ul");
  cart.forEach(c => {
    const li = document.createElement("li");
    li.textContent = `${c.name} x ${c.quantity}`;
    ul.appendChild(li);
  });
  wrap.appendChild(ul);
  const total = cart.reduce((sum, c) => sum + c.price * c.quantity, 0);
  const totDiv = document.createElement("div");
  totDiv.className = "cart-total";
  totDiv.textContent = `Total: ₦${total.toLocaleString()}`;
  wrap.appendChild(totDiv);
  return wrap;
}

function sendViaWhatsApp() {
  const msg = encodeURIComponent(generateOrderText());
  // Save order as previous order
  localStorage.setItem("jp_previousOrder", JSON.stringify(cart));
  // Clear cart but keep for display until sent
  const tempCart = [...cart];
  cart = [];
  localStorage.setItem("jp_cart", JSON.stringify(cart));
  
  appendBubble("bot", "Order sent! We'll contact you shortly.");
  successSound.play();
  
  // Show empty cart
  setTimeout(() => {
    showMenu();
  }, 500);
  
  // Open WhatsApp
  setTimeout(() => {
    window.open(`https://wa.me/2349064296917?text=${msg}`, "_blank");
  }, 100);
}

function generateOrderText() {
  let text = `New Order from ${customerName}\nAddress: ${customerAddress}\n\nOrder Details:\n`;
  let total = 0;
  cart.forEach(c => {
    let line = `${c.name} x ${c.quantity}`;
    let amount = c.price * c.quantity;
    total += amount;
    text += `${line} - ₦${amount.toLocaleString()}\n`;
  });
  text += `\nTotal: ₦${total.toLocaleString()}`;
  return text;
}
// HTML Bubble
function appendBubble(type, msg, typing = false) {
  const p = document.createElement("p");
  p.className = typing ? "bot-bubble typing-indicator" : (type === "bot" ? "bot-bubble" : "user-bubble");
  
  if (typing) {
    p.textContent = msg;
  } else {
    p.textContent = msg;
  }
  
  messagesDiv.appendChild(p);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
  if (type === "bot" && !typing) messageSound.play();
}





























// === Chef Joseph AI Chatbot - Smart with Storage + Voice Input ===

// DOM Elements
const chatContainer = document.getElementById("aiChat-container");
const messagesDiv = document.getElementById("aiChat-messages");
const openBtn = document.getElementById("aiChat-open-btn");
const closeBtn = document.getElementById("aiChat-close");
const sendBtn = document.getElementById("aiChat-send-btn");
const userInput = document.getElementById("aiChat-user-input");
const voiceBtn = document.getElementById("aiChat-voice-btn"); // mic icon
const welcomeBox = document.getElementById("aiChat-welcome-options");
const liveBtn = document.getElementById("ai-start-live");
const waBtn = document.getElementById("ai-start-whatsapp");
const inputArea = document.getElementById("aiChat-input-area");

// Sounds
const openSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-game-notification-alert-1075.mp3");
const messageSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-positive-interface-echo-3157.mp3");
const successSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-achievement-bell-600.mp3");

// Chat state
let awaitingName = false;
let awaitingAddress = false;
let awaitingMealChoice = false;

let customerName = localStorage.getItem("jp_customerName") || "";
let customerAddress = "";
let cart = [];
let currentMenuMessageId = null;
let lastOrderedItem = localStorage.getItem("jp_lastItem") || "";

/* Menus */
const lunchMenu = [
  { id: 1, name: "Ofe Owerri", price: 25000 },
  { id: 2, name: "Nsala", price: 20000 },
  { id: 3, name: "Onugbu Soup", price: 18000 },
  { id: 4, name: "Jollof Rice", price: 15000 },
  { id: 5, name: "Pepper Soup", price: 10000 },
  { id: 6, name: "Drinks", price: 5000 },
  { id: 7, name: "Nkwobi", price: 20000 }
];

const breakfastMenu = [
  { id: "b1", name: "Akara & Pap", price: 5000 },
  { id: "b2", name: "Bread & Tea", price: 3500 },
  { id: "b3", name: "Noodles & Egg", price: 4500 }
];

// Greeting by time
function getGreeting() {
  const hr = new Date().getHours();
  if (hr >= 5 && hr < 12) return "Good morning";
  if (hr >= 12 && hr < 17) return "Good afternoon";
  return "Good evening";
}

// Special suggestion by time
function getSuggestion() {
  const hr = new Date().getHours();
  if (hr >= 5 && hr < 11) return "You can also try our Akara & Pap ☀️";
  if (hr >= 11 && hr < 16) return "You may love our Pepper Soup this afternoon!";
  return "You might enjoy our Nkwobi tonight!";
}

// On load, update popup greeting
window.onload = () => {
  setTimeout(() => {
    chatContainer.style.display = "flex";
    openSound.play();
    const g = getGreeting();
    const wText = document.getElementById("ai-welcome-text");
    if (wText) wText.innerText = `${g}! I'm Chef Joseph.`;
  }, 2000);
};

openBtn.onclick = () => { chatContainer.style.display = "flex"; openSound.play(); };
closeBtn.onclick = () => { chatContainer.style.display = "none"; };

liveBtn.onclick = () => {
  welcomeBox.style.display = "none";
  inputArea.style.display = "flex";
  const g = getGreeting();

  if (customerName) {
    if (lastOrderedItem) {
      appendBubble("bot", `${g}, ${customerName}! Would you like ${lastOrderedItem} again or try something new?`);
    } else {
      appendBubble("bot", `${g}, ${customerName}! Welcome back. Would you like to place an order?`);
    }
    awaitingMealChoice = true;
  } else {
    appendBubble("bot", `${g}! May I have your name please?`);
    awaitingName = true;
  }
};

// Send, Voice and Input
sendBtn.onclick = sendMessage;

userInput.addEventListener("keypress", e => {
  if (e.key === "Enter") sendMessage();
});

// Voice to text (mic icon)
if (voiceBtn) {
  voiceBtn.onclick = () => {
    if (!('webkitSpeechRecognition' in window)) {
      alert("Voice input not supported in this browser.");
      return;
    }
    const recognition = new webkitSpeechRecognition();
    recognition.lang = "en-US";
    recognition.onresult = event => {
      const transcript = event.results[0][0].transcript;
      userInput.value = transcript;
    };
    recognition.start();
  };
}

function sendMessage() {
  const text = userInput.value.trim();
  if (!text) return;
  appendBubble("user", text);
  userInput.value = "";

  if (awaitingName) {
    customerName = text;
    localStorage.setItem("jp_customerName", customerName);
    awaitingName = false;
    awaitingAddress = true;
    appendBubble("bot", `Thanks ${text}! Please enter your delivery address:`);
    return;
  }

  if (awaitingAddress) {
    customerAddress = text;
    awaitingAddress = false;
    const hr = new Date().getHours();
    let q = hr >= 5 && hr < 11 ? "Great! What would you like to eat for breakfast?" :
            hr >= 11 && hr < 16 ? "Great! What would you like to have for lunch?" :
            "Great! What would you like this evening?";
    appendBubble("bot", q);
    awaitingMealChoice = true;
    return;
  }

  if (awaitingMealChoice) {
    awaitingMealChoice = false;
    appendBubble("bot", "Sure! Here's our menu:");
    showMenu();
    return;
  }
  // textToSpeech or GPT-if desired
}

// SHOW MENU
function showMenu() {
  const hr = new Date().getHours();
  const items = (hr >= 5 && hr < 11) ? breakfastMenu : lunchMenu;

  // Suggest special
  const suggestion = getSuggestion();
  appendBubble("bot", suggestion);

  if (currentMenuMessageId) {
    const prev = document.getElementById(currentMenuMessageId);
    if (prev) prev.remove();
  }

  const id = 'menu-' + Date.now();
  currentMenuMessageId = id;

  const container = document.createElement("div");
  container.className = "bot-bubble menu-container";
  container.id = id;

  const listDiv = document.createElement("div");
  listDiv.className = "menu-items";

  items.forEach(it => {
    const div = document.createElement("div");
    div.className = "menu-item";

    const nameSpan = document.createElement("span");
    nameSpan.textContent = `${it.name} - ₦${it.price.toLocaleString()}`;

    const controls = document.createElement("div");
    controls.className = "item-controls";

    const minusBtn = document.createElement("button");
    minusBtn.textContent = "-";
    minusBtn.className = "quantity-btn orange-btn";
    minusBtn.onclick = () => updateCart(it, -1);

    const qty = document.createElement("span");
    qty.className = "item-quantity";
    qty.textContent = 0;

    const plusBtn = document.createElement("button");
    plusBtn.textContent = "+";
    plusBtn.className = "quantity-btn orange-btn";
    plusBtn.onclick = () => updateCart(it, 1);

    controls.appendChild(minusBtn);
    controls.appendChild(qty);
    controls.appendChild(plusBtn);

    div.appendChild(nameSpan);
    div.appendChild(controls);
    listDiv.appendChild(div);
  });

  container.appendChild(listDiv);
  container.appendChild(createCartSummary());

  const action = document.createElement("div");
  action.className = "menu-actions";

  if (cart.length > 0) {
    const clearButton = document.createElement("button");
    clearButton.textContent = "Clear Order";
    clearButton.className = "clear-btn orange-btn";
    clearButton.onclick = clearCart;

    const waBtn2 = document.createElement("button");
    waBtn2.textContent = "Send via WhatsApp";
    waBtn2.className = "whatsapp-btn green-btn";
    waBtn2.onclick = sendViaWhatsApp;

    action.appendChild(clearButton);
    action.appendChild(waBtn2);
  }

  container.appendChild(action);
  messagesDiv.appendChild(container);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// Cart
function updateCart(item, change) {
  const found = cart.find(c => c.id === item.id);
  if (found) {
    found.quantity += change;
    if (found.quantity <= 0) {
      cart = cart.filter(c => c.id !== item.id);
    }
  } else if (change > 0) {
    cart.push({ id: item.id, name: item.name, price: item.price, quantity: 1 });
  }
  lastOrderedItem = item.name;
  localStorage.setItem("jp_lastItem", lastOrderedItem);
  showMenu();
}

function clearCart() {
  cart = [];
  showMenu();
  appendBubble("bot", "Cart cleared.");
}

function createCartSummary() {
  const wrap = document.createElement("div");
  wrap.className = "cart-summary";
  const title = document.createElement("h4");
  title.textContent = "Your Order:";
  wrap.appendChild(title);
  if (cart.length === 0) {
    wrap.appendChild(document.createTextNode("Your cart is empty"));
    return wrap;
  }
  const ul = document.createElement("ul");
  cart.forEach(c => {
    const li = document.createElement("li");
    li.textContent = `${c.name} x ${c.quantity}`;
    ul.appendChild(li);
  });
  wrap.appendChild(ul);

  const total = cart.reduce((sum, c) => sum + c.price * c.quantity, 0);
  const totDiv = document.createElement("div");
  totDiv.className = "cart-total";
  totDiv.textContent = `Total: ₦${total.toLocaleString()}`;
  wrap.appendChild(totDiv);
  return wrap;
}

function sendViaWhatsApp() {
  const msg = encodeURIComponent(generateOrderText());
  cart = [];
  showMenu();
  appendBubble("bot", "Order sent! We'll contact you shortly.");
  localStorage.setItem("jp_lastItem", lastOrderedItem);
  successSound.play();
  setTimeout(() => {
    window.open(`https://wa.me/2349064296917?text=${msg}`, "_blank");
  }, 100);
}

function generateOrderText() {
  let text = `New Order from ${customerName}\nAddress: ${customerAddress}\n\nOrder Details:\n`;
  let total = 0;
  cart.forEach(c => {
    let line = `${c.name} x ${c.quantity}`;
    let amount = c.price * c.quantity;
    total += amount;
    text += `${line} - ₦${amount.toLocaleString()}\n`;
  });
  text += `\nTotal: ₦${total.toLocaleString()}`;
  return text;
}

// HTML Bubble
function appendBubble(type, msg, typing = false) {
  const p = document.createElement("p");
  p.className = typing ? "bot-bubble typing-indicator" : (type === "bot" ? "bot-bubble" : "user-bubble");
  p.textContent = msg;
  messagesDiv.appendChild(p);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
  if (type === "bot" && !typing) messageSound.play();
}
