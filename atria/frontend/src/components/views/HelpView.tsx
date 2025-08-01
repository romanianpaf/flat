import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { HelpCircle, Phone, Mail, MessageCircle, FileText, CreditCard, Users } from 'lucide-react'

export function HelpView() {
  const faqItems = [
    {
      question: "Cum calculez plata lunară?",
      answer: "Plata lunară se calculează pe baza cotei părți indivize și a numărului de persoane din apartament. Sistemul face automat acest calcul când sunt procesate facturile.",
      category: "Plăți"
    },
    {
      question: "Cum pot încărca documente?",
      answer: "Documentele pot fi încărcate din secțiunea 'Documente'. Accesul la documente depinde de rolul tău în asociație.",
      category: "Documente"
    },
    {
      question: "Cum funcționează chat-ul?",
      answer: "Chat-ul permite comunicarea în timp real între toți membrii asociației. Mesajele sunt vizibile pentru toți utilizatorii activi.",
      category: "Chat"
    },
    {
      question: "Ce roluri există în sistem?",
      answer: "Sistemul are următoarele roluri: Administrator, Președinte, Contabil, Moderator și Locatar. Fiecare rol are acces la funcționalități specifice.",
      category: "Utilizatori"
    },
    {
      question: "Cum pot publica un anunț în marketplace?",
      answer: "Din secțiunea 'Marketplace' poți adăuga anunțuri pentru servicii, închirieri sau vânzări. Completează formularul cu detaliile anunțului.",
      category: "Marketplace"
    },
    {
      question: "Cum fac o sugestie?",
      answer: "În secțiunea 'Sugestii' poți propune îmbunătățiri pentru asociație. Alți membri pot vota sugestiile tale.",
      category: "Sugestii"
    }
  ]

  const userGuides = [
    {
      title: "Ghid pentru Locatari",
      description: "Cum să folosești platforma ca locatar",
      icon: Users,
      topics: ["Vizualizare plăți", "Chat comunitate", "Marketplace", "Sugestii"]
    },
    {
      title: "Ghid pentru Contabili",
      description: "Gestionarea financiară a asociației",
      icon: CreditCard,
      topics: ["Procesare facturi", "Generare plăți", "Rapoarte financiare", "Documente"]
    },
    {
      title: "Ghid pentru Președinți",
      description: "Administrarea completă a asociației",
      icon: FileText,
      topics: ["Gestionare utilizatori", "Automatizări", "Toate funcționalitățile"]
    }
  ]

  const contactInfo = [
    {
      type: "Email Suport",
      value: "suport@asociatii-proprietari.ro",
      icon: Mail
    },
    {
      type: "Telefon Suport",
      value: "+40 21 123 4567",
      icon: Phone
    },
    {
      type: "Chat Live",
      value: "Disponibil L-V 9:00-17:00",
      icon: MessageCircle
    }
  ]

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Centru de Ajutor</h1>
          <p className="text-gray-600">Găsește răspunsuri și învață să folosești platforma</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {userGuides.map((guide, index) => {
          const Icon = guide.icon
          return (
            <Card key={index} className="hover:shadow-lg transition-shadow cursor-pointer">
              <CardHeader>
                <div className="flex items-center space-x-3">
                  <div className="p-2 bg-blue-100 rounded-lg">
                    <Icon className="w-6 h-6 text-blue-600" />
                  </div>
                  <div>
                    <CardTitle className="text-lg">{guide.title}</CardTitle>
                    <CardDescription>{guide.description}</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  {guide.topics.map((topic, topicIndex) => (
                    <div key={topicIndex} className="flex items-center space-x-2">
                      <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                      <span className="text-sm text-gray-600">{topic}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )
        })}
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center">
            <HelpCircle className="w-5 h-5 mr-2" />
            Întrebări Frecvente
          </CardTitle>
          <CardDescription>
            Răspunsuri la cele mai comune întrebări
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Accordion type="single" collapsible className="w-full">
            {faqItems.map((item, index) => (
              <AccordionItem key={index} value={`item-${index}`}>
                <AccordionTrigger className="text-left">
                  <div className="flex items-center space-x-3">
                    <Badge variant="outline">{item.category}</Badge>
                    <span>{item.question}</span>
                  </div>
                </AccordionTrigger>
                <AccordionContent>
                  <p className="text-gray-700 leading-relaxed">{item.answer}</p>
                </AccordionContent>
              </AccordionItem>
            ))}
          </Accordion>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Contact Suport</CardTitle>
          <CardDescription>
            Ai nevoie de ajutor suplimentar? Contactează echipa noastră de suport
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {contactInfo.map((contact, index) => {
              const Icon = contact.icon
              return (
                <div key={index} className="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                  <Icon className="w-5 h-5 text-blue-600" />
                  <div>
                    <div className="font-medium text-sm">{contact.type}</div>
                    <div className="text-gray-600 text-sm">{contact.value}</div>
                  </div>
                </div>
              )
            })}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Funcționalități Principale</CardTitle>
          <CardDescription>
            Prezentare generală a funcționalităților platformei
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Pentru Locatari</h3>
              <ul className="space-y-2">
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span className="text-sm">Vizualizare și plată facturilor lunare</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span className="text-sm">Acces la documentele asociației</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span className="text-sm">Participare la chat comunitate</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span className="text-sm">Publicare anunțuri în marketplace</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span className="text-sm">Trimitere sugestii și votare</span>
                </li>
              </ul>
            </div>
            
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Pentru Administratori</h3>
              <ul className="space-y-2">
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <span className="text-sm">Gestionare utilizatori și roluri</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <span className="text-sm">Procesare facturi și generare plăți</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <span className="text-sm">Gestionare documente cu control acces</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <span className="text-sm">Configurare automatizări IoT</span>
                </li>
                <li className="flex items-center space-x-2">
                  <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <span className="text-sm">Moderare sugestii și marketplace</span>
                </li>
              </ul>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
