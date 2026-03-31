import { getApiUrl } from '../../api/config'

interface Price {
  value: number
  currency: string
}

interface Category {
  type: string
  sum: Price
}

export interface Expenses {
  user_email: string
  categories: Category[]
}

interface Compensation {
  settlement: Price
  from: string
  to: string
}

export interface CalculationResponse {
  users: Expenses[]
  compensation: Compensation | null
  id: string
}

export interface ReportStatus {
  id: string
  status: 'pending' | 'generating' | 'completed' | 'failed'
  createdAt: string
  downloadUrl?: string
  error?: string
}

export async function fetchCalculation(withUser?: string): Promise<CalculationResponse> {
  const url = new URL(getApiUrl('/api/calculate'), window.location.origin)
  
  if (withUser) {
    url.searchParams.set('with_user', withUser)
  }

  const response = await fetch(url.toString(), {
    credentials: 'include',
  })

  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.detail || error.error || 'Failed to fetch calculation')
  }

  return response.json()
}

export async function initiateReportGeneration(id: string): Promise<ReportStatus> {
  // TODO: Consolidate URL parameter!
  const response = await fetch(getApiUrl(`/api/report/calculation?id=${id}`), {
    method: 'POST',
    credentials: 'include',
  })

  if (!response.ok) {
    throw new Error('Failed to initiate report generation')
  }

  return response.json()
}

export async function getReportStatus(id: string): Promise<ReportStatus> {
  const response = await fetch(getApiUrl(`/api/report/${id}/status`), {
    credentials: 'include',
  })

  if (!response.ok) {
    throw new Error('Failed to fetch report status')
  }

  return response.json()
}

export async function downloadReport(id: string): Promise<Response> {
  return fetch(getApiUrl(`/api/report/${id}/download`), {
    credentials: 'include',
  })
}
