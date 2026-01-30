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

export async function fetchCalculation(): Promise<CalculationResponse> {
  const response = await fetch('http://localhost:8080/api/calculate', {
    credentials: 'include',
  })

  if (!response.ok) {
    throw new Error('Failed to fetch calculation')
  }

  return response.json()
}
