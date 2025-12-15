// Logout confirmation function
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

// Global variables
let galleryItems = [];
let currentFilter = 'all';

// DOM Elements
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const galleryGrid = document.getElementById('galleryGrid');
const uploadModal = document.getElementById('uploadModal');
const imageViewModal = document.getElementById('imageViewModal');
const closeUploadModal = document.getElementById('closeUploadModal');
const closeImageViewModal = document.getElementById('closeImageViewModal');
const uploadImageBtn = document.getElementById('uploadImageBtn');
const cancelUploadBtn = document.getElementById('cancelUploadBtn');
const saveImageBtn = document.getElementById('saveImageBtn');
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const imagePreview = document.getElementById('imagePreview');
const imageForm = document.getElementById('imageForm');
const modalImageView = document.getElementById('modalImageView');
const modalImageTitle = document.getElementById('modalImageTitle');
const modalImageDescription = document.getElementById('modalImageDescription');
const filterBtns = document.querySelectorAll('.filter-btn');
const searchBox = document.getElementById('searchGallery');
const statsCards = document.querySelectorAll('.stat-card');

// Load gallery items from database
async function loadGalleryItems() {
    try {
        const response = await fetch('get-gallery.php');
        const result = await response.json();
        
        if (result.success) {
            galleryItems = result.data;
            updateStats();
            filterGallery(currentFilter);
        } else {
            console.error('Error loading gallery:', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Update stats cards
function updateStats() {
    const total = galleryItems.length;
    const food = galleryItems.filter(item => item.category === 'food').length;
    const events = galleryItems.filter(item => item.category === 'event').length;
    const videos = galleryItems.filter(item => item.category === 'videos').length;
    
    // Update stats cards
    statsCards[0].querySelector('.stat-value').textContent = total;
    statsCards[1].querySelector('.stat-value').textContent = food;
    statsCards[2].querySelector('.stat-value').textContent = events;
    statsCards[3].querySelector('.stat-value').textContent = videos;
}

// Filter gallery items
function filterGallery(filter) {
    currentFilter = filter;
    let filteredItems = galleryItems;
    
    if (filter !== 'all') {
        // Map filter values to match database categories
        const filterMap = {
            'food': 'food',
            'events': 'event',
            'staff': 'videos' // Note: Changed 'staff' to 'videos' in admin
        };
        const dbCategory = filterMap[filter] || filter;
        filteredItems = galleryItems.filter(item => item.category === dbCategory);
    }
    
    renderGallery(filteredItems);
}

// Render gallery items
function renderGallery(itemsToRender) {
    galleryGrid.innerHTML = '';
    
    if (itemsToRender.length === 0) {
        galleryGrid.innerHTML = `
            <div class="no-items" style="grid-column: 1/-1; text-align: center; padding: 40px;">
                <i class="fas fa-image" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">No gallery items found</h3>
                <p style="color: #999;">Upload your first image or video using the "Upload Image" button</p>
            </div>
        `;
        return;
    }
    
    itemsToRender.forEach(item => {
        const galleryItem = document.createElement('div');
        galleryItem.className = 'gallery-item reveal';
        
        // Format date
        const uploadDate = new Date(item.upload_date);
        const formattedDate = uploadDate.toLocaleDateString();
        
        // Category class and label
        let categoryClass = '';
        let categoryLabel = '';
        switch(item.category) {
            case 'food':
                categoryClass = 'category-food';
                categoryLabel = 'Food';
                break;
            case 'event':
                categoryClass = 'category-events';
                categoryLabel = 'Event';
                break;
            case 'videos':
                categoryClass = 'category-staff';
                categoryLabel = 'Video';
                break;
        }
        
        // File size calculation (simulated)
        const fileSize = item.file_type === 'image' ? '2.5 MB' : '15.2 MB';
        
        galleryItem.innerHTML = `
            <img src="${item.thumbnail_url || item.file_url}" alt="${item.title}" class="gallery-item-img">
            <div class="gallery-item-overlay">
                <div class="gallery-item-actions">
                    <button class="gallery-action-btn view" data-id="${item.id}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="gallery-action-btn edit" data-id="${item.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="gallery-action-btn delete" data-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="gallery-item-info">
                <span class="gallery-item-category ${categoryClass}">${categoryLabel}</span>
                <div class="gallery-item-title">${item.title}</div>
                <div class="gallery-item-meta">
                    <span>${formattedDate}</span>
                    <span>${fileSize}</span>
                </div>
            </div>
        `;
        
        galleryGrid.appendChild(galleryItem);
    });
    
    // Add event listeners to action buttons
    document.querySelectorAll('.gallery-action-btn.view').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = parseInt(this.getAttribute('data-id'));
            viewImage(itemId);
        });
    });
    
    document.querySelectorAll('.gallery-action-btn.edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = parseInt(this.getAttribute('data-id'));
            editImage(itemId);
        });
    });
    
    document.querySelectorAll('.gallery-action-btn.delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = parseInt(this.getAttribute('data-id'));
            deleteImage(itemId);
        });
    });
    
    // Initialize reveal animations for new items
    setTimeout(() => {
        const reveals = galleryGrid.querySelectorAll('.reveal');
        reveals.forEach(reveal => {
            const windowHeight = window.innerHeight;
            const elementTop = reveal.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < windowHeight - elementVisible) {
                reveal.classList.add('active');
            }
        });
    }, 100);
}

