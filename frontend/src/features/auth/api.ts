// Logout API function - handles destroying session on server
export async function logout(): Promise<void> {
  // Get the API base URL - use backend URL if frontend is on different origin
  const apiUrl = getApiBaseUrl()

  try {
    const response = await fetch(`${apiUrl}/api/logout`, {
      method: 'POST',
      credentials: 'include',
    })

    if (!response.ok) {
      console.error('Logout failed:', response.status)
    }
  } catch (error) {
    console.error('Failed to logout:', error)
  }
}

// Get the API base URL - use backend URL if frontend is on different origin
function getApiBaseUrl(): string {
  const currentOrigin = window.location.origin
  // If we're on localhost:5173 (Vite dev), point to backend at 8080
  if (currentOrigin.includes('5173')) {
    return 'http://localhost:8080'
  }
  // Otherwise use current origin
  return currentOrigin
}




