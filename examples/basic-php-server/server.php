<?php

declare(strict_types=1);

function readMcpMessage(): ?array
{
    $header = '';
    $contentLength = null;

    while (true) {
        $line = fgets(STDIN);
        if ($line === false) {
            return null;
        }

        if ($line === "\r\n" || $line === "\n") {
            break;
        }

        $header .= $line;
        if (preg_match('/^Content-Length:\s*(\d+)\s*\r?\n$/i', $line, $matches)) {
            $contentLength = (int) $matches[1];
        }
    }

    if ($contentLength === null) {
        return null;
    }

    $raw = '';
    while (strlen($raw) < $contentLength) {
        $chunk = fread(STDIN, $contentLength - strlen($raw));
        if ($chunk === false || $chunk === '') {
            return null;
        }
        $raw .= $chunk;
    }

    $payload = json_decode($raw, true);
    return is_array($payload) ? $payload : null;
}

function sendMcpMessage(array $payload): void
{
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Failed to encode MCP JSON payload.');
    }

    $data = "Content-Length: " . strlen($json) . "\r\n\r\n" . $json;
    fwrite(STDOUT, $data);
    fflush(STDOUT);
}

function handleToolCall(string $name, array $arguments): array
{
    if ($name === 'greet_user') {
        $nameArgument = $arguments['name'] ?? '';
        if ($nameArgument === '') {
            throw new InvalidArgumentException('The "name" argument is required.');
        }

        return [
            'content' => [[
                'type' => 'text',
                'text' => 'Hello, ' . $nameArgument . '!',
            ]],
            'structuredContent' => [
                'message' => 'Hello, ' . $nameArgument . '!',
                'name' => $nameArgument,
            ],
        ];
    }

    throw new InvalidArgumentException('Unknown tool: ' . $name);
}

$serverInfo = [
    'name' => 'mcp-guide-basic-server',
    'version' => '0.1.0',
    'protocolVersion' => '2025-06-18',
];

while (true) {
    $message = readMcpMessage();
    if ($message === null) {
        break;
    }

    $method = $message['method'] ?? null;
    $id = $message['id'] ?? null;

    if ($method === 'initialize') {
        sendMcpMessage([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'protocolVersion' => '2025-06-18',
                'capabilities' => [
                    'tools' => [
                        'listChanged' => false,
                    ],
                ],
                'serverInfo' => $serverInfo,
            ],
        ]);
        continue;
    }

    if ($method === 'notifications/initialized') {
        continue;
    }

    if ($method === 'tools/list') {
        sendMcpMessage([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'tools' => [[
                    'name' => 'greet_user',
                    'description' => 'Greet a user by name.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'The name of the person to greet.',
                            ],
                        ],
                        'required' => ['name'],
                    ],
                ]],
            ],
        ]);
        continue;
    }

    if ($method === 'tools/call') {
        try {
            $toolName = $message['params']['name'] ?? '';
            $arguments = $message['params']['arguments'] ?? [];
            if (!is_array($arguments)) {
                throw new InvalidArgumentException('The tool arguments must be an object.');
            }

            $result = handleToolCall($toolName, $arguments);

            sendMcpMessage([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'content' => $result['content'],
                    'structuredContent' => $result['structuredContent'],
                ],
            ]);
        } catch (Throwable $e) {
            sendMcpMessage([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32602,
                    'message' => $e->getMessage(),
                ],
            ]);
        }
        continue;
    }

    sendMcpMessage([
        'jsonrpc' => '2.0',
        'id' => $id,
        'error' => [
            'code' => -32601,
            'message' => 'Method not found: ' . (string) $method,
        ],
    ]);
}
