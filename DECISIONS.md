# DECISIONS.md

Decisiones técnicas en orden cronológico. Las de diseño "fundacionales" se infieren del
código y del README (marcadas *(inferido)*); las de la sesión del **2026-06-07** constan
con su commit.

---

## Decisiones fundacionales (previas a esta sesión)

### F1 — Modular monolith con auto-discovery *(inferido del código)*
- **Qué**: módulos en `app/Modules/X` con `routes.php` + Controller/Service/Repository/
  Request, descubiertos por glob en `bootstrap/app.php`; sin registro manual.
- **Por qué**: aislar dominios manteniendo un solo deploy/proceso.
- **Tradeoff**: los `routes.php` dependen de un `$router` global inyectado → falso
  positivo en PHPStan (gestionado vía baseline).

### F2 — Raw PDO, sin ORM *(confirmado en README "Why not Laravel?")*
- **Qué**: repositorios con SQL directo sobre un `PDO` singleton.
- **Por qué**: control total de queries, mínimas dependencias, todo el flujo es legible.
- **Tradeoff**: más boilerplate por entidad; sin migraciones/relaciones mágicas.

### F3 — DI por constructor con autowiring, sin facades *(inferido)*
- **Qué**: `Container` resuelve dependencias por reflection; middleware parametrizado vía
  `makeWith` (`Clase:param`).
- **Por qué**: explicitud, testeabilidad, nada de service locator global.

### F4 — Multi-tenancy por `tenant_id` UUID + JWT claim *(inferido/confirmado)*
- **Qué**: `tenant_id CHAR(36)` en tablas de datos; el JWT lleva `tenant_id`;
  `TenantMiddleware` lo valida; los repos filtran por él.
- **Por qué**: aislamiento row-level simple sin esquemas por tenant.

### F5 — JWT con revocación por DB *(confirmado en código)*
- **Qué**: el access token se guarda en `usuarios.token`; `AuthMiddleware`/`JwtGuard`
  verifican que exista (revocación), además del `exp`.
- **Por qué**: poder invalidar tokens (logout) antes de su expiración.
- **Tradeoff**: un `SELECT` por request autenticado.

### F6 — Cola de jobs DB-backed + eventos síncronos *(confirmado)*
- **Qué**: tabla `jobs`, `JobDispatcher`, worker `queue:work`; `EventDispatcher`
  síncrono.
- **Por qué**: async básico sin broker externo (Redis/RabbitMQ).

*(Cronología git previa, sin detalle de razón: `wf` → `package test` → varios `test ci`
→ `fix workflow path` → `tenant clients`/`merge tenants`. Indica que la multi-tenancy de
clientes y el CI se incorporaron justo antes de esta sesión.)*

---

## Sesión 2026-06-07

### D1 — Eliminar el clon embebido `backend/` (commit `66fd2a2`)
- **Qué**: borrar el directorio `backend/` (4159 archivos trackeados: clon viejo del
  propio repo, con su `.git` y `vendor/` commiteado, 3 commits atrasado).
- **Por qué**: duplicado desactualizado que confundía; su `docker-compose`/`composer`
  apuntaban a rutas heredadas (`./backend`, `../../modulos/ia`).
- **Tradeoff/cuidado**: contenía el único `app/Modules/IA` y un `.env` con secreto →
  se rescató el módulo y se anotó el secreto antes de borrar.

### D2 — IA como add-on opcional, fuera del base (commit `66fd2a2`)
- **Qué**: quitar `cynchro/modux-ia` del `require` y el repositorio `path` local; moverlo
  a `suggest`; no incluir `app/Modules/IA` en el base; documentar instalación opcional.
- **Por qué**: Packagist confirma que es una **library independiente** (`composer require
  cynchro/modux-ia`); el framework "viene sin IA por default". Mantiene las ~5 deps.
- **Tradeoff**: el base quedaba antes en estado inconsistente (requería el SDK sin el
  módulo que lo usa); se corrigió.

