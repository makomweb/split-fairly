import { useState, useImperativeHandle, useRef, forwardRef, useEffect } from 'react'
import { Button } from '@/components/ui/button'
import { trackExpense } from '@/features/expense/api'
import { ExpenseFormFields } from '@/features/expense/ExpenseFormFields'
import { FormStatusMessages } from '@/features/expense/FormStatusMessages'

interface TrackExpenseProps {
  onComplete?: () => void
  onValidityChange?: (isValid: boolean) => void
  onLoadingChange?: (isLoading: boolean) => void
}

export const TrackExpense = forwardRef<
  { submit: () => Promise<void> },
  TrackExpenseProps
>(({ onComplete, onValidityChange, onLoadingChange }, ref) => {
  const [price, setPrice] = useState('')
  const [currency, setCurrency] = useState('EUR')
  const [what, setWhat] = useState('')
  const [type, setType] = useState<'Groceries' | 'Non-Food Expenses' | 'Out-of-pocket Expenses'>('Groceries')
  const [location, setLocation] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState(false)
  const formRef = useRef<HTMLFormElement>(null)
  const whatInputRef = useRef<HTMLInputElement>(null)

  // Focus on "what" field when component mounts
  useEffect(() => {
    whatInputRef.current?.focus()
  }, [])

  // Check form validity
  const isFormValid = what.trim() !== '' && location.trim() !== '' && price.trim() !== '' && parseFloat(price) > 0

  // Notify parent of validity changes
  const [prevValid, setPrevValid] = useState(isFormValid)
  if (prevValid !== isFormValid) {
    setPrevValid(isFormValid)
    onValidityChange?.(isFormValid)
  }

  // Expose submit method to parent
  useImperativeHandle(ref, () => ({
    submit: async () => {
      if (formRef.current) {
        formRef.current.requestSubmit()
      }
    },
    focus: () => {
      whatInputRef.current?.focus()
    },
  }))

  function handleTypeChange(newType: string) {
    setType(newType as any)
    // Auto-populate "what" with "cash" when selecting Lent, clear when leaving Lent
    if (newType === 'Lent') {
      if (!what) {
        setWhat('cash')
      }
    } else if (type === 'Lent' && what === 'cash') {
      setWhat('')
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError(null)
    setSuccess(false)
    setLoading(true)
    onLoadingChange?.(true)

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
      // Clear form (but keep "what" for lent expenses)
      if (type !== 'Lent') {
        setWhat('')
      }
      setLocation('')
      setPrice('')
      // Focus on "what" field for next entry
      setTimeout(() => {
        whatInputRef.current?.focus()
      }, 0)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to track expense')
    } finally {
      setLoading(false)
      onLoadingChange?.(false)
    }
  }

  return (
    <div className="w-full p-4 md:p-6 pb-24">
      <div className="max-w-2xl mx-auto">
        <h2 className="text-2xl font-bold mb-6">Track Expense</h2>
        
        <form ref={formRef} onSubmit={handleSubmit} className="space-y-6">
          <ExpenseFormFields
            what={what}
            onWhatChange={setWhat}
            type={type}
            onTypeChange={handleTypeChange}
            location={location}
            onLocationChange={setLocation}
            price={price}
            onPriceChange={setPrice}
            currency={currency}
            onCurrencyChange={setCurrency}
            loading={loading}
            whatInputRef={whatInputRef}
          />

          <FormStatusMessages error={error} success={success} />
        </form>
      </div>
    </div>
  )
})
