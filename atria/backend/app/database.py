from typing import Dict, List, Optional
from datetime import datetime
import json


class InMemoryDB:
    def __init__(self):
        self.users: Dict[int, dict] = {}
        self.asociatii: Dict[int, dict] = {}
        self.facturi: Dict[int, dict] = {}
        self.plati_lunare: Dict[int, dict] = {}
        self.plati: Dict[int, dict] = {}
        self.documente: Dict[int, dict] = {}
        self.chat_messages: Dict[int, dict] = {}
        self.servicii_marketplace: Dict[int, dict] = {}
        self.sugestii: Dict[int, dict] = {}
        self.voturi_sugestii: Dict[int, dict] = {}
        self.automatizari_iot: Dict[int, dict] = {}
        
        self.next_id = {
            'users': 1,
            'asociatii': 1,
            'facturi': 1,
            'plati_lunare': 1,
            'plati': 1,
            'documente': 1,
            'chat_messages': 1,
            'servicii_marketplace': 1,
            'sugestii': 1,
            'voturi_sugestii': 1,
            'automatizari_iot': 1
        }
        
        self._init_sample_data()
    
    def _init_sample_data(self):
        asociatie_sample = {
            'id': 1,
            'nume': 'Asociația de Proprietari Bloc A1',
            'adresa': 'Str. Exemplu Nr. 1, București',
            'cui': 'RO12345678',
            'cont_bancar': 'RO49AAAA1B31007593840000',
            'banca': 'Banca Transilvania',
            'administrator': 'Ion Popescu',
            'telefon_administrator': '0721234567',
            'email_administrator': 'admin@bloc-a1.ro',
            'created_at': datetime.now().isoformat()
        }
        self.asociatii[1] = asociatie_sample
        self.next_id['asociatii'] = 2
        
        users_sample = [
            {
                'id': 1,
                'email': 'admin@bloc-a1.ro',
                'password': 'admin123',
                'nume': 'Popescu',
                'prenume': 'Ion',
                'telefon': '0721234567',
                'apartament': None,
                'cota_parte_indiviza': None,
                'numar_persoane': None,
                'role': 'admin',
                'asociatie_id': 1,
                'created_at': datetime.now().isoformat(),
                'is_active': True
            },
            {
                'id': 2,
                'email': 'presedinte@bloc-a1.ro',
                'password': 'pres123',
                'nume': 'Ionescu',
                'prenume': 'Maria',
                'telefon': '0721234568',
                'apartament': 'Ap. 1',
                'cota_parte_indiviza': 0.05,
                'numar_persoane': 2,
                'role': 'presedinte',
                'asociatie_id': 1,
                'created_at': datetime.now().isoformat(),
                'is_active': True
            },
            {
                'id': 3,
                'email': 'locatar1@bloc-a1.ro',
                'password': 'loc123',
                'nume': 'Georgescu',
                'prenume': 'Andrei',
                'telefon': '0721234569',
                'apartament': 'Ap. 2',
                'cota_parte_indiviza': 0.04,
                'numar_persoane': 3,
                'role': 'locatar',
                'asociatie_id': 1,
                'created_at': datetime.now().isoformat(),
                'is_active': True
            }
        ]
        
        for user in users_sample:
            self.users[user['id']] = user
        self.next_id['users'] = 4
    
    def get_next_id(self, table: str) -> int:
        current_id = self.next_id[table]
        self.next_id[table] += 1
        return current_id
    
    def create(self, table: str, data: dict) -> dict:
        table_dict = getattr(self, table)
        new_id = self.get_next_id(table)
        data['id'] = new_id
        data['created_at'] = datetime.now().isoformat()
        table_dict[new_id] = data
        return data
    
    def get_by_id(self, table: str, id: int) -> Optional[dict]:
        table_dict = getattr(self, table)
        return table_dict.get(id)
    
    def get_all(self, table: str) -> List[dict]:
        table_dict = getattr(self, table)
        return list(table_dict.values())
    
    def get_by_field(self, table: str, field: str, value) -> List[dict]:
        table_dict = getattr(self, table)
        return [item for item in table_dict.values() if item.get(field) == value]
    
    def update(self, table: str, id: int, data: dict) -> Optional[dict]:
        table_dict = getattr(self, table)
        if id in table_dict:
            table_dict[id].update(data)
            return table_dict[id]
        return None
    
    def delete(self, table: str, id: int) -> bool:
        table_dict = getattr(self, table)
        if id in table_dict:
            del table_dict[id]
            return True
        return False

db = InMemoryDB()
