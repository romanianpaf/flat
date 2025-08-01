import { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Building2, MapPin, Phone, Mail, Edit, Save, X } from 'lucide-react'

interface Asociatie {
  id: number
  nume: string
  adresa: string
  cui: string
  cont_bancar: string
  banca: string
  telefon?: string
  email?: string
  presedinte_nume?: string
  administrator_nume?: string
  numar_apartamente: number
  created_at: string
}

export function AsociatieView() {
  const { user, token } = useAuth()
  const [asociatie, setAsociatie] = useState<Asociatie | null>(null)
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(false)
  const [editForm, setEditForm] = useState<Partial<Asociatie>>({})

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  useEffect(() => {
    fetchAsociatie()
  }, [])

  const fetchAsociatie = async () => {
    try {
      const response = await fetch(`${API_URL}/api/asociatie`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setAsociatie(data)
        setEditForm(data)
      }
    } catch (error) {
      console.error('Error fetching association:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleSave = async () => {
    try {
      const response = await fetch(`${API_URL}/api/asociatie`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(editForm),
      })

      if (response.ok) {
        setEditing(false)
        fetchAsociatie()
      }
    } catch (error) {
      console.error('Error updating association:', error)
    }
  }

  const handleCancel = () => {
    setEditing(false)
    setEditForm(asociatie || {})
  }

  const canEdit = user?.role && ['admin', 'presedinte'].includes(user.role)

  if (loading) {
    return <div className="flex justify-center items-center h-64">Se încarcă...</div>
  }

  if (!asociatie) {
    return <div className="flex justify-center items-center h-64">Nu s-au găsit informații despre asociație.</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Informații Asociație</h1>
          <p className="text-gray-600">Detaliile asociației de proprietari</p>
        </div>
        {canEdit && !editing && (
          <Button onClick={() => setEditing(true)}>
            <Edit className="w-4 h-4 mr-2" />
            Editează
          </Button>
        )}
        {editing && (
          <div className="flex space-x-2">
            <Button onClick={handleSave}>
              <Save className="w-4 h-4 mr-2" />
              Salvează
            </Button>
            <Button variant="outline" onClick={handleCancel}>
              <X className="w-4 h-4 mr-2" />
              Anulează
            </Button>
          </div>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <Building2 className="w-5 h-5 mr-2" />
              Informații Generale
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <Label htmlFor="nume">Nume Asociație</Label>
              {editing ? (
                <Input
                  id="nume"
                  value={editForm.nume || ''}
                  onChange={(e) => setEditForm({ ...editForm, nume: e.target.value })}
                />
              ) : (
                <p className="text-lg font-semibold">{asociatie.nume}</p>
              )}
            </div>
            
            <div>
              <Label htmlFor="adresa">Adresa</Label>
              {editing ? (
                <Textarea
                  id="adresa"
                  value={editForm.adresa || ''}
                  onChange={(e) => setEditForm({ ...editForm, adresa: e.target.value })}
                />
              ) : (
                <p className="flex items-start">
                  <MapPin className="w-4 h-4 mr-2 mt-1 text-gray-500" />
                  {asociatie.adresa}
                </p>
              )}
            </div>

            <div>
              <Label htmlFor="cui">CUI</Label>
              {editing ? (
                <Input
                  id="cui"
                  value={editForm.cui || ''}
                  onChange={(e) => setEditForm({ ...editForm, cui: e.target.value })}
                />
              ) : (
                <p className="font-mono">{asociatie.cui}</p>
              )}
            </div>

            <div>
              <Label>Număr Apartamente</Label>
              <p className="text-2xl font-bold text-blue-600">{asociatie.numar_apartamente}</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Informații Bancare</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <Label htmlFor="cont_bancar">Cont Bancar</Label>
              {editing ? (
                <Input
                  id="cont_bancar"
                  value={editForm.cont_bancar || ''}
                  onChange={(e) => setEditForm({ ...editForm, cont_bancar: e.target.value })}
                />
              ) : (
                <p className="font-mono text-lg">{asociatie.cont_bancar}</p>
              )}
            </div>

            <div>
              <Label htmlFor="banca">Banca</Label>
              {editing ? (
                <Input
                  id="banca"
                  value={editForm.banca || ''}
                  onChange={(e) => setEditForm({ ...editForm, banca: e.target.value })}
                />
              ) : (
                <p>{asociatie.banca}</p>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Contact</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <Label htmlFor="telefon">Telefon</Label>
              {editing ? (
                <Input
                  id="telefon"
                  value={editForm.telefon || ''}
                  onChange={(e) => setEditForm({ ...editForm, telefon: e.target.value })}
                />
              ) : (
                <p className="flex items-center">
                  <Phone className="w-4 h-4 mr-2 text-gray-500" />
                  {asociatie.telefon || 'Nu este specificat'}
                </p>
              )}
            </div>

            <div>
              <Label htmlFor="email">Email</Label>
              {editing ? (
                <Input
                  id="email"
                  type="email"
                  value={editForm.email || ''}
                  onChange={(e) => setEditForm({ ...editForm, email: e.target.value })}
                />
              ) : (
                <p className="flex items-center">
                  <Mail className="w-4 h-4 mr-2 text-gray-500" />
                  {asociatie.email || 'Nu este specificat'}
                </p>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Conducere</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <Label htmlFor="presedinte_nume">Președinte</Label>
              {editing ? (
                <Input
                  id="presedinte_nume"
                  value={editForm.presedinte_nume || ''}
                  onChange={(e) => setEditForm({ ...editForm, presedinte_nume: e.target.value })}
                />
              ) : (
                <p className="font-semibold">{asociatie.presedinte_nume || 'Nu este specificat'}</p>
              )}
            </div>

            <div>
              <Label htmlFor="administrator_nume">Administrator</Label>
              {editing ? (
                <Input
                  id="administrator_nume"
                  value={editForm.administrator_nume || ''}
                  onChange={(e) => setEditForm({ ...editForm, administrator_nume: e.target.value })}
                />
              ) : (
                <p className="font-semibold">{asociatie.administrator_nume || 'Nu este specificat'}</p>
              )}
            </div>

            <div>
              <Label>Data Înființării</Label>
              <p className="text-sm text-gray-600">
                {new Date(asociatie.created_at).toLocaleDateString('ro-RO')}
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Statistici Rapide</CardTitle>
          <CardDescription>
            Informații generale despre activitatea asociației
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="text-center p-4 bg-blue-50 rounded-lg">
              <div className="text-2xl font-bold text-blue-600">{asociatie.numar_apartamente}</div>
              <div className="text-sm text-gray-600">Apartamente</div>
            </div>
            <div className="text-center p-4 bg-green-50 rounded-lg">
              <div className="text-2xl font-bold text-green-600">100%</div>
              <div className="text-sm text-gray-600">Ocupare</div>
            </div>
            <div className="text-center p-4 bg-yellow-50 rounded-lg">
              <div className="text-2xl font-bold text-yellow-600">12</div>
              <div className="text-sm text-gray-600">Luni active</div>
            </div>
            <div className="text-center p-4 bg-purple-50 rounded-lg">
              <div className="text-2xl font-bold text-purple-600">RON</div>
              <div className="text-sm text-gray-600">Moneda</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
