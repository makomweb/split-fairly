import './index.css'
import { useState } from 'react'
import { AuthProvider, useAuth } from './features/auth/AuthContext'
import { Login } from './features/auth/Login'
import { TrackExpense } from './features/expense/TrackExpense'
import { Calculation } from './features/calculation/Calculation'
import { Button } from './components/ui/button'

type View = 'track' | 'calculate'

function AppContent() {
  const { user, logout } = useAuth()
  const [view, setView] = useState<View>('track')

  if (!user) {
    return <Login />
  }

  return (
    <div>
      <nav className="bg-white border-b">
        <div className="max-w-4xl mx-auto px-4 py-3 flex justify-between items-center">
          <div className="flex gap-2">
            <Button
              onClick={() => setView('track')}
              variant={view === 'track' ? 'default' : 'ghost'}
              size="sm"
            >
              Track Expense
            </Button>
            <Button
              onClick={() => setView('calculate')}
              variant={view === 'calculate' ? 'default' : 'ghost'}
              size="sm"
            >
              View Calculation
            </Button>
          </div>
          <Button onClick={logout} variant="outline" size="sm">
            Logout
          </Button>
        </div>
      </nav>
      
      {view === 'track' ? <TrackExpense /> : <Calculation />}
    </div>
  )
}

export default function App() {
  return (
    <AuthProvider>
      <AppContent />
    </AuthProvider>
  )
}