### D3 — `docker-compose` build desde la raíz (commit `66fd2a2`)
- **Qué**: `context: ./backend` → `context: .`, volumen `.:/var/www/html`.
- **Por qué**: el código real vive en la raíz; el build fallaba apuntando a `./backend`.

### D4 — Resolver 2 conflictos de merge en README manteniendo HEAD (commit `66fd2a2`)
- **Qué**: el README arrastraba marcadores `<<<<<<<` sin resolver desde "merge tenants"
  (sección Job queue y tabla "Why not Laravel?").
- **Por qué**: HEAD era el superset correcto (documenta el Job queue real).

### D5 — Actualizar PHPStan 1.12 → 2.x (commit `8b3678c`)
- **Qué**: subir a `^2.0` (2.2.2); 2.x detectó `Response::$rawBody` como propiedad muerta
  (rama inalcanzable en `send()`).
- **Decisión del usuario**: **eliminar el código muerto** (vs. completar la API con un
  método `raw()`). Se quitó la propiedad y su rama.
- **Por qué**: 2.x es más estricto y útil; el código no usaba `rawBody`.
- **Tradeoff**: se descartó la capacidad latente de respuestas no-JSON; baseline
  regenerado a formato 2.x (90 avisos preexistentes documentados).

### D6 — Limpiar `.env.example` (commit `8b3678c`)
- **Qué**: quitar variables `AI_*/LLAMA_*/RAG_*` (dejando una nota).
- **Por qué**: coherente con IA opcional (D2); esas vars solo aplican si se instala el
  módulo IA.

### D7 — Quality gate de doble capa (commit `8b3678c`)
- **Qué**: `.githooks/pre-push` (corre `composer validate/lint/analyse/test`, se activa
  con `git config core.hooksPath .githooks`) + `.github/workflows/ci.yml` (mismos checks +
  build Docker).
- **Por qué**: la base es pública y versionada; no se puede pushear algo roto.
- **Tradeoff**: el hook es config local por clon (no se versiona la activación) →
  documentado en README; bypass de emergencia con `git push --no-verify`.

### D8 — Limpiar el secreto del historial con `git filter-repo` (durante el push)
- **Qué**: GitHub Push Protection bloqueó el push por la Groq API key en `backend/.env`.
  Como los commits **no estaban en `origin`**, se reescribió el historial local para
  eliminar `backend/.env` por completo y luego se pusheó.
- **Decisión del usuario**: **limpiar historial** (vs. usar "allow secret", que dejaría el
  secreto público para siempre).
- **Por qué**: es la opción limpia y sin impacto downstream (commits no publicados).
- **Tradeoff**: reescribe hashes locales (sin efecto en forks/clones porque no se habían
  publicado). **Pendiente**: rotar la key en Groq (estuvo en disco en claro).

### D9 — ADR 0001: arquitectura de la capa SaaS (commit `86fc12f`)
- **Qué**: `docs/adr/0001-saas-identity-entitlements-billing.md`. Define el mapa
  base-vs-módulos y las decisiones D-internas:
  - **Insight central**: `entitlements ≠ billing`. Entitlements = *autorización* → base.
    Billing = *comercial* → módulo. Billing **puebla** `tenant_entitlements`; el base solo
    **lee** (así `modux-ia` no arrastra billing).
  - **D1 (ADR)**: features **namespaced** (`ia.rag`, `bots.outbound`).
  - **D2 (ADR)**: quota **anclada al ciclo de la suscripción**, con `period_start/
    period_end` **denormalizados** en `tenant_entitlements` para que el base no dependa de
    la tabla `subscriptions` del módulo billing. Fallback de mes calendario sin billing +
    comando `entitlements:roll-periods` como red de seguridad.
  - **D3 (ADR)**: `scope` (api key, qué endpoint) y `entitlement` (tenant, qué tiene) son
    **ortogonales** → ambos checks. Pipeline:
    `Auth → Tenant → Scope(403) → Entitlement(402/403) → Quota(429 + Retry-After)`.
- **Por qué**: separar identidad/autorización (chasis liviano) de lo pesado/comercial
  (módulos), evitando acoplar add-ons al billing.
