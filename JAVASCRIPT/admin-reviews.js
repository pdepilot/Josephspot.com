// Global variables
let allReviews = [];
let currentFilter = 'all';
let currentSearch = '';
let currentPage = 1;
const reviewsPerPage = 10;
let previousPendingCount = 0;

// DOM Elements
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const reviewsList = document.getElementById('reviewsList');
const reviewDetailsModal = document.getElementById('reviewDetailsModal');
const closeModal = document.getElementById('closeModal');
const filterBtns = document.querySelectorAll('.filter-btn');
const searchBox = document.querySelector('.search-box input');

// Modal elements
const reviewDetailsAvatar = document.getElementById('reviewDetailsAvatar');
const reviewerName = document.getElementById('reviewerName');
const reviewerEmail = document.getElementById('reviewerEmail');
const modalRatingStars = document.getElementById('modalRatingStars');
const modalRatingValue = document.getElementById('modalRatingValue');
const reviewDetailsText = document.getElementById('reviewDetailsText');
const reviewOrderId = document.getElementById('reviewOrderId');
const reviewDate = document.getElementById('reviewDate');
const reviewStatus = document.getElementById('reviewStatus');
const reviewItems = document.getElementById('reviewItems');
const replyTextarea = document.getElementById('replyTextarea');
const rejectReviewBtn = document.getElementById('rejectReviewBtn');
const approveReviewBtn = document.getElementById('approveReviewBtn');
const saveReplyBtn = document.getElementById('saveReplyBtn');

// Add DOM elements for stats
const totalReviewsEl = document.querySelector('.stat-card.total .stat-value');
const avgRatingEl = document.querySelector('.stat-card.positive .stat-value');
const pendingCountEl = document.querySelector('.stat-card.pending .stat-value');
const negativeCountEl = document.querySelector('.stat-card.negative .stat-value');
const totalChangeEl = document.querySelector('.stat-card.total .stat-change');
const ratingChangeEl = document.querySelector('.stat-card.positive .stat-change');
const pendingChangeEl = document.querySelector('.stat-card.pending .stat-change');
const negativeChangeEl = document.querySelector('.stat-card.negative .stat-change');

// Add this helper function
function getAdminImageUrl(review) {
    let imageFilename = review.image_url || review.images;
    
    if (!imageFilename) {
        return null;
    }
    
    // Clean up filename
    imageFilename = imageFilename.split('/').pop();
    
    // Admin panel is at /admin/, so go up one level
    return '../uploads/reviews/' + imageFilename;
}

