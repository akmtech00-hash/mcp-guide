# Getting started

This guide introduces the repository and the first working example.

## Learning objectives

- understand what MCP is
- recognize the difference between a host, client, and server
- see the core lifecycle of a request
- run a minimal MCP server in pure PHP

## Prerequisites

- PHP 8.2 or newer
- a terminal with a local shell
- basic knowledge of JSON and command-line tools

## What MCP is

The Model Context Protocol is a standard way for an AI application to connect to external tools, data sources, and structured context providers. Instead of hard-coding tool access into a model, the host or client can discover available capabilities and invoke them in a standard request lifecycle.

## First practical example

The repository starts with a minimal server that supports:

- `initialize`
- `tools/list`
- `tools/call`

The example uses the official MCP JSON-RPC message format over stdio.

## Commands

```bash
cd examples/basic-php-server
php client.php
```

## Key idea

MCP is not a replacement for REST APIs. Instead, it is a protocol layer that lets a model or host request capabilities from a server in a structured, discoverable way.

Next: read `01-what-is-mcp.md`.
