from fastapi import APIRouter, Depends, HTTPException, status
from typing import List, Optional
from datetime import datetime, timedelta
from .models import (
    User, UserRole, Asociatie, Factura, PlataLunara, Plata, 
    Document, ChatMessage, ServiciuMarketplace, Sugestie, 
    VotSugestie, AutomatizareIoT
)
from .database import db
from .auth import get_current_user, require_role, authenticate_user, create_access_token

router = APIRouter()

@router.post("/auth/login")
async def login(credentials: dict):
    email = credentials.get("email")
    password = credentials.get("password")
    
    if not email or not password:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Email and password are required"
        )
    
    user = authenticate_user(email, password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect email or password",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    access_token_expires = timedelta(minutes=30)
    access_token = create_access_token(
        data={"sub": user["id"]}, expires_delta=access_token_expires
    )
    return {"access_token": access_token, "token_type": "bearer", "user": user}

@router.get("/auth/me")
async def get_current_user_info(current_user: dict = Depends(get_current_user)):
    return current_user

@router.get("/users", response_model=List[dict])
async def get_users(current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE]))):
    users = db.get_by_field('users', 'asociatie_id', current_user['asociatie_id'])
    for user in users:
        user.pop('password', None)
    return users

@router.post("/users")
async def create_user(user_data: User, current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE]))):
    existing_users = db.get_by_field('users', 'email', user_data.email)
    if existing_users:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    user_dict = user_data.model_dump()
    user_dict['asociatie_id'] = current_user['asociatie_id']
    new_user = db.create('users', user_dict)
    new_user.pop('password', None)  # Remove password from response
    return new_user

@router.get("/asociatii")
async def get_asociatie(current_user: dict = Depends(get_current_user)):
    asociatie = db.get_by_id('asociatii', current_user['asociatie_id'])
    if not asociatie:
        raise HTTPException(status_code=404, detail="Association not found")
    return asociatie

@router.put("/asociatii")
async def update_asociatie(asociatie_data: Asociatie, current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE]))):
    updated = db.update('asociatii', current_user['asociatie_id'], asociatie_data.model_dump(exclude={'id'}))
    if not updated:
        raise HTTPException(status_code=404, detail="Association not found")
    return updated

@router.get("/facturi", response_model=List[dict])
async def get_facturi(current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE, UserRole.CONTABIL]))):
    return db.get_by_field('facturi', 'asociatie_id', current_user['asociatie_id'])

@router.post("/facturi")
async def create_factura(factura_data: Factura, current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE, UserRole.CONTABIL]))):
    factura_dict = factura_data.model_dump()
    factura_dict['asociatie_id'] = current_user['asociatie_id']
    return db.create('facturi', factura_dict)

@router.put("/facturi/{factura_id}/process")
async def process_factura(factura_id: int, current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE, UserRole.CONTABIL]))):
    factura = db.get_by_id('facturi', factura_id)
    if not factura or factura['asociatie_id'] != current_user['asociatie_id']:
        raise HTTPException(status_code=404, detail="Invoice not found")
    
    users = db.get_by_field('users', 'asociatie_id', current_user['asociatie_id'])
    apartment_users = [u for u in users if u.get('apartament') and u.get('cota_parte_indiviza')]
    
    if not apartment_users:
        raise HTTPException(status_code=400, detail="No apartment users found for calculation")
    
    total_cota_parte = sum(u['cota_parte_indiviza'] for u in apartment_users)
    total_persoane = sum(u['numar_persoane'] or 0 for u in apartment_users)
    
    suma_cota_parte_total = factura['suma_totala'] * 0.5
    suma_persoane_total = factura['suma_totala'] * 0.5
    
    for user in apartment_users:
        suma_cota_parte = (user['cota_parte_indiviza'] / total_cota_parte) * suma_cota_parte_total
        suma_per_persoana = ((user['numar_persoane'] or 0) / total_persoane) * suma_persoane_total if total_persoane > 0 else 0
        suma_totala = suma_cota_parte + suma_per_persoana
        
        plata_lunara = {
            'asociatie_id': current_user['asociatie_id'],
            'user_id': user['id'],
            'luna': factura['data_factura'][:7].split('-')[1],  # Extract month
            'an': int(factura['data_factura'][:4]),  # Extract year
            'suma_totala': suma_totala,
            'suma_cota_parte': suma_cota_parte,
            'suma_per_persoana': suma_per_persoana,
            'detalii_calcul': {
                'factura_id': factura_id,
                'tip_serviciu': factura['tip_serviciu'],
                'cota_parte_indiviza': user['cota_parte_indiviza'],
                'numar_persoane': user['numar_persoane']
            },
            'status': 'neplatita',
            'data_scadenta': (datetime.fromisoformat(factura['data_scadenta']) + timedelta(days=30)).isoformat()
        }
        db.create('plati_lunare', plata_lunara)
    
    db.update('facturi', factura_id, {'status': 'procesata'})
    return {"message": "Invoice processed and monthly payments created"}

