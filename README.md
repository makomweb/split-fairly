# Split Fairly

![app-ci-workflow](https://github.com/makomweb/split-fairly/actions/workflows/app-ci.yaml/badge.svg)
[![codecov](https://codecov.io/gh/makomweb/split-fairly/graph/badge.svg?token=O6WQ8USL6T)](https://codecov.io/gh/makomweb/split-fairly)

## Goal

This is a full-stack, cloud-native web application for tracking and splitting expenses and settling debts among individuals and groups. It is built on PHP 8.4, Symfony 8, MySQL 8, and React 19, and uses **event sourcing** to maintain a complete audit trail of all financial transactions.

It follows DDD principles to separate the application into generic, core, and supporting domains — enforced by [deptrac](https://github.com/deptrac/deptrac). This makes it easy to achieve full code coverage for the core domain.

It also showcases Kubernetes deployment using Helm.

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
┌────────────────────────────────────────────────────────────────────────┐
│                          Kubernetes Cluster                            │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐      │
│  │   nginx (web)    │  │ app (PHP-FPM)    │  │ worker           │      │
│  │ Deployment       │  │ Deployment       │  │ (Messenger)      │      │
│  │ NodePort:30190   │  │ Pod × 1          │  │ Pod × 1          │      │
│  │ Pods × 1         │─→│ FastCGI :9000    │  │ One-shot pattern │      │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘      │
│         │                       │                       │              │
│         │ Serves SPA            │ Business logic        │ Async tasks  │
│         │ EasyAdmin             │ API endpoints         │ from queue   │
│         │ Static assets         │ Session mgmt (DB)     │              │
│         │                       │ Event sourcing (DB)   │              │
│         │                       │                       │              │
│         └───────────┬───────────┴───────────┬───────────┘              │
│                     │                       │                          │
│              ┌──────▼───────────────────────▼──┐                       │
│              │   MySQL StatefulSet             │                       │
│              │   PVC Storage (8Gi)             │                       │
│              │   - Event store                 │                       │
│              │   - Sessions                    │                       │
│              │   - Application data            │                       │
│              └─────────────────────────────────┘                       │
│                     △                                                  │
│                     │ init Job                                         │
│              ┌──────┴──────┐                                           │
│              │ db-init     │                                           │
│              │ (one-time)  │                                           │
│              └─────────────┘                                           │
│                                                                        │
│  ┌──────────────────────────────────────────────────────────────┐      │
│  │ Grafana Alloy (k8s-monitoring)                               │      │
│  │ - Collects logs from all pods                                │      │
│  │ - Metrics collection & forwarding                            │      │
│  └──────────────────────────────────────────────────────────────┘      │
│                                                                        │
└────────────────────────────────────────────────────────────────────────┘
```

**Components:**
- **nginx (web)**: Serves React SPA frontend, static assets, EasyAdmin UI. Proxies API requests to PHP-FPM via FastCGI.
- **PHP-FPM (app)**: Symfony backend handling business logic, API endpoints, and session management. Stores sessions in MySQL.
- **Worker**: Processes async jobs via Messenger with one-shot pattern (processes single message per pod lifecycle, then restarts for fresh environment).
- **MySQL**: Persistent data storage with StatefulSet and PVC. Stores event sourcing audit trail, sessions, and application data.
- **db-init Job**: One-time database initialization (schema, fixtures, migrations).
- **Grafana Alloy**: Log collection and forwarding for observability across all cluster components.
