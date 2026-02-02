interface FormStatusMessagesProps {
  error: string | null
  success: boolean
}

export function FormStatusMessages({ error, success }: FormStatusMessagesProps) {
  return (
    <>
      {error && (
        <div className="rounded-lg bg-destructive/10 border border-destructive/20 p-4 text-destructive text-sm">
          {error}
        </div>
      )}

      {success && (
        <div className="rounded-lg bg-green-50 border border-green-200 p-4 text-green-700 text-sm font-medium">
          ✓ Expense tracked successfully!
        </div>
      )}
    </>
  )
}
