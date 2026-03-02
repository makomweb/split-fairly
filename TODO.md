# TODO

- add small Kubernetes section to the README.md containing a diagram about the used K8s 
- fix CORS issue with backend when running the HELM app
- don't hard code http://localhost:8080 in frontend
- make HELM chart template more DRY
resources
- simplify Dockerfiles:
    - flow of building docker images via Makefile
    - reduce complexity and keep dev, prod, debug stages at a comprehensible state
    - use alpine images for production and keep regular images for development
- put frontend tests into the CI
- check logoutput from app + worker and see if it can easily be aggregated

## Missing features

- when lending money:
    - specify to whom
- when calculating
    - specify with whom you wanna split expenses?
        - 2 people / group of people
    - consider lend money in the calculation
- admin panel:
    - show events from the events database + provide edit features for the admin
- check the worker if it can be optimized in terms of avoiding long running PHP tasks

