import { useEffect, useState } from 'react'
import { fetchCalculation, CalculationResponse, Expenses } from './api'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'

export function Calculation() {
  const [data, setData] = useState<CalculationResponse | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const loadCalculation = async () => {
    try {
      setLoading(true)
      setError(null)
      const result = await fetchCalculation()
      setData(result)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load calculation')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadCalculation()
  }, [])

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
      <div className="w-full p-4 md:p-6">
        <div className="max-w-2xl mx-auto">
          <Card className="border-destructive">
            <CardContent className="pt-6">
              <p className="text-destructive text-center mb-4">{error}</p>
              <Button onClick={loadCalculation} className="w-full" variant="outline">
                🔄 Retry
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    )
  }

  const calculateTotal = (expenses: Expenses) => {
    const totals = expenses.categories.reduce((acc, category) => {
      const currency = category.sum.currency
      acc[currency] = (acc[currency] || 0) + category.sum.value
      return acc
    }, {} as Record<string, number>)
    return totals
  }

  return (
    <div className="w-full p-4 md:p-6 pb-safe">
      <div className="max-w-2xl mx-auto space-y-4">

        {!data || data.users.length === 0 ? (
          <Card>
            <CardContent className="py-12 text-center text-muted-foreground">
              <div className="text-4xl mb-3">📝</div>
              <p className="text-base">No expenses tracked yet.</p>
              <p className="text-sm mt-2">Start tracking to see calculations!</p>
            </CardContent>
          </Card>
        ) : (
          <>
            {/* Compensation card */}
            {data.compensation && (
              <Card className="bg-gradient-to-br from-blue-50 to-indigo-50 border-blue-200">
                <CardHeader className="pb-3">
                  <CardTitle className="text-base font-semibold text-blue-900 flex items-center gap-2">
                    <span className="text-xl">💸</span>
                    Settlement Required
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm text-blue-700 mb-1">From</p>
                      <p className="font-semibold text-blue-900">{data.compensation.from}</p>
                    </div>
                    <div className="text-2xl">→</div>
                    <div className="text-right">
                      <p className="text-sm text-blue-700 mb-1">To</p>
                      <p className="font-semibold text-blue-900">{data.compensation.to}</p>
                    </div>
                  </div>
                  <Separator />
                  <div className="text-center">
                    <p className="text-sm text-blue-700 mb-1">Amount</p>
                    <p className="text-3xl font-bold text-blue-900">
                      {data.compensation.settlement.value.toFixed(2)}
                      <span className="text-lg ml-2">{data.compensation.settlement.currency}</span>
                    </p>
                  </div>
                </CardContent>
              </Card>
            )}
            
            {/* User expenses */}
            {data.users.map((expenses) => {
              const totals = calculateTotal(expenses)
              return (
                <Card key={expenses.user_email}>
                  <CardHeader className="pb-3 px-3">
                    <CardTitle className="text-base font-semibold flex items-center justify-between px-3">
                      <span className="truncate">{expenses.user_email}</span>
                      <Badge variant="secondary" className="ml-2 shrink-0">
                        {expenses.categories.length} items
                      </Badge>
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-2">
                      {expenses.categories.map((category, idx) => (
                        <div
                          key={idx}
                          className="flex justify-between items-center gap-3 p-3 bg-muted/50 rounded-lg"
                        >
                          <span className="font-medium text-sm truncate">
                            {category.what}
                          </span>
                          <span className="font-mono text-sm shrink-0">
                            {category.sum.value.toFixed(2)} {category.sum.currency}
                          </span>
                        </div>
                      ))}
                      
                      <Separator className="my-3" />
                      
                      <div className="space-y-1.5">
                        {Object.entries(totals).map(([currency, total]) => (
                          <div 
                            key={currency}
                            className="flex justify-between items-center text-sm font-semibold px-3"
                          >
                            <span className="text-muted-foreground">Total</span>
                            <span className="font-mono">
                              {total.toFixed(2)} {currency}
                            </span>
                          </div>
                        ))}
                      </div>
                    </div>
                  </CardContent>
                </Card>
              )
            })}
          </>
        )}
      </div>
    </div>
  )
}
