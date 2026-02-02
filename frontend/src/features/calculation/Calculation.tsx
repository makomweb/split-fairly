import { useEffect, useState } from 'react'
import { fetchCalculation, CalculationResponse } from './api'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { CompensationCard } from './CompensationCard'
import { UserExpenseCard } from './UserExpenseCard'
import { DownloadReportButton } from './DownloadReportButton'
import { EmptyState } from './EmptyState'

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

  return (
    <div className="w-full p-4 md:p-6 pb-safe">
      <div className="max-w-2xl mx-auto space-y-4">
        {!data || data.users.length === 0 ? (
          <EmptyState 
            title="No expenses tracked yet."
            description="Start tracking to see calculations!"
            emoji="📝"
          />
        ) : (
          <>
            {data.compensation && (
              <CompensationCard
                from={data.compensation.from}
                to={data.compensation.to}
                value={data.compensation.settlement.value}
                currency={data.compensation.settlement.currency}
              />
            )}

            {data.users.map((expenses) => (
              <UserExpenseCard key={expenses.user_email} expenses={expenses} />
            ))}

            <DownloadReportButton />
          </>
        )}
      </div>
    </div>
  )
}
