import { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { FileText, Plus, Settings, CheckCircle, Clock, AlertCircle } from 'lucide-react'

interface Factura {
  id: number
  furnizor: string
  numar_factura: string
  data_factura: string
  data_scadenta: string
  suma_totala: number
  tip_serviciu: string
  descriere?: string
  status: string
  created_at: string
}

export function FacturiView() {
  const { token } = useAuth()
  const [facturi, setFacturi] = useState<Factura[]>([])
  const [loading, setLoading] = useState(true)
  const [addDialog, setAddDialog] = useState(false)
  const [newFactura, setNewFactura] = useState({
    furnizor: '',
    numar_factura: '',
    data_factura: '',
    data_scadenta: '',
    suma_totala: '',
    tip_serviciu: '',
    descriere: ''
  })

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  useEffect(() => {
    fetchFacturi()
  }, [])

  const fetchFacturi = async () => {
    try {
      const response = await fetch(`${API_URL}/api/facturi`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setFacturi(data)
      }
    } catch (error) {
      console.error('Error fetching invoices:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleAddFactura = async () => {
    try {
      const response = await fetch(`${API_URL}/api/facturi`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...newFactura,
          suma_totala: parseFloat(newFactura.suma_totala),
          data_factura: new Date(newFactura.data_factura).toISOString(),
          data_scadenta: new Date(newFactura.data_scadenta).toISOString(),
        }),
      })

      if (response.ok) {
        setAddDialog(false)
        setNewFactura({
          furnizor: '',
          numar_factura: '',
          data_factura: '',
          data_scadenta: '',
          suma_totala: '',
          tip_serviciu: '',
          descriere: ''
        })
        fetchFacturi()
      }
    } catch (error) {
      console.error('Error adding invoice:', error)
    }
  }

  const handleProcessFactura = async (facturaId: number) => {
    try {
      const response = await fetch(`${API_URL}/api/facturi/${facturaId}/process`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })

      if (response.ok) {
        fetchFacturi()
      }
    } catch (error) {
      console.error('Error processing invoice:', error)
    }
  }

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'platita':
        return <Badge className="bg-green-100 text-green-800"><CheckCircle className="w-3 h-3 mr-1" />Plătită</Badge>
      case 'procesata':
        return <Badge className="bg-blue-100 text-blue-800"><Settings className="w-3 h-3 mr-1" />Procesată</Badge>
      default:
        return <Badge className="bg-yellow-100 text-yellow-800"><Clock className="w-3 h-3 mr-1" />Neprocesată</Badge>
    }
  }

  const formatCurrency = (amount: number) => {
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
          <h1 className="text-3xl font-bold text-gray-900">Facturi</h1>
          <p className="text-gray-600">Gestionează facturile și procesează plățile lunare</p>
        </div>
        <Dialog open={addDialog} onOpenChange={setAddDialog}>
          <DialogTrigger asChild>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Adaugă Factură
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Adaugă Factură Nouă</DialogTitle>
              <DialogDescription>
                Completează detaliile facturii pentru procesare
              </DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="furnizor">Furnizor</Label>
                <Input
                  id="furnizor"
                  value={newFactura.furnizor}
                  onChange={(e) => setNewFactura({ ...newFactura, furnizor: e.target.value })}
                  placeholder="Numele furnizorului"
                />
              </div>
              <div>
                <Label htmlFor="numar_factura">Număr Factură</Label>
                <Input
                  id="numar_factura"
                  value={newFactura.numar_factura}
                  onChange={(e) => setNewFactura({ ...newFactura, numar_factura: e.target.value })}
                  placeholder="Nr. facturii"
                />
              </div>
              <div>
                <Label htmlFor="tip_serviciu">Tip Serviciu</Label>
                <Input
                  id="tip_serviciu"
                  value={newFactura.tip_serviciu}
                  onChange={(e) => setNewFactura({ ...newFactura, tip_serviciu: e.target.value })}
                  placeholder="Ex: încălzire, apă, electricitate"
                />
              </div>
              <div>
                <Label htmlFor="suma_totala">Suma Totală (RON)</Label>
                <Input
                  id="suma_totala"
                  type="number"
                  step="0.01"
                  value={newFactura.suma_totala}
                  onChange={(e) => setNewFactura({ ...newFactura, suma_totala: e.target.value })}
                  placeholder="0.00"
                />
              </div>
              <div>
                <Label htmlFor="data_factura">Data Facturii</Label>
                <Input
                  id="data_factura"
                  type="date"
                  value={newFactura.data_factura}
                  onChange={(e) => setNewFactura({ ...newFactura, data_factura: e.target.value })}
                />
              </div>
              <div>
                <Label htmlFor="data_scadenta">Data Scadentă</Label>
                <Input
                  id="data_scadenta"
                  type="date"
                  value={newFactura.data_scadenta}
                  onChange={(e) => setNewFactura({ ...newFactura, data_scadenta: e.target.value })}
                />
              </div>
              <div>
                <Label htmlFor="descriere">Descriere (opțional)</Label>
                <Textarea
                  id="descriere"
                  value={newFactura.descriere}
                  onChange={(e) => setNewFactura({ ...newFactura, descriere: e.target.value })}
                  placeholder="Detalii suplimentare..."
                />
              </div>
              <Button onClick={handleAddFactura} className="w-full">
                Adaugă Factură
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total facturi</CardTitle>
            <FileText className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{facturi.length}</div>
            <p className="text-xs text-muted-foreground">
              {facturi.filter(f => f.status === 'neprocesata').length} neprocesate
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Suma totală</CardTitle>
            <AlertCircle className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {formatCurrency(facturi.reduce((sum, f) => sum + f.suma_totala, 0))}
            </div>
            <p className="text-xs text-muted-foreground">
              Toate facturile
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Procesate</CardTitle>
            <CheckCircle className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {facturi.filter(f => f.status === 'procesata' || f.status === 'platita').length}
            </div>
            <p className="text-xs text-muted-foreground">
              din {facturi.length} total
            </p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista Facturi</CardTitle>
          <CardDescription>
            Toate facturile înregistrate în sistem
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Furnizor</TableHead>
                <TableHead>Număr</TableHead>
                <TableHead>Tip Serviciu</TableHead>
                <TableHead>Suma</TableHead>
                <TableHead>Data Facturii</TableHead>
                <TableHead>Scadență</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Acțiuni</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {facturi.map((factura) => (
                <TableRow key={factura.id}>
                  <TableCell className="font-medium">{factura.furnizor}</TableCell>
                  <TableCell>{factura.numar_factura}</TableCell>
                  <TableCell>{factura.tip_serviciu}</TableCell>
                  <TableCell>{formatCurrency(factura.suma_totala)}</TableCell>
                  <TableCell>
                    {new Date(factura.data_factura).toLocaleDateString('ro-RO')}
                  </TableCell>
                  <TableCell>
                    {new Date(factura.data_scadenta).toLocaleDateString('ro-RO')}
                  </TableCell>
                  <TableCell>{getStatusBadge(factura.status)}</TableCell>
                  <TableCell>
                    {factura.status === 'neprocesata' && (
                      <Button 
                        size="sm" 
                        onClick={() => handleProcessFactura(factura.id)}
                      >
                        <Settings className="w-4 h-4 mr-2" />
                        Procesează
                      </Button>
                    )}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  )
}
