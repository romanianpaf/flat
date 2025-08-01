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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { Settings, Plus, Play, Wifi, Lock } from 'lucide-react'

interface AutomatizareIoT {
  id: number
  nume: string
  tip: string
  endpoint_mqtt: string
  configuratie: any
  acces_roluri: string[]
  is_active: boolean
  created_at: string
}

export function AutomatizariView() {
  const { user, token } = useAuth()
  const [automatizari, setAutomatizari] = useState<AutomatizareIoT[]>([])
  const [loading, setLoading] = useState(true)
  const [addDialog, setAddDialog] = useState(false)
  const [triggerDialog, setTriggerDialog] = useState<{ open: boolean; automatizare?: AutomatizareIoT }>({ open: false })
  const [newAutomatizare, setNewAutomatizare] = useState({
    nume: '',
    tip: '',
    endpoint_mqtt: '',
    configuratie: '{}',
    acces_roluri: [] as string[]
  })

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  const tipuriAutomatizare = [
    'piscina',
    'bariera',
    'iluminat',
    'interfon',
    'lift',
    'altele'
  ]

  const roleLabels = {
    'admin': 'Administrator',
    'presedinte': 'Președinte',
    'contabil': 'Contabil',
    'locatar': 'Locatar',
    'moderator': 'Moderator'
  }

  useEffect(() => {
    fetchAutomatizari()
  }, [])

  const fetchAutomatizari = async () => {
    try {
      const response = await fetch(`${API_URL}/api/automatizari`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setAutomatizari(data)
      }
    } catch (error) {
      console.error('Error fetching automations:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleAddAutomatizare = async () => {
    try {
      const response = await fetch(`${API_URL}/api/automatizari`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...newAutomatizare,
          configuratie: JSON.parse(newAutomatizare.configuratie || '{}')
        }),
      })

      if (response.ok) {
        setAddDialog(false)
        setNewAutomatizare({
          nume: '',
          tip: '',
          endpoint_mqtt: '',
          configuratie: '{}',
          acces_roluri: []
        })
        fetchAutomatizari()
      }
    } catch (error) {
      console.error('Error adding automation:', error)
    }
  }

  const handleTriggerAutomatizare = async (command: any) => {
    if (!triggerDialog.automatizare) return

    try {
      const response = await fetch(`${API_URL}/api/automatizari/${triggerDialog.automatizare.id}/trigger`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(command),
      })

      if (response.ok) {
        setTriggerDialog({ open: false })
      }
    } catch (error) {
      console.error('Error triggering automation:', error)
    }
  }

  const handleRoleChange = (role: string, checked: boolean) => {
    if (checked) {
      setNewAutomatizare({
        ...newAutomatizare,
        acces_roluri: [...newAutomatizare.acces_roluri, role]
      })
    } else {
      setNewAutomatizare({
        ...newAutomatizare,
        acces_roluri: newAutomatizare.acces_roluri.filter(r => r !== role)
      })
    }
  }

  const getStatusBadge = (isActive: boolean) => {
    return isActive 
      ? <Badge className="bg-green-100 text-green-800">Activă</Badge>
      : <Badge className="bg-gray-100 text-gray-800">Inactivă</Badge>
  }

  const getTipBadge = (tip: string) => {
    return <Badge variant="outline" className="capitalize">{tip}</Badge>
  }

  const canManage = user?.role && ['admin', 'presedinte'].includes(user.role)

  if (loading) {
    return <div className="flex justify-center items-center h-64">Se încarcă...</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Automatizări IoT</h1>
          <p className="text-gray-600">Controlează dispozitivele inteligente ale asociației</p>
        </div>
        {canManage && (
          <Dialog open={addDialog} onOpenChange={setAddDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="w-4 h-4 mr-2" />
                Adaugă Automatizare
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Automatizare Nouă</DialogTitle>
                <DialogDescription>
                  Configurează o nouă automatizare IoT
                </DialogDescription>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="nume">Nume</Label>
                  <Input
                    id="nume"
                    value={newAutomatizare.nume}
                    onChange={(e) => setNewAutomatizare({ ...newAutomatizare, nume: e.target.value })}
                    placeholder="Numele automatizării"
                  />
                </div>
                <div>
                  <Label htmlFor="tip">Tip</Label>
                  <Select value={newAutomatizare.tip} onValueChange={(value) => setNewAutomatizare({ ...newAutomatizare, tip: value })}>
                    <SelectTrigger>
                      <SelectValue placeholder="Selectează tipul" />
                    </SelectTrigger>
                    <SelectContent>
                      {tipuriAutomatizare.map(tip => (
                        <SelectItem key={tip} value={tip} className="capitalize">{tip}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label htmlFor="endpoint_mqtt">Endpoint MQTT</Label>
                  <Input
                    id="endpoint_mqtt"
                    value={newAutomatizare.endpoint_mqtt}
                    onChange={(e) => setNewAutomatizare({ ...newAutomatizare, endpoint_mqtt: e.target.value })}
                    placeholder="mqtt://broker.example.com:1883/topic"
                  />
                </div>
                <div>
                  <Label htmlFor="configuratie">Configurație JSON</Label>
                  <Textarea
                    id="configuratie"
                    value={newAutomatizare.configuratie}
                    onChange={(e) => setNewAutomatizare({ ...newAutomatizare, configuratie: e.target.value })}
                    placeholder='{"key": "value"}'
                  />
                </div>
                <div>
                  <Label>Acces pentru rolurile:</Label>
                  <div className="space-y-2 mt-2">
                    {Object.entries(roleLabels).map(([role, label]) => (
                      <div key={role} className="flex items-center space-x-2">
                        <Checkbox
                          id={role}
                          checked={newAutomatizare.acces_roluri.includes(role)}
                          onCheckedChange={(checked) => handleRoleChange(role, checked as boolean)}
                        />
                        <Label htmlFor={role}>{label}</Label>
                      </div>
                    ))}
                  </div>
                </div>
                <Button onClick={handleAddAutomatizare} className="w-full">
                  Adaugă Automatizare
                </Button>
              </div>
            </DialogContent>
          </Dialog>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total automatizări</CardTitle>
            <Settings className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{automatizari.length}</div>
            <p className="text-xs text-muted-foreground">
              Configurate
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active</CardTitle>
            <Wifi className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {automatizari.filter(a => a.is_active).length}
            </div>
            <p className="text-xs text-muted-foreground">
              Funcționale
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Accesibile</CardTitle>
            <Lock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {automatizari.filter(a => a.acces_roluri.includes(user?.role || '')).length}
            </div>
            <p className="text-xs text-muted-foreground">
              Pentru tine
            </p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista Automatizări</CardTitle>
          <CardDescription>
            Toate automatizările IoT configurate
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nume</TableHead>
                <TableHead>Tip</TableHead>
                <TableHead>Endpoint MQTT</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Acces Roluri</TableHead>
                <TableHead>Acțiuni</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {automatizari.map((automatizare) => (
                <TableRow key={automatizare.id}>
                  <TableCell className="font-medium">{automatizare.nume}</TableCell>
                  <TableCell>{getTipBadge(automatizare.tip)}</TableCell>
                  <TableCell className="font-mono text-sm">{automatizare.endpoint_mqtt}</TableCell>
                  <TableCell>{getStatusBadge(automatizare.is_active)}</TableCell>
                  <TableCell>
                    <div className="flex flex-wrap gap-1">
                      {automatizare.acces_roluri.map(role => (
                        <Badge key={role} variant="secondary" className="text-xs">
                          {roleLabels[role as keyof typeof roleLabels] || role}
                        </Badge>
                      ))}
                    </div>
                  </TableCell>
                  <TableCell>
                    {automatizare.acces_roluri.includes(user?.role || '') && automatizare.is_active && (
                      <Dialog 
                        open={triggerDialog.open && triggerDialog.automatizare?.id === automatizare.id} 
                        onOpenChange={(open) => setTriggerDialog({ open, automatizare: open ? automatizare : undefined })}
                      >
                        <DialogTrigger asChild>
                          <Button size="sm">
                            <Play className="w-4 h-4 mr-2" />
                            Activează
                          </Button>
                        </DialogTrigger>
                        <DialogContent>
                          <DialogHeader>
                            <DialogTitle>Activează {automatizare.nume}</DialogTitle>
                            <DialogDescription>
                              Trimite comandă către dispozitivul IoT
                            </DialogDescription>
                          </DialogHeader>
                          <div className="space-y-4">
                            <div className="p-4 bg-gray-50 rounded-lg">
                              <p className="text-sm text-gray-600">Endpoint: {automatizare.endpoint_mqtt}</p>
                              <p className="text-sm text-gray-600">Tip: {automatizare.tip}</p>
                            </div>
                            <div className="flex space-x-2">
                              <Button 
                                onClick={() => handleTriggerAutomatizare({ action: 'open' })}
                                className="flex-1"
                              >
                                Deschide
                              </Button>
                              <Button 
                                onClick={() => handleTriggerAutomatizare({ action: 'close' })}
                                variant="outline"
                                className="flex-1"
                              >
                                Închide
                              </Button>
                            </div>
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

      {automatizari.length === 0 && (
        <Card>
          <CardContent className="text-center py-8">
            <Settings className="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <p className="text-gray-500">Nu există automatizări configurate încă.</p>
            {canManage && (
              <p className="text-sm text-gray-400">Adaugă prima automatizare IoT!</p>
            )}
          </CardContent>
        </Card>
      )}
    </div>
  )
}
