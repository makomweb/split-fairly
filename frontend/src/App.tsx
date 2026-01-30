import './index.css'
import { AuthProvider, useAuth } from './features/auth/AuthContext'
import { Login } from './features/auth/Login'
import { TrackExpense } from './features/expense/TrackExpense'
import { Calculation } from './features/calculation/Calculation'
import { Button } from './components/ui/button'
import { Tabs, TabsContent, TabsList, TabsTrigger } from './components/ui/tabs'
import { HeartHandshake } from 'lucide-react'

function AppContent() {
  const { user, logout } = useAuth()

  if (!user) {
    return <Login />
  }

  return (
    <div className="min-h-svh flex flex-col">
      {/* Sticky header */}
      <header className="sticky top-0 z-10 bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md">
        <div className="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
          <div className="flex items-center gap-2">
            <HeartHandshake className="h-6 w-6 text-white" />
            <h1 className="text-xl font-bold text-white">Split Fairly</h1>
          </div>
          <Button onClick={logout} variant="secondary" size="sm">
            Logout
          </Button>
        </div>
      </header>
      
      {/* Main content with tabs */}
      <Tabs defaultValue="track" className="flex-1 flex flex-col">
        <div className="border-b bg-gradient-to-r from-slate-100 to-slate-200">
          <div className="max-w-4xl mx-auto px-4">
            <TabsList className="w-full h-14 grid grid-cols-2 bg-transparent p-0">
              <TabsTrigger 
                value="track" 
                className="rounded-none border-b-4 border-transparent data-[state=active]:border-blue-600 data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-none bg-slate-200 text-slate-600 hover:bg-slate-300 font-semibold transition-all"
              >
                🛍️ Track
              </TabsTrigger>
              <TabsTrigger 
                value="calculate"
                className="rounded-none border-b-4 border-transparent data-[state=active]:border-blue-600 data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-none bg-slate-200 text-slate-600 hover:bg-slate-300 font-semibold transition-all"
              >
                📊 Calculate
              </TabsTrigger>
            </TabsList>
          </div>
        </div>

        <TabsContent value="track" className="flex-1 mt-0">
          <TrackExpense />
        </TabsContent>
        
        <TabsContent value="calculate" className="flex-1 mt-0">
          <Calculation />
        </TabsContent>
      </Tabs>
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
