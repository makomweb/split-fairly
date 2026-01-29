interface Price {
  value: number
  currency: string
}

interface Expense {
  price: Price
  what: string
  location: string
}

export interface UserExpenses {
  userId: string
  expenses: Expense[]
}

export async function fetchCalculation(): Promise<UserExpenses[]> {
  const response = await fetch('http://localhost:8080/api/calculate', {
    credentials: 'include',
  })

  if (!response.ok) {
    throw new Error('Failed to fetch calculation')
  }

  return response.json()
}
