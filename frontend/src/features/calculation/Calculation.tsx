import { useEffect, useState } from 'react'
import { fetchCalculation, CalculationResponse, Expenses } from './api'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

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
      <div className="flex min-h-svh w-full flex-col p-6 md:p-10">
        <div className="max-w-4xl mx-auto w-full">
          <p className="text-center text-gray-600">Loading calculation...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="flex min-h-svh w-full flex-col p-6 md:p-10">
        <div className="max-w-4xl mx-auto w-full">
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

  const calculateTotal = (expenses: Expenses) => {
    const totals = expenses.categories.reduce((acc, category) => {
      const currency = category.sum.currency
      acc[currency] = (acc[currency] || 0) + category.sum.value
      return acc
    }, {} as Record<string, number>)
    return totals
  }

  return (
    <div className="flex min-h-svh w-full flex-col p-6 md:p-10">
      <div className="max-w-4xl mx-auto w-full space-y-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold">Expense Calculation</h1>
          <Button onClick={loadCalculation} variant="outline" size="sm">
            Refresh
          </Button>
        </div>

        {!data || data.users.length === 0 ? (
          <Card>
            <CardContent className="pt-6">
              <p className="text-center text-gray-600">No expenses tracked yet.</p>
            </CardContent>
          </Card>
        ) : (
          <>
            {data.compensation && (
              <Card className="bg-blue-50 border-blue-200">
                <CardHeader>
                  <CardTitle className="text-lg text-blue-900">💰 Compensation</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    <p className="text-gray-700">
                      <span className="font-semibold">{data.compensation.from}</span>
                      {' '}pays{' '}
                      <span className="font-semibold">{data.compensation.to}</span>
                    </p>
                    <p className="text-2xl font-bold text-blue-900">
                      {data.compensation.amount.toFixed(2)} {data.compensation.currency}
                    </p>
                  </div>
                </CardContent>
              </Card>
            )}
            {data.users.map((expenses) => {
              const totals = calculateTotal(expenses)
              return (
                <Card key={expenses.user_email}>
                  <CardHeader>
                    <CardTitle className="text-lg">{expenses.user_email}</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-3">
                      {expenses.categories.map((category, idx) => (
                        <div
                          key={idx}
                          className="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                        >
                          <p className="font-medium">{category.what}</p>
                          <p className="font-semibold">
                            {category.sum.value.toFixed(2)} {category.sum.currency}
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
            })}
          </>
        )}
      </div>
    </div>
  )
}
