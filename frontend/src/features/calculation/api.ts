interface Price {
  value: number
  currency: string
}

interface Category {
  what: string
  sum: Price
}

export interface Expenses {
  user_uuid: string
  user_email: string
  categories: Category[]
}

export async function fetchCalculation(): Promise<Expenses[]> {
  const response = await fetch('http://localhost:8080/api/calculate', {
    credentials: 'include',
  })

  if (!response.ok) {
    throw new Error('Failed to fetch calculation')
  }

  return response.json()
}
