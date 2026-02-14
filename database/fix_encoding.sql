-- Script para corregir la codificación de caracteres en todas las tablas
-- Ejecutar después de que la base de datos esté creada

USE sacrgapi_database;

-- Configurar codificación para la sesión
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Actualizar la base de datos para usar UTF-8
ALTER DATABASE sacrgapi_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Actualizar todas las tablas para usar UTF-8
ALTER TABLE usuarios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE programas CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE instituciones CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE personal CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE estudiante CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE talleres CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE cursos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE calificaciones CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE inventario CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE estudiante_taller CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Actualizar datos existentes que puedan tener problemas de codificación
-- Esto corregirá los caracteres mal codificados
UPDATE programas SET descripcion = 'Programa de Formación Técnica en Informática' WHERE id_programas = 1;
UPDATE programas SET descripcion = 'Programa de Formación Técnica en Administración' WHERE id_programas = 2;
UPDATE programas SET descripcion = 'Programa de Formación Técnica en Contabilidad' WHERE id_programas = 3;
UPDATE programas SET descripcion = 'Programa de Formación Técnica en Turismo' WHERE id_programas = 4;
UPDATE programas SET descripcion = 'Programa de Formación Técnica en Electricidad' WHERE id_programas = 5;

-- Verificar que los cambios se aplicaron correctamente
SELECT 'Verificación de codificación:' as status;
SELECT id_programas, descripcion FROM programas WHERE id_programas = 1;
