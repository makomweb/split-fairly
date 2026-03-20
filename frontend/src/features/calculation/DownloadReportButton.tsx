import { Button } from '@/components/ui/button'
import { useState } from 'react'
import { initiateReportGeneration, getReportStatus, downloadReport, ReportStatus } from './api'

export function DownloadReportButton() {
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [reportStatus, setReportStatus] = useState<ReportStatus | null>(null)

  const pollReportStatus = async (reportId: string) => {
    let attempts = 0
    const maxAttempts = 120 // 2 minutes with 1s intervals

    while (attempts < maxAttempts) {
      try {
        const status = await getReportStatus(reportId)
        setReportStatus(status)

        if (status.status === 'completed') {
          return status
        } else if (status.status === 'failed') {
          throw new Error(status.error || 'Report generation failed')
        }

        // Wait 1 second before next poll
        await new Promise(resolve => setTimeout(resolve, 1000))
        attempts++
      } catch (err) {
        throw err
      }
    }

    throw new Error('Report generation timed out')
  }

  const handleDownload = async () => {
    setLoading(true)
    setError(null)
    setReportStatus(null)

    try {
      // Initiate report generation
      const initResponse = await initiateReportGeneration()
      setReportStatus(initResponse)

      // Poll for completion
      const completedStatus = await pollReportStatus(initResponse.id)

      // Download the report
      if (completedStatus.downloadUrl) {
        const reportResponse = await downloadReport(completedStatus.id)
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
      }
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
          {loading ? (
            <>
              {reportStatus?.status === 'generating' ? 'Generating...' : 'Preparing...'}
            </>
          ) : (
            'Download PDF'
          )}
        </Button>
      </div>
      {error && (
        <div className="text-destructive text-sm text-right">{error}</div>
      )}
    </div>
  )
}
