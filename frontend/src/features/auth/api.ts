interface LoginResponse {
  user: string
}

interface LoginError {
  message: string
}

export async function login(
  email: string,
  password: string,
  rememberMe: boolean = false
): Promise<LoginResponse> {
  const response = await fetch('http://localhost:8080/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include',
    body: JSON.stringify({
      email,
      password,
      _remember_me: rememberMe,
    }),
  })

  if (!response.ok) {
    const error = await response.json() as LoginError
    throw new Error(error.message || 'Login failed')
  }

  return response.json() as Promise<LoginResponse>
}
