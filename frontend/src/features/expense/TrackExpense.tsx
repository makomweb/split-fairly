import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { RadioGroup, RadioOption } from '@/components/ui/radio'
import { trackExpense } from '@/features/expense/api'

export function TrackExpense() {
  const [price, setPrice] = useState('')
  const [currency, setCurrency] = useState('EUR')
  const [what, setWhat] = useState('')
  const [type, setType] = useState<'Groceries' | 'Non-Food Expenses' | 'Out-of-pocket Expenses'>('Groceries')
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
        type,
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
    <div className="w-full p-4 md:p-6 pb-safe">
      <div className="max-w-2xl mx-auto">
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* What field */}
          <div className="space-y-2">
            <Label htmlFor="what" className="text-base">
              What did you buy?
            </Label>
            <div className="flex flex-col">
              <Input
                id="what"
                type="text"
                placeholder="Coffee, Lunch, Taxi..."
                value={what}
                onChange={(e) => setWhat(e.target.value)}
                disabled={loading}
                required
                autoComplete="off"
                className="h-12 text-base w-full"
              />
              <div className="mt-2">
                <RadioGroup value={type} onValueChange={(v) => setType(v as any)} className="flex gap-2">
                  <RadioOption value="Groceries">Groceries</RadioOption>
                  <RadioOption value="Non-Food">Non-Food</RadioOption>
                  <RadioOption value="Lent">Lent</RadioOption>
                </RadioGroup>
              </div>
            </div>
          </div>

          {/* Location field */}
          <div className="space-y-2">
            <Label htmlFor="location" className="text-base">
              Where?
            </Label>
            <Input
              id="location"
              type="text"
              placeholder="Starbucks, Downtown..."
              value={location}
              onChange={(e) => setLocation(e.target.value)}
              disabled={loading}
              required
              autoComplete="off"
              className="h-12 text-base"
            />
          </div>

          {/* Price field */}
          <div className="space-y-2">
            <Label htmlFor="price" className="text-base">
              How much?
            </Label>
            <div className="flex gap-2">
              <div className="relative flex-1">
                <Input
                  id="price"
                  type="number"
                  inputMode="decimal"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  value={price}
                  onChange={(e) => setPrice(e.target.value)}
                  disabled={loading}
                  required
                  className="h-12 text-base pr-16"
                />
                <Badge 
                  variant="secondary" 
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-medium"
                >
                  {currency}
                </Badge>
              </div>
              <select
                value={currency}
                onChange={(e) => setCurrency(e.target.value)}
                disabled={loading}
                className="h-12 w-20 rounded-md border border-input bg-background px-3 text-base font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="Currency"
              >
                <option value="EUR">€</option>
                <option value="USD">$</option>
                <option value="GBP">£</option>
                <option value="CHF">CHF</option>
              </select>
            </div>
          </div>

          {/* Error message */}
          {error && (
            <div className="rounded-lg bg-destructive/10 border border-destructive/20 p-4 text-destructive text-sm">
              {error}
            </div>
          )}

          {/* Success message */}
          {success && (
            <div className="rounded-lg bg-green-50 border border-green-200 p-4 text-green-700 text-sm font-medium">
              ✓ Expense tracked successfully!
            </div>
          )}

          {/* Submit button */}
          <Button 
            type="submit" 
            disabled={loading} 
            className="w-full h-12 text-base font-semibold"
            size="lg"
          >
            {loading ? 'Saving...' : 'Track Expense'}
          </Button>
        </form>
      </div>
    </div>
  )
}
