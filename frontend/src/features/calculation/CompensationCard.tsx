import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { getAvatarColor } from '@/lib/avatar-colors'

interface CompensationCardProps {
  from: string
  to: string
  value: number
  currency: string
}

export function CompensationCard({ from, to, value, currency }: CompensationCardProps) {
  return (
    <Card className="bg-gradient-to-br from-blue-50 to-indigo-50 border-blue-200">
      <CardHeader className="pb-3">
        <CardTitle className="text-base font-semibold text-blue-900 flex items-center gap-2">
          <span className="text-xl">💸</span>
          Settlement Required
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="flex items-center justify-between gap-4">
          {/* From */}
          <div className="flex-1 min-w-0">
            <p className="text-xs text-blue-700 mb-1">From</p>
            <div className="flex items-center gap-2 min-w-0">
              <Avatar className="h-8 w-8 shrink-0">
                <AvatarFallback className={`text-xs font-semibold ${getAvatarColor(from).bg} ${getAvatarColor(from).text}`}>
                  {from.substring(0, 2).toUpperCase()}
                </AvatarFallback>
              </Avatar>
              <p className="font-semibold text-blue-900 truncate text-sm">{from}</p>
            </div>
          </div>

          {/* Pays */}
          <div className="shrink-0">
            <p className="text-xs text-blue-700 mb-1 text-center">Pays</p>
            <p className="font-bold text-blue-900 text-sm whitespace-nowrap">
              {value.toFixed(2)} {currency}
            </p>
          </div>

          {/* To */}
          <div className="flex-1 min-w-0">
            <p className="text-xs text-blue-700 mb-1 text-right">To</p>
            <div className="flex items-center gap-2 flex-row-reverse min-w-0">
              <Avatar className="h-8 w-8 shrink-0">
                <AvatarFallback className={`text-xs font-semibold ${getAvatarColor(to).bg} ${getAvatarColor(to).text}`}>
                  {to.substring(0, 2).toUpperCase()}
                </AvatarFallback>
              </Avatar>
              <p className="font-semibold text-blue-900 truncate text-sm text-right">{to}</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}
