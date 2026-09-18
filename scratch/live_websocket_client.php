<?php

// Live WebSocket Listener for Pusher events on Remote Server

$appKey = '093079a2a87c59e12c7a';
$cluster = 'ap2';
$companyId = 6;
$authToken = '124|5YD0DFGtq8WnZV5wKoYfr76FwPJf4INM1IwZYhrz2275f664';
$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';

echo "=== REMOTE SERVER LIVE WEBSOCKET EVENT CAPTURE ===\n";
echo "Server URL: $baseUrl\n";
echo "Company ID: $companyId\n";
echo "Pusher Key: $appKey (Cluster: $cluster)\n\n";

$host = "ws-$cluster.pusher.com";
$port = 443;
$path = "/app/$appKey?protocol=7&client=js&version=7.0.3&flash=false";

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$socket = stream_socket_client("ssl://$host:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);

if (!$socket) {
    echo "Connection failed: $errstr ($errno)\n";
    exit(1);
}

// Handshake
$key = base64_encode(random_bytes(16));
$headers = "GET $path HTTP/1.1\r\n" .
           "Host: $host\r\n" .
           "Upgrade: websocket\r\n" .
           "Connection: Upgrade\r\n" .
           "Sec-WebSocket-Key: $key\r\n" .
           "Sec-WebSocket-Version: 13\r\n\r\n";

fwrite($socket, $headers);
$response = fread($socket, 2048);

if (!str_contains($response, '101 Switching Protocols')) {
    echo "Handshake Failed:\n$response\n";
    exit(1);
}

function encodeFrame($payload, $opcode = 0x1) {
    $b1 = 0x80 | ($opcode & 0x0f);
    $length = strlen($payload);
    if ($length <= 125) {
        $header = pack('CC', $b1, $length | 0x80);
    } else if ($length > 125 && $length < 65536) {
        $header = pack('CCn', $b1, 126 | 0x80, $length);
    } else {
        $header = pack('CCN', $b1, 127 | 0x80, $length);
    }
    $mask = random_bytes(4);
    $maskedPayload = '';
    for ($i = 0; $i < $length; $i++) {
        $maskedPayload .= $payload[$i] ^ $mask[$i % 4];
    }
    return $header . $mask . $maskedPayload;
}

function readFrames($socket) {
    $header = fread($socket, 2);
    if (strlen($header) < 2) return [];

    $opcode = ord($header[0]) & 0x0f;
    $isMasked = (ord($header[1]) & 0x80) !== 0;
    $length = ord($header[1]) & 0x7f;

    if ($length === 126) {
        $ext = fread($socket, 2);
        $length = unpack('n', $ext)[1];
    } elseif ($length === 127) {
        $ext = fread($socket, 8);
        $length = unpack('J', $ext)[1];
    }

    $masks = '';
    if ($isMasked) {
        $masks = fread($socket, 4);
    }

    $payload = '';
    while (strlen($payload) < $length) {
        $chunk = fread($socket, $length - strlen($payload));
        if ($chunk === false || $chunk === '') break;
        $payload .= $chunk;
    }

    if ($isMasked && $masks !== '') {
        $unmasked = '';
        for ($i = 0; $i < strlen($payload); $i++) {
            $unmasked .= $payload[$i] ^ $masks[$i % 4];
        }
        $payload = $unmasked;
    }

    return [
        'opcode' => $opcode,
        'payload' => $payload
    ];
}

$firstFrame = readFrames($socket);
$firstMessage = $firstFrame['payload'] ?? '';
$connectionData = json_decode($firstMessage, true);
$eventData = json_decode($connectionData['data'] ?? '{}', true);
$socketId = $eventData['socket_id'] ?? null;

if (!$socketId) {
    echo "Failed to get socket_id from handshake: $firstMessage\n";
    exit(1);
}

echo "Assigned Socket ID: $socketId\n\n";

require __DIR__ . '/../vendor/autoload.php';
$client = new \GuzzleHttp\Client(['base_uri' => $baseUrl, 'verify' => false]);

$channels = [
    "private-company.{$companyId}.chats",
];

foreach ($channels as $chan) {
    echo "Authenticating '$chan' with Bearer token...\n";
    $res = $client->post('/api/v1/broadcasting/auth', [
        'headers' => [
            'Authorization' => "Bearer $authToken",
            'Accept' => 'application/json',
        ],
        'form_params' => [
            'socket_id' => $socketId,
            'channel_name' => $chan,
        ]
    ]);
    $authJson = json_decode((string)$res->getBody(), true);
    $authSignature = $authJson['auth'];

    $subMsg = json_encode([
        'event' => 'pusher:subscribe',
        'data' => [
            'auth' => $authSignature,
            'channel' => $chan,
        ]
    ]);
    fwrite($socket, encodeFrame($subMsg));
    echo "Subscribed to '$chan' successfully!\n";
}

echo "\n=============================================================\n";
echo "LIVE CAPTURE ACTIVE (10 MINUTE WINDOW).\n";
echo "READY FOR INBOUND WHATSAPP MESSAGE TO +91 9818923734!\n";
echo "=============================================================\n\n";

$startTime = time();
$timeoutSeconds = 600; // 10 minutes

stream_set_timeout($socket, 1);

while ((time() - $startTime) < $timeoutSeconds) {
    $read = [$socket];
    $write = null;
    $except = null;
    if (stream_select($read, $write, $except, 1) > 0) {
        $frame = readFrames($socket);
        if (empty($frame)) continue;

        $opcode = $frame['opcode'];
        $payload = $frame['payload'];

        if ($opcode === 0x9) { // Ping frame
            fwrite($socket, encodeFrame($payload, 0xA)); // Send Pong
            continue;
        }

        if ($opcode === 0x1) { // Text frame
            $parsed = json_decode($payload, true);
            $eventName = $parsed['event'] ?? 'unknown';
            $channel = $parsed['channel'] ?? 'N/A';
            
            // Auto handle Pusher ping/pong
            if ($eventName === 'pusher:ping') {
                fwrite($socket, encodeFrame(json_encode(['event' => 'pusher:pong', 'data' => new \stdClass()])));
                continue;
            }

            if ($eventName === 'pusher_internal:subscription_succeeded') {
                echo "[" . date('H:i:s') . "] ✅ CHANNEL SUBSCRIPTION CONFIRMED FOR: $channel\n";
                continue;
            }

            $payloadData = isset($parsed['data']) && is_string($parsed['data']) 
                ? json_decode($parsed['data'], true) 
                : ($parsed['data'] ?? []);

            echo "\n[" . date('H:i:s') . "] ⚡ REAL-TIME BROADCAST EVENT RECEIVED OVER WEBSOCKET!\n";
            echo "Channel: $channel\n";
            echo "Event Name: $eventName\n";
            echo "Payload:\n" . json_encode($payloadData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            echo "-------------------------------------------------------------\n";
        }
    }
}

echo "\nLive capture ended after 10 minutes.\n";