// Load reviews from API
async function loadReviews() {
    try {
        showLoading();
        console.log('Loading reviews with filter:', currentFilter, 'search:', currentSearch);
        
        // Use relative path - since admin folder is at same level as api folder
        let apiUrl = `../api/reviews_api.php?status=${currentFilter}&page=${currentPage}&limit=${reviewsPerPage}`;
        
        if (currentSearch) {
            apiUrl += `&search=${encodeURIComponent(currentSearch)}`;
        }
        
        console.log('API URL:', apiUrl);
        
        const response = await fetch(apiUrl);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API Response:', data);
        
        if (data.success) {
            allReviews = data.data || [];
            console.log(`Loaded ${allReviews.length} reviews`);
            
            if (data.stats) {
                updateStats(data.stats);
            }
            
            renderReviews(allReviews);
        } else {
            throw new Error(data.message || 'API returned failure');
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
        showError(`Failed to load reviews: ${error.message}`);
        
        // Fallback to sample data if API fails
        useSampleData();
    }
}

// Update statistics cards
function updateStats(stats) {
    console.log('Updating stats with:', stats);
    
    totalReviewsEl.textContent = stats.total_reviews || 0;
    avgRatingEl.textContent = stats.avg_rating ? parseFloat(stats.avg_rating).toFixed(1) : '0.0';
    pendingCountEl.textContent = stats.pending_count || 0;
    negativeCountEl.textContent = stats.negative_count || 0;
    
    // Update notification badge
    const notificationBadge = document.querySelector('.notification-badge');
    const pendingCount = stats.pending_count || 0;
    notificationBadge.textContent = pendingCount > 99 ? '99+' : pendingCount;
    notificationBadge.style.display = pendingCount > 0 ? 'flex' : 'none';
    
    // Store for notification comparison
    previousPendingCount = pendingCount;
    
    // Update change indicators (sample values - you can calculate real changes later)
    totalChangeEl.textContent = '+15%';
    ratingChangeEl.textContent = '+0.2';
    pendingChangeEl.textContent = pendingCount > 0 ? `+${pendingCount}` : '0';
    negativeChangeEl.textContent = '-3';
}

// Filter reviews
function filterReviews(filter) {
    currentFilter = filter;
    currentPage = 1;
    
    // For positive/negative filters, we need to fetch all approved reviews
    // and filter by rating on the client side
    if (filter === 'positive' || filter === 'negative') {
        currentFilter = 'approved';
    }
    
    loadReviews();
    
    // Update active filter button
    filterBtns.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.filter-btn[data-filter="${filter}"]`).classList.add('active');
}

// Render reviews in list
function renderReviews(reviewsToRender) {
    reviewsList.innerHTML = '';
    
    // Apply rating filters if needed
    let filteredReviews = reviewsToRender;
    
    if (currentFilter === 'positive') {
        filteredReviews = reviewsToRender.filter(review => review.rating >= 4);
    } else if (currentFilter === 'negative') {
        filteredReviews = reviewsToRender.filter(review => review.rating <= 2);
    }
    
    if (filteredReviews.length === 0) {
        reviewsList.innerHTML = `
            <div class="no-reviews" style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 10px;">No reviews found</h3>
                <p>There are no reviews matching your criteria.</p>
            </div>
        `;
        return;
    }
    
    filteredReviews.forEach((review, index) => {
        const reviewItem = document.createElement('div');
        reviewItem.className = `review-item reveal reveal-delay-${index % 4}`;
        
        // Format date
        const reviewDate = new Date(review.created_at);
        const formattedDate = reviewDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Status badge
        let statusClass = '';
        let statusText = '';
        
        switch(review.status) {
            case 'approved':
                statusClass = 'status-published';
                statusText = 'Published';
                break;
            case 'pending':
                statusClass = 'status-pending';
                statusText = 'Pending';
                break;
            case 'rejected':
                statusClass = 'status-rejected';
                statusText = 'Rejected';
                break;
        }
        
        // Generate star rating
        let starsHTML = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= review.rating) {
                starsHTML += '<i class="fas fa-star"></i>';
            } else {
                starsHTML += '<i class="far fa-star"></i>';
            }
        }
        
        // Get initials for avatar
        const initials = review.customer_name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
        
        // Check if verified
        const verifiedBadge = review.is_verified ? 
            '<span class="verified-badge" style="color: #4CAF50; margin-left: 5px;"><i class="fas fa-check-circle"></i></span>' : '';
        
        // Get image URL
        const imageUrl = getAdminImageUrl(review);
        
        // Build review item HTML
        let reviewHTML = `
            <div class="review-avatar" style="background-color: ${getAvatarColor(review.customer_name)};">
                ${initials}
            </div>
            <div class="review-content">
                <div class="review-header">
                    <div class="reviewer-info">
                        <h4>${review.customer_name} ${verifiedBadge}</h4>
                        <p>${review.customer_email || 'No email provided'}</p>
                    </div>
                    <div class="review-rating">
                        <div class="rating-stars">
                            ${starsHTML}
                        </div>
                        <span class="rating-value">${review.rating}.0</span>
                        <span class="review-status ${statusClass}">${statusText}</span>
                    </div>
                </div>
                <div class="review-text">
                    ${review.review_text}
                </div>`;
        
        // Add image preview if exists
        if (imageUrl) {
            reviewHTML += `
                <div class="review-image-preview" style="margin: 10px 0;">
                    <img src="${imageUrl}" 
                         alt="Review image" 
                         style="max-width: 150px; max-height: 100px; border-radius: 5px; border: 1px solid #ddd;"
                         onerror="this.style.display='none'; console.error('Admin: Failed to load', this.src);">
                </div>`;
        }
        
        // Add admin reply if exists
        if (review.admin_reply) {
            reviewHTML += `
                <div class="admin-reply">
                    <strong style="color: #2196F3;">Admin Reply:</strong>
                    <p style="margin: 5px 0 0 0;">${review.admin_reply}</p>
                </div>`;
        }
        
        // Add review meta and actions
        reviewHTML += `
                <div class="review-meta">
                    <div class="review-date">
                        ${formattedDate} ${review.order_id ? `• Order ${review.order_id}` : ''}
                    </div>
                    <div class="review-actions">
                        <button class="review-action-btn reply" data-id="${review.id}">
                            <i class="fas fa-reply"></i>
                            ${review.admin_reply ? 'Edit Reply' : 'Reply'}
                        </button>
                        ${review.status === 'pending' ? `
                        <button class="review-action-btn approve" data-id="${review.id}">
                            <i class="fas fa-check"></i>
                            Approve
                        </button>
                        <button class="review-action-btn reject" data-id="${review.id}">
                            <i class="fas fa-times"></i>
                            Reject
                        </button>
                        ` : ''}
                        ${review.status === 'approved' ? `
                        <button class="review-action-btn reject" data-id="${review.id}">
                            <i class="fas fa-times"></i>
                            Unpublish
                        </button>
                        ` : ''}
                        <button class="review-action-btn delete" data-id="${review.id}">
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        reviewItem.innerHTML = reviewHTML;
        reviewsList.appendChild(reviewItem);
    });
    
    // Add event listeners to action buttons
    addReviewActionListeners();
    
    // Trigger scroll reveal animation
    setTimeout(() => {
        const reveals = document.querySelectorAll('.reveal');
        reveals.forEach(reveal => {
            reveal.classList.add('active');
        });
    }, 100);
}

