-- Script para instalaciones existentes: añade columna nombre a inventario y crea tabla reparaciones
-- Ejecutar una sola vez sobre la base de datos ya creada.

ALTER TABLE inventario ADD COLUMN nombre VARCHAR(200) NULL AFTER cod_equipo;

CREATE TABLE IF NOT EXISTS reparaciones (
    id_reparacion INT AUTO_INCREMENT PRIMARY KEY,
    inventario_id_inventario INT NOT NULL,
    fecha DATE NOT NULL,
    motivo TEXT,
    FOREIGN KEY (inventario_id_inventario) REFERENCES inventario(id_inventario)
);
