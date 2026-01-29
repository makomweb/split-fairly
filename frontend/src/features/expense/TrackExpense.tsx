import { useState } from 'react'
import { useAuth } from '@/features/auth/AuthContext'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { trackExpense } from '@/features/expense/api'

export function TrackExpense() {
  const { user, logout } = useAuth()
  const [price, setPrice] = useState('')
  const [currency, setCurrency] = useState('EUR')
  const [what, setWhat] = useState('')
  const [location, setLocation] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState(false)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError(null)
    setSuccess(false)
    setLoading(true)

    try {
      await trackExpense({
        price: {
          value: parseFloat(price),
          currency,
        },
        what,
        location,
      })
      setSuccess(true)
      // Clear form
      setWhat('')
      setLocation('')
      setPrice('')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to track expense')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="flex min-h-svh w-full flex-col p-6 md:p-10">
      <div className="w-full max-w-2xl mx-auto">
        {/* Header */}
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold">Track Expense</h1>
            <p className="text-sm text-muted-foreground">{user?.email}</p>
          </div>
          <Button onClick={logout} variant="outline" size="sm">
            Logout
          </Button>
        </div>

        {/* Form */}
        <div className="rounded-lg border border-border bg-card p-6 shadow-sm">
          <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            <div className="grid gap-2">
              <Label htmlFor="what">What?</Label>
              <Input
                id="what"
                type="text"
                placeholder="e.g., Coffee, Lunch, Taxi"
                value={what}
                onChange={(e) => setWhat(e.target.value)}
                disabled={loading}
                required
              />
            </div>

            <div className="grid gap-2">
              <Label htmlFor="location">Where?</Label>
              <Input
                id="location"
                type="text"
                placeholder="e.g., Starbucks, Downtown"
                value={location}
                onChange={(e) => setLocation(e.target.value)}
                disabled={loading}
                required
              />
            </div>

            <div className="grid gap-2">
              <Label htmlFor="price">How much?</Label>
              <div className="flex gap-2">
                <Input
                  id="price"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  value={price}
                  onChange={(e) => setPrice(e.target.value)}
                  disabled={loading}
                  required
                  className="flex-1"
                />
                <select
                  value={currency}
                  onChange={(e) => setCurrency(e.target.value)}
                  disabled={loading}
                  className="w-24 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="EUR">EUR</option>
                  <option value="USD">USD</option>
                  <option value="GBP">GBP</option>
                  <option value="CHF">CHF</option>
                </select>
              </div>
            </div>

            {error && (
              <div className="p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                {error}
              </div>
            )}

            {success && (
              <div className="p-3 bg-green-50 border border-green-200 rounded text-green-700 text-sm">
                Expense tracked successfully!
              </div>
            )}

            <Button type="submit" disabled={loading} className="w-full">
              {loading ? 'Tracking...' : 'Track Expense'}
            </Button>
          </form>
        </div>
      </div>
    </div>
  )
}
