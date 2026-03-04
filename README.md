# Split Fairly

![app-ci-workflow](https://github.com/makomweb/split-fairly/actions/workflows/app-ci.yaml/badge.svg)
[![codecov](https://codecov.io/gh/makomweb/split-fairly/graph/badge.svg?token=O6WQ8USL6T)](https://codecov.io/gh/makomweb/split-fairly)

A modern web application for transparently splitting expenses and settling debts among groups. Built with **event sourcing** to maintain a complete audit trail of all financial transactions.

Follows DDD principles for separating the application into generic, core, and supporting domains - enforced by [deptrac](https://github.com/deptrac/deptrac).

## Screenshots

<table>
  <tr>
    <td><img src="./images/login.png" alt="Login view" width="300px"></td>
    <td><img src="./images/track.png" alt="Track view" width="300px"></td>
    <td><img src="./images/calculate.png" alt="Calculate view" width="300px"></td>
  </tr>
</table>

## Prerequisites

### For Docker Compose (Local Development)
- [Make](https://www.gnu.org/software/make/)
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

### For Kubernetes Deployment
- [Make](https://www.gnu.org/software/make/)
- [Docker](https://www.docker.com/)
- [Kubernetes](https://kubernetes.io/) (kind, minikube, or Docker Desktop)
- [Helm 3](https://helm.sh/)

## Local Development (Docker Compose)

```bash
make start    # Build images, start services, and open in browser
make help     # Show all available targets
```

Visit `http://localhost:8000` in your browser.

## Architecture

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

## Kubernetes Deployment

```bash
# Build production images
make prod

# Deploy to cluster
helm install app ./helm
# or:
helm upgrade --install app ./helm

# Watch pods come up
kubectl get pods -w

# View logs for all pods with the PHP label (app + worker)
kubectl logs -f -l technology=php

# Access the application via NodePort:
# Link: http://localhost:30190
# Or configure port forwarding via:
kubectl port-forward svc/app-split-fairly-web 8080:80
# Link: http://localhost:8080
```

### Kubernetes Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Kubernetes Cluster                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────┐  ┌──────────────────┐                 │
│  │   nginx (web)    │  │ app (PHP-FPM)    │                 │
│  │ Split-Fairly     │  │ Deployment       │                 │
│  │ NodePort:30190   │─→│ Pods × 1         │                 │
│  └──────────────────┘  └──────────────────┘                 │
│         │                       │                           │
│         │ Serves SPA            │ Processes requests        │
│         │ EasyAdmin             │ Event sourcing            │
│         │                       │ Session management        │
│         │                 ┌─────▼──────┐                    │
│         │                 │   worker   │                    │
│         │                 │ Pod × 1    │                    │
│         │                 │ Async jobs │                    │
│         │                 └────────────┘                    │
│         │                       │                           │
│         └───────────┬───────────┘                           │
│                     │                                       │
│              ┌──────▼──────┐                                │
│              │   MySQL     │                                │
│              │ StatefulSet │                                │
│              │ PVC Storage │                                │
│              └─────────────┘                                │
│                     △                                       │
│                     │ init Job                              │
│              ┌──────┴──────┐                                │
│              │ db-init     │                                │
│              │ (one-time)  │                                │
│              └─────────────┘                                │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Components:**
- **nginx (web)**: Serves React SPA frontend, EasyAdmin assets, proxies API to PHP
- **PHP-FPM (app)**: Symfony backend, handles business logic & API endpoints
- **Worker**: Processes async jobs via Messenger (background tasks)
- **MySQL**: Persistent data storage with PVC
- **db-init Job**: One-time database initialization (schema + fixtures)
