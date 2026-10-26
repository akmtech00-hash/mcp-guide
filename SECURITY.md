# Security Policy

## Supported versions

This repository is primarily a learning project and may evolve quickly. Security updates are handled as they are discovered.

## Reporting vulnerabilities

Please do not open a public issue for a security vulnerability. Instead, contact the maintainers privately and include:

- the affected component
- reproduction steps
- expected vs. actual behavior
- impact assessment

## Security expectations

- validate all tool input
- treat tool descriptions as untrusted
- avoid unrestricted shell access
- never expose secrets or production data
- avoid cross-tenant data leaks in application examples
