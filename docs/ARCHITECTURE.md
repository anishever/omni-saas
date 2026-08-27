# Omni SaaS — V1 Technical Architecture

## Product

AI-powered omnichannel marketing, automation, CRM, and customer conversation SaaS.

## Stack

- Frontend: React + TypeScript + Vite + Tailwind CSS
- Backend: Laravel + PHP
- Database: PostgreSQL
- Cache/queues: Redis
- Realtime: WebSockets
- Storage: S3-compatible object storage
- Vector search: PostgreSQL + pgvector

## Core principle

Channels are adapters around a common message engine. The application must not contain channel-specific business logic throughout the CRM, inbox, campaigns, or automation modules.

```text
React UI
   |
Laravel API
   |
Application services
   |
Message Engine ---- Automation Engine ---- AI Engine
   |
Channel Adapters
   +-- WhatsApp
   +-- Instagram
   +-- Messenger
   +-- SMS
   +-- Email
   +-- Web Chat
```

## Multi-tenancy

Every tenant-owned resource is scoped by `tenant_id`. Requests resolve the authenticated user, tenant, workspace, and resource before access is granted. Cross-tenant access must be impossible through both application policies and query scopes.

## V1 delivery order

1. Authentication, tenants, workspaces, RBAC
2. Contacts, tags, custom fields, leads
3. WhatsApp Cloud API and webhooks
4. Unified inbox and realtime messaging
5. AI agents and knowledge base
6. Campaigns and segmentation
7. Automation builder and execution engine
8. Billing, usage, analytics, and Super Admin

## Security baseline

- Encrypt channel credentials and provider secrets.
- Never commit `.env` or API keys.
- Verify webhook signatures where supported.
- Apply API rate limits.
- Validate all inbound payloads.
- Use authorization policies for every tenant resource.
- Maintain audit logs for sensitive actions.
- Store media in controlled object storage.
