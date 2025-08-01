import React, { useState, useEffect, useRef } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Send, MessageCircle } from 'lucide-react'

interface ChatMessage {
  id: number
  user_id: number
  mesaj: string
  tip_mesaj: string
  created_at: string
  user?: {
    nume: string
    prenume: string
    role: string
  }
}

export function ChatView() {
  const { user, token } = useAuth()
  const [messages, setMessages] = useState<ChatMessage[]>([])
  const [newMessage, setNewMessage] = useState('')
  const [loading, setLoading] = useState(true)
  const messagesEndRef = useRef<HTMLDivElement>(null)

  const API_URL = (import.meta as any).env.VITE_API_URL || 'http://localhost:8000'

  useEffect(() => {
    fetchMessages()
    const interval = setInterval(fetchMessages, 5000) // Poll every 5 seconds
    return () => clearInterval(interval)
  }, [])

  useEffect(() => {
    scrollToBottom()
  }, [messages])

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }

  const fetchMessages = async () => {
    try {
      const response = await fetch(`${API_URL}/api/chat/messages`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      })
      if (response.ok) {
        const data = await response.json()
        const messagesWithUsers = data.map((msg: ChatMessage) => ({
          ...msg,
          user: {
            nume: msg.user_id === 1 ? 'Popescu' : msg.user_id === 2 ? 'Ionescu' : 'Georgescu',
            prenume: msg.user_id === 1 ? 'Ion' : msg.user_id === 2 ? 'Maria' : 'Andrei',
            role: msg.user_id === 1 ? 'admin' : msg.user_id === 2 ? 'presedinte' : 'locatar'
          }
        }))
        setMessages(messagesWithUsers)
      }
    } catch (error) {
      console.error('Error fetching messages:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleSendMessage = async () => {
    if (!newMessage.trim()) return

    try {
      const response = await fetch(`${API_URL}/api/chat/messages`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
          mesaj: newMessage,
          tip_mesaj: 'text'
        }),
      })

      if (response.ok) {
        setNewMessage('')
        fetchMessages()
      }
    } catch (error) {
      console.error('Error sending message:', error)
    }
  }

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      handleSendMessage()
    }
  }

  const getRoleColor = (role: string) => {
    switch (role) {
      case 'admin':
        return 'bg-red-100 text-red-800'
      case 'presedinte':
        return 'bg-blue-100 text-blue-800'
      case 'contabil':
        return 'bg-green-100 text-green-800'
      case 'moderator':
        return 'bg-purple-100 text-purple-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  const getInitials = (nume: string, prenume: string) => {
    return `${prenume.charAt(0)}${nume.charAt(0)}`.toUpperCase()
  }

  if (loading) {
    return <div className="flex justify-center items-center h-64">Se încarcă...</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Chat Asociație</h1>
          <p className="text-gray-600">Comunicare în timp real cu vecinii</p>
        </div>
      </div>

      <Card className="h-[600px] flex flex-col">
        <CardHeader className="flex-shrink-0">
          <CardTitle className="flex items-center">
            <MessageCircle className="w-5 h-5 mr-2" />
            Chat General
          </CardTitle>
        </CardHeader>
        
        <CardContent className="flex-1 flex flex-col p-0">
          <div className="flex-1 overflow-y-auto p-4 space-y-4">
            {messages.length === 0 ? (
              <div className="text-center text-gray-500 py-8">
                <MessageCircle className="w-12 h-12 mx-auto mb-4 text-gray-300" />
                <p>Nu există mesaje încă. Fii primul care începe conversația!</p>
              </div>
            ) : (
              messages.map((message) => (
                <div
                  key={message.id}
                  className={`flex ${message.user_id === user?.id ? 'justify-end' : 'justify-start'}`}
                >
                  <div className={`flex max-w-xs lg:max-w-md ${message.user_id === user?.id ? 'flex-row-reverse' : 'flex-row'}`}>
                    <Avatar className="w-8 h-8">
                      <AvatarFallback className={getRoleColor(message.user?.role || 'locatar')}>
                        {getInitials(message.user?.nume || 'U', message.user?.prenume || 'U')}
                      </AvatarFallback>
                    </Avatar>
                    <div className={`mx-2 ${message.user_id === user?.id ? 'text-right' : 'text-left'}`}>
                      <div className="text-xs text-gray-500 mb-1">
                        {message.user?.prenume} {message.user?.nume}
                        <span className="ml-2">
                          {new Date(message.created_at).toLocaleTimeString('ro-RO', {
                            hour: '2-digit',
                            minute: '2-digit'
                          })}
                        </span>
                      </div>
                      <div
                        className={`rounded-lg px-3 py-2 ${
                          message.user_id === user?.id
                            ? 'bg-blue-500 text-white'
                            : 'bg-gray-100 text-gray-900'
                        }`}
                      >
                        {message.mesaj}
                      </div>
                    </div>
                  </div>
                </div>
              ))
            )}
            <div ref={messagesEndRef} />
          </div>
          
          <div className="border-t p-4">
            <div className="flex space-x-2">
              <Input
                value={newMessage}
                onChange={(e) => setNewMessage(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="Scrie un mesaj..."
                className="flex-1"
              />
              <Button onClick={handleSendMessage} disabled={!newMessage.trim()}>
                <Send className="w-4 h-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