// Add avatar color based on name
function getAvatarColor(name) {
    const colors = [
        '#8b4513', // Saddle Brown
        '#a0522d', // Sienna
        '#d2691e', // Chocolate
        '#cd853f', // Peru
        '#b8860b', // Dark Goldenrod
        '#daa520', // Goldenrod
        '#bdb76b', // Dark Khaki
    ];
    
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    
    return colors[Math.abs(hash) % colors.length];
}

// Add event listeners for review actions
function addReviewActionListeners() {
    document.querySelectorAll('.review-action-btn.reply').forEach(btn => {
        btn.addEventListener('click', function() {
            const reviewId = parseInt(this.getAttribute('data-id'));
            showReviewDetails(reviewId);
        });
    });
    
    document.querySelectorAll('.review-action-btn.approve').forEach(btn => {
        btn.addEventListener('click', async function() {
            const reviewId = parseInt(this.getAttribute('data-id'));
            await approveReview(reviewId);
        });
    });
    
    document.querySelectorAll('.review-action-btn.reject').forEach(btn => {
        btn.addEventListener('click', async function() {
            const reviewId = parseInt(this.getAttribute('data-id'));
            await rejectReview(reviewId);
        });
    });
    
    document.querySelectorAll('.review-action-btn.delete').forEach(btn => {
        btn.addEventListener('click', async function() {
            const reviewId = parseInt(this.getAttribute('data-id'));
            if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
                await deleteReview(reviewId);
            }
        });
    });
}

// Show review details in modal
async function showReviewDetails(reviewId) {
    try {
        const apiUrl = `../api/reviews_api.php?id=${reviewId}`;
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        if (data.success && data.data) {
            const review = data.data;
            
            // Format date
            const reviewDateObj = new Date(review.created_at);
            const formattedDate = reviewDateObj.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Get initials for avatar
            const initials = review.customer_name
                .split(' ')
                .map(word => word[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);
            
            // Update modal content
            reviewDetailsAvatar.textContent = initials;
            reviewDetailsAvatar.style.backgroundColor = getAvatarColor(review.customer_name);
            reviewerName.textContent = review.customer_name;
            reviewerEmail.textContent = review.customer_email || 'No email provided';
            reviewDetailsText.textContent = review.review_text;
            reviewOrderId.textContent = review.order_id || 'N/A';
            reviewDate.textContent = formattedDate;
            reviewItems.textContent = review.menu_items || 'Not specified';
            
            // Status
            let statusText = '';
            switch(review.status) {
                case 'approved':
                    statusText = 'Published';
                    break;
                case 'pending':
                    statusText = 'Pending Approval';
                    break;
                case 'rejected':
                    statusText = 'Rejected';
                    break;
            }
            reviewStatus.textContent = statusText;
            reviewStatus.className = `review-status status-${review.status}`;
            
            // Rating stars
            modalRatingValue.textContent = review.rating + '.0';
            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= review.rating) {
                    starsHTML += '<i class="fas fa-star"></i>';
                } else {
                    starsHTML += '<i class="far fa-star"></i>';
                }
            }
            modalRatingStars.innerHTML = starsHTML;
            
            // Admin reply
            replyTextarea.value = review.admin_reply || '';
            
            // Set data attribute for review ID
            reviewDetailsModal.dataset.reviewId = review.id;
            
            // Update button text based on status
            if (review.status === 'approved') {
                approveReviewBtn.innerHTML = '<i class="fas fa-times"></i> Unpublish';
                approveReviewBtn.className = 'btn btn-danger';
                approveReviewBtn.onclick = () => rejectReview(review.id);
            } else if (review.status === 'pending') {
                approveReviewBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
                approveReviewBtn.className = 'btn btn-success';
                approveReviewBtn.onclick = () => approveReview(review.id);
            } else if (review.status === 'rejected') {
                approveReviewBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
                approveReviewBtn.className = 'btn btn-success';
                approveReviewBtn.onclick = () => approveReview(review.id);
            }
            
            // Show modal
            reviewDetailsModal.style.display = 'flex';
        }
    } catch (error) {
        console.error('Error loading review details:', error);
        showError('Failed to load review details');
    }
}

// Approve review
async function approveReview(reviewId) {
    try {
        const formData = new FormData();
        formData.append('action', 'approve_review');
        formData.append('review_id', reviewId);
        
        const apiUrl = '../admin/admin-reviews-backend.php';
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Review approved successfully', 'success');
            loadReviews();
            
            // Close modal if open
            if (reviewDetailsModal.style.display === 'flex' && 
                parseInt(reviewDetailsModal.dataset.reviewId) === reviewId) {
                reviewDetailsModal.style.display = 'none';
            }
        } else {
            showError(data.message || 'Failed to approve review');
        }
    } catch (error) {
        console.error('Error approving review:', error);
        showError('Failed to approve review');
    }
}

