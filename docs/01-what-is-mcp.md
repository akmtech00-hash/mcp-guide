# What is MCP?

## Purpose

The Model Context Protocol (MCP) exists to help AI applications connect to tools and data in a consistent, standardized way. Instead of building custom, one-off integrations for each system, applications can use MCP as a common protocol for discovery, authorization, and execution.

## Why it matters

Large language models often need access to:

- business APIs
- internal documentation
- databases
- support workflows
- application state

MCP gives those systems a standard interface.

## Core participants

- Host: the application or environment that launches or manages the client.
- Client: the component that connects to the server and handles request/response flow.
- Server: the component that exposes tools, resources, prompts, and business capabilities.
- Model: the reasoning component that consumes structured output and user context.

## MCP is not a replacement for existing APIs

MCP is a protocol layer on top of business logic. A Laravel application may still have REST endpoints, queues, and database services. The MCP server simply exposes selected capabilities in a standardized form.

This is often the safest architecture: keep existing business logic in Laravel and expose that logic via a thin MCP interface.

## Further reading

- `docs/00-getting-started.md`
- `resources/official-documentation.md`
