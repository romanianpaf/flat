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
import { FileText, Upload, Download, FolderOpen, Plus } from 'lucide-react'

interface Document {
  id: number
  nume: string
  descriere?: string
  fisier_url: string
  tip_document: string
  acces_roluri: string[]
  uploaded_by: number
  created_at: string
}

export function DocumenteView() {
  const { user, token } = useAuth()
  const [documente, setDocumente] = useState<Document[]>([])
  const [loading, setLoading] = useState(true)
  const [uploadDialog, setUploadDialog] = useState(false)
  const [newDocument, setNewDocument] = useState({
    nume: '',
    descriere: '',
    tip_document: '',
    acces_roluri: [] as string[]
  })

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  const roleLabels = {
    'admin': 'Administrator',
    'presedinte': 'Președinte',
    'contabil': 'Contabil',
    'locatar': 'Locatar',
    'moderator': 'Moderator'
  }

  const documentTypes = [
    'Proces verbal',
    'Hotărâre AGA',
    'Contract',
    'Factură',
    'Regulament',
    'Altele'
  ]

  useEffect(() => {
    fetchDocumente()
  }, [])

  const fetchDocumente = async () => {
    try {
      const response = await fetch(`${API_URL}/api/documente`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        setDocumente(data)
      }
    } catch (error) {
      console.error('Error fetching documents:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleUploadDocument = async () => {
    try {
      const response = await fetch(`${API_URL}/api/documente`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          ...newDocument,
          fisier_url: `https://example.com/documents/${newDocument.nume.replace(/\s+/g, '_')}.pdf`
        }),
      })

      if (response.ok) {
        setUploadDialog(false)
        setNewDocument({
          nume: '',
          descriere: '',
          tip_document: '',
          acces_roluri: []
        })
        fetchDocumente()
      }
    } catch (error) {
      console.error('Error uploading document:', error)
    }
  }

  const handleRoleChange = (role: string, checked: boolean) => {
    if (checked) {
      setNewDocument({
        ...newDocument,
        acces_roluri: [...newDocument.acces_roluri, role]
      })
    } else {
      setNewDocument({
        ...newDocument,
        acces_roluri: newDocument.acces_roluri.filter(r => r !== role)
      })
    }
  }

  const canUpload = user?.role && ['admin', 'presedinte', 'contabil'].includes(user.role)

  if (loading) {
    return <div className="flex justify-center items-center h-64">Se încarcă...</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Documente</h1>
          <p className="text-gray-600">Biblioteca de documente a asociației</p>
        </div>
        {canUpload && (
          <Dialog open={uploadDialog} onOpenChange={setUploadDialog}>
            <DialogTrigger asChild>
              <Button>
                <Upload className="w-4 h-4 mr-2" />
                Încarcă Document
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Încarcă Document Nou</DialogTitle>
                <DialogDescription>
                  Adaugă un document în biblioteca asociației
                </DialogDescription>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="nume">Nume Document</Label>
                  <Input
                    id="nume"
                    value={newDocument.nume}
                    onChange={(e) => setNewDocument({ ...newDocument, nume: e.target.value })}
                    placeholder="Numele documentului"
                  />
                </div>
                <div>
                  <Label htmlFor="tip_document">Tip Document</Label>
                  <Select value={newDocument.tip_document} onValueChange={(value) => setNewDocument({ ...newDocument, tip_document: value })}>
                    <SelectTrigger>
                      <SelectValue placeholder="Selectează tipul" />
                    </SelectTrigger>
                    <SelectContent>
                      {documentTypes.map(type => (
                        <SelectItem key={type} value={type}>{type}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label htmlFor="descriere">Descriere</Label>
                  <Textarea
                    id="descriere"
                    value={newDocument.descriere}
                    onChange={(e) => setNewDocument({ ...newDocument, descriere: e.target.value })}
                    placeholder="Descrierea documentului..."
                  />
                </div>
                <div>
                  <Label>Acces pentru rolurile:</Label>
                  <div className="space-y-2 mt-2">
                    {Object.entries(roleLabels).map(([role, label]) => (
                      <div key={role} className="flex items-center space-x-2">
                        <Checkbox
                          id={role}
                          checked={newDocument.acces_roluri.includes(role)}
                          onCheckedChange={(checked) => handleRoleChange(role, checked as boolean)}
                        />
                        <Label htmlFor={role}>{label}</Label>
                      </div>
                    ))}
                  </div>
                </div>
                <Button onClick={handleUploadDocument} className="w-full">
                  Încarcă Document
                </Button>
              </div>
            </DialogContent>
          </Dialog>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total documente</CardTitle>
            <FolderOpen className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{documente.length}</div>
            <p className="text-xs text-muted-foreground">
              Disponibile pentru tine
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Tipuri documente</CardTitle>
            <FileText className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {new Set(documente.map(d => d.tip_document)).size}
            </div>
            <p className="text-xs text-muted-foreground">
              Categorii diferite
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Adăugate recent</CardTitle>
            <Plus className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {documente.filter(d => {
                const weekAgo = new Date()
                weekAgo.setDate(weekAgo.getDate() - 7)
                return new Date(d.created_at) > weekAgo
              }).length}
            </div>
            <p className="text-xs text-muted-foreground">
              În ultima săptămână
            </p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Lista Documente</CardTitle>
          <CardDescription>
            Toate documentele la care ai acces
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nume Document</TableHead>
                <TableHead>Tip</TableHead>
                <TableHead>Descriere</TableHead>
                <TableHead>Acces Roluri</TableHead>
                <TableHead>Data Adăugării</TableHead>
                <TableHead>Acțiuni</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {documente.map((document) => (
                <TableRow key={document.id}>
                  <TableCell className="font-medium">{document.nume}</TableCell>
                  <TableCell>
                    <Badge variant="outline">{document.tip_document}</Badge>
                  </TableCell>
                  <TableCell className="max-w-xs truncate">
                    {document.descriere || 'Fără descriere'}
                  </TableCell>
                  <TableCell>
                    <div className="flex flex-wrap gap-1">
                      {document.acces_roluri.map(role => (
                        <Badge key={role} variant="secondary" className="text-xs">
                          {roleLabels[role as keyof typeof roleLabels] || role}
                        </Badge>
                      ))}
                    </div>
                  </TableCell>
                  <TableCell>
                    {new Date(document.created_at).toLocaleDateString('ro-RO')}
                  </TableCell>
                  <TableCell>
                    <Button size="sm" variant="outline">
                      <Download className="w-4 h-4 mr-2" />
                      Descarcă
                    </Button>
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
