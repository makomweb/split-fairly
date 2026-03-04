import './index.css'
import { useState, useRef } from 'react'
import { AuthProvider, useAuth } from './features/auth/AuthContext'
import { TrackExpense } from './features/expense/TrackExpense'
import { Calculation } from './features/calculation/Calculation'
import { Button } from './components/ui/button'
import { HeartHandshake, Loader, Plus } from 'lucide-react'

function AppContent() {
  const { user, logout, isLoading } = useAuth()
  const [currentTab, setCurrentTab] = useState('track')
  const [showTrackForm, setShowTrackForm] = useState(true)
  const [isFormValid, setIsFormValid] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [hasUserSelected, setHasUserSelected] = useState(false)
  const trackFormRef = useRef<{ submit: () => Promise<void> }>(null)

  if (isLoading) {
    return (
      <div className="min-h-svh flex items-center justify-center">
        <Loader className="h-8 w-8 animate-spin text-blue-600" />
      </div>
    )
  }

  // If no user, AuthContext will have already redirected to /login
  if (!user) {
    return null
  }

  return (
    <div className="min-h-svh flex flex-col pb-safe">
      {/* Sticky header */}
      <header className="sticky top-0 z-20 bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md">
        <div className="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
          <div className="flex items-center gap-2">
            <HeartHandshake className="h-6 w-6 text-white" />
            <h1 className="text-xl font-bold text-white">Split Fairly</h1>
          </div>
          <div className="flex items-center gap-4">
            <div className="text-white text-sm">
              {user.email.length > 16 ? user.email.substring(0, 16) + '...' : user.email}
            </div>
            <Button 
              onClick={() => logout()}
              variant="secondary" 
              size="sm"
            >
              Logout
            </Button>
          </div>
        </div>
      </header>
      
      {/* Main content */}
      <div className="flex-1">
        {currentTab === 'calculate' && (
          <Calculation onUserSelected={setHasUserSelected} />
        )}
        {currentTab === 'track' && !showTrackForm && (
          <div className="w-full p-4 md:p-6 flex items-center justify-center min-h-full">
            <div className="text-center text-muted-foreground">
              <p>Use the "+ Track" button to add a new expense</p>
            </div>
          </div>
        )}
        {showTrackForm && (
          <TrackExpense 
            ref={trackFormRef}
            onComplete={() => setShowTrackForm(false)}
            onValidityChange={setIsFormValid}
            onLoadingChange={setIsSubmitting}
          />
        )}
      </div>

      {/* Bottom Navigation */}
      <div className="fixed bottom-0 left-0 right-0 z-20 border-t bg-white shadow-lg">
        <div className="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between gap-2">
          <button
            onClick={() => {
              if (showTrackForm && isFormValid && !isSubmitting) {
                // If tracking and form is valid, submit it
                trackFormRef.current?.submit()
              } else {
                // Otherwise navigate to tracking view
                setShowTrackForm(true)
                setCurrentTab('track')
                // Focus on what input after state updates
                setTimeout(() => {
                  trackFormRef.current?.focus?.()
                }, 0)
              }
            }}
            className={`flex-1 h-12 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 text-sm ${
              showTrackForm
                ? isFormValid
                  ? 'bg-blue-600 text-white hover:bg-blue-700 cursor-pointer'
                  : 'bg-blue-200 text-blue-600 cursor-default opacity-75'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
            }`}
          >
            {showTrackForm ? (isSubmitting ? 'Saving...' : '➕ Track') : '➕ Track'}
          </button>
          
          <button
            onClick={() => {
              setCurrentTab('calculate')
              setShowTrackForm(false)
            }}
            className={`flex-1 h-12 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 text-sm ${
              currentTab === 'calculate'
                ? hasUserSelected
                  ? 'bg-blue-600 text-white'
                  : 'bg-blue-200 text-blue-600 opacity-75'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
            }`}
          >
            📊 Calculate
          </button>
        </div>
      </div>
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


