interface ExpenseData {
  time: string
  user: string
  what: string
  location: string
  price: number
  purpose: string
}

export async function trackExpense(expense: ExpenseData): Promise<void> {
  const response = await fetch('http://localhost:8080/api/track', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include',
    body: JSON.stringify(expense),
  })

  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.message || 'Failed to track expense')
  }

  return response.json()
}
