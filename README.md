# Omni SaaS

AI-powered omnichannel marketing, automation, CRM, and customer conversation platform.

## V1 Foundation

- Laravel API backend
- React + TypeScript frontend
- PostgreSQL
- Redis queues/cache
- Multi-tenant architecture
- Role-based access control
- WhatsApp-first channel engine

## Repository Structure

```text
omni-saas/
├── backend/      # Laravel API
├── frontend/     # React application
├── docs/         # Product and technical specifications
├── docker/       # Local infrastructure configuration
└── docker-compose.yml
```

## Status

V1 foundation is being built incrementally. Secrets and provider credentials must never be committed; use `.env` locally and `.env.example` as the template.
