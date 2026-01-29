import './index.css'
import { AuthProvider, useAuth } from './features/auth/AuthContext'
import { Login } from './features/auth/Login'
import { TrackExpense } from './features/expense/TrackExpense'
import { Calculation } from './features/calculation/Calculation'
import { Button } from './components/ui/button'
import { Tabs, TabsContent, TabsList, TabsTrigger } from './components/ui/tabs'

function AppContent() {
  const { user, logout } = useAuth()

  if (!user) {
    return <Login />
  }

  return (
    <div className="min-h-svh flex flex-col">
      {/* Sticky header */}
      <header className="sticky top-0 z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b">
        <div className="max-w-4xl mx-auto px-4 py-3 flex justify-between items-center">
          <h1 className="text-lg font-semibold">Split Fairly</h1>
          <Button onClick={logout} variant="outline" size="sm">
            Logout
          </Button>
        </div>
      </header>
      
      {/* Main content with tabs */}
      <Tabs defaultValue="track" className="flex-1 flex flex-col">
        <div className="border-b">
          <div className="max-w-4xl mx-auto px-4">
            <TabsList className="w-full h-12 grid grid-cols-2 bg-transparent p-0">
              <TabsTrigger 
                value="track" 
                className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none"
              >
                💰 Track
              </TabsTrigger>
              <TabsTrigger 
                value="calculate"
                className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none"
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