// Reject review
async function rejectReview(reviewId) {
    try {
        const formData = new FormData();
        formData.append('action', 'reject_review');
        formData.append('review_id', reviewId);
        
        const apiUrl = '../admin/admin-reviews-backend.php';
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Review rejected successfully', 'success');
            loadReviews();
            
            // Close modal if open
            if (reviewDetailsModal.style.display === 'flex' && 
                parseInt(reviewDetailsModal.dataset.reviewId) === reviewId) {
                reviewDetailsModal.style.display = 'none';
            }
        } else {
            showError(data.message || 'Failed to reject review');
        }
    } catch (error) {
        console.error('Error rejecting review:', error);
        showError('Failed to reject review');
    }
}

// Delete review
async function deleteReview(reviewId) {
    try {
        const formData = new FormData();
        formData.append('action', 'delete_review');
        formData.append('review_id', reviewId);
        
        const apiUrl = '../admin/admin-reviews-backend.php';
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Review deleted successfully', 'success');
            loadReviews();
            
            // Close modal if open
            if (reviewDetailsModal.style.display === 'flex' && 
                parseInt(reviewDetailsModal.dataset.reviewId) === reviewId) {
                reviewDetailsModal.style.display = 'none';
            }
        } else {
            showError(data.message || 'Failed to delete review');
        }
    } catch (error) {
        console.error('Error deleting review:', error);
        showError('Failed to delete review');
    }
}

// Save admin reply
async function saveAdminReply() {
    const reviewId = reviewDetailsModal.dataset.reviewId;
    const replyText = replyTextarea.value;
    
    if (!reviewId) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_reply');
        formData.append('review_id', reviewId);
        formData.append('admin_reply', replyText);
        
        const apiUrl = '../admin/admin-reviews-backend.php';
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Reply saved successfully', 'success');
            loadReviews();
            reviewDetailsModal.style.display = 'none';
        } else {
            showError(data.message || 'Failed to save reply');
        }
    } catch (error) {
        console.error('Error saving reply:', error);
        showError('Failed to save reply');
    }
}

// Search functionality
function searchReviews(searchTerm) {
    currentSearch = searchTerm;
    currentPage = 1;
    loadReviews();
}

