/**
 * WebSocket Client for Real-time Notifications
 */

class NotificationWebSocket {
    constructor(channel = 'main_dashboard') {
        this.channel = channel;
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 3; // Reduced from 10 to fail faster
        this.reconnectDelay = 3000;
        this.isConnecting = false;
        this.shouldReconnect = true;
        this.fallbackToPolling = false;
        this.callbacks = {
            'new_order': [],
            'new_message': [],
            'new_reservation': [],
            'notification': []
        };
    }
    
    connect() {
        // Don't attempt to reconnect if we've already fallen back to polling
        if (this.fallbackToPolling || this.isConnecting) {
            return;
        }
        
        try {
            this.isConnecting = true;
            // Use wss:// for secure connections, ws:// for local development
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const host = window.location.hostname;
            const port = '8080';
            const wsUrl = `${protocol}//${host}:${port}`;
            
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected successfully');
                this.isConnecting = false;
                this.reconnectAttempts = 0;
                this.shouldReconnect = true;
                this.subscribe(this.channel);
                if (this.onConnect) this.onConnect();
            };
            
            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                } catch (e) {
                    console.error('Error parsing WebSocket message:', e);
                }
            };
            
            this.ws.onerror = (error) => {
                this.isConnecting = false;
                // Only log error on first attempt, reduce console spam
                if (this.reconnectAttempts === 0) {
                    console.warn('WebSocket server not available. Falling back to polling mode.');
                }
            };
            
            this.ws.onclose = (event) => {
                this.isConnecting = false;
                // Only log if it wasn't a clean close or if it's the first attempt
                if (!event.wasClean && this.reconnectAttempts === 0) {
                    // Already logged in onerror, no need to log again
                }
                if (this.shouldReconnect) {
                    this.reconnect();
                }
            };
        } catch (e) {
            this.isConnecting = false;
            if (this.reconnectAttempts === 0) {
                console.warn('WebSocket connection failed. Falling back to polling mode.');
            }
            this.reconnect();
        }
    }
    
    subscribe(channel) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                action: 'subscribe',
                channel: channel
            }));
        }
    }
    
    unsubscribe(channel) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                action: 'unsubscribe',
                channel: channel
            }));
        }
    }
    
    handleMessage(data) {
        if (data.type && this.callbacks[data.type]) {
            this.callbacks[data.type].forEach(callback => {
                callback(data);
            });
        }
        
        // Generic notification callback
        this.callbacks['notification'].forEach(callback => {
            callback(data);
        });
    }
    
    on(event, callback) {
        if (this.callbacks[event]) {
            this.callbacks[event].push(callback);
        }
    }
    
    off(event, callback) {
        if (this.callbacks[event]) {
            const index = this.callbacks[event].indexOf(callback);
            if (index > -1) {
                this.callbacks[event].splice(index, 1);
            }
        }
    }
    
    reconnect() {
        if (!this.shouldReconnect || this.fallbackToPolling) {
            return;
        }
        
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            // Only log first reconnection attempt, reduce console spam
            if (this.reconnectAttempts === 1) {
                console.log('Attempting to reconnect WebSocket...');
            }
            setTimeout(() => {
                if (this.shouldReconnect && !this.fallbackToPolling) {
                    this.connect();
                }
            }, this.reconnectDelay);
        } else {
            // Max attempts reached, fall back to polling
            this.shouldReconnect = false;
            this.fallbackToPolling = true;
            console.info('WebSocket unavailable. Using polling mode for notifications.');
            this.startPolling();
        }
    }
    
    startPolling() {
        // Fallback to HTTP polling if WebSocket fails
        // Check if polling is already started to avoid multiple intervals
        if (this.pollingInterval) {
            return;
        }
        
        this.pollingInterval = setInterval(() => {
            // Try common notification update function names
            if (typeof loadNotifications === 'function') {
                loadNotifications();
            } else if (typeof updateNotifications === 'function') {
                updateNotifications();
            }
        }, 5000); // Poll every 5 seconds
    }
    
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }
    
    disconnect() {
        this.shouldReconnect = false;
        this.stopPolling();
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
    }
}

// Global instance
let notificationWS = null;

// Initialize WebSocket connection
function initWebSocket(channel = 'main_dashboard') {
    try {
        notificationWS = new NotificationWebSocket(channel);
        notificationWS.connect();
        return notificationWS;
    } catch (e) {
        // Silently fail - polling will handle notifications
        console.debug('WebSocket initialization skipped:', e);
        return null;
    }
}

