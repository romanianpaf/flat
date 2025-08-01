import { useState } from 'react'
import { useAuth } from '../contexts/AuthContext'
import { Sidebar } from './Sidebar'
import { Header } from './Header'
import { PlatiView } from './views/PlatiView'
import { FacturiView } from './views/FacturiView'
import { DocumenteView } from './views/DocumenteView'
import { ChatView } from './views/ChatView'
import { MarketplaceView } from './views/MarketplaceView'
import { SugestiiView } from './views/SugestiiView'
import { AutomatizariView } from './views/AutomatizariView'
import { UtilizatoriView } from './views/UtilizatoriView'
import { AsociatieView } from './views/AsociatieView'
import { HelpView } from './views/HelpView'

export type ViewType = 
  | 'plati' 
  | 'facturi' 
  | 'documente' 
  | 'chat' 
  | 'marketplace' 
  | 'sugestii' 
  | 'automatizari' 
  | 'utilizatori' 
  | 'asociatie' 
  | 'help'

export function Dashboard() {
  const [currentView, setCurrentView] = useState<ViewType>('plati')
  const { } = useAuth()

  const renderView = () => {
    switch (currentView) {
      case 'plati':
        return <PlatiView />
      case 'facturi':
        return <FacturiView />
      case 'documente':
        return <DocumenteView />
      case 'chat':
        return <ChatView />
      case 'marketplace':
        return <MarketplaceView />
      case 'sugestii':
        return <SugestiiView />
      case 'automatizari':
        return <AutomatizariView />
      case 'utilizatori':
        return <UtilizatoriView />
      case 'asociatie':
        return <AsociatieView />
      case 'help':
        return <HelpView />
      default:
        return <PlatiView />
    }
  }

  return (
    <div className="flex h-screen bg-gray-100">
      <Sidebar currentView={currentView} onViewChange={setCurrentView} />
      <div className="flex-1 flex flex-col overflow-hidden">
        <Header />
        <main className="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
          {renderView()}
        </main>
      </div>
    </div>
  )
}
