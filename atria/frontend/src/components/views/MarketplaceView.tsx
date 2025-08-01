import { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Store, Plus, Phone } from 'lucide-react'

interface ServiciuMarketplace {
  id: number
  user_id: number
  titlu: string
  descriere: string
  tip: string
  pret?: number
  contact: string
  status: string
  created_at: string
}

export function MarketplaceView() {
  const { token } = useAuth()
  const [servicii, setServicii] = useState<ServiciuMarketplace[]>([])
  const [loading, setLoading] = useState(true)
  const [addDialog, setAddDialog] = useState(false)
  const [newService, setNewService] = useState({
    titlu: '',
    descriere: '',
    tip: '',
    pret: '',
    contact: ''
  })

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'


  useEffect(() => {
    fetchServicii()
  }, [])

  const fetchServicii = async () => {
    try {
      const response = await fetch(`${API_URL}/api/marketplace`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setServicii(data)
      }
    } catch (error) {
      console.error('Error fetching marketplace services:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleAddService = async () => {
    try {
      const response = await fetch(`${API_URL}/api/marketplace`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...newService,
          pret: newService.pret ? parseFloat(newService.pret) : null,
        }),
      })

      if (response.ok) {
        setAddDialog(false)
        setNewService({
          titlu: '',
          descriere: '',
          tip: '',
          pret: '',
          contact: ''
        })
        fetchServicii()
      }
    } catch (error) {
      console.error('Error adding service:', error)
    }
  }

  const getTypeBadge = (tip: string) => {
    switch (tip) {
      case 'serviciu':
        return <Badge className="bg-blue-100 text-blue-800">Serviciu</Badge>
      case 'inchiriere':
        return <Badge className="bg-green-100 text-green-800">Închiriere</Badge>
      case 'vanzare':
        return <Badge className="bg-purple-100 text-purple-800">Vânzare</Badge>
      default:
        return <Badge variant="outline">{tip}</Badge>
    }
  }

  const formatCurrency = (amount?: number) => {
    if (!amount) return 'La cerere'
    return new Intl.NumberFormat('ro-RO', {
      style: 'currency',
      currency: 'RON'
    }).format(amount)
  }

  if (loading) {
    return <div className="flex justify-center items-center h-64">Se încarcă...</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Marketplace</h1>
          <p className="text-gray-600">Servicii, închirieri și vânzări în comunitate</p>
        </div>
        <Dialog open={addDialog} onOpenChange={setAddDialog}>
          <DialogTrigger asChild>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Adaugă Anunț
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Adaugă Anunț Nou</DialogTitle>
              <DialogDescription>
                Publică un serviciu, închiriere sau vânzare
              </DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="titlu">Titlu</Label>
                <Input
                  id="titlu"
                  value={newService.titlu}
                  onChange={(e) => setNewService({ ...newService, titlu: e.target.value })}
                  placeholder="Titlul anunțului"
                />
              </div>
              <div>
                <Label htmlFor="tip">Tip Anunț</Label>
                <Select value={newService.tip} onValueChange={(value) => setNewService({ ...newService, tip: value })}>
                  <SelectTrigger>
                    <SelectValue placeholder="Selectează tipul" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="serviciu">Serviciu</SelectItem>
                    <SelectItem value="inchiriere">Închiriere</SelectItem>
                    <SelectItem value="vanzare">Vânzare</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label htmlFor="pret">Preț (RON) - opțional</Label>
                <Input
                  id="pret"
                  type="number"
                  step="0.01"
                  value={newService.pret}
                  onChange={(e) => setNewService({ ...newService, pret: e.target.value })}
                  placeholder="0.00"
                />
              </div>
              <div>
                <Label htmlFor="contact">Contact</Label>
                <Input
                  id="contact"
                  value={newService.contact}
                  onChange={(e) => setNewService({ ...newService, contact: e.target.value })}
                  placeholder="Telefon sau email"
                />
              </div>
              <div>
                <Label htmlFor="descriere">Descriere</Label>
                <Textarea
                  id="descriere"
                  value={newService.descriere}
                  onChange={(e) => setNewService({ ...newService, descriere: e.target.value })}
                  placeholder="Descrierea detaliată..."
                />
              </div>
              <Button onClick={handleAddService} className="w-full">
                Publică Anunț
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total anunțuri</CardTitle>
            <Store className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{servicii.length}</div>
            <p className="text-xs text-muted-foreground">
              {servicii.filter(s => s.status === 'activ').length} active
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Servicii</CardTitle>
            <Store className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {servicii.filter(s => s.tip === 'serviciu').length}
            </div>
            <p className="text-xs text-muted-foreground">
              Servicii oferite
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Vânzări</CardTitle>
            <Store className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {servicii.filter(s => s.tip === 'vanzare').length}
            </div>
            <p className="text-xs text-muted-foreground">
              Produse de vânzare
            </p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {servicii.map((serviciu) => (
          <Card key={serviciu.id} className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <div className="flex items-start justify-between">
                <CardTitle className="text-lg">{serviciu.titlu}</CardTitle>
                {getTypeBadge(serviciu.tip)}
              </div>
              <CardDescription className="text-sm">
                {serviciu.descriere}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-500">Preț:</span>
                  <span className="font-semibold">{formatCurrency(serviciu.pret)}</span>
                </div>
                <div className="flex items-center space-x-2">
                  <Phone className="h-4 w-4 text-gray-400" />
                  <span className="text-sm">{serviciu.contact}</span>
                </div>
                <div className="text-xs text-gray-500">
                  Publicat: {new Date(serviciu.created_at).toLocaleDateString('ro-RO')}
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {servicii.length === 0 && (
        <Card>
          <CardContent className="text-center py-8">
            <Store className="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <p className="text-gray-500">Nu există anunțuri încă.</p>
            <p className="text-sm text-gray-400">Fii primul care publică un anunț!</p>
          </CardContent>
        </Card>
      )}
    </div>
  )
}
