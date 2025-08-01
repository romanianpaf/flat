from pydantic import BaseModel
from typing import Optional, List, Dict, Any
from datetime import datetime
from enum import Enum

class UserRole(str, Enum):
    LOCATAR = "locatar"  # tenant
    CONTABIL = "contabil"  # accountant
    CEX = "cex"  # CEO
    PRESEDINTE = "presedinte"  # president
    MODERATOR = "moderator"
    ADMIN = "admin"

class User(BaseModel):
    id: Optional[int] = None
    email: str
    password: str
    nume: str
    prenume: str
    telefon: Optional[str] = None
    apartament: Optional[str] = None
    cota_parte_indiviza: Optional[float] = None  # undivided share percentage
    numar_persoane: Optional[int] = None
    role: UserRole
    asociatie_id: Optional[int] = None
    created_at: Optional[datetime] = None
    is_active: bool = True

class Asociatie(BaseModel):
    id: Optional[int] = None
    nume: str
    adresa: str
    cui: str  # Romanian tax ID
    cont_bancar: str
    banca: str
    administrator: str
    telefon_administrator: str
    email_administrator: str
    created_at: Optional[datetime] = None

class Factura(BaseModel):
    id: Optional[int] = None
    asociatie_id: int
    furnizor: str
    numar_factura: str
    data_factura: datetime
    data_scadenta: datetime
    suma_totala: float
    tip_serviciu: str  # heating, water, electricity, maintenance, etc.
    descriere: Optional[str] = None
    fisier_url: Optional[str] = None
    status: str = "neprocesata"  # neprocesata, procesata, platita
    created_at: Optional[datetime] = None

class PlataLunara(BaseModel):
    id: Optional[int] = None
    asociatie_id: int
    user_id: int
    luna: int
    an: int
    suma_totala: float
    suma_cota_parte: float
    suma_per_persoana: float
    detalii_calcul: Dict[str, Any]
    status: str = "neplatita"  # neplatita, platita, partial
    data_scadenta: datetime
    created_at: Optional[datetime] = None

class Plata(BaseModel):
    id: Optional[int] = None
    user_id: int
    plata_lunara_id: int
    suma: float
    data_plata: datetime
    metoda_plata: str
    referinta: Optional[str] = None
    created_at: Optional[datetime] = None

class Document(BaseModel):
    id: Optional[int] = None
    asociatie_id: int
    nume: str
    descriere: Optional[str] = None
    fisier_url: str
    tip_document: str
    acces_roluri: List[UserRole]
    uploaded_by: int
    created_at: Optional[datetime] = None

class ChatMessage(BaseModel):
    id: Optional[int] = None
    asociatie_id: int
    user_id: int
    mesaj: str
    tip_mesaj: str = "text"  # text, image, file
    fisier_url: Optional[str] = None
    created_at: Optional[datetime] = None

class ServiciuMarketplace(BaseModel):
    id: Optional[int] = None
    asociatie_id: int
    user_id: int
    titlu: str
    descriere: str
    tip: str  # serviciu, inchiriere, vanzare
    pret: Optional[float] = None
    contact: str
    imagini: Optional[List[str]] = None
    status: str = "activ"  # activ, inactiv, vandut
    created_at: Optional[datetime] = None

class Sugestie(BaseModel):
    id: Optional[int] = None
    asociatie_id: int
    user_id: int
    titlu: str
    descriere: str
    categorie: str
    voturi_pro: int = 0
    voturi_contra: int = 0
    status: str = "activa"  # activa, implementata, respinsa
    created_at: Optional[datetime] = None

class VotSugestie(BaseModel):
    id: Optional[int] = None
    sugestie_id: int
    user_id: int
    vot: str  # pro, contra
    created_at: Optional[datetime] = None

class AutomatizareIoT(BaseModel):
    id: Optional[int] = None
    asociatie_id: int
    nume: str
    tip: str  # piscina, bariera, etc.
    endpoint_mqtt: str
    configuratie: Dict[str, Any]
    acces_roluri: List[UserRole]
    is_active: bool = True
    created_at: Optional[datetime] = None