@router.get("/plati-lunare")
async def get_plati_lunare(current_user: dict = Depends(get_current_user)):
    if current_user['role'] in [UserRole.ADMIN, UserRole.PRESEDINTE, UserRole.CONTABIL]:
        return db.get_by_field('plati_lunare', 'asociatie_id', current_user['asociatie_id'])
    else:
        return db.get_by_field('plati_lunare', 'user_id', current_user['id'])

@router.post("/plati-lunare/{plata_id}/pay")
async def pay_monthly_payment(plata_id: int, suma: float, metoda_plata: str, current_user: dict = Depends(get_current_user)):
    plata_lunara = db.get_by_id('plati_lunare', plata_id)
    if not plata_lunara:
        raise HTTPException(status_code=404, detail="Monthly payment not found")
    
    if plata_lunara['user_id'] != current_user['id'] and current_user['role'] not in [UserRole.ADMIN, UserRole.PRESEDINTE, UserRole.CONTABIL]:
        raise HTTPException(status_code=403, detail="Not authorized to pay this invoice")
    
    plata = {
        'user_id': current_user['id'],
        'plata_lunara_id': plata_id,
        'suma': suma,
        'data_plata': datetime.now().isoformat(),
        'metoda_plata': metoda_plata,
        'referinta': f"Plata-{plata_id}-{datetime.now().strftime('%Y%m%d%H%M%S')}"
    }
    
    new_plata = db.create('plati', plata)
    
    total_paid = sum(p['suma'] for p in db.get_by_field('plati', 'plata_lunara_id', plata_id))
    if total_paid >= plata_lunara['suma_totala']:
        db.update('plati_lunare', plata_id, {'status': 'platita'})
    else:
        db.update('plati_lunare', plata_id, {'status': 'partial'})
    
    return new_plata

@router.get("/documente")
async def get_documente(current_user: dict = Depends(get_current_user)):
    documente = db.get_by_field('documente', 'asociatie_id', current_user['asociatie_id'])
    accessible_docs = []
    for doc in documente:
        if current_user['role'] in doc.get('acces_roluri', []):
            accessible_docs.append(doc)
    return accessible_docs

@router.post("/documente")
async def upload_document(document_data: Document, current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE, UserRole.CONTABIL]))):
    doc_dict = document_data.model_dump()
    doc_dict['asociatie_id'] = current_user['asociatie_id']
    doc_dict['uploaded_by'] = current_user['id']
    return db.create('documente', doc_dict)

@router.get("/chat/messages")
async def get_chat_messages(limit: int = 50, current_user: dict = Depends(get_current_user)):
    messages = db.get_by_field('chat_messages', 'asociatie_id', current_user['asociatie_id'])
    sorted_messages = sorted(messages, key=lambda x: x['created_at'], reverse=True)[:limit]
    return list(reversed(sorted_messages))

@router.post("/chat/messages")
async def send_chat_message(message_data: ChatMessage, current_user: dict = Depends(get_current_user)):
    msg_dict = message_data.model_dump()
    msg_dict['asociatie_id'] = current_user['asociatie_id']
    msg_dict['user_id'] = current_user['id']
    return db.create('chat_messages', msg_dict)