// Show loading state
function showLoading() {
    reviewsList.innerHTML = `
        <div class="loading-state" style="text-align: center; padding: 40px;">
            <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #8b4513; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
            <p>Loading reviews...</p>
        </div>
    `;
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#4CAF50' : '#F44336'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Show error message
function showError(message) {
    showNotification(message, 'error');
}

// Sample data fallback function
function useSampleData() {
    console.log('Using sample data as fallback');
    
    // Create sample stats
    const sampleStats = {
        total_reviews: 45,
        avg_rating: 4.2,
        pending_count: 8,
        negative_count: 3
    };
    
    // Create sample reviews
    const sampleReviews = [
        {
            id: 1,
            customer_name: 'John Smith',
            customer_email: 'john@example.com',
            rating: 5,
            review_text: 'Excellent service and food quality! Will definitely order again.',
            created_at: '2024-01-15 14:30:00',
            order_id: 'ORD-12345',
            status: 'approved',
            admin_reply: 'Thank you for your kind words! We look forward to serving you again.',
            is_verified: true,
            menu_items: 'Burger, Fries, Coke'
        },
        {
            id: 2,
            customer_name: 'Sarah Johnson',
            customer_email: 'sarah@example.com',
            rating: 3,
            review_text: 'Food was good but delivery was late by 30 minutes.',
            created_at: '2024-01-14 19:45:00',
            order_id: 'ORD-12344',
            status: 'pending',
            admin_reply: null,
            is_verified: false,
            menu_items: 'Pizza, Salad'
        },
        {
            id: 3,
            customer_name: 'Mike Brown',
            customer_email: 'mike@example.com',
            rating: 1,
            review_text: 'Very disappointed with the quality. Food was cold and tasteless.',
            created_at: '2024-01-13 12:15:00',
            order_id: 'ORD-12343',
            status: 'approved',
            admin_reply: 'We apologize for your experience. Please contact our support team for a refund.',
            is_verified: true,
            menu_items: 'Steak, Mashed Potatoes'
        }
    ];
    
    allReviews = sampleReviews;
    updateStats(sampleStats);
    renderReviews(allReviews);
    
    showNotification('Using sample data. API connection failed.', 'error');
}

// Real-time notification check (every 30 seconds)
function startNotificationPolling() {
    setInterval(async () => {
        try {
            const apiUrl = '../api/reviews_api.php?status=all&limit=1';
            const response = await fetch(apiUrl);
            const data = await response.json();
            
            if (data.success && data.stats) {
                const newPendingCount = data.stats.pending_count || 0;
                
                // Show desktop notification if new pending reviews
                if (newPendingCount > previousPendingCount) {
                    if (Notification.permission === "granted") {
                        new Notification("New Review Pending", {
                            body: `You have ${newPendingCount} pending reviews to approve`,
                            icon: "../images/logo3.png"
                        });
                    }
                    
                    // Update stats
                    updateStats(data.stats);
                }
                
                previousPendingCount = newPendingCount;
            }
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }, 30000); // Check every 30 seconds
}

// Request notification permission
function requestNotificationPermission() {
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }
}

// Show pending reviews dropdown
function showPendingReviewsDropdown() {
    const pendingReviews = allReviews.filter(review => review.status === 'pending');
    
    // Create dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'notification-dropdown';
    dropdown.style.cssText = `
        position: absolute;
        top: 40px;
        right: 0;
        width: 300px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        max-height: 400px;
        overflow-y: auto;
    `;
    
    if (pendingReviews.length === 0) {
        dropdown.innerHTML = `
            <div style="padding: 20px; text-align: center; color: #666;">
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #4CAF50; margin-bottom: 10px;"></i>
                <p>No pending reviews</p>
            </div>
        `;
    } else {
        dropdown.innerHTML = `
            <div style="padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <strong>Pending Reviews (${pendingReviews.length})</strong>
                <button class="view-all" style="background: #8b4513; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">View All</button>
            </div>
        `;
        
        pendingReviews.slice(0, 5).forEach(review => {
            const reviewItem = document.createElement('div');
            reviewItem.style.cssText = `
                padding: 15px;
                border-bottom: 1px solid #f5f5f5;
                cursor: pointer;
                transition: background 0.2s;
            `;
            reviewItem.innerHTML = `
                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: ${getAvatarColor(review.customer_name)}; color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 0.8rem;">
                        ${review.customer_name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <strong style="font-size: 0.9rem;">${review.customer_name}</strong>
                        <div style="font-size: 0.8rem; color: #666;">${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}</div>
                    </div>
                </div>
                <p style="font-size: 0.85rem; color: #333; margin: 0; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${review.review_text}</p>
            `;
            
            reviewItem.addEventListener('click', () => {
                showReviewDetails(review.id);
                dropdown.remove();
            });
            
            dropdown.appendChild(reviewItem);
        });
        
        if (pendingReviews.length > 5) {
            const moreItem = document.createElement('div');
            moreItem.style.cssText = `
                padding: 10px;
                text-align: center;
                color: #8b4513;
                cursor: pointer;
            `;
            moreItem.textContent = `+ ${pendingReviews.length - 5} more pending reviews`;
            moreItem.addEventListener('click', () => {
                filterReviews('pending');
                dropdown.remove();
            });
            dropdown.appendChild(moreItem);
        }
    }
    
    // Remove existing dropdown if any
    const existingDropdown = document.querySelector('.notification-dropdown');
    if (existingDropdown) {
        existingDropdown.remove();
    }
    
    // Add to DOM
    document.querySelector('.notification-icon').appendChild(dropdown);
    
    // Close dropdown when clicking outside
    setTimeout(() => {
        const closeDropdown = (e) => {
            if (!dropdown.contains(e.target) && !e.target.closest('.notification-icon')) {
                dropdown.remove();
                document.removeEventListener('click', closeDropdown);
            }
        };
        document.addEventListener('click', closeDropdown);
    }, 100);
}

// Scroll reveal functionality
function revealOnScroll() {
    const reveals = document.querySelectorAll('.reveal');
    
    for (let i = 0; i < reveals.length; i++) {
        const windowHeight = window.innerHeight;
        const elementTop = reveals[i].getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < windowHeight - elementVisible) {
            reveals[i].classList.add('active');
        }
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Load initial reviews
    loadReviews();
    
    // Initialize scroll reveal
    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll();
    
    // Filter buttons
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterReviews(this.getAttribute('data-filter'));
        });
    });
    
    // Search functionality
    searchBox.addEventListener('input', function() {
        searchReviews(this.value);
    });
    
    // Modal actions
    closeModal.addEventListener('click', function() {
        reviewDetailsModal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === reviewDetailsModal) {
            reviewDetailsModal.style.display = 'none';
        }
    });
    
    approveReviewBtn.addEventListener('click', async function() {
        const reviewId = reviewDetailsModal.dataset.reviewId;
        if (reviewId) {
            await approveReview(reviewId);
        }
    });
    
    rejectReviewBtn.addEventListener('click', async function() {
        const reviewId = reviewDetailsModal.dataset.reviewId;
        if (reviewId) {
            await rejectReview(reviewId);
        }
    });
    
    saveReplyBtn.addEventListener('click', async function() {
        await saveAdminReply();
    });
    
    // Notification bell click (show pending reviews)
    document.querySelector('.notification-icon').addEventListener('click', function() {
        showPendingReviewsDropdown();
    });
    
    // Request notification permission
    requestNotificationPermission();
    
    // Start polling for new reviews
    startNotificationPolling();
});

