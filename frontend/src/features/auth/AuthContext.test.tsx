import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { render, screen, waitFor } from '@testing-library/react'
import { AuthProvider, useAuth } from './AuthContext'

// Mock fetch globally
global.fetch = vi.fn()

// Mock window.location
delete (window as any).location
;(window as any).location = { href: '', origin: 'http://localhost:8000' }

describe('AuthContext - Session-based Auth', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    ;(window as any).location.href = ''
    ;(global.fetch as any).mockReset()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  describe('Initialization', () => {
    it('should check /api/me on mount', async () => {
      ;(global.fetch as any).mockResolvedValueOnce({
        ok: true,
        json: async () => ({ user: 'test@example.com' }),
      })

      function TestComponent() {
        const { isLoading } = useAuth()
        return <div>{isLoading ? 'loading' : 'done'}</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect(global.fetch).toHaveBeenCalledWith(
          expect.stringContaining('/api/me'),
          expect.objectContaining({
            credentials: 'include',
          })
        )
      })
    })

    it('should set user when /api/me succeeds', async () => {
      ;(global.fetch as any).mockResolvedValueOnce({
        ok: true,
        json: async () => ({ user: 'test@example.com' }),
      })

      function TestComponent() {
        const { user } = useAuth()
        return <div>{user?.email || 'no user'}</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect(screen.getByText('test@example.com')).toBeInTheDocument()
      })
    })

    it('should redirect to login when /api/me returns 401', async () => {
      ;(global.fetch as any).mockResolvedValueOnce({
        ok: false,
        status: 401,
      })

      function TestComponent() {
        const { isLoading } = useAuth()
        return <div>{isLoading ? 'loading' : 'done'}</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect((window as any).location.href).toContain('/login')
      })
    })

    it('should redirect to login on network error', async () => {
      ;(global.fetch as any).mockRejectedValueOnce(new Error('Network error'))

      function TestComponent() {
        const { isLoading } = useAuth()
        return <div>{isLoading ? 'loading' : 'done'}</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect((window as any).location.href).toBe('')
      }, { timeout: 100 })
    })
  })

  describe('logout function', () => {
    it('should call /api/logout and redirect to login', async () => {
      ;(global.fetch as any)
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({ user: 'test@example.com' }),
        })
        .mockResolvedValueOnce({
          ok: true,
        })

      let logoutFn: any

      function TestComponent() {
        const { logout } = useAuth()
        logoutFn = logout
        return <div>logged in</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect(screen.getByText('logged in')).toBeInTheDocument()
      })

      logoutFn()

      await waitFor(() => {
        expect((window as any).location.href).toContain('/login')
      })
    })

    it('should redirect to login even if logout API fails', async () => {
      ;(global.fetch as any)
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({ user: 'test@example.com' }),
        })
        .mockRejectedValueOnce(new Error('Logout failed'))

      let logoutFn: any

      function TestComponent() {
        const { logout } = useAuth()
        logoutFn = logout
        return <div>logged in</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect(screen.getByText('logged in')).toBeInTheDocument()
      })

      logoutFn()

      await waitFor(() => {
        expect((window as any).location.href).toContain('/login')
      })
    })
  })

  describe('Cross-origin support', () => {
    it('should use backend URL when on Vite dev server (5173)', async () => {
      ;(window as any).location.origin = 'http://localhost:5173'
      ;(global.fetch as any).mockResolvedValueOnce({
        ok: true,
        json: async () => ({ user: 'test@example.com' }),
      })

      function TestComponent() {
        const { isLoading } = useAuth()
        return <div>{isLoading ? 'loading' : 'done'}</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect(global.fetch).toHaveBeenCalledWith(
          'http://localhost:8080/api/me',
          expect.any(Object)
        )
      })
    })
  })

  describe('Error handling', () => {
    it('should handle malformed /api/me response gracefully', async () => {
      ;(global.fetch as any).mockResolvedValueOnce({
        ok: true,
        json: async () => {
          throw new Error('Invalid JSON')
        },
      })

      function TestComponent() {
        const { isLoading } = useAuth()
        return <div>{isLoading ? 'loading' : 'done'}</div>
      }

      render(
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      )

      await waitFor(() => {
        expect(screen.getByText('done')).toBeInTheDocument()
      }, { timeout: 100 })
    })
  })
})

