<?php

declare(strict_types=1);

function sendJsonRpc(array $payload, $stream): void
{
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Could not encode JSON-RPC payload.');
    }

    $body = "Content-Length: " . strlen($json) . "\r\n\r\n" . $json;
    fwrite($stream, $body);
    fflush($stream);
}

function readJsonRpc($stream): array
{
    $contentLength = null;

    while (true) {
        $line = fgets($stream);
        if ($line === false) {
            throw new RuntimeException('Could not read from MCP server stream.');
        }

        if ($line === "\r\n" || $line === "\n") {
            break;
        }

        if (preg_match('/^Content-Length:\s*(\d+)\s*\r?\n$/i', $line, $matches)) {
            $contentLength = (int) $matches[1];
        }
    }

    if ($contentLength === null) {
        throw new RuntimeException('Content-Length header is missing.');
    }

    $raw = '';
    while (strlen($raw) < $contentLength) {
        $chunk = fread($stream, $contentLength - strlen($raw));
        if ($chunk === false || $chunk === '') {
            throw new RuntimeException('Unexpected end of stream while reading JSON-RPC payload.');
        }
        $raw .= $chunk;
    }

    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        throw new RuntimeException('Invalid JSON payload received from the MCP server.');
    }

    return $payload;
}

$descriptor = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open('php ' . escapeshellarg(__DIR__ . '/server.php'), $descriptor, $pipes);
if (!is_resource($process)) {
    throw new RuntimeException('Could not start the MCP server process.');
}

stream_set_blocking($pipes[0], true);
stream_set_blocking($pipes[1], true);
stream_set_blocking($pipes[2], true);

sendJsonRpc([
    'jsonrpc' => '2.0',
    'id' => 1,
    'method' => 'initialize',
    'params' => [
        'protocolVersion' => '2025-06-18',
        'capabilities' => [],
        'clientInfo' => [
            'name' => 'basic-php-client',
            'version' => '0.1.0',
        ],
    ],
], $pipes[0]);

print_r(readJsonRpc($pipes[1]));
echo PHP_EOL;

sendJsonRpc([
    'jsonrpc' => '2.0',
    'method' => 'notifications/initialized',
], $pipes[0]);

sendJsonRpc([
    'jsonrpc' => '2.0',
    'id' => 2,
    'method' => 'tools/list',
    'params' => [],
], $pipes[0]);

print_r(readJsonRpc($pipes[1]));
echo PHP_EOL;

sendJsonRpc([
    'jsonrpc' => '2.0',
    'id' => 3,
    'method' => 'tools/call',
    'params' => [
        'name' => 'greet_user',
        'arguments' => [
            'name' => 'Alice',
        ],
    ],
], $pipes[0]);

print_r(readJsonRpc($pipes[1]));
echo PHP_EOL;

fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);
proc_terminate($process);
proc_close($process);
