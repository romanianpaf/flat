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
import { Users, Plus, UserCheck, UserX } from 'lucide-react'

interface User {
  id: number
  email: string
  nume: string
  prenume: string
  telefon?: string
  apartament?: string
  cota_parte_indiviza?: number
  numar_persoane?: number
  role: string
  asociatie_id: number
  is_active: boolean
}

export function UtilizatoriView() {
  const { user, token } = useAuth()
  const [utilizatori, setUtilizatori] = useState<User[]>([])
  const [loading, setLoading] = useState(true)
  const [addDialog, setAddDialog] = useState(false)
  const [newUser, setNewUser] = useState({
    email: '',
    nume: '',
    prenume: '',
    telefon: '',
    apartament: '',
    cota_parte_indiviza: '',
    numar_persoane: '',
    role: 'locatar'
  })

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  const roles = [
    { value: 'admin', label: 'Administrator' },
    { value: 'presedinte', label: 'Președinte' },
    { value: 'contabil', label: 'Contabil' },
    { value: 'locatar', label: 'Locatar' },
    { value: 'moderator', label: 'Moderator' }
  ]

  useEffect(() => {
    fetchUtilizatori()
  }, [])

  const fetchUtilizatori = async () => {
    try {
      const response = await fetch(`${API_URL}/api/users`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setUtilizatori(data)
      }
    } catch (error) {
      console.error('Error fetching users:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleAddUser = async () => {
    try {
      const response = await fetch(`${API_URL}/api/users`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...newUser,
          cota_parte_indiviza: newUser.cota_parte_indiviza ? parseFloat(newUser.cota_parte_indiviza) : null,
          numar_persoane: newUser.numar_persoane ? parseInt(newUser.numar_persoane) : null,
        }),
      })

      if (response.ok) {
        setAddDialog(false)
        setNewUser({
          email: '',
          nume: '',
          prenume: '',
          telefon: '',
          apartament: '',
          cota_parte_indiviza: '',
          numar_persoane: '',
          role: 'locatar'
        })
        fetchUtilizatori()
      }
    } catch (error) {
      console.error('Error adding user:', error)
    }
  }

  const handleToggleUserStatus = async (userId: number, isActive: boolean) => {
    try {
      const response = await fetch(`${API_URL}/api/users/${userId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({ is_active: !isActive }),
      })

      if (response.ok) {
        fetchUtilizatori()
      }
    } catch (error) {
      console.error('Error updating user status:', error)
    }
  }

  const getRoleBadge = (role: string) => {
    const roleColors = {
      'admin': 'bg-red-100 text-red-800',
      'presedinte': 'bg-blue-100 text-blue-800',
      'contabil': 'bg-green-100 text-green-800',
      'moderator': 'bg-purple-100 text-purple-800',
      'locatar': 'bg-gray-100 text-gray-800'
    }
    
    const roleLabels = {
      'admin': 'Administrator',
      'presedinte': 'Președinte',
      'contabil': 'Contabil',
      'moderator': 'Moderator',
      'locatar': 'Locatar'
    }

    return (
      <Badge className={roleColors[role as keyof typeof roleColors] || 'bg-gray-100 text-gray-800'}>
        {roleLabels[role as keyof typeof roleLabels] || role}
      </Badge>
    )
  }

  const getStatusBadge = (isActive: boolean) => {
    return isActive 
      ? <Badge className="bg-green-100 text-green-800"><UserCheck className="w-3 h-3 mr-1" />Activ</Badge>
      : <Badge className="bg-red-100 text-red-800"><UserX className="w-3 h-3 mr-1" />Inactiv</Badge>
  }

  const canManage = user?.role && ['admin', 'presedinte'].includes(user.role)

  if (loading) {
    return <div className="flex justify-center items-center h-64">Se încarcă...</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Utilizatori</h1>
          <p className="text-gray-600">Gestionează utilizatorii asociației</p>
        </div>
        {canManage && (
          <Dialog open={addDialog} onOpenChange={setAddDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="w-4 h-4 mr-2" />
                Adaugă Utilizator
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Utilizator Nou</DialogTitle>
                <DialogDescription>
                  Adaugă un nou utilizator în asociație
                </DialogDescription>
              </DialogHeader>
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="prenume">Prenume</Label>
                    <Input
                      id="prenume"
                      value={newUser.prenume}
                      onChange={(e) => setNewUser({ ...newUser, prenume: e.target.value })}
                      placeholder="Prenume"
                    />
                  </div>
                  <div>
                    <Label htmlFor="nume">Nume</Label>
                    <Input
                      id="nume"
                      value={newUser.nume}
                      onChange={(e) => setNewUser({ ...newUser, nume: e.target.value })}
                      placeholder="Nume"
                    />
                  </div>
                </div>
                <div>
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    value={newUser.email}
                    onChange={(e) => setNewUser({ ...newUser, email: e.target.value })}
                    placeholder="email@exemplu.ro"
                  />
                </div>
                <div>
                  <Label htmlFor="telefon">Telefon</Label>
                  <Input
                    id="telefon"
                    value={newUser.telefon}
                    onChange={(e) => setNewUser({ ...newUser, telefon: e.target.value })}
                    placeholder="0712345678"
                  />
                </div>
                <div>
                  <Label htmlFor="apartament">Apartament</Label>
                  <Input
                    id="apartament"
                    value={newUser.apartament}
                    onChange={(e) => setNewUser({ ...newUser, apartament: e.target.value })}
                    placeholder="Ap. 12"
                  />
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="cota_parte">Cotă Parte (%)</Label>
                    <Input
                      id="cota_parte"
                      type="number"
                      step="0.01"
                      value={newUser.cota_parte_indiviza}
                      onChange={(e) => setNewUser({ ...newUser, cota_parte_indiviza: e.target.value })}
                      placeholder="2.5"
                    />
                  </div>
                  <div>
                    <Label htmlFor="numar_persoane">Nr. Persoane</Label>
                    <Input
                      id="numar_persoane"
                      type="number"
                      value={newUser.numar_persoane}
                      onChange={(e) => setNewUser({ ...newUser, numar_persoane: e.target.value })}
                      placeholder="2"
                    />
                  </div>
                </div>
                <div>
                  <Label htmlFor="role">Rol</Label>
                  <Select value={newUser.role} onValueChange={(value) => setNewUser({ ...newUser, role: value })}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {roles.map(role => (
                        <SelectItem key={role.value} value={role.value}>{role.label}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <Button onClick={handleAddUser} className="w-full">
                  Adaugă Utilizator
                </Button>
              </div>
            </DialogContent>
          </Dialog>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total utilizatori</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{utilizatori.length}</div>
            <p className="text-xs text-muted-foreground">
              Înregistrați
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Activi</CardTitle>
            <UserCheck className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {utilizatori.filter(u => u.is_active).length}
            </div>
            <p className="text-xs text-muted-foreground">
              Utilizatori activi
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Locatari</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {utilizatori.filter(u => u.role === 'locatar').length}
            </div>
            <p className="text-xs text-muted-foreground">
              Proprietari/Locatari
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Administratori</CardTitle>
            <UserCheck className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {utilizatori.filter(u => ['admin', 'presedinte', 'contabil'].includes(u.role)).length}
            </div>
            <p className="text-xs text-muted-foreground">
              Roluri administrative
            </p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista Utilizatori</CardTitle>
          <CardDescription>
            Toți utilizatorii înregistrați în asociație
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nume</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Apartament</TableHead>
                <TableHead>Cotă Parte</TableHead>
                <TableHead>Nr. Persoane</TableHead>
                <TableHead>Rol</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Acțiuni</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {utilizatori.map((utilizator) => (
                <TableRow key={utilizator.id}>
                  <TableCell className="font-medium">
                    {utilizator.prenume} {utilizator.nume}
                  </TableCell>
                  <TableCell>{utilizator.email}</TableCell>
                  <TableCell>{utilizator.apartament || 'N/A'}</TableCell>
                  <TableCell>
                    {utilizator.cota_parte_indiviza ? `${utilizator.cota_parte_indiviza}%` : 'N/A'}
                  </TableCell>
                  <TableCell>{utilizator.numar_persoane || 'N/A'}</TableCell>
                  <TableCell>{getRoleBadge(utilizator.role)}</TableCell>
                  <TableCell>{getStatusBadge(utilizator.is_active)}</TableCell>
                  <TableCell>
                    {canManage && utilizator.id !== user?.id && (
                      <div className="flex space-x-2">
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => handleToggleUserStatus(utilizator.id, utilizator.is_active)}
                        >
                          {utilizator.is_active ? 'Dezactivează' : 'Activează'}
                        </Button>
                      </div>
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
