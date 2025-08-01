import { useAuth } from '../contexts/AuthContext'
import { ViewType } from './Dashboard'
import { cn } from '@/lib/utils'
import { 
  CreditCard, 
  FileText, 
  FolderOpen, 
  MessageCircle, 
  Store, 
  Lightbulb, 
  Settings, 
  Users, 
  Building2, 
  HelpCircle,
  LogOut
} from 'lucide-react'
import { Button } from '@/components/ui/button'

interface SidebarProps {
  currentView: ViewType
  onViewChange: (view: ViewType) => void
}

export function Sidebar({ currentView, onViewChange }: SidebarProps) {
  const { user, logout } = useAuth()

  const menuItems = [
    { id: 'plati' as ViewType, label: 'Plăți', icon: CreditCard, roles: ['admin', 'presedinte', 'contabil', 'locatar'] },
    { id: 'facturi' as ViewType, label: 'Facturi', icon: FileText, roles: ['admin', 'presedinte', 'contabil'] },
    { id: 'documente' as ViewType, label: 'Documente', icon: FolderOpen, roles: ['admin', 'presedinte', 'contabil', 'locatar'] },
    { id: 'chat' as ViewType, label: 'Chat', icon: MessageCircle, roles: ['admin', 'presedinte', 'contabil', 'locatar', 'moderator'] },
    { id: 'marketplace' as ViewType, label: 'Marketplace', icon: Store, roles: ['admin', 'presedinte', 'contabil', 'locatar', 'moderator'] },
    { id: 'sugestii' as ViewType, label: 'Sugestii', icon: Lightbulb, roles: ['admin', 'presedinte', 'contabil', 'locatar', 'moderator'] },
    { id: 'automatizari' as ViewType, label: 'Automatizări', icon: Settings, roles: ['admin', 'presedinte'] },
    { id: 'utilizatori' as ViewType, label: 'Utilizatori', icon: Users, roles: ['admin', 'presedinte'] },
    { id: 'asociatie' as ViewType, label: 'Asociația', icon: Building2, roles: ['admin', 'presedinte'] },
    { id: 'help' as ViewType, label: 'Ajutor', icon: HelpCircle, roles: ['admin', 'presedinte', 'contabil', 'locatar', 'moderator'] },
  ]

  const visibleItems = menuItems.filter(item => 
    item.roles.includes(user?.role || '')
  )

  return (
    <div className="w-64 bg-white shadow-lg flex flex-col">
      <div className="p-6 border-b">
        <div className="flex items-center space-x-3">
          <Building2 className="h-8 w-8 text-blue-600" />
          <div>
            <h1 className="text-lg font-semibold text-gray-900">Asociații</h1>
            <p className="text-sm text-gray-500">Proprietari</p>
          </div>
        </div>
      </div>

      <nav className="flex-1 p-4 space-y-2">
        {visibleItems.map((item) => {
          const Icon = item.icon
          return (
            <button
              key={item.id}
              onClick={() => onViewChange(item.id)}
              className={cn(
                "w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-left transition-colors",
                currentView === item.id
                  ? "bg-blue-100 text-blue-700"
                  : "text-gray-600 hover:bg-gray-100"
              )}
            >
              <Icon className="h-5 w-5" />
              <span className="font-medium">{item.label}</span>
            </button>
          )
        })}
      </nav>

      <div className="p-4 border-t">
        <div className="mb-4 p-3 bg-gray-50 rounded-lg">
          <p className="text-sm font-medium text-gray-900">
            {user?.prenume} {user?.nume}
          </p>
          <p className="text-xs text-gray-500 capitalize">{user?.role}</p>
          {user?.apartament && (
            <p className="text-xs text-gray-500">{user.apartament}</p>
          )}
        </div>
        
        <Button
          variant="outline"
          size="sm"
          onClick={logout}
          className="w-full"
        >
          <LogOut className="h-4 w-4 mr-2" />
          Deconectare
        </Button>
      </div>
    </div>
  )
}
