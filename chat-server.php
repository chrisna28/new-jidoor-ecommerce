<?php
/**
 * JiDoor Chat Server (Revisi #7)
 * Daemon WebSocket Ratchet — jalankan: php chat-server.php
 * Klien terhubung ke ws://localhost:8080/chat
 */

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require __DIR__ . '/vendor/autoload.php';

// Ratchet 0.4 memicu deprecation notice di PHP 8.4 — aman diabaikan
error_reporting(E_ALL & ~E_DEPRECATED);

// Samakan dengan application/config/database.php
define('CHAT_DB_DSN',  'mysql:host=127.0.0.1;port=8889;dbname=ecommerce_db;charset=utf8mb4');
define('CHAT_DB_USER', 'root');
define('CHAT_DB_PASS', 'root');

// Library CI memeriksa konstanta ini
define('BASEPATH', __DIR__ . '/system/');

require_once __DIR__ . '/application/libraries/Chat_Token.php';

class ChatServer implements MessageComponentInterface {
    protected $clients;   // SplObjectStorage: conn => meta
    protected $users;     // user_id => conn
    protected $db;        // PDO

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users   = [];
        $this->db = new PDO(CHAT_DB_DSN, CHAT_DB_USER, CHAT_DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "[" . date('H:i:s') . "] Chat server siap di ws://localhost:8080/chat\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!is_array($data) || !isset($data['type'])) { return; }

        // Pesan pertama = autentikasi via token HMAC
        if ($data['type'] === 'auth') {
            $check = Chat_Token::check($data['token'] ?? '');
            if ($check === FALSE || $check[0] !== (int)$data['user_id'] || $check[1] !== $data['role']) {
                $from->close();
                return;
            }
            $from->meta = ['user_id' => (int)$data['user_id'], 'role' => $data['role']];
            $this->users[$from->meta['user_id']] = $from;
            echo "[" . date('H:i:s') . "] Auth OK: {$data['role']} #{$data['user_id']}\n";
            return;
        }

        // Pesan chat: validasi, simpan DB, route ke penerima jika online
        if ($data['type'] === 'message') {
            if (!isset($from->meta)) { return; } // belum auth
            $text = trim((string)($data['text'] ?? ''));
            if ($text === '' || mb_strlen($text) > 1000) { return; }

            if ($from->meta['role'] === 'user') {
                $convId = $this->getOrCreateConversationId($from->meta['user_id']);
            } else {
                // Admin mengirim ke percakapan tertentu (dikirim klien)
                $convId = (int)($data['conversation_id'] ?? 0);
            }
            if (!$convId) { return; }

            // Konteks produk ala Shopee (opsional): simpan hanya jika produknya eksis
            $productId = (int)($data['product_id'] ?? 0);
            $product   = $productId ? $this->getProduct($productId) : null;

            $stmt = $this->db->prepare(
                'INSERT INTO messages (conversation_id, sender_id, sender_role, message, product_id)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$convId, $from->meta['user_id'], $from->meta['role'], $text, $product['id'] ?? null]);

            // Update counter + waktu percakapan
            $counter = ($from->meta['role'] === 'admin') ? 'unread_user' : 'unread_admin';
            $this->db->prepare(
                "UPDATE conversations SET {$counter} = {$counter} + 1,
                 last_message_at = NOW() WHERE id = ?"
            )->execute([$convId]);

            $payload = json_encode([
                'type'            => 'message',
                'from'            => $from->meta['user_id'],
                'role'            => $from->meta['role'],
                'conversation_id' => $convId,
                'text'            => $text,
                'sent_at'         => date('H:i'),
                'date'            => date('Y-m-d'),
                'product'         => $product,
            ]);

            // Route ke penerima: admin <-> pemilik percakapan
            $targetId = ($from->meta['role'] === 'admin')
                ? $this->getConversationOwnerId($convId)
                : $this->getOnlineAdminId();

            if ($targetId && isset($this->users[$targetId])) {
                $this->users[$targetId]->send($payload);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->meta)) {
            unset($this->users[$conn->meta['user_id']]);
        }
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    /** Ambil id percakapan user, buat baru jika belum ada */
    private function getOrCreateConversationId(int $userId): int {
        $stmt = $this->db->prepare('SELECT id FROM conversations WHERE user_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) { return (int)$row['id']; }
        $this->db->prepare('INSERT INTO conversations (user_id) VALUES (?)')->execute([$userId]);
        return (int)$this->db->lastInsertId();
    }

    /** Data ringkas produk untuk kartu konteks chat (null jika tidak eksis) */
    private function getProduct(int $productId): ?array {
        $stmt = $this->db->prepare('SELECT id, name, slug, price, image FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) { return null; }
        return [
            'id'    => (int)$row['id'],
            'name'  => $row['name'],
            'slug'  => $row['slug'],
            'price' => (float)$row['price'],
            'image' => $row['image'],
        ];
    }

    private function getConversationOwnerId(int $convId): ?int {
        $stmt = $this->db->prepare('SELECT user_id FROM conversations WHERE id = ?');
        $stmt->execute([$convId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['user_id'] : null;
    }

    private function getOnlineAdminId(): ?int {
        foreach ($this->users as $uid => $conn) {
            if ($conn->meta['role'] === 'admin') { return $uid; }
        }
        return null;
    }
}

$app = new Ratchet\App('localhost', 8080);
$app->route('/chat', new ChatServer, ['*']);
$app->run();
