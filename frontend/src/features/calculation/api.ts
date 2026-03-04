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

export async function downloadCalculationReport(): Promise<Response> {
  return fetch(getApiUrl('/api/report/calculation'), {
    credentials: 'include',
  })
}
