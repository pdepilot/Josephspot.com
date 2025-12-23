/**
 * WebSocket Client for Real-time Notifications
 */

class NotificationWebSocket {
    constructor(channel = 'main_dashboard') {
        this.channel = channel;
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 3000;
        this.callbacks = {
            'new_order': [],
            'new_message': [],
            'new_reservation': [],
            'notification': []
        };
    }
    
    connect() {
        try {
            // Use wss:// for secure connections, ws:// for local development
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const host = window.location.hostname;
            const port = '8080';
            const wsUrl = `${protocol}//${host}:${port}`;
            
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
                this.reconnectAttempts = 0;
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
                console.error('WebSocket error:', error);
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                this.reconnect();
            };
        } catch (e) {
            console.error('WebSocket connection error:', e);
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
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Reconnecting... Attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
            setTimeout(() => {
                this.connect();
            }, this.reconnectDelay);
        } else {
            console.error('Max reconnection attempts reached. Falling back to polling.');
            // Fallback to polling
            this.startPolling();
        }
    }
    
    startPolling() {
        // Fallback to HTTP polling if WebSocket fails
        setInterval(() => {
            if (typeof updateNotifications === 'function') {
                updateNotifications();
            }
        }, 5000); // Poll every 5 seconds
    }
    
    disconnect() {
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
        console.error('Failed to initialize WebSocket:', e);
        return null;
    }
}