- **Tradeoff**: el contrato de entitlements pasa a ser API pública → SemVer estricto.

### D10 — Fase 1: auth multi-esquema vía guards (commit `7bae294`)
- **Qué**: `GuardInterface`; `AuthMiddleware` refactorizado de `(PDO)` a
  `(JwtGuard, ApiKeyGuard)`, itera guards y produce un `Principal`; `ApiKeyManager`
  (token `mk_<env>_<id>_<secret>`, en DB solo `prefix` + `sha256`); `ScopeMiddleware`;
  migración `0007_create_api_keys_table`; `Request::principal()/setPrincipal()`.
- **Por qué**: habilitar "API para terceros" (API keys + scopes) sin romper el JWT.
- **Cómo se mitigó el riesgo**: `AuthMiddleware` sigue llamando `setUser()` →
  `Tenant`/`PermissionMiddleware` y `$request->user()` intactos (retrocompatibilidad).
  Discriminación de esquema por prefijo `mk_` (JWT vs API key). 24 tests + e2e Docker.
- **Tradeoff**: refactor de un componente crítico de producción (asumido y cubierto).

### D11 — Fase 1.b: módulo `ApiKeys` con CRUD (commit `69ad4aa`)
- **Qué**: `app/Modules/ApiKeys` (`POST/GET/GET{id}/DELETE /api-keys`), protegido por
  `AuthMiddleware + TenantMiddleware + ScopeMiddleware:apikeys.manage`. `ApiKeyRepository`
  nunca expone `hash`. `ApiKeyManager` deja de ser `final` (para mocking en tests).
- **Por qué**: que un tenant gestione sus propias keys desde la app; emisión antes solo
  programática.
- **Decisión de diseño clave**: gating por scope `apikeys.manage` → usuarios de la app
  (scope `'*'`) gestionan transparentemente, pero una API key solo administra otras si se
  le concede el scope explícito → **previene escalada de privilegios**.
- **Tradeoff**: `ApiKeyManager` no-`final` (se aceptó por testeabilidad).

### D12 — Fase 2: `WebhookVerifier` (HMAC + timestamp + anti-replay)
- **Qué**: `WebhookVerifierInterface` + `App\Support\Webhook\WebhookVerifier`. Esquema
  propio sin dependencias: cabecera `X-Signature: t=<ts>,v1=<hmac>`, firma
  `HMAC-SHA256("<ts>.<rawBody>", secret)`, ventana de tiempo, comparación constante
  (`hash_equals`) y **anti-replay por nonce de la firma vía `CacheInterface`** (TTL =
  ventana). `sign()` para webhooks salientes. Binding singleton en `bootstrap/app.php`.
  Se añadió `Request::rawBody()` (el cuerpo crudo se captura una vez en el constructor y
  lo reusa `parseJson()`).
- **Por qué**: cierra "endurecer webhooks" del review y es prerequisito de los webhooks de
  pago (Fase 6). Liviano y transversal → va en el base.
- **Decisión de diseño**: esquema genérico propio de Modux; los adaptadores de pasarela
  (Stripe/MP) podrán reusar `sign()`/`verify()` o aportar su parsing y delegar el HMAC.
  Sin middleware dedicado en esta fase (el secret depende de cada integración → se usa en
  el controller del webhook).
- **Tradeoff**: leer `php://input` siempre en el constructor de `Request` (antes solo si
  `Content-Type: application/json`); irrelevante para GET/multipart, necesario para
  verificar firmas. Validación: 7 tests unitarios con `ArrayCache`/`Request` reales +
  sanity de wiring del container (no requiere DB).

---

## Convención de trabajo adoptada (meta-decisión)

Cada fase se entrega con: implementación siguiendo los patrones existentes → tests
unitarios → batería completa (`composer test/analyse/lint` en verde) → **validación e2e
con Docker + MySQL real** → actualización de README y del ADR → commit descriptivo. El
pre-push hook (D7) garantiza que nada roto llegue a `origin`.
