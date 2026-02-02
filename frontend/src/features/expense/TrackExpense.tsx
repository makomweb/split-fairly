import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { trackExpense } from '@/features/expense/api'
import { ExpenseFormFields } from '@/features/expense/ExpenseFormFields'
import { FormStatusMessages } from '@/features/expense/FormStatusMessages'

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
          <ExpenseFormFields
            what={what}
            onWhatChange={setWhat}
            type={type}
            onTypeChange={(v) => setType(v as any)}
            location={location}
            onLocationChange={setLocation}
            price={price}
            onPriceChange={setPrice}
            currency={currency}
            onCurrencyChange={setCurrency}
            loading={loading}
          />

          <FormStatusMessages error={error} success={success} />

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
