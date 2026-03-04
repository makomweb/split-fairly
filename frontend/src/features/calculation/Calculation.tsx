import { useEffect, useState } from 'react'
import { fetchCalculation, CalculationResponse } from './api'
import { fetchUsers, type User } from '@/features/expense/api'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { CompensationCard } from './CompensationCard'
import { UserExpenseCard } from './UserExpenseCard'
import { DownloadReportButton } from './DownloadReportButton'
import { EmptyState } from './EmptyState'

export function Calculation({ onUserSelected }: { onUserSelected?: (selected: boolean) => void }) {
  const [data, setData] = useState<CalculationResponse | null>(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [users, setUsers] = useState<User[]>([])
  const [usersLoading, setUsersLoading] = useState(false)
  const [selectedUser, setSelectedUser] = useState<string>('')

  // Notify parent when user is selected
  useEffect(() => {
    onUserSelected?.(selectedUser !== '')
  }, [selectedUser, onUserSelected])

  // Focus on person select when component mounts
  useEffect(() => {
    const timer = setTimeout(() => {
      const selectElement = document.getElementById('person-select') as HTMLSelectElement
      selectElement?.focus()
    }, 200)
    return () => clearTimeout(timer)
  }, [])

  // Also focus when users finish loading
  useEffect(() => {
    if (!usersLoading && users.length > 0) {
      const selectElement = document.getElementById('person-select') as HTMLSelectElement
      selectElement?.focus()
    }
  }, [usersLoading])

  const loadCalculation = async (withUser: string = selectedUser) => {
    try {
      setLoading(true)
      setError(null)
      const result = await fetchCalculation(withUser || undefined)
      setData(result)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load calculation')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    // Load users for dropdown - don't auto-select
    setUsersLoading(true)
    fetchUsers()
      .then((fetchedUsers) => {
        setUsers(fetchedUsers)
        // Don't auto-select - let user choose manually
      })
      .catch((err) => {
        console.error('Failed to load users:', err)
      })
      .finally(() => setUsersLoading(false))
  }, [])

  useEffect(() => {
    // Only load calculation when user is explicitly selected
    if (selectedUser) {
      loadCalculation(selectedUser)
    }
  }, [selectedUser])

  if (loading) {
    return (
      <div className="w-full p-4 md:p-6">
        <div className="max-w-2xl mx-auto">
          <div className="flex flex-col items-center justify-center py-12 text-muted-foreground">
            <div className="animate-pulse mb-3 text-3xl">💰</div>
            <p>Loading calculation...</p>
          </div>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="w-full p-4 md:p-6 pb-safe">
        <div className="max-w-2xl mx-auto space-y-4">
          {/* Person Selector - Always visible at top */}
          <Card>
            <CardContent className="pt-6">
              <div className="space-y-2">
                <label htmlFor="person-select" className="text-base font-semibold">
                  💰 Settle up with:
                </label>
                <select
                  id="person-select"
                  value={selectedUser}
                  onChange={(e) => setSelectedUser(e.target.value)}
                  disabled={usersLoading || loading}
                  className="w-full h-12 rounded-md border-2 border-input bg-background px-3 text-base ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option value="">{usersLoading ? 'Loading users...' : 'Select a person...'}</option>
                  {users.map((user) => (
                    <option key={user.id} value={user.email}>
                      {user.email}
                    </option>
                  ))}
                </select>
              </div>
            </CardContent>
          </Card>

          <Card className="border-destructive">
            <CardContent className="pt-6">
              <p className="text-destructive text-center mb-4">{error}</p>
              <Button onClick={() => loadCalculation(selectedUser)} className="w-full" variant="outline">
                🔄 Retry
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    )
  }

  return (
    <div className="w-full p-4 md:p-6 pb-safe">
      <div className="max-w-2xl mx-auto space-y-4">
        {/* Person Selector - Always visible at top */}
        <Card>
          <CardContent className="pt-6">
            <div className="space-y-2">
              <label htmlFor="person-select" className="text-base font-semibold">
                💰 Settle up with:
              </label>
              <select
                id="person-select"
                value={selectedUser}
                onChange={(e) => setSelectedUser(e.target.value)}
                disabled={usersLoading || loading}
                className="w-full h-12 rounded-md border-2 border-input bg-background px-3 text-base ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <option value="">{usersLoading ? 'Loading users...' : 'Select a person...'}</option>
                {users.map((user) => (
                  <option key={user.id} value={user.email}>
                    {user.email}
                  </option>
                ))}
              </select>
            </div>
          </CardContent>
        </Card>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-12 text-muted-foreground">
            <div className="animate-pulse mb-3 text-3xl">💰</div>
            <p>Loading calculation...</p>
          </div>
        ) : !selectedUser ? (
          <EmptyState 
            title="Select a person to begin"
            description="Choose who you want to settle up with"
            emoji="👥"
          />
        ) : !data || data.users.length === 0 ? (
          <EmptyState 
            title="No expenses to settle."
            description="No transactions between you and this person."
            emoji="✨"
          />
        ) : (
          <>
            {data.compensation && (
              <CompensationCard
                from={data.compensation.from}
                to={data.compensation.to}
                value={data.compensation.settlement.value}
                currency={data.compensation.settlement.currency}
              />
            )}

            {data.users.map((expenses) => (
              <UserExpenseCard key={expenses.user_email} expenses={expenses} />
            ))}

            <DownloadReportButton />
          </>
        )}
      </div>
    </div>
  )
}
