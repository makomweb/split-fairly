# Split Fairly

![app-ci-workflow](https://github.com/makomweb/split-fairly/actions/workflows/app-ci.yaml/badge.svg)
[![codecov](https://codecov.io/gh/makomweb/split-fairly/graph/badge.svg?token=O6WQ8USL6T)](https://codecov.io/gh/makomweb/split-fairly)

A modern web application for transparently splitting expenses and settling debts among groups. Built with **event sourcing** to maintain a complete audit trail of all financial transactions.

Track shared costs, calculate who owes whom, and manage group finances effortlessly. Perfect for roommates, travel groups, and collaborative projects.

Follows DDD principles for separting the application into generic, core, and supporting domains - enforced by [deptrac](https://github.com/deptrac/deptrac).

## Architecture diagram

```
                        Browser / Client
                               │
              ┌────────────────┼─────────────────┐
              │ :8000          │ :8080           │ :5173 (dev)
              ▼                ▼                 ▼
       ┌────────────┐  ┌──────────────┐  ┌───────────────┐
       │  dashboard │  │  web (Nginx) │  │  npm-dev      │
       │  (Homer)   │  │              │  │  (Vite/React/ │
       └────────────┘  └──────┬───────┘  │   TypeScript) │
                              │ FastCGI  └───────────────┘
                              │ :9000
                              ▼
                    ┌──────────────────┐     async       ┌──────────────┐
                    │  app (PHP-FPM)   │────messages────▶│  worker      │
                    │  Symfony         │                 │  (Messenger) │
                    └──────────────────┘                 └──────────────┘
                              │                                 │
                              └────────────────┬────────────────┘
                                               │ SQL
                                               ▼
                                      ┌─────────────────┐
                                      │  db (MySQL)     │
                                      └─────────────────┘

  ───────────────────────── Backend Layers ─────────────────────────

  ┌─────────────────────────────────────────────────────────────────┐
  │  Supporting  │ Controllers · Auth · Repositories · Async        │
  │              │ Normalizers · Instrumentation · EventListeners   │
  ├──────────────┴──────────────────────────────────────────────────┤
  │  Core        │ ExpenseTracker · Calculator                      │
  │  (Domain)    │ Event Sourcing · Expenses · Compensation         │
  ├──────────────┴──────────────────────────────────────────────────┤
  │  Generic     │ Symfony · Doctrine · Twig · DomPDF · Monolog     |
  |              │ PhpParser · phpDocumenter · OpenAPI              |
  └─────────────────────────────────────────────────────────────────┘
         Supporting depends on Core & Generic · Core has no deps
```

## Prerequisites

- [Make](https://www.gnu.org/software/make/)
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

## Getting Started

```bash
make build    # Build image, start services, and open in browser
make init     # Initialize database and load fixtures
```

Visit `http://localhost:8000` in your browser.

## Screenshots

<table>
  <tr>
    <td><img src="./images/login.png" alt="Login view" width="300px"></td>
    <td><img src="./images/track.png" alt="Track view" width="300px"></td>
    <td><img src="./images/calculate.png" alt="Calculate view" width="300px"></td>
  </tr>
</table>
