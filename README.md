# f1.atria.live

## Documentație API pentru aplicația mobilă

- OpenAPI 3.0: `docs/openapi.yaml`
  - Poate fi importat în Postman/Insomnia/Swagger UI.
  - Exemplu Swagger UI local: `npx swagger-ui-watcher docs/openapi.yaml`
- Postman Collection: `docs/postman_collection.json`
  - Import direct în Postman. Setați variabila `access_token` după login.

### Autentificare
- Tip: OAuth2 Bearer (Laravel Passport)
- Login: `POST https://f1.atria.live/api/v2/login`
- Header-uri standard pentru JSON:API:
  - `Accept: application/vnd.api+json`
  - `Content-Type: application/vnd.api+json`

### Baze utile
- Baza URL API: `https://f1.atria.live/api/v2`
- Pentru fluxul complet și exemple, vezi `API_DOCUMENTATION.md` și `api-structure.json`.


