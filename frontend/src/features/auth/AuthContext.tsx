import { createContext, useContext, useState, useEffect, ReactNode } from 'react'
import { logout as apiLogout } from './api'
import { getApiBaseUrl, getApiUrl } from '../../api/config'

interface User {
  email: string
}

interface AuthContextType {
  user: User | null
  logout: () => Promise<void>
  isLoading: boolean
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    // Check server session on app load
    const checkAuth = async () => {
      try {
        const response = await fetch(getApiUrl('/api/me'), {
          method: 'GET',
          credentials: 'include', // Include cookies for session auth
          headers: {
            'Content-Type': 'application/json',
          },
        })
        
        if (response.ok) {
          const data = await response.json()
          setUser({ email: data.user })
        } else {
          setUser(null)
          // Redirect to login if not authenticated
          window.location.href = `${getApiBaseUrl()}/login`
        }
      } catch (error) {
        console.error('Failed to check authentication:', error)
        setUser(null)
      } finally {
        setIsLoading(false)
      }
    }

    checkAuth()
  }, [])

  const logout = async () => {
    try {
      // Call API logout to destroy session on server
      await apiLogout()
    } catch (error) {
      console.error('Error during logout:', error)
    } finally {
      // Redirect to login page
      window.location.href = `${getApiBaseUrl()}/login`
    }
  }

  return (
    <AuthContext.Provider
      value={{
        user,
        logout,
        isLoading,
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}
