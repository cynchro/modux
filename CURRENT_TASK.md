# CURRENT_TASK.md

## En qué estoy trabajando ahora

**Expansión de la capa SaaS del framework**, guiada por
`docs/adr/0001-saas-identity-entitlements-billing.md`. Origen: una observación externa
de que "Modux trae el chasis pero falta la capa comercial del SaaS y la apertura a
terceros".

División acordada:
- **API para terceros** (API keys / scopes / webhooks endurecidos) → en el **framework base**.
- **Billing / entitlements / packs** → como **módulos opcionales instalables** estilo
  `modux-ia` (`cynchro/modux-billing`, `-stripe`, `-mercadopago`), salvo el **motor de
  entitlements**, que va en el base (es autorización, no comercial).

### Estado por fase (del ADR 0001)
- ✅ **Fase 1** — auth multi-esquema (`GuardInterface`, `Principal`, `JwtGuard`,
  `ApiKeyGuard`, `ApiKeyManager`), `ScopeMiddleware`, migración `0007_create_api_keys_table`.
  Commit `7bae294`.
- ✅ **Fase 1.b** — módulo `ApiKeys` con CRUD (`/api-keys`), scope `apikeys.manage`.
  Commit `69ad4aa`.
- ✅ **Fase 2** — `App\Support\Webhook\WebhookVerifier` (`WebhookVerifierInterface`):
  HMAC-SHA256 sobre `<ts>.<rawBody>`, ventana de timestamp y anti-replay vía
  `CacheInterface`. Añadido `Request::rawBody()`. Binding en `bootstrap/app.php`.
  Cierra "endurecer webhooks" del review.
- ✅ **Fase 3** — motor de entitlements (LECTURA + gating): migración
  `0008_create_tenant_entitlements_table`; `Entitlement` + `EntitlementSet` (value objects
  puros); `EntitlementResolverInterface` + `DbEntitlementResolver`; `EntitlementMiddleware:
  <feature>` (402); `PaymentRequiredException`. La **escritura** de `tenant_entitlements`
  llegará con billing (Fase 5) o se hace a mano (`source='manual'`).
- ⏭️ **Fase 4 (SIGUIENTE)** — `usage_events` + `UsageRecorderInterface` + `QuotaMiddleware`
  + comando `entitlements:roll-periods`. `QuotaMiddleware` calculará `used` contando
  `usage_events` desde `EntitlementSet::get($feature)->periodStart` y llamará
  `remaining($feature, $used)` (que ya existe y es puro). 429 + `Retry-After`.
- ⬜ Fase 5–7 — `cynchro/modux-billing` (+ `-stripe`/`-mercadopago`), opcional `modux-oauth`.

## Problemas actuales

- **Ninguno bloqueante.** La base está 100% verde: **205 tests / 305 assertions**,
  **PHPStan 2.x 0 errores**, **PHPCS limpio**. Validado e2e contra MySQL real (Docker):
  migraciones, login JWT, API keys (auth + CRUD), entitlements (resolver + middleware
  contra DB real, gating 200 vs 402). El `WebhookVerifier` (sin DB) se validó con tests +
  sanity de wiring del container.

## Qué ya intenté / cómo se llegó acá (esta sesión, 2026-06-07)

1. Detecté y eliminé el clon embebido `backend/` (duplicado desactualizado, trackeado en
   el repo). Rescaté el módulo IA antes de borrar.
2. Confirmé (Packagist) que `cynchro/modux-ia` es library independiente → lo dejé como
   add-on **opcional** (sacado del `require`, movido a `suggest`; `app/Modules/IA` fuera
   del base).
3. Actualicé PHPStan 1.12 → 2.x; corregí el único hallazgo real (`Response::$rawBody`
   muerto, eliminado); regeneré baseline.
4. Monté el quality gate (`.githooks/pre-push` + `.github/workflows/ci.yml`).
5. Escribí el ADR 0001 y cerré sus decisiones D1/D2/D3 con el usuario.
6. Implementé Fase 1 y Fase 1.b (cada una con tests unitarios + validación e2e Docker).

Metodología establecida y esperada para cada fase: implementar siguiendo los patrones
existentes → tests unitarios → **batería completa** (`composer test/analyse/lint`) →
**validación e2e con Docker + MySQL real** → actualizar README + ADR → commit con mensaje
detallado. El pre-push hook bloquea push si algo falla.

## Bloqueos / pendientes (no detienen la Fase 2)

- **Acción del usuario — rotar la Groq API key**: estuvo commiteada en `backend/.env`.
  Se limpió del historial con `git filter-repo` y **nunca llegó a GitHub** (push
  bloqueado por Push Protection y luego historial reescrito), pero estuvo en disco en
  claro → conviene rotarla en el panel de Groq. La key vieja:
  `REDACTED`.
- **3 commits locales sin pushear** (`origin/main` = `8b3678c`, ya verificado):
  `86fc12f` (ADR 0001), `7bae294` (Fase 1), `69ad4aa` (Fase 1.b). El push de tooling
  (`8b3678c`) ya está en remoto. El usuario decide cuándo pushear el resto.
- **Deuda preexistente no tocada**: `Cliente::create()`/`update()` son stubs
  (`RuntimeException('not implemented yet')`) — vienen así del scaffolding, fuera del
  alcance actual.

## Decisiones abiertas pendientes para fases futuras (del ADR)

- Naming de features namespaced (`ia.rag`, `bots.outbound`) — **cerrada: sí**.
- Quota anclada al `current_period_end` con `period_start/period_end` denormalizados en
  `tenant_entitlements` — **cerrada: sí**.
- `scope` + `entitlement` ambos checks, ortogonales — **cerrada: sí**.
- Status codes del pipeline: 402 (entitlement faltante que se compra), 403 (scope),
  429 + `Retry-After` (cuota) — definidos en el ADR, a implementar en Fases 3-4.
