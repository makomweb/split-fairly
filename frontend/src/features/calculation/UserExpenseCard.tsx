import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { getAvatarColor } from '@/lib/avatar-colors'
import { Expenses } from './api'

interface UserExpenseCardProps {
  expenses: Expenses
}

export function UserExpenseCard({ expenses }: UserExpenseCardProps) {
  const spentCategories = expenses.categories.filter(c => c.type !== 'Lent')
  const lentCategories = expenses.categories.filter(c => c.type === 'Lent')
  const colors = getAvatarColor(expenses.user_email)

  const spentTotals = spentCategories.reduce((acc, category) => {
    const currency = category.sum.currency
    acc[currency] = (acc[currency] || 0) + category.sum.value
    return acc
  }, {} as Record<string, number>)

  return (
    <Card>
      <CardHeader className="pb-3 overflow-visible">
        <div className="text-base font-semibold flex items-center justify-between min-w-0 h-8">
          <div className="flex items-center gap-3 min-w-0">
            <Avatar className="h-8 w-8 shrink-0">
              <AvatarFallback className={`text-xs font-semibold ${colors.bg} ${colors.text}`}>
                {expenses.user_email.substring(0, 2).toUpperCase()}
              </AvatarFallback>
            </Avatar>
            <span className="truncate">{expenses.user_email}</span>
          </div>
          <Badge variant="secondary" className="ml-2 shrink-0">
            {expenses.categories.length} items
          </Badge>
        </div>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {spentCategories.length > 0 && (
            <div className="space-y-2">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Split Expenses</p>
              {spentCategories.map((category, idx) => (
                <div
                  key={idx}
                  className="flex justify-between items-center gap-3 p-3 bg-muted/50 rounded-lg"
                >
                  <span className="font-medium text-sm truncate">
                    {category.type}
                  </span>
                  <span className="font-mono text-sm shrink-0">
                    {category.sum.value.toFixed(2)} {category.sum.currency}
                  </span>
                </div>
              ))}
            </div>
          )}

          <div className="space-y-1.5">
            {Object.entries(spentTotals).map(([currency, total]) => (
              <div 
                key={`spent-${currency}`}
                className="flex justify-between items-center text-sm px-3"
              >
                <span className="font-semibold">Split</span>
                <span className="font-mono font-semibold">
                  {total.toFixed(2)} {currency}
                </span>
              </div>
            ))}
          </div>

          {lentCategories.length > 0 && (
            <>
              <Separator className="my-3" />
              <div className="space-y-2">
                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Lent Money</p>
                {lentCategories.map((category, idx) => (
                  <div
                    key={idx}
                    className="flex justify-between items-center gap-3 p-3 bg-amber-50 rounded-lg border border-amber-200"
                  >
                    <span className="font-medium text-sm truncate">
                      {category.type}
                    </span>
                    <span className="font-mono text-sm shrink-0 text-amber-900">
                      {category.sum.value.toFixed(2)} {category.sum.currency}
                    </span>
                  </div>
                ))}
              </div>
            </>
          )}
        </div>
      </CardContent>
    </Card>
  )
}
