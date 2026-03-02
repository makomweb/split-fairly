# Split Fairly

![app-ci-workflow](https://github.com/makomweb/split-fairly/actions/workflows/app-ci.yaml/badge.svg)
[![codecov](https://codecov.io/gh/makomweb/split-fairly/graph/badge.svg?token=O6WQ8USL6T)](https://codecov.io/gh/makomweb/split-fairly)

A modern web application for transparently splitting expenses and settling debts among groups. Built with **event sourcing** to maintain a complete audit trail of all financial transactions.

Track shared costs, calculate who owes whom, and manage group finances effortlessly. Perfect for roommates, travel groups, and collaborative projects.

Follows DDD principles for separting the application into generic, core, and supporting domains - enforced by [deptrac](https://github.com/deptrac/deptrac).

## Kubernetes Deployment

This application is fully containerized and deployable to Kubernetes. Use the Helm chart to deploy:

```bash
# Deploy to Kubernetes (requires kind, minikube, or Docker Desktop with K8s enabled)
helm install app ./helm

# Access the application
kubectl port-forward svc/app-split-fairly-web 8080:80
# → http://localhost:8080

# Or via NodePort (if enabled)
# → http://localhost:30190
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

**Access:** `http://localhost:30190` (direct) or `http://localhost:8080` (port-forward)

### Getting Started with Kubernetes

```bash
# Prerequisites: Docker Desktop (or kind/minikube) with Kubernetes enabled

# Build production images
make prod

# Deploy to cluster
helm upgrade --install app ./helm

# Watch pods come up
kubectl get pods -w

# View application logs
kubectl logs deployment/app-split-fairly-app -f
kubectl logs deployment/app-split-fairly-worker -f

# Access the application
# - Direct: http://localhost:30190
# - Port-forward: kubectl port-forward svc/app-split-fairly-web 8080:80

# Login credentials (auto-loaded from fixtures)
# Email: admin@example.com
# Password: secret
```

## Local Development (Docker Compose)

For development without Kubernetes:

```bash
make start    # Build images, start services, and open in browser
make help     # Show all available targets
```

Visit `http://localhost:8000` in your browser.

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

### For Docker Compose (Local Development)
- [Make](https://www.gnu.org/software/make/)
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

### For Kubernetes Deployment
- [Make](https://www.gnu.org/software/make/)
- [Docker](https://www.docker.com/)
- [Kubernetes](https://kubernetes.io/) (kind, minikube, or Docker Desktop)
- [Helm 3](https://helm.sh/)

## Getting Started

### Quick Start (Docker Compose)

```bash
make start    # Build image, start services, and open in browser
make help     # to show all targets
```

Visit `http://localhost:8000` in your browser.

### Kubernetes Deployment

```bash
make prod                           # Build production images
helm install app ./helm             # Deploy to Kubernetes
kubectl port-forward svc/app-split-fairly-web 8080:80  # Access app
```

Visit `http://localhost:8080` (or `http://localhost:30190` directly if NodePort is enabled).

## Screenshots

<table>
  <tr>
    <td><img src="./images/login.png" alt="Login view" width="300px"></td>
    <td><img src="./images/track.png" alt="Track view" width="300px"></td>
    <td><img src="./images/calculate.png" alt="Calculate view" width="300px"></td>
  </tr>
</table>
