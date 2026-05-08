<?php

return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            ALTER TABLE clientes
                ADD COLUMN tenant_id CHAR(36) NOT NULL,
                ADD CONSTRAINT fk_clientes_tenant
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                ADD INDEX idx_clientes_tenant (tenant_id)
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE clientes DROP FOREIGN KEY fk_clientes_tenant');
        $pdo->exec('ALTER TABLE clientes DROP INDEX idx_clientes_tenant');
        $pdo->exec('ALTER TABLE clientes DROP COLUMN tenant_id');
    }
};