// View image in modal
function viewImage(itemId) {
    const item = galleryItems.find(i => i.id === itemId);
    
    if (!item) return;
    
    modalImageView.src = item.file_url;
    modalImageTitle.textContent = item.title;
    modalImageDescription.textContent = item.description || 'No description provided';
    
    imageViewModal.style.display = 'flex';
}

// Edit image
async function editImage(itemId) {
    const item = galleryItems.find(i => i.id === itemId);
    
    if (!item) return;
    
    // Create edit form
    const editForm = `
        <div class="modal" id="editModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Gallery Item</h3>
                    <button class="close-modal" onclick="document.getElementById('editModal').style.display='none'">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <div class="form-group">
                            <label for="editTitle">Title</label>
                            <input type="text" id="editTitle" value="${item.title.replace(/"/g, '&quot;')}" required>
                        </div>
                        <div class="form-group">
                            <label for="editDescription">Description</label>
                            <textarea id="editDescription">${item.description || ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="editCategory">Category</label>
                            <select id="editCategory" required>
                                <option value="food" ${item.category === 'food' ? 'selected' : ''}>Food</option>
                                <option value="event" ${item.category === 'event' ? 'selected' : ''}>Event</option>
                                <option value="videos" ${item.category === 'videos' ? 'selected' : ''}>Video</option>
                            </select>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', editForm);
    const editModal = document.getElementById('editModal');
    editModal.style.display = 'flex';
    
    // Handle form submission
    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('id', itemId);
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('description', document.getElementById('editDescription').value);
        formData.append('category', document.getElementById('editCategory').value);
        
        try {
            const response = await fetch('update-gallery.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Gallery item updated successfully!');
                editModal.style.display = 'none';
                loadGalleryItems(); // Reload gallery items
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error updating item: ' + error.message);
        }
    });
}

// Delete image
async function deleteImage(itemId) {
    const item = galleryItems.find(i => i.id === itemId);
    
    if (!item) return;
    
    if (confirm(`Are you sure you want to delete "${item.title}"? This action cannot be undone.`)) {
        try {
            const formData = new FormData();
            formData.append('id', itemId);
            
            const response = await fetch('delete-gallery.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Gallery item deleted successfully!');
                loadGalleryItems(); // Reload gallery items
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error deleting item: ' + error.message);
        }
    }
}

// Upload area click handler
uploadArea.addEventListener('click', function() {
    fileInput.click();
});

// File input change handler
fileInput.addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
        }
        
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Save image handler
saveImageBtn.addEventListener('click', async function() {
    if (!fileInput.files[0]) {
        alert('Please select an image or video to upload');
        return;
    }
    
    const title = document.getElementById('imageTitle').value;
    const description = document.getElementById('imageDescription').value;
    const category = document.getElementById('imageCategory').value;
    
    if (!title || !category) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Create FormData object
    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);
    formData.append('category', category);
    formData.append('file', fileInput.files[0]);
    
    try {
        const response = await fetch('create-gallery.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Reset form and close modal
            imageForm.reset();
            imagePreview.style.display = 'none';
            uploadModal.style.display = 'none';
            
            alert('Gallery item uploaded successfully!');
            loadGalleryItems(); // Reload gallery items
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error uploading file: ' + error.message);
    }
});

// Open upload modal
uploadImageBtn.addEventListener('click', function() {
    uploadModal.style.display = 'flex';
});

// Close modals
closeUploadModal.addEventListener('click', function() {
    uploadModal.style.display = 'none';
});

closeImageViewModal.addEventListener('click', function() {
    imageViewModal.style.display = 'none';
});

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === uploadModal) {
        uploadModal.style.display = 'none';
    }
    if (event.target === imageViewModal) {
        imageViewModal.style.display = 'none';
    }
});

// Cancel upload
cancelUploadBtn.addEventListener('click', function() {
    uploadModal.style.display = 'none';
    imageForm.reset();
    imagePreview.style.display = 'none';
});

// Filter buttons event listeners
filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        filterBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterGallery(this.getAttribute('data-filter'));
    });
});

// Search functionality
searchBox.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const filteredItems = galleryItems.filter(item => 
        item.title.toLowerCase().includes(searchTerm) ||
        (item.description && item.description.toLowerCase().includes(searchTerm))
    );
    renderGallery(filteredItems);
});

// Scroll Reveal Functionality
function revealOnScroll() {
    const reveals = document.querySelectorAll('.reveal');
    
    for (let i = 0; i < reveals.length; i++) {
        const windowHeight = window.innerHeight;
        const elementTop = reveals[i].getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < windowHeight - elementVisible) {
            reveals[i].classList.add('active');
        } else {
            reveals[i].classList.remove('active');
        }
    }
}

// Mobile sidebar toggler functionality
mobileMenuToggle.addEventListener('click', function() {
    sidebar.classList.toggle('active');
    sidebarOverlay.classList.toggle('active');
});

sidebarOverlay.addEventListener('click', function() {
    sidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
});

// Close sidebar when clicking on a menu item on mobile
const menuItemLinks = document.querySelectorAll('.menu-item a');
menuItemLinks.forEach(item => {
    item.addEventListener('click', function() {
        if (window.innerWidth <= 992) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }
    });
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Load gallery items
    loadGalleryItems();
    
    // Simple animation for stats cards on load
    statsCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Set initial state for animation
    statsCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    // Initialize scroll reveal
    window.addEventListener('scroll', revealOnScroll);
    // Trigger once on load to check initial position
    revealOnScroll();
});
