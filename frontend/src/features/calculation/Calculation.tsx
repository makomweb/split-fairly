import { useEffect, useState } from 'react'
import { fetchCalculation, UserExpenses } from './api'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

export function Calculation() {
  const [data, setData] = useState<UserExpenses[]>([])
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
      <div className="min-h-screen bg-gray-50 p-4">
        <div className="max-w-4xl mx-auto">
          <p className="text-center text-gray-600">Loading calculation...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 p-4">
        <div className="max-w-4xl mx-auto">
          <Card>
            <CardContent className="pt-6">
              <p className="text-red-600 text-center">{error}</p>
              <Button onClick={loadCalculation} className="w-full mt-4">
                Retry
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    )
  }

  const calculateTotal = (expenses: UserExpenses) => {
    const totals = expenses.expenses.reduce((acc, expense) => {
      const currency = expense.price.currency
      acc[currency] = (acc[currency] || 0) + expense.price.value
      return acc
    }, {} as Record<string, number>)
    return totals
  }

  return (
    <div className="min-h-screen bg-gray-50 p-4">
      <div className="max-w-4xl mx-auto space-y-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold">Expense Calculation</h1>
          <Button onClick={loadCalculation} variant="outline" size="sm">
            Refresh
          </Button>
        </div>

        {data.length === 0 ? (
          <Card>
            <CardContent className="pt-6">
              <p className="text-center text-gray-600">No expenses tracked yet.</p>
            </CardContent>
          </Card>
        ) : (
          data.map((userExpenses) => {
            const totals = calculateTotal(userExpenses)
            return (
              <Card key={userExpenses.userId}>
                <CardHeader>
                  <CardTitle className="text-lg">
                    User: {userExpenses.userId}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {userExpenses.expenses.map((expense, idx) => (
                      <div
                        key={idx}
                        className="flex justify-between items-start p-3 bg-gray-50 rounded-lg"
                      >
                        <div className="flex-1">
                          <p className="font-medium">{expense.what}</p>
                          <p className="text-sm text-gray-600">{expense.location}</p>
                        </div>
                        <p className="font-semibold">
                          {expense.price.value.toFixed(2)} {expense.price.currency}
                        </p>
                      </div>
                    ))}
                    
                    <div className="border-t pt-3 mt-3">
                      <div className="font-bold text-right space-y-1">
                        {Object.entries(totals).map(([currency, total]) => (
                          <div key={currency}>
                            Total: {total.toFixed(2)} {currency}
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )
          })
        )}
      </div>
    </div>
  )
}
