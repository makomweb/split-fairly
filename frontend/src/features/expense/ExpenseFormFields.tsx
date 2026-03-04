import { useEffect, useState, forwardRef } from 'react'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { RadioGroup, RadioOption } from '@/components/ui/radio'
import { fetchUsers, type User } from '@/features/expense/api'

interface ExpenseFormFieldsProps {
  what: string
  onWhatChange: (value: string) => void
  type: 'Groceries' | 'Non-Food Expenses' | 'Out-of-pocket Expenses'
  onTypeChange: (value: string) => void
  location: string
  onLocationChange: (value: string) => void
  price: string
  onPriceChange: (value: string) => void
  currency: string
  onCurrencyChange: (value: string) => void
  loading: boolean
  whatInputRef?: React.RefObject<HTMLInputElement>
}

export function ExpenseFormFields({
  what,
  onWhatChange,
  type,
  onTypeChange,
  location,
  onLocationChange,
  price,
  onPriceChange,
  currency,
  onCurrencyChange,
  loading,
  whatInputRef,
}: ExpenseFormFieldsProps) {
  const [users, setUsers] = useState<User[]>([])
  const [usersLoading, setUsersLoading] = useState(false)

  useEffect(() => {
    if (type === 'Lent') {
      setUsersLoading(true)
      fetchUsers()
        .then(setUsers)
        .catch(console.error)
        .finally(() => setUsersLoading(false))
    }
  }, [type])

  const isLending = type === 'Lent'
  const locationLabel = isLending ? 'Whom?' : 'Where?'
  const locationPlaceholder = isLending ? 'Select a person...' : 'Starbucks, Downtown...'

  return (
    <>
      {/* What field */}
      <div className="space-y-2">
        <Label htmlFor="what" className="text-base">
          What?
        </Label>
        <Input
          ref={whatInputRef}
          id="what"
          type="text"
          placeholder="Coffee, Lunch, Taxi..."
          value={what}
          onChange={(e) => onWhatChange(e.target.value)}
          disabled={loading}
          required
          autoComplete="off"
          className="h-12 text-base w-full"
        />
        {/* Type selector */}
        <RadioGroup value={type} onValueChange={onTypeChange} className="flex gap-2">
          <RadioOption value="Groceries">Groceries</RadioOption>
          <RadioOption value="Non-Food">Non-Food</RadioOption>
          <RadioOption value="Lent">Lent</RadioOption>
        </RadioGroup>
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
              onChange={(e) => onPriceChange(e.target.value)}
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
            onChange={(e) => onCurrencyChange(e.target.value)}
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

      {/* Location field */}
      <div className="space-y-2">
        <Label htmlFor="location" className="text-base">
          {locationLabel}
        </Label>
        {isLending ? (
          <select
            id="location"
            value={location}
            onChange={(e) => onLocationChange(e.target.value)}
            disabled={loading || usersLoading}
            required
            className="h-12 w-full rounded-md border border-input bg-background px-3 text-base ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="">{usersLoading ? 'Loading users...' : 'Select a person...'}</option>
            {users.map((user) => (
              <option key={user.id} value={user.email}>
                {user.email}
              </option>
            ))}
          </select>
        ) : (
          <Input
            id="location"
            type="text"
            placeholder={locationPlaceholder}
            value={location}
            onChange={(e) => onLocationChange(e.target.value)}
            disabled={loading}
            required
            autoComplete="off"
            className="h-12 text-base"
          />
        )}
      </div>
    </>
  )
}
