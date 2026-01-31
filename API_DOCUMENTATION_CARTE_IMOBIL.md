# Carte de imobil (API + configurare)

## Env flags (backend)

În `backend/.env`:

- `REQUIRE_CNP=true|false`
- `MASK_CNP_FOR_RESIDENT=true|false`

## Endpoint-uri (prefix: `/api/v2`)

Toate endpoint-urile folosesc `Authorization: Bearer <token>` (Passport) și răspund JSON standardizat:

```json
{ "success": true, "data": { ... }, "errors": null }
```

Erori de validare (422):

```json
{ "success": false, "data": null, "errors": [ { "field": "first_name", "message": "..." } ] }
```

### 1) Apartamentele mele

`GET /api/v2/my-apartments`

Response:

```json
{
  "success": true,
  "data": {
    "apartments": [
      { "id": 10, "tenant_id": 2, "number": "12", "staircase": "A", "floor": "3" }
    ]
  },
  "errors": null
}
```

### 2) Listă persoane (per apartament)

`GET /api/v2/apartments/{id}/occupants`

Resident: câmpurile sensibile sunt mascate (ex: `cnp_masked`), admin vede integral (`cnp`, `id_series`, `id_number`).

### 3) Adaugă persoană

`POST /api/v2/apartments/{id}/occupants`

Body (exemplu):

```json
{
  "first_name": "Ion",
  "last_name": "Popescu",
  "cnp": "1234567890123",
  "id_series": "RX",
  "id_number": "123456",
  "domicile_address": "Str. ...",
  "role_in_unit": "owner",
  "other_role_text": null,
  "move_in_date": "2024-01-01",
  "move_out_date": null,
  "is_minor": false,
  "legal_guardian_name": null,
  "phone": null,
  "email": null,
  "notes": null
}
```

### 4) Update persoană

`PUT /api/v2/occupants/{id}`

Notă: dacă persoana era `rejected`, la prima editare revine automat în `draft` (și se șterge motivul).

### 5) Șterge persoană

`DELETE /api/v2/occupants/{id}` (soft-delete)

### 6) Submit cerere (îngheață editarea)

`POST /api/v2/apartments/{id}/occupants/submit`

### 7) Approve / Reject (admin/comitet)

`POST /api/v2/apartments/{id}/occupants/approve`

`POST /api/v2/apartments/{id}/occupants/reject`

Body:

```json
{ "reason": "Lipsește domiciliul pentru o persoană." }
```

### 8) Listă cereri submitted (admin/comitet)

`GET /api/v2/occupants/submissions`

### 9) Export PDF

`GET /api/v2/apartments/{id}/occupants/export.pdf`

- Resident: doar dacă toate persoanele sunt `approved`
- Admin/comitet: oricând (în tenant)
- Audit: se scrie `exported` în `occupant_change_logs` (per persoană)

