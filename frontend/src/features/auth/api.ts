export interface LoginResponse {
  user: string
}

export interface LoginError {
  message: string
}

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8080/api'

export async function login(
  username: string,
  password: string
): Promise<LoginResponse> {
  const credentials = btoa(`${username}:${password}`)

  const response = await fetch(`${API_BASE_URL}/login`, {
    method: 'POST',
    headers: {
      'Authorization': `Basic ${credentials}`,
      'Content-Type': 'application/json',
    },
    credentials: 'include',
  })

  if (!response.ok) {
    const error = await response.json() as LoginError
    throw new Error(error.message || 'Login failed')
  }

  return response.json() as Promise<LoginResponse>
}
