# Mini-PRD — Sistema de turnos (PoC del agente)

> Documento de negocio de ejemplo: el tipo de markdown que se le daría al agente
> experto en el framework. El agente lo descompone en un módulo y lo implementa
> espejando las convenciones de Modux, verificando con `lint + analyse + test`.

## Contexto

Backend administrable (API) para un negocio que ofrece servicios con cita previa
(peluquería, consultorio, etc.). Multi-tenant: cada negocio es un tenant aislado.
El frontend de clientes es **otro proyecto** y consume esta API.

## Alcance de este PoC

Un único módulo backend: **`Turno`** (la reserva). Reutiliza el módulo `Cliente`
existente (a quién pertenece el turno).

## Entidad: Turno

| Campo          | Tipo      | Regla                                              |
|----------------|-----------|----------------------------------------------------|
| `cliente_id`   | int       | requerido; FK a `clientes` del mismo tenant        |
| `servicio`     | string    | requerido; 2–255 chars                             |
| `fecha_hora`   | datetime  | requerido; **debe ser futuro** al crear/reprogramar|
| `duracion_min` | int       | requerido; **> 0**                                 |
| `estado`       | enum      | `pendiente` (default) / `confirmado` / `cancelado` |

## Reglas de dominio

1. **Sin solapamientos**: un cliente no puede tener dos turnos no cancelados que
   se pisen en el tiempo (intervalo `[fecha_hora, fecha_hora + duracion_min)`).
2. **Futuro**: al crear o reprogramar, `fecha_hora` debe ser posterior a ahora.
3. **Cancelar libera**: un turno `cancelado` no ocupa horario (se excluye del
   chequeo de solapamiento).
4. Todo queda **aislado por tenant** (row-level, vía `TenantMiddleware`).

## Autorización (RBAC)

- Listar/ver turnos: rol con permiso `turnos` en nivel lectura.
- Crear/editar/eliminar: rol con permiso `turnos` en nivel escritura
  (`turnos:write`).

## Endpoints

```
GET    /turnos          listar (del tenant)         [turnos]
GET    /turnos/{id}     ver uno                      [turnos]
POST   /turnos          crear                        [turnos:write]
PUT    /turnos/{id}     reprogramar / cambiar estado [turnos:write]
DELETE /turnos/{id}     eliminar                     [turnos:write]
```

## Fuera de alcance

Frontend de clientes, notificaciones, pagos, disponibilidad de profesionales
(se modelaría como módulos adicionales en una iteración siguiente).
