import './index.css'
import { AuthProvider, useAuth } from './features/auth/AuthContext'
import { Login } from './features/auth/Login'
import { Welcome } from './features/app/Welcome'

function AppContent() {
  const { user } = useAuth()

  return user ? <Welcome /> : <Login />
}

export default function App() {
  return (
    <AuthProvider>
      <AppContent />
    </AuthProvider>
  )
}
