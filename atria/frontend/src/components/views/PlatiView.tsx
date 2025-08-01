import { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { CreditCard, Euro, Calendar, CheckCircle, AlertCircle } from 'lucide-react'

interface PlataLunara {
  id: number
  user_id: number
  luna: number
  an: number
  suma_totala: number
  suma_cota_parte: number
  suma_per_persoana: number
  status: string
  data_scadenta: string
  detalii_calcul: any
}

export function PlatiView() {
  const { token } = useAuth()
  const [platiLunare, setPlatiLunare] = useState<PlataLunara[]>([])
  const [loading, setLoading] = useState(true)
  const [paymentDialog, setPaymentDialog] = useState<{ open: boolean; plata?: PlataLunara }>({ open: false })
  const [paymentForm, setPaymentForm] = useState({ suma: '', metoda_plata: 'transfer' })

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  useEffect(() => {
    fetchPlatiLunare()
  }, [])

  const fetchPlatiLunare = async () => {
    try {
      const response = await fetch(`${API_URL}/api/plati-lunare`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setPlatiLunare(data)
      }
    } catch (error) {
      console.error('Error fetching monthly payments:', error)
    } finally {
      setLoading(false)
    }
  }

  const handlePayment = async () => {
    if (!paymentDialog.plata) return

    try {
      const response = await fetch(`${API_URL}/api/plati-lunare/${paymentDialog.plata.id}/pay`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          suma: parseFloat(paymentForm.suma),
          metoda_plata: paymentForm.metoda_plata,
        }),
      })

      if (response.ok) {
        setPaymentDialog({ open: false })
        setPaymentForm({ suma: '', metoda_plata: 'transfer' })
        fetchPlatiLunare()
      }
    } catch (error) {
      console.error('Error processing payment:', error)
    }
  }

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'platita':
        return <Badge className="bg-green-100 text-green-800"><CheckCircle className="w-3 h-3 mr-1" />Plătită</Badge>
      case 'partial':
        return <Badge className="bg-yellow-100 text-yellow-800"><AlertCircle className="w-3 h-3 mr-1" />Parțial</Badge>
      default:
        return <Badge variant="destructive"><AlertCircle className="w-3 h-3 mr-1" />Neplătită</Badge>
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
          <h1 className="text-3xl font-bold text-gray-900">Plăți Lunare</h1>
          <p className="text-gray-600">Gestionează plățile pentru întreținere și servicii</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total de plată</CardTitle>
            <Euro className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {formatCurrency(platiLunare.filter(p => p.status === 'neplatita').reduce((sum, p) => sum + p.suma_totala, 0))}
            </div>
            <p className="text-xs text-muted-foreground">
              {platiLunare.filter(p => p.status === 'neplatita').length} plăți restante
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Plăți efectuate</CardTitle>
            <CheckCircle className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {platiLunare.filter(p => p.status === 'platita').length}
            </div>
            <p className="text-xs text-muted-foreground">
              din {platiLunare.length} total
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Următoarea scadență</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {platiLunare.filter(p => p.status === 'neplatita').length > 0 
                ? new Date(platiLunare.filter(p => p.status === 'neplatita')[0]?.data_scadenta).toLocaleDateString('ro-RO')
                : 'N/A'
              }
            </div>
            <p className="text-xs text-muted-foreground">
              Data limită de plată
            </p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Istoric Plăți</CardTitle>
          <CardDescription>
            Lista tuturor plăților lunare și statusul acestora
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Perioada</TableHead>
                <TableHead>Suma Totală</TableHead>
                <TableHead>Cotă Parte</TableHead>
                <TableHead>Per Persoană</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Scadență</TableHead>
                <TableHead>Acțiuni</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {platiLunare.map((plata) => (
                <TableRow key={plata.id}>
                  <TableCell className="font-medium">
                    {plata.luna}/{plata.an}
                  </TableCell>
                  <TableCell>{formatCurrency(plata.suma_totala)}</TableCell>
                  <TableCell>{formatCurrency(plata.suma_cota_parte)}</TableCell>
                  <TableCell>{formatCurrency(plata.suma_per_persoana)}</TableCell>
                  <TableCell>{getStatusBadge(plata.status)}</TableCell>
                  <TableCell>
                    {new Date(plata.data_scadenta).toLocaleDateString('ro-RO')}
                  </TableCell>
                  <TableCell>
                    {plata.status !== 'platita' && (
                      <Dialog open={paymentDialog.open && paymentDialog.plata?.id === plata.id} 
                              onOpenChange={(open) => setPaymentDialog({ open, plata: open ? plata : undefined })}>
                        <DialogTrigger asChild>
                          <Button size="sm">
                            <CreditCard className="w-4 h-4 mr-2" />
                            Plătește
                          </Button>
                        </DialogTrigger>
                        <DialogContent>
                          <DialogHeader>
                            <DialogTitle>Efectuează Plata</DialogTitle>
                            <DialogDescription>
                              Plata pentru perioada {plata.luna}/{plata.an} - {formatCurrency(plata.suma_totala)}
                            </DialogDescription>
                          </DialogHeader>
                          <div className="space-y-4">
                            <div>
                              <Label htmlFor="suma">Suma de plată</Label>
                              <Input
                                id="suma"
                                type="number"
                                step="0.01"
                                value={paymentForm.suma}
                                onChange={(e) => setPaymentForm({ ...paymentForm, suma: e.target.value })}
                                placeholder={plata.suma_totala.toString()}
                              />
                            </div>
                            <div>
                              <Label htmlFor="metoda">Metoda de plată</Label>
                              <Select value={paymentForm.metoda_plata} onValueChange={(value) => setPaymentForm({ ...paymentForm, metoda_plata: value })}>
                                <SelectTrigger>
                                  <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                  <SelectItem value="transfer">Transfer bancar</SelectItem>
                                  <SelectItem value="cash">Numerar</SelectItem>
                                  <SelectItem value="card">Card bancar</SelectItem>
                                </SelectContent>
                              </Select>
                            </div>
                            <Button onClick={handlePayment} className="w-full">
                              Confirmă Plata
                            </Button>
                          </div>
                        </DialogContent>
                      </Dialog>
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
