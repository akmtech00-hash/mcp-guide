# Basic PHP MCP Server

This example shows a minimal MCP server implemented in plain PHP using the official MCP JSON-RPC messaging model over stdio.

## Why plain PHP first?

The official PHP SDK space has moved quickly. To avoid concealing protocol behavior behind uncertain package APIs, this repository starts with a minimal protocol-level implementation that is easier to inspect, test, and reason about.

This server supports:

- initialize
- tools/list
- tools/call
- notifications/initialized

## Commands

```bash
cd examples/basic-php-server
php client.php
```

## Expected output

The client should print the initialization response, the tool list, and the tool call result in JSON.