// Add CSS for animations
const animationsStyle = document.createElement('style');
animationsStyle.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .notification-dropdown {
        animation: fadeIn 0.2s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .notification-dropdown div:hover {
        background: #f9f9f9;
    }
    
    .verified-badge {
        color: #4CAF50;
        margin-left: 5px;
    }
    
    .admin-reply {
        background: #f0f7ff;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        border-left: 3px solid #2196F3;
    }
`;
document.head.appendChild(animationsStyle);


// admin-reviews.js - Real-Time Reviews Management

class ReviewsRealTimeManager {
    constructor() {
        this.lastUpdate = new Date().toISOString();
        this.pollingInterval = 3000; // 3 seconds
        this.statsInterval = 30000; // 30 seconds
        this.isPolling = false;
        
        // DOM Elements
        this.reviewsList = document.getElementById('reviewsList');
        this.searchInput = document.getElementById('searchInput');
        this.filterButtons = document.querySelectorAll('.filter-btn');
        
        // Stats elements
        this.totalReviewsEl = document.getElementById('totalReviews');
        this.avgRatingEl = document.getElementById('avgRating');
        this.pendingCountEl = document.getElementById('pendingCount');
        this.negativeCountEl = document.getElementById('negativeCount');
        
        this.currentFilter = 'all';
        this.currentSearch = '';
        
        this.init();
    }

    init() {
        console.log('Initializing Reviews Real-Time Manager...');
        
        this.setupEventListeners();
        this.loadInitialData();
        this.startPolling();
        this.startStatsPolling();
    }

    setupEventListeners() {
        // Filter buttons
        this.filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentFilter = btn.dataset.filter;
                this.loadReviews();
            });
        });

        // Search input
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.currentSearch = e.target.value.trim();
                    this.loadReviews();
                }, 500);
            });
        }

        // Real-time indicator
        this.createRealTimeIndicator();
    }

    createRealTimeIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'real-time-indicator';
        indicator.innerHTML = `
            <div class="pulse"></div>
            <span>Live Updates</span>
        `;
        indicator.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 999;
            box-shadow: var(--shadow);
            animation: fadeIn 0.3s ease;
            cursor: pointer;
        `;
        
        indicator.addEventListener('click', () => {
            this.loadReviews();
            this.showNotification('Refreshing reviews...', 'info');
        });
        
        document.body.appendChild(indicator);
        this.realTimeIndicator = indicator;
    }

    async loadInitialData() {
        await Promise.all([
            this.loadReviews(),
            this.loadStats(),
            this.checkForNewReviews()
        ]);
    }

    async loadReviews() {
        try {
            this.showLoadingState(true);
            
            const params = new URLSearchParams({
                action: 'get_all_reviews',
                filter: this.currentFilter,
                limit: 50
            });
            
            if (this.currentSearch) {
                params.append('q', this.currentSearch);
            }
            
            const response = await fetch(`admin-reviews-backend.php?${params}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderReviews(data.reviews);
                this.lastUpdate = new Date().toISOString();
            } else {
                this.showError('Failed to load reviews');
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
            this.showError('Network error. Please check your connection.');
        } finally {
            this.showLoadingState(false);
        }
    }

    renderReviews(reviews) {
        if (!this.reviewsList) return;

        if (!reviews || reviews.length === 0) {
            this.reviewsList.innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--text-light);">
                    <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                    <h3>No reviews found</h3>
                    <p>${this.currentSearch ? 'No reviews match your search' : 'There are no reviews matching your criteria'}</p>
                </div>
            `;
            return;
        }

        this.reviewsList.innerHTML = reviews.map(review => this.createReviewHTML(review)).join('');
        
        // Add animation for new reviews
        const newReviews = reviews.filter(r => r.is_new);
        if (newReviews.length > 0) {
            this.highlightNewReviews(newReviews);
        }
    }

    createReviewHTML(review) {
        const statusClass = {
            'approved': 'status-published',
            'pending': 'status-pending',
            'rejected': 'status-rejected'
        }[review.status] || 'status-pending';

        const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
        const timeAgo = review.time_ago || this.calculateTimeAgo(review.created_at);
        const avatar = review.avatar_initials || (review.customer_name ? review.customer_name.charAt(0).toUpperCase() : 'U');
        
        return `
            <div class="review-item ${review.is_new ? 'new' : ''}" data-id="${review.id}">
                <div class="review-avatar" style="background: ${this.getAvatarColor(review.customer_name)}">
                    ${avatar}
                </div>
                <div class="review-content">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <h4>${review.customer_name || 'Anonymous'}
                                ${review.is_verified ? '<i class="fas fa-check-circle verified-badge"></i>' : ''}
                            </h4>
                            <p>${review.customer_email || ''}</p>
                        </div>
                        <div class="review-rating">
                            <div class="rating-stars" title="${review.rating} stars" style="color: #ffc107;">
                                ${stars}
                            </div>
                            <span class="rating-value">${review.rating}.0</span>
                            <span class="review-status ${statusClass}">${review.status}</span>
                        </div>
                    </div>
                    <div class="review-text">
                        ${this.formatReviewText(review.review_text)}
                    </div>
                    <div class="review-meta">
                        <div class="review-date">
                            <i class="far fa-clock"></i> ${timeAgo}
                            ${review.order_id ? ` • Order #${review.order_id}` : ''}
                        </div>
                        <div class="review-actions">
                            ${review.status === 'pending' ? `
                                <button class="review-action-btn approve" onclick="approveReview(${review.id})">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="review-action-btn reject" onclick="rejectReview(${review.id})">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            ` : ''}
                            <button class="review-action-btn reply" onclick="openReviewModal(${review.id})">
                                <i class="fas fa-reply"></i> Reply
                            </button>
                            <button class="review-action-btn delete" onclick="deleteReview(${review.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    ${review.admin_reply ? `
                        <div class="admin-reply">
                            <strong><i class="fas fa-user-shield"></i> Admin Reply:</strong>
                            <p>${review.admin_reply}</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    formatReviewText(text) {
        if (!text) return 'No review text provided.';
        // Simple formatting - you can enhance this
        return text.replace(/\n/g, '<br>');
    }

    calculateTimeAgo(dateString) {
        const created = new Date(dateString);
        const now = new Date();
        const diffMs = now - created;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} min ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    }

    getAvatarColor(name) {
        // Generate consistent color based on name
        const colors = [
            '#8b4513', '#a0522d', '#d2691e', '#ff7b54',
            '#4CAF50', '#2196F3', '#9C27B0', '#FF9800',
            '#F44336', '#607D8B'
        ];
        
        if (!name) return colors[0];
        
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        
        return colors[Math.abs(hash) % colors.length];
    }

    highlightNewReviews(newReviews) {
        newReviews.forEach(review => {
            const element = document.querySelector(`.review-item[data-id="${review.id}"]`);
            if (element) {
                element.classList.add('new');
                
                // Remove highlight after 5 seconds
                setTimeout(() => {
                    element.classList.remove('new');
                }, 5000);
            }
        });
    }

    async loadStats() {
        try {
            const response = await fetch('admin-reviews-backend.php?action=get_review_stats');
            const data = await response.json();
            
            if (data.success) {
                this.updateStatsDisplay(data.stats);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    updateStatsDisplay(stats) {
        if (this.totalReviewsEl) this.totalReviewsEl.textContent = stats.total_reviews || 0;
        if (this.avgRatingEl) this.avgRatingEl.textContent = (stats.avg_rating || 0).toFixed(1);
        if (this.pendingCountEl) this.pendingCountEl.textContent = stats.pending_count || 0;
        if (this.negativeCountEl) this.negativeCountEl.textContent = stats.negative_count || 0;
        
        // Update change percentages (example values - you can calculate real changes)
        document.getElementById('totalChange').textContent = stats.total_change || '+0%';
        document.getElementById('ratingChange').textContent = stats.rating_change || '+0.0';
        document.getElementById('pendingChange').textContent = stats.pending_change || '0';
        document.getElementById('negativeChange').textContent = stats.negative_change || '0';
    }

    async checkForNewReviews() {
        try {
            const params = new URLSearchParams({
                action: 'get_new_reviews',
                last_check: this.lastUpdate
            });
            
            const response = await fetch(`admin-reviews-backend.php?${params}`);
            const data = await response.json();
            
            if (data.success && data.reviews.length > 0) {
                this.showNewReviewsAlert(data.reviews.length);
                this.lastUpdate = data.server_time;
            }
        } catch (error) {
            console.error('Error checking for new reviews:', error);
        }
    }

    showNewReviewsAlert(count) {
        if (count === 0) return;
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = 'floating-notification';
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-star" style="color: var(--warning);"></i>
                <div>
                    <strong>${count} new review${count > 1 ? 's' : ''}</strong>
                    <div style="font-size: 0.8em; opacity: 0.9;">Click to refresh</div>
                </div>
                <i class="fas fa-sync-alt" style="margin-left: auto;"></i>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            bottom: 70px;
            right: 20px;
            background: white;
            color: var(--text);
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--warning);
            z-index: 999;
            animation: slideIn 0.3s ease;
            cursor: pointer;
            max-width: 300px;
        `;
        
        notification.onclick = () => {
            this.loadReviews();
            notification.remove();
        };
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        
        // Poll for updates every 3 seconds
        this.pollInterval = setInterval(async () => {
            await this.checkForNewReviews();
        }, this.pollingInterval);
    }

    startStatsPolling() {
        // Update stats every 30 seconds
        this.statsIntervalId = setInterval(async () => {
            await this.loadStats();
        }, this.statsInterval);
    }

    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
        if (this.statsIntervalId) {
            clearInterval(this.statsIntervalId);
        }
        this.isPolling = false;
    }

    showLoadingState(show) {
        if (show) {
            this.reviewsList.classList.add('loading');
        } else {
            this.reviewsList.classList.remove('loading');
        }
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `
            <div style="text-align: center; padding: 20px; color: var(--danger);">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
                <button onclick="window.location.reload()" style="margin-top: 10px; padding: 8px 16px; background: var(--danger); color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Retry
                </button>
            </div>
        `;
        
        this.reviewsList.innerHTML = '';
        this.reviewsList.appendChild(errorDiv);
    }

    showNotification(message, type = 'info') {
        showNotification(message, type);
    }
}

// Global notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = 'notification';
    
    const colors = {
        'success': '#4CAF50',
        'error': '#F44336',
        'warning': '#FF9800',
        'info': '#2196F3'
    };
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: var(--shadow);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 300px;
        max-width: 400px;
    `;
    
    notification.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <div>${message}</div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Review action functions
async function approveReview(reviewId) {
    try {
        const formData = new FormData();
        formData.append('action', 'approve_review');
        formData.append('review_id', reviewId);
        
        const response = await fetch('admin-reviews-backend.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`Review #${reviewId} approved successfully`, 'success');
            if (window.reviewsManager) {
                window.reviewsManager.loadReviews();
                window.reviewsManager.loadStats();
            }
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error approving review', 'error');
    }
}

async function rejectReview(reviewId) {
    if (!confirm('Are you sure you want to reject this review?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'reject_review');
        formData.append('review_id', reviewId);
        
        const response = await fetch('admin-reviews-backend.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`Review #${reviewId} rejected successfully`, 'success');
            if (window.reviewsManager) {
                window.reviewsManager.loadReviews();
                window.reviewsManager.loadStats();
            }
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error rejecting review', 'error');
    }
}

async function deleteReview(reviewId) {
    if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_review');
        formData.append('review_id', reviewId);
        
        const response = await fetch('admin-reviews-backend.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`Review #${reviewId} deleted successfully`, 'success');
            if (window.reviewsManager) {
                window.reviewsManager.loadReviews();
                window.reviewsManager.loadStats();
            }
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error deleting review', 'error');
    }
}

// Review modal functions
async function openReviewModal(reviewId) {
    try {
        const response = await fetch(`admin-reviews-backend.php?action=get_review&id=${reviewId}`);
        const data = await response.json();
        
        if (data.success) {
            const review = data.review;
            const modal = document.getElementById('reviewDetailsModal');
            
            // Populate modal
            document.getElementById('reviewerName').textContent = review.customer_name || 'Anonymous';
            document.getElementById('reviewerEmail').textContent = review.customer_email || 'No email provided';
            document.getElementById('reviewDetailsAvatar').textContent = review.avatar_initials || 'U';
            document.getElementById('reviewDetailsAvatar').style.background = 
                window.reviewsManager ? window.reviewsManager.getAvatarColor(review.customer_name) : '#8b4513';
            document.getElementById('reviewDetailsText').textContent = review.review_text || 'No review text';
            document.getElementById('reviewOrderId').textContent = review.order_id || 'N/A';
            document.getElementById('reviewDate').textContent = review.formatted_date || 'N/A';
            document.getElementById('reviewStatus').textContent = review.status;
            document.getElementById('reviewItems').textContent = review.menu_items || 'Not specified';
            document.getElementById('modalRatingValue').textContent = review.rating + '.0';
            
            // Create stars
            const starsContainer = document.getElementById('modalRatingStars');
            starsContainer.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElement('i');
                star.className = i <= review.rating ? 'fas fa-star' : 'far fa-star';
                star.style.color = '#ffc107';
                starsContainer.appendChild(star);
            }
            
            // Set reply textarea
            document.getElementById('replyTextarea').value = review.admin_reply || '';
            
            // Update action buttons
            document.getElementById('approveReviewBtn').onclick = () => approveReview(reviewId);
            document.getElementById('rejectReviewBtn').onclick = () => rejectReview(reviewId);
            document.getElementById('saveReplyBtn').onclick = () => saveReply(reviewId);
            
            // Show modal
            modal.style.display = 'flex';
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error loading review details', 'error');
    }
}

async function saveReply(reviewId) {
    const replyText = document.getElementById('replyTextarea').value.trim();
    
    if (!replyText) {
        showNotification('Please enter a reply', 'warning');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_reply');
        formData.append('review_id', reviewId);
        formData.append('admin_reply', replyText);
        
        const response = await fetch('admin-reviews-backend.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Reply saved successfully', 'success');
            if (window.reviewsManager) {
                window.reviewsManager.loadReviews();
            }
            
            // Close modal
            document.getElementById('reviewDetailsModal').style.display = 'none';
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error saving reply', 'error');
    }
}

// Modal close functionality
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('reviewDetailsModal');
    const closeBtn = document.getElementById('closeModal');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Initialize reviews manager
    window.reviewsManager = new ReviewsRealTimeManager();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    @keyframes highlight {
        0% {
            background: rgba(139, 69, 19, 0.1);
        }
        100% {
            background: transparent;
        }
    }
    
    .review-item.new {
        animation: highlight 2s ease;
    }
    
    .real-time-indicator .pulse {
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    
    .floating-notification {
        animation: slideIn 0.3s ease;
    }
    
    .notification {
        animation: slideIn 0.3s ease;
    }
    
    .loading {
        position: relative;
        opacity: 0.7;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #8b4513;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translate(-50%, -50%);
    }
    
    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
`;
document.head.appendChild(style);