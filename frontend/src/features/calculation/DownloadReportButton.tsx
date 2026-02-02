import { Button } from '@/components/ui/button'
import { useState } from 'react'
import { downloadCalculationReport } from './api'

export function DownloadReportButton() {
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const handleDownload = async () => {
    setLoading(true)
    setError(null)

    try {
      const reportResponse = await downloadCalculationReport()
      if (!reportResponse.ok) {
        throw new Error('Failed to download report')
      }

      const blob = await reportResponse.blob()
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = 'split-fairly-calculation.pdf'
      a.click()
      URL.revokeObjectURL(url)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to download report')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-2">
      <div className="flex justify-end">
        <Button
          variant="outline"
          disabled={loading}
          onClick={handleDownload}
        >
          {loading ? 'Preparing...' : 'Download PDF'}
        </Button>
      </div>
      {error && (
        <div className="text-destructive text-sm text-right">{error}</div>
      )}
    </div>
  )
}
