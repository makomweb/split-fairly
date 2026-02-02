import { Card, CardContent } from '@/components/ui/card'

interface EmptyStateProps {
  title: string
  description?: string
  emoji?: string
}

export function EmptyState({ title, description, emoji = '📝' }: EmptyStateProps) {
  return (
    <Card>
      <CardContent className="py-12 text-center text-muted-foreground">
        <div className="text-4xl mb-3">{emoji}</div>
        <p className="text-base">{title}</p>
        {description && <p className="text-sm mt-2">{description}</p>}
      </CardContent>
    </Card>
  )
}
