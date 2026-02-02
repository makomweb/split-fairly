import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { RadioGroup, RadioOption } from '@/components/ui/radio'

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
}: ExpenseFormFieldsProps) {
  return (
    <>
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
            onChange={(e) => onWhatChange(e.target.value)}
            disabled={loading}
            required
            autoComplete="off"
            className="h-12 text-base w-full"
          />
          <div className="mt-2">
            <RadioGroup value={type} onValueChange={onTypeChange} className="flex gap-2">
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
          onChange={(e) => onLocationChange(e.target.value)}
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
    </>
  )
}
