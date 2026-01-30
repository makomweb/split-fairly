const AVATAR_COLORS = [
  { bg: 'bg-red-500', text: 'text-red-50' },
  { bg: 'bg-orange-500', text: 'text-orange-50' },
  { bg: 'bg-amber-500', text: 'text-amber-50' },
  { bg: 'bg-yellow-500', text: 'text-yellow-50' },
  { bg: 'bg-lime-500', text: 'text-lime-50' },
  { bg: 'bg-green-500', text: 'text-green-50' },
  { bg: 'bg-emerald-500', text: 'text-emerald-50' },
  { bg: 'bg-teal-500', text: 'text-teal-50' },
  { bg: 'bg-cyan-500', text: 'text-cyan-50' },
  { bg: 'bg-blue-500', text: 'text-blue-50' },
  { bg: 'bg-indigo-500', text: 'text-indigo-50' },
  { bg: 'bg-purple-500', text: 'text-purple-50' },
  { bg: 'bg-pink-500', text: 'text-pink-50' },
]

export function getAvatarColor(email: string) {
  let hash = 0
  for (let i = 0; i < email.length; i++) {
    hash = ((hash << 5) - hash) + email.charCodeAt(i)
    hash = hash & hash
  }
  const index = Math.abs(hash) % AVATAR_COLORS.length
  return AVATAR_COLORS[index]
}
