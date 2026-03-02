/**
 * Get the API base URL based on the current environment.
 *
 * In development (Vite on localhost:5173):
 *   - Returns http://localhost:8080 (backend on different port)
 *
 * In production (deployed app):
 *   - Returns the same origin as the frontend
 *   - Works when frontend and backend are served from same domain/port
 */
export function getApiBaseUrl(): string {
  const currentOrigin = window.location.origin

  // Development: Frontend on localhost:5173, backend on localhost:8080
  if (currentOrigin.includes('5173')) {
    return 'http://localhost:8080'
  }

  // Production/Docker: Frontend and backend share origin
  return currentOrigin
}

/**
 * Get the full API endpoint URL
 */
export function getApiUrl(path: string): string {
  const baseUrl = getApiBaseUrl()
  // Ensure path starts with /
  const normalizedPath = path.startsWith('/') ? path : `/${path}`
  return `${baseUrl}${normalizedPath}`
}
