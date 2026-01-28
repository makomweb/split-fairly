import './index.css'
import { AuthProvider, useAuth } from './features/auth/AuthContext'
import { Login } from './features/auth/Login'
import { TrackExpense } from './features/expense/TrackExpense'

function AppContent() {
  const { user } = useAuth()

  return user ? <TrackExpense /> : <Login />
}

export default function App() {
  return (
    <AuthProvider>
      <AppContent />
    </AuthProvider>
  )
}
