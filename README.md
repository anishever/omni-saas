# Omni SaaS

AI-powered omnichannel marketing, automation, CRM, and customer conversation platform.

## V1 Stack

- Laravel 12 / PHP 8.3+
- MySQL 8
- React + TypeScript + Vite
- Tailwind CSS
- Redis queues/cache
- Laravel Reverb for realtime
- Laravel Sanctum for API authentication
- S3-compatible object storage
- Qdrant for AI/RAG vector search

## Repository Structure

```text
omni-saas/
├── backend/      # Laravel API
├── frontend/     # React application
├── docs/         # Product and technical specifications
├── docker/       # Local infrastructure configuration
└── docker-compose.yml
```

## Development Roadmap

1. Foundation: auth, tenants, workspaces, RBAC
2. CRM: contacts, tags, custom fields, leads
3. WhatsApp Cloud API and webhooks
4. Unified inbox and realtime messaging
5. AI agents and knowledge base
6. Campaigns and segmentation
7. Automation engine
8. Billing, analytics, and Super Admin

## Security

Never commit `.env`, provider credentials, access tokens, or API keys. Use `.env.example` as the local configuration template.

## Status

V1 foundation is under active development.
