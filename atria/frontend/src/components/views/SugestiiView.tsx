import { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Lightbulb, Plus, ThumbsUp, ThumbsDown, MessageSquare } from 'lucide-react'

interface Sugestie {
  id: number
  user_id: number
  titlu: string
  descriere: string
  categorie: string
  voturi_pro: number
  voturi_contra: number
  status: string
  created_at: string
}

export function SugestiiView() {
  const { token } = useAuth()
  const [sugestii, setSugestii] = useState<Sugestie[]>([])
  const [loading, setLoading] = useState(true)
  const [addDialog, setAddDialog] = useState(false)
  const [newSugestie, setNewSugestie] = useState({
    titlu: '',
    descriere: '',
    categorie: ''
  })

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  const categorii = [
    'Întreținere',
    'Securitate',
    'Spații comune',
    'Regulament',
    'Îmbunătățiri',
    'Altele'
  ]

  useEffect(() => {
    fetchSugestii()
  }, [])

  const fetchSugestii = async () => {
    try {
      const response = await fetch(`${API_URL}/api/sugestii`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setSugestii(data)
      }
    } catch (error) {
      console.error('Error fetching suggestions:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleAddSugestie = async () => {
    try {
      const response = await fetch(`${API_URL}/api/sugestii`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(newSugestie),
      })

      if (response.ok) {
        setAddDialog(false)
        setNewSugestie({
          titlu: '',
          descriere: '',
          categorie: ''
        })
        fetchSugestii()
      }
    } catch (error) {
      console.error('Error adding suggestion:', error)
    }
  }

  const handleVote = async (sugestieId: number, vot: string) => {
    try {
      const response = await fetch(`${API_URL}/api/sugestii/${sugestieId}/vote`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({ vot }),
      })

      if (response.ok) {
        fetchSugestii()
      }
    } catch (error) {
      console.error('Error voting:', error)
    }
  }

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'implementata':
        return <Badge className="bg-green-100 text-green-800">Implementată</Badge>
      case 'respinsa':
        return <Badge className="bg-red-100 text-red-800">Respinsă</Badge>
      default:
        return <Badge className="bg-blue-100 text-blue-800">Activă</Badge>
    }
  }

  const getCategoryBadge = (categorie: string) => {
    return <Badge variant="outline">{categorie}</Badge>
  }

  if (loading) {
    return <div className="flex justify-center items-center h-64">Se încarcă...</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Sugestii</h1>
          <p className="text-gray-600">Vocea comunității - propune îmbunătățiri</p>
        </div>
        <Dialog open={addDialog} onOpenChange={setAddDialog}>
          <DialogTrigger asChild>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Adaugă Sugestie
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Sugestie Nouă</DialogTitle>
              <DialogDescription>
                Propune o îmbunătățire pentru asociație
              </DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="titlu">Titlu</Label>
                <Input
                  id="titlu"
                  value={newSugestie.titlu}
                  onChange={(e) => setNewSugestie({ ...newSugestie, titlu: e.target.value })}
                  placeholder="Titlul sugestiei"
                />
              </div>
              <div>
                <Label htmlFor="categorie">Categorie</Label>
                <Select value={newSugestie.categorie} onValueChange={(value) => setNewSugestie({ ...newSugestie, categorie: value })}>
                  <SelectTrigger>
                    <SelectValue placeholder="Selectează categoria" />
                  </SelectTrigger>
                  <SelectContent>
                    {categorii.map(cat => (
                      <SelectItem key={cat} value={cat}>{cat}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label htmlFor="descriere">Descriere</Label>
                <Textarea
                  id="descriere"
                  value={newSugestie.descriere}
                  onChange={(e) => setNewSugestie({ ...newSugestie, descriere: e.target.value })}
                  placeholder="Descrierea detaliată a sugestiei..."
                />
              </div>
              <Button onClick={handleAddSugestie} className="w-full">
                Trimite Sugestie
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total sugestii</CardTitle>
            <Lightbulb className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{sugestii.length}</div>
            <p className="text-xs text-muted-foreground">
              Toate sugestiile
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active</CardTitle>
            <MessageSquare className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {sugestii.filter(s => s.status === 'activa').length}
            </div>
            <p className="text-xs text-muted-foreground">
              În discuție
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Implementate</CardTitle>
            <ThumbsUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {sugestii.filter(s => s.status === 'implementata').length}
            </div>
            <p className="text-xs text-muted-foreground">
              Realizate
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Respinse</CardTitle>
            <ThumbsDown className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {sugestii.filter(s => s.status === 'respinsa').length}
            </div>
            <p className="text-xs text-muted-foreground">
              Nu se implementează
            </p>
          </CardContent>
        </Card>
      </div>

      <div className="space-y-4">
        {sugestii.map((sugestie) => (
          <Card key={sugestie.id}>
            <CardHeader>
              <div className="flex items-start justify-between">
                <div className="space-y-2">
                  <CardTitle className="text-lg">{sugestie.titlu}</CardTitle>
                  <div className="flex items-center space-x-2">
                    {getCategoryBadge(sugestie.categorie)}
                    {getStatusBadge(sugestie.status)}
                  </div>
                </div>
                <div className="text-sm text-gray-500">
                  {new Date(sugestie.created_at).toLocaleDateString('ro-RO')}
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <p className="text-gray-700 mb-4">{sugestie.descriere}</p>
              
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4">
                  <div className="flex items-center space-x-2">
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => handleVote(sugestie.id, 'pro')}
                      className="flex items-center space-x-1"
                    >
                      <ThumbsUp className="w-4 h-4" />
                      <span>{sugestie.voturi_pro}</span>
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => handleVote(sugestie.id, 'contra')}
                      className="flex items-center space-x-1"
                    >
                      <ThumbsDown className="w-4 h-4" />
                      <span>{sugestie.voturi_contra}</span>
                    </Button>
                  </div>
                </div>
                
                <div className="text-sm text-gray-500">
                  Total voturi: {sugestie.voturi_pro + sugestie.voturi_contra}
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {sugestii.length === 0 && (
        <Card>
          <CardContent className="text-center py-8">
            <Lightbulb className="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <p className="text-gray-500">Nu există sugestii încă.</p>
            <p className="text-sm text-gray-400">Fii primul care propune o îmbunătățire!</p>
          </CardContent>
        </Card>
      )}
    </div>
  )
}
