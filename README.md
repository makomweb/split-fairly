# Split Fairly

A modern web application for transparently splitting expenses and settling debts among groups. Built with **event sourcing** to maintain a complete audit trail of all financial transactions.

Track shared costs, calculate who owes whom, and manage group finances effortlessly. Perfect for roommates, travel groups, and collaborative projects.

Follows DDD principles for separting the application into generic, core, and supporting domains - enforced by [deptrac](https://github.com/deptrac/deptrac).

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

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
  <div style="flex: 1; min-width: 300px;">
    <img src="./images/login.png" alt="login-screenshot" style="width: 100%;">
  </div>
  <div style="flex: 1; min-width: 300px;">
    <img src="./images/track.png" alt="track-screenshot" style="width: 100%;">
  </div>
  <div style="flex: 1; min-width: 300px;">
    <img src="./images/calculate.png" alt="calculate-screenshot" style="width: 100%;">
  </div>
</div>
