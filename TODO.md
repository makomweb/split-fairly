# TODO / missing features

- [x] add small Kubernetes section to the README.md containing a diagram about the used K8s 
- [x] fix CORS issue with backend when running the HELM app
- [x] don't hard code http://localhost:8080 in frontend
- [x] put frontend tests into the CI
- [x] check logoutput from app + worker and see if it can easily be aggregated
- [x] simplify Dockerfiles:
    - flow of building docker images via Makefile
    - reduce complexity and keep dev, prod, debug stages at a comprehensible state
    - use alpine images for production and keep regular images for development
- when lending money:
    - specify to whom
    - update UI so that it contains a selection dialog
    - add endpoint to list other users so that a user can easily choose the user he/she is lending money
- when calculating:
    - specify with whom you wanna split expenses?
        - 2 people / group of people
        - add required UI controls for this
    - consider lend money in the calculation
- admin panel:
    - show events from the events database + provide edit features for the admin
- check the worker if it can be optimized in terms of avoiding long running PHP tasks:
    - see: ../fullstack-demo/helm/app/templates/worker-deploy.yaml
        (is a folder next to the current project) for inspiration
        - this worker is a "one shot" worker which handles a single message before it is restarted to handle the next message
        - it has some advantages/disadvantages
        - analyze it and take it as inspiration