@router.get("/marketplace")
async def get_marketplace_services(current_user: dict = Depends(get_current_user)):
    return db.get_by_field('servicii_marketplace', 'asociatie_id', current_user['asociatie_id'])

@router.post("/marketplace")
async def create_marketplace_service(service_data: ServiciuMarketplace, current_user: dict = Depends(get_current_user)):
    service_dict = service_data.model_dump()
    service_dict['asociatie_id'] = current_user['asociatie_id']
    service_dict['user_id'] = current_user['id']
    return db.create('servicii_marketplace', service_dict)

@router.get("/sugestii")
async def get_sugestii(current_user: dict = Depends(get_current_user)):
    return db.get_by_field('sugestii', 'asociatie_id', current_user['asociatie_id'])

@router.post("/sugestii")
async def create_sugestie(sugestie_data: Sugestie, current_user: dict = Depends(get_current_user)):
    sugestie_dict = sugestie_data.model_dump()
    sugestie_dict['asociatie_id'] = current_user['asociatie_id']
    sugestie_dict['user_id'] = current_user['id']
    return db.create('sugestii', sugestie_dict)

@router.post("/sugestii/{sugestie_id}/vote")
async def vote_sugestie(sugestie_id: int, vot: str, current_user: dict = Depends(get_current_user)):
    if vot not in ['pro', 'contra']:
        raise HTTPException(status_code=400, detail="Vote must be 'pro' or 'contra'")
    
    existing_votes = db.get_by_field('voturi_sugestii', 'sugestie_id', sugestie_id)
    user_vote = next((v for v in existing_votes if v['user_id'] == current_user['id']), None)
    
    if user_vote:
        db.update('voturi_sugestii', user_vote['id'], {'vot': vot})
    else:
        vote_data = {
            'sugestie_id': sugestie_id,
            'user_id': current_user['id'],
            'vot': vot
        }
        db.create('voturi_sugestii', vote_data)
    
    all_votes = db.get_by_field('voturi_sugestii', 'sugestie_id', sugestie_id)
    voturi_pro = len([v for v in all_votes if v['vot'] == 'pro'])
    voturi_contra = len([v for v in all_votes if v['vot'] == 'contra'])
    
    db.update('sugestii', sugestie_id, {
        'voturi_pro': voturi_pro,
        'voturi_contra': voturi_contra
    })
    
    return {"message": "Vote recorded successfully"}

@router.get("/automatizari")
async def get_automatizari(current_user: dict = Depends(get_current_user)):
    automatizari = db.get_by_field('automatizari_iot', 'asociatie_id', current_user['asociatie_id'])
    accessible_automations = []
    for auto in automatizari:
        if current_user['role'] in auto.get('acces_roluri', []):
            accessible_automations.append(auto)
    return accessible_automations

@router.post("/automatizari")
async def create_automatizare(auto_data: AutomatizareIoT, current_user: dict = Depends(require_role([UserRole.ADMIN, UserRole.PRESEDINTE]))):
    auto_dict = auto_data.model_dump()
    auto_dict['asociatie_id'] = current_user['asociatie_id']
    return db.create('automatizari_iot', auto_dict)

@router.post("/automatizari/{auto_id}/trigger")
async def trigger_automatizare(auto_id: int, command: dict, current_user: dict = Depends(get_current_user)):
    automatizare = db.get_by_id('automatizari_iot', auto_id)
    if not automatizare or automatizare['asociatie_id'] != current_user['asociatie_id']:
        raise HTTPException(status_code=404, detail="Automation not found")
    
    if current_user['role'] not in automatizare.get('acces_roluri', []):
        raise HTTPException(status_code=403, detail="Not authorized to trigger this automation")
    
    return {
        "message": f"Automation '{automatizare['nume']}' triggered successfully",
        "command": command,
        "endpoint": automatizare['endpoint_mqtt']
    }
