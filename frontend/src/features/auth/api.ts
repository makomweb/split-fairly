import { getApiUrl } from '../../api/config'

// Logout API function - handles destroying session on server
export async function logout(): Promise<void> {
  try {
    const response = await fetch(getApiUrl('/api/logout'), {
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
