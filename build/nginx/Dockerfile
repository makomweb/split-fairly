FROM node:25.2-alpine AS frontend

WORKDIR /frontend

COPY frontend .

RUN npm install && npm run build

# The vite config outputs to ../backend/public/build from /frontend context
# That path resolves to /backend/public/build, copy it to /dist
RUN cp -r /backend/public/build /dist

# -----------------------------------
# nginx-Stage (serve frontend + proxy API)
# -----------------------------------
FROM nginx:stable-alpine3.23

# Create public directory structure
RUN mkdir -p /var/www/project/public/build && \
    chmod -R 755 /var/www/project/public

# Copy built frontend assets
COPY --from=frontend /dist /var/www/project/public/build

# Copy EasyAdmin bundles from backend source
COPY backend/public/bundles /var/www/project/public/bundles

# Copy nginx configuration
COPY build/nginx/default.conf /etc/nginx/conf.d/default.conf
