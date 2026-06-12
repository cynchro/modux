<?php

/**
 * Tabla `turnos` (sistema de turnos / citas). Tenant-scoped y referenciando al
 * módulo `clientes`. El chequeo de solapamiento vive en TurnoRepository.
 */
return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS turnos (
                id           INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tenant_id    CHAR(36)     NOT NULL,
                cliente_id   INT          NOT NULL,
                servicio     VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                fecha_hora   DATETIME     NOT NULL,
                duracion_min INT          NOT NULL,
                estado       VARCHAR(20)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
                CONSTRAINT fk_turnos_tenant
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                CONSTRAINT fk_turnos_cliente
                    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
                INDEX idx_turnos_tenant (tenant_id),
                INDEX idx_turnos_overlap (tenant_id, cliente_id, fecha_hora)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS turnos');
    }
};
