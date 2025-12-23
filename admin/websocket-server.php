<?php
/**
 * WebSocket Server for Real-time Notifications
 * Run this with: php -q admin/websocket-server.php
 * Or use: php admin/websocket-server.php
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// WebSocket server configuration
define('WS_HOST', '0.0.0.0');
define('WS_PORT', 8080);

// Simple WebSocket server implementation
class WebSocketServer {
    private $socket;
    private $clients = [];
    private $channels = [
        'orders' => [],
        'contact_messages' => [],
        'reservations' => [],
        'main_dashboard' => []
    ];
    
    public function __construct() {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, WS_HOST, WS_PORT);
        socket_listen($this->socket, 5);
        
        echo "WebSocket server started on " . WS_HOST . ":" . WS_PORT . "\n";
    }
    
    public function run() {
        while (true) {
            $sockets = array_column($this->clients, 'socket');
            $sockets[] = $this->socket;
            
            $changed = $sockets;
            socket_select($changed, $null, $null, 0, 10);
            
            if (in_array($this->socket, $changed)) {
                $newSocket = socket_accept($this->socket);
                $this->handleNewConnection($newSocket);
                $key = array_search($this->socket, $changed);
                unset($changed[$key]);
            }
            
            foreach ($changed as $socket) {
                $client = $this->getClientBySocket($socket);
                if ($client) {
                    $data = @socket_read($socket, 1024, PHP_NORMAL_READ);
                    if ($data === false || empty($data)) {
                        $this->disconnectClient($client);
                    } else {
                        $this->handleMessage($client, $data);
                    }
                }
            }
            
            // Check for new notifications from database
            $this->checkForNewNotifications();
            
            usleep(100000); // 0.1 second delay
        }
    }
    
    private function handleNewConnection($socket) {
        $handshake = socket_read($socket, 1024);
        
        if (preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $handshake, $matches)) {
            $key = $matches[1];
            $responseKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            
            $response = "HTTP/1.1 101 Switching Protocols\r\n";
            $response .= "Upgrade: websocket\r\n";
            $response .= "Connection: Upgrade\r\n";
            $response .= "Sec-WebSocket-Accept: $responseKey\r\n\r\n";
            
            socket_write($socket, $response);
            
            $clientId = uniqid();
            $this->clients[$clientId] = [
                'id' => $clientId,
                'socket' => $socket,
                'channels' => []
            ];
            
            echo "New client connected: $clientId\n";
        }
    }
    
    private function getClientBySocket($socket) {
        foreach ($this->clients as $client) {
            if ($client['socket'] === $socket) {
                return $client;
            }
        }
        return null;
    }
    
    private function handleMessage($client, $data) {
        $message = $this->unmask($data);
        $decoded = json_decode($message, true);
        
        if ($decoded && isset($decoded['action'])) {
            switch ($decoded['action']) {
                case 'subscribe':
                    if (isset($decoded['channel'])) {
                        $this->subscribeToChannel($client, $decoded['channel']);
                    }
                    break;
                case 'unsubscribe':
                    if (isset($decoded['channel'])) {
                        $this->unsubscribeFromChannel($client, $decoded['channel']);
                    }
                    break;
            }
        }
    }
    
    private function subscribeToChannel($client, $channel) {
        if (isset($this->channels[$channel])) {
            if (!in_array($client['id'], $this->channels[$channel])) {
                $this->channels[$channel][] = $client['id'];
                $client['channels'][] = $channel;
                $this->clients[$client['id']] = $client;
                
                echo "Client {$client['id']} subscribed to channel: $channel\n";
            }
        }
    }
    
    private function unsubscribeFromChannel($client, $channel) {
        if (isset($this->channels[$channel])) {
            $key = array_search($client['id'], $this->channels[$channel]);
            if ($key !== false) {
                unset($this->channels[$channel][$key]);
                $this->channels[$channel] = array_values($this->channels[$channel]);
            }
        }
        
        $key = array_search($channel, $client['channels']);
        if ($key !== false) {
            unset($client['channels'][$key]);
            $client['channels'] = array_values($client['channels']);
            $this->clients[$client['id']] = $client;
        }
    }
    
    private function broadcastToChannel($channel, $message) {
        if (!isset($this->channels[$channel])) {
            return;
        }
        
        $data = json_encode($message);
        $encoded = $this->mask($data);
        
        foreach ($this->channels[$channel] as $clientId) {
            if (isset($this->clients[$clientId])) {
                $client = $this->clients[$clientId];
                @socket_write($client['socket'], $encoded);
            }
        }
    }
    
    private function checkForNewNotifications() {
        static $lastCheck = 0;
        $now = time();
        
        // Check every 2 seconds
        if ($now - $lastCheck < 2) {
            return;
        }
        $lastCheck = $now;
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            return;
        }
        
        // Check for new orders
        $ordersResult = $conn->query("
            SELECT COUNT(*) as count 
            FROM orders 
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
            AND order_status IN ('pending', 'processing')
        ");
        if ($ordersResult && $row = $ordersResult->fetch_assoc()) {
            if ($row['count'] > 0) {
                $this->broadcastToChannel('orders', [
                    'type' => 'new_order',
                    'count' => (int)$row['count'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                $this->broadcastToChannel('main_dashboard', [
                    'type' => 'new_order',
                    'count' => (int)$row['count'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Check for new contact messages
        $messagesResult = $conn->query("
            SELECT COUNT(*) as count 
            FROM contact_messages 
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
            AND status = 'unread'
        ");
        if ($messagesResult && $row = $messagesResult->fetch_assoc()) {
            if ($row['count'] > 0) {
                $this->broadcastToChannel('contact_messages', [
                    'type' => 'new_message',
                    'count' => (int)$row['count'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                $this->broadcastToChannel('main_dashboard', [
                    'type' => 'new_message',
                    'count' => (int)$row['count'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Check for new reservations
        $reservationsResult = $conn->query("
            SELECT COUNT(*) as count 
            FROM reservations 
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
            AND status = 'pending'
        ");
        if ($reservationsResult && $row = $reservationsResult->fetch_assoc()) {
            if ($row['count'] > 0) {
                $this->broadcastToChannel('reservations', [
                    'type' => 'new_reservation',
                    'count' => (int)$row['count'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                $this->broadcastToChannel('main_dashboard', [
                    'type' => 'new_reservation',
                    'count' => (int)$row['count'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        $conn->close();
    }
    
    private function disconnectClient($client) {
        foreach ($client['channels'] as $channel) {
            $this->unsubscribeFromChannel($client, $channel);
        }
        
        socket_close($client['socket']);
        unset($this->clients[$client['id']]);
        
        echo "Client disconnected: {$client['id']}\n";
    }
    
    private function mask($text) {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } else {
            $header = pack('CCNN', $b1, 127, $length);
        }
        
        return $header . $text;
    }
    
    private function unmask($text) {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        
        $decoded = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $decoded .= $data[$i] ^ $masks[$i % 4];
        }
        
        return $decoded;
    }
}

// Start the server
$server = new WebSocketServer();
$server->run();
?>

