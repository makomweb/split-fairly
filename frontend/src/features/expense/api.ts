import { getApiUrl } from '../../api/config'

interface Price {
  value: number
  currency: string
}

interface ExpenseData {
  price: Price
  what: string
  type: 'Groceries' | 'Non-Food' | 'Lent'
  location: string
}

export interface User {
  id: string
  email: string
}

export async function trackExpense(expense: ExpenseData): Promise<void> {
  const response = await fetch(getApiUrl('/api/track'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include',
    body: JSON.stringify(expense),
  })

  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.error || error.message || 'Failed to track expense')
  }

  return response.json()
}

export async function fetchUsers(): Promise<User[]> {
  const response = await fetch(getApiUrl('/api/users'), {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include',
  })

  if (!response.ok) {
    throw new Error('Failed to fetch users')
  }

  const data = await response.json()
  return data.users
}

