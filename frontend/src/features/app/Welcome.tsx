import { useAuth } from '@/features/auth/AuthContext'
import { Button } from '@/components/ui/button'

export function Welcome() {
  const { user, logout } = useAuth()

  return (
    <div className="flex min-h-svh w-full flex-col items-center justify-center p-6 md:p-10">
      <div className="w-full max-w-md">
        <div className="rounded-lg border border-border bg-card p-8 shadow-sm">
          <div className="flex flex-col gap-6">
            <div className="flex flex-col gap-2 text-center">
              <h1 className="text-3xl font-bold">Welcome</h1>
              <p className="text-xl text-muted-foreground">
                {user?.email}
              </p>
            </div>
            
            <div className="flex flex-col gap-4 pt-4">
              <div className="text-center text-sm text-muted-foreground">
                This is your authenticated dashboard. More features coming soon!
              </div>
              
              <Button 
                onClick={logout} 
                variant="outline"
                className="w-full"
              >
                Logout
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
