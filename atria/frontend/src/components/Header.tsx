import { useAuth } from '../contexts/AuthContext'
import { Bell, Search } from 'lucide-react'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'

export function Header() {
  const { user } = useAuth()

  return (
    <header className="bg-white shadow-sm border-b px-6 py-4">
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <h2 className="text-xl font-semibold text-gray-900">
            Bună ziua, {user?.prenume}!
          </h2>
        </div>
        
        <div className="flex items-center space-x-4">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              placeholder="Căutare..."
              className="pl-10 w-64"
            />
          </div>
          
          <Button variant="ghost" size="sm">
            <Bell className="h-5 w-5" />
          </Button>
        </div>
      </div>
    </header>
  )
}
