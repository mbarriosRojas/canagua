-- =============================================
-- SISTEMA AUTOMATIZADO PARA EL CONTROL Y REGISTRO 
-- DE LA GESTIÓN ADMINISTRATIVA (SACRGAPI)
-- =============================================

-- Crear base de datos si no existe con codificación UTF-8
CREATE DATABASE IF NOT EXISTS sacrgapi_database 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
USE sacrgapi_database;

-- Configurar codificación para la sesión
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- =============================================
-- TABLA DE USUARIOS DEL SISTEMA (NUEVA)
-- =============================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'supervisor', 'operador') DEFAULT 'operador',
    activo BOOLEAN DEFAULT TRUE,
    creado_por INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id_usuario)
);

-- =============================================
-- TABLA PROGRAMAS
-- =============================================
CREATE TABLE programas (
    id_programas INT AUTO_INCREMENT PRIMARY KEY,
    cod_programas VARCHAR(20) UNIQUE NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    sub_area VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA INSTITUCIONES
-- =============================================
CREATE TABLE instituciones (
    id_instituciones INT AUTO_INCREMENT PRIMARY KEY,
    cod_institucion VARCHAR(20) UNIQUE NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    telefono_enlace VARCHAR(20),
    datos_docente_enlace TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABLA ESTUDIANTE
-- =============================================
CREATE TABLE estudiante (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    cedula_estudiante VARCHAR(20) UNIQUE NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_2 VARCHAR(100),
    sexo ENUM('M', 'F') NOT NULL,
    lugar_nacimiento VARCHAR(100),
    cedula_representante VARCHAR(20),
    telefono VARCHAR(20),
    instituciones_id_instituciones INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instituciones_id_instituciones) REFERENCES instituciones(id_instituciones)
);

-- =============================================
-- TABLA PERSONAL
-- =============================================
CREATE TABLE personal (
    id_personal INT AUTO_INCREMENT PRIMARY KEY,
    cedula_personal VARCHAR(20) UNIQUE NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    fecha_ingreso DATE,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABLA TALLERES
-- =============================================
CREATE TABLE talleres (
    id_taller INT AUTO_INCREMENT PRIMARY KEY,
    programas_id_programas INT NOT NULL,
    instituciones_id_instituciones INT NOT NULL,
    personal_id_personal INT NOT NULL,
    cod_taller VARCHAR(20) UNIQUE NOT NULL,
    cod_institucion VARCHAR(20),
    cod_programa VARCHAR(20),
    cod_personal VARCHAR(20),
    ano_escolar YEAR NOT NULL,
    grado VARCHAR(20),
    seccion VARCHAR(20),
    lapso ENUM('I', 'II', 'III') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (programas_id_programas) REFERENCES programas(id_programas),
    FOREIGN KEY (instituciones_id_instituciones) REFERENCES instituciones(id_instituciones),
    FOREIGN KEY (personal_id_personal) REFERENCES personal(id_personal)
);

-- =============================================
-- TABLA CURSOS
-- =============================================
CREATE TABLE cursos (
    id_cursos INT AUTO_INCREMENT PRIMARY KEY,
    personal_id_personal INT NOT NULL,
    cod_curso VARCHAR(20) UNIQUE NOT NULL,
    nombre_curso VARCHAR(255) NOT NULL,
    cedula_persona VARCHAR(20),
    duracion INT, -- en horas
    ano YEAR NOT NULL,
    num_de_clases INT,
    periodo ENUM('I', 'II', 'III', 'IV') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personal_id_personal) REFERENCES personal(id_personal)
);

-- =============================================
-- TABLA CALIFICACIONES
-- =============================================
CREATE TABLE calificaciones (
    id_calificaciones INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id_estudiante INT NOT NULL,
    cedula_estudiante VARCHAR(20),
    cod_taller VARCHAR(20),
    talleres_id_taller INT,
    instituciones_id_instituciones INT,
    literal VARCHAR(5),
    numeral DECIMAL(5,2),
    momento_i DECIMAL(5,2),
    momento_ii DECIMAL(5,2),
    momento_iii DECIMAL(5,2),
    momento_i_numeral DECIMAL(5,2),
    momento_i_literal VARCHAR(5),
    momento_ii_numeral DECIMAL(5,2),
    momento_ii_literal VARCHAR(5),
    momento_iii_numeral DECIMAL(5,2),
    momento_iii_literal VARCHAR(5),
    promedio DECIMAL(5,2),
    promedio_final DECIMAL(5,2),
    literal_final VARCHAR(5),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id_estudiante) REFERENCES estudiante(id_estudiante),
    FOREIGN KEY (talleres_id_taller) REFERENCES talleres(id_taller) ON DELETE CASCADE,
    FOREIGN KEY (instituciones_id_instituciones) REFERENCES instituciones(id_instituciones) ON DELETE CASCADE
);

-- =============================================
-- TABLA INVENTARIO
-- =============================================
CREATE TABLE inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    personal_id_personal INT NOT NULL,
    cod_equipo VARCHAR(20) UNIQUE NOT NULL,
    ubicacion VARCHAR(100),
    cedula_personal VARCHAR(20),
    cantidad INT DEFAULT 1,
    estado ENUM('Bueno', 'Regular', 'Malo', 'Fuera de Servicio') DEFAULT 'Bueno',
    serial VARCHAR(100),
    marca VARCHAR(100),
    modelo VARCHAR(100),
    color VARCHAR(50),
    medidas VARCHAR(100),
    capacidad VARCHAR(100),
    otras_caracteristicas TEXT,
    observacion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personal_id_personal) REFERENCES personal(id_personal)
);

-- =============================================
-- TABLA PARTICIPANTES
-- =============================================
CREATE TABLE participantes (
    id_participantes INT AUTO_INCREMENT PRIMARY KEY,
    cursos_id_cursos INT NOT NULL,
    cedula_participante VARCHAR(20) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    sexo ENUM('M', 'F') NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    direccion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cursos_id_cursos) REFERENCES cursos(id_cursos)
);

-- =============================================
-- TABLA NOTAS
-- =============================================
CREATE TABLE notas (
    id_notas INT AUTO_INCREMENT PRIMARY KEY,
    participantes_id_participantes INT NOT NULL,
    cedula_participante VARCHAR(20),
    cod_curso VARCHAR(20),
    aprobo BOOLEAN DEFAULT FALSE,
    participo BOOLEAN DEFAULT TRUE,
    no_aprobo BOOLEAN DEFAULT FALSE,
    recibio_certificado BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participantes_id_participantes) REFERENCES participantes(id_participantes)
);

-- =============================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- =============================================
CREATE INDEX idx_usuarios_username ON usuarios(username);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_estudiante_cedula ON estudiante(cedula_estudiante);
CREATE INDEX idx_personal_cedula ON personal(cedula_personal);
CREATE INDEX idx_talleres_cod ON talleres(cod_taller);
CREATE INDEX idx_cursos_cod ON cursos(cod_curso);
CREATE INDEX idx_inventario_cod ON inventario(cod_equipo);

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (username, email, password_hash, nombre, apellido, rol, creado_por) 
VALUES ('admin', 'admin@sacrgapi.com', '$2y$10$oZvMv/mqns.gnBi1w/QiF.uF/guZ/8gbC8PIjzu4f0k2bLqBBggD2', 'Administrador', 'Sistema', 'admin', 1);

-- =============================================
-- DATOS DE EJEMPLO PARA DEMOSTRACIÓN
-- =============================================

-- Insertar programas de ejemplo
INSERT INTO programas (cod_programas, descripcion, sub_area) VALUES
('PROG001', 'Programa de Formación Técnica en Informática', 'Tecnología'),
('PROG002', 'Programa de Capacitación Docente', 'Educación'),
('PROG003', 'Programa de Desarrollo Comunitario', 'Social'),
('PROG004', 'Programa de Emprendimiento', 'Empresarial'),
('PROG005', 'Programa de Idiomas', 'Lingüística');

-- Insertar instituciones de ejemplo
INSERT INTO instituciones (cod_institucion, descripcion, telefono_enlace, datos_docente_enlace) VALUES
('INST001', 'Instituto Tecnológico Nacional', '0212-555-0001', 'Dr. Juan Pérez - Coordinador Académico'),
('INST002', 'Universidad Central de Venezuela', '0212-555-0002', 'Dra. María González - Directora de Extensión'),
('INST003', 'Centro de Formación Profesional', '0212-555-0003', 'Ing. Carlos Rodríguez - Jefe de Programas'),
('INST004', 'Instituto de Capacitación Laboral', '0212-555-0004', 'Lic. Ana Silva - Coordinadora de Proyectos'),
('INST005', 'Centro de Estudios Avanzados', '0212-555-0005', 'Prof. Luis Herrera - Director Académico');

-- Insertar personal de ejemplo
INSERT INTO personal (cedula_personal, apellido, nombre, cargo, fecha_ingreso) VALUES
('V-12345678', 'García', 'Ana', 'Coordinadora General', '2024-01-15'),
('V-87654321', 'López', 'Pedro', 'Instructor Técnico', '2024-02-01'),
('V-11223344', 'Martínez', 'Carmen', 'Supervisora Académica', '2024-01-20'),
('V-55667788', 'Rodríguez', 'Luis', 'Instructor de Programación', '2024-02-15'),
('V-99887766', 'Silva', 'Elena', 'Coordinadora de Proyectos', '2024-01-10'),
('V-44332211', 'Herrera', 'Roberto', 'Supervisor de Calidad', '2024-03-01');

-- Insertar estudiantes de ejemplo
INSERT INTO estudiante (cedula_estudiante, apellido, nombre, apellido_2, sexo, lugar_nacimiento, cedula_representante, telefono, instituciones_id_instituciones) VALUES
('V-11111111', 'Pérez', 'María', 'González', 'F', 'Caracas', 'V-22222222', '0412-111-1111', 1),
('V-33333333', 'González', 'Carlos', 'López', 'M', 'Valencia', 'V-44444444', '0414-333-3333', 1),
('V-55555555', 'Martínez', 'Ana', 'Silva', 'F', 'Maracaibo', 'V-66666666', '0424-555-5555', 2),
('V-77777777', 'Rodríguez', 'José', 'Herrera', 'M', 'Barquisimeto', 'V-88888888', '0416-777-7777', 2),
('V-99999999', 'López', 'Carmen', 'Pérez', 'F', 'Valencia', 'V-10101010', '0426-999-9999', 3),
('V-12121212', 'Silva', 'Luis', 'García', 'M', 'Caracas', 'V-13131313', '0412-121-1212', 3);

-- Insertar talleres de ejemplo
INSERT INTO talleres (programas_id_programas, instituciones_id_instituciones, personal_id_personal, cod_taller, cod_institucion, cod_programa, cod_personal, ano_escolar, grado, seccion, lapso) VALUES
(1, 1, 1, 'TALL001', 'INST001', 'PROG001', 'V-12345678', 2024, '1er', 'A', 'I'),
(1, 1, 2, 'TALL002', 'INST001', 'PROG001', 'V-87654321', 2024, '2do', 'B', 'I'),
(2, 2, 3, 'TALL003', 'INST002', 'PROG002', 'V-11223344', 2024, '3er', 'A', 'I'),
(1, 3, 4, 'TALL004', 'INST003', 'PROG001', 'V-55667788', 2024, '1er', 'C', 'I'),
(3, 4, 5, 'TALL005', 'INST004', 'PROG003', 'V-99887766', 2024, '2do', 'A', 'I');

-- Insertar cursos de ejemplo
INSERT INTO cursos (personal_id_personal, cod_curso, nombre_curso, cedula_persona, duracion, ano, num_de_clases, periodo) VALUES
(1, 'CUR001', 'Introducción a la Programación', 'V-12345678', 40, 2024, 20, 'I'),
(2, 'CUR002', 'Desarrollo Web con PHP', 'V-87654321', 60, 2024, 30, 'I'),
(3, 'CUR003', 'Metodologías de Enseñanza', 'V-11223344', 32, 2024, 16, 'I'),
(4, 'CUR004', 'Base de Datos MySQL', 'V-55667788', 48, 2024, 24, 'I'),
(5, 'CUR005', 'Gestión de Proyectos Comunitarios', 'V-99887766', 36, 2024, 18, 'I');

-- Insertar calificaciones de ejemplo
INSERT INTO calificaciones (estudiante_id_estudiante, cedula_estudiante, cod_taller, literal, numeral, momento_i, momento_ii, momento_iii, promedio) VALUES
(1, 'V-11111111', 'TALL001', 'A', 18.5, 18.0, 19.0, 18.5, 18.5),
(2, 'V-33333333', 'TALL001', 'B', 16.0, 15.5, 16.5, 16.0, 16.0),
(3, 'V-55555555', 'TALL002', 'A', 19.0, 18.5, 19.5, 19.0, 19.0),
(4, 'V-77777777', 'TALL002', 'B', 17.0, 16.5, 17.5, 17.0, 17.0),
(5, 'V-99999999', 'TALL003', 'A', 18.0, 17.5, 18.5, 18.0, 18.0),
(6, 'V-12121212', 'TALL003', 'B', 16.5, 16.0, 17.0, 16.5, 16.5);

-- Insertar inventario de ejemplo
INSERT INTO inventario (personal_id_personal, cod_equipo, ubicacion, cedula_personal, cantidad, estado, serial, marca, modelo, color, medidas, capacidad, otras_caracteristicas, observacion) VALUES
(1, 'EQ001', 'Aula 101', 'V-12345678', 1, 'Bueno', 'SN123456', 'Dell', 'OptiPlex 7090', 'Negro', '35x25x8 cm', '8GB RAM', 'Intel i5, 256GB SSD', 'Equipo en perfecto estado'),
(1, 'EQ002', 'Aula 101', 'V-12345678', 1, 'Bueno', 'SN789012', 'HP', 'ProBook 450', 'Plata', '35x25x3 cm', '16GB RAM', 'Intel i7, 512GB SSD', 'Laptop para instructor'),
(2, 'EQ003', 'Aula 102', 'V-87654321', 1, 'Regular', 'SN345678', 'Lenovo', 'ThinkPad E15', 'Negro', '36x25x2 cm', '8GB RAM', 'AMD Ryzen 5, 256GB SSD', 'Requiere mantenimiento menor'),
(3, 'EQ004', 'Oficina Principal', 'V-11223344', 1, 'Bueno', 'SN901234', 'Canon', 'PIXMA G3110', 'Blanco', '45x35x15 cm', 'Impresión A4', 'Impresora multifuncional', 'Nueva, sin usar'),
(4, 'EQ005', 'Aula 103', 'V-55667788', 1, 'Bueno', 'SN567890', 'Epson', 'PowerLite 1781W', 'Blanco', '30x25x10 cm', '3000 lúmenes', 'Proyector portátil', 'Excelente calidad de imagen');

-- Insertar participantes de ejemplo
INSERT INTO participantes (cursos_id_cursos, cedula_participante, apellido, nombre, sexo, telefono, correo, direccion) VALUES
(1, 'V-11111111', 'Pérez', 'María', 'F', '0412-111-1111', 'maria.perez@email.com', 'Av. Principal, Caracas'),
(1, 'V-33333333', 'González', 'Carlos', 'M', '0414-333-3333', 'carlos.gonzalez@email.com', 'Calle 2, Valencia'),
(2, 'V-55555555', 'Martínez', 'Ana', 'F', '0424-555-5555', 'ana.martinez@email.com', 'Av. Libertador, Maracaibo'),
(2, 'V-77777777', 'Rodríguez', 'José', 'M', '0416-777-7777', 'jose.rodriguez@email.com', 'Carrera 5, Barquisimeto'),
(3, 'V-99999999', 'López', 'Carmen', 'F', '0426-999-9999', 'carmen.lopez@email.com', 'Av. Bolívar, Valencia'),
(4, 'V-12121212', 'Silva', 'Luis', 'M', '0412-121-1212', 'luis.silva@email.com', 'Calle 10, Caracas');

-- Insertar notas de ejemplo
INSERT INTO notas (participantes_id_participantes, cedula_participante, cod_curso, aprobo, participo, no_aprobo, recibio_certificado) VALUES
(1, 'V-11111111', 'CUR001', TRUE, TRUE, FALSE, TRUE),
(2, 'V-33333333', 'CUR001', TRUE, TRUE, FALSE, TRUE),
(3, 'V-55555555', 'CUR002', TRUE, TRUE, FALSE, TRUE),
(4, 'V-77777777', 'CUR002', FALSE, TRUE, TRUE, FALSE),
(5, 'V-99999999', 'CUR003', TRUE, TRUE, FALSE, TRUE),
(6, 'V-12121212', 'CUR004', TRUE, TRUE, FALSE, TRUE);

-- =============================================
-- TRIGGERS PARA AUDITORÍA
-- =============================================

-- Nota: Los triggers con DELIMITER no funcionan bien con docker-compose exec
-- Se puede crear manualmente después si es necesario:
-- 
-- DELIMITER //
-- CREATE TRIGGER calcular_promedio_calificaciones
-- BEFORE UPDATE ON calificaciones
-- FOR EACH ROW
-- BEGIN
--     IF NEW.momento_i IS NOT NULL AND NEW.momento_ii IS NOT NULL AND NEW.momento_iii IS NOT NULL THEN
--         SET NEW.promedio = (NEW.momento_i + NEW.momento_ii + NEW.momento_iii) / 3;
--     END IF;
-- END//
-- DELIMITER ;

-- =============================================
-- TABLA INTERMEDIA ESTUDIANTE-TALLER
-- =============================================

CREATE TABLE estudiante_taller (
    id_estudiante_taller INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id_estudiante INT NOT NULL,
    talleres_id_taller INT NOT NULL,
    instituciones_id_instituciones INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (estudiante_id_estudiante) REFERENCES estudiante(id_estudiante) ON DELETE CASCADE,
    FOREIGN KEY (talleres_id_taller) REFERENCES talleres(id_taller) ON DELETE CASCADE,
    FOREIGN KEY (instituciones_id_instituciones) REFERENCES instituciones(id_instituciones) ON DELETE CASCADE,
    UNIQUE KEY unique_estudiante_taller (estudiante_id_estudiante, talleres_id_taller)
);

-- =============================================
-- VISTAS ÚTILES
-- =============================================

-- Vista para información completa de talleres
CREATE VIEW vista_talleres_completa AS
SELECT 
    t.id_taller,
    t.cod_taller,
    t.ano_escolar,
    t.grado,
    t.seccion,
    t.lapso,
    p.descripcion as programa,
    i.descripcion as institucion,
    CONCAT(per.nombre, ' ', per.apellido) as instructor,
    per.cargo
FROM talleres t
JOIN programas p ON t.programas_id_programas = p.id_programas
JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
JOIN personal per ON t.personal_id_personal = per.id_personal
WHERE t.activo = TRUE;

-- Vista para información de estudiantes con calificaciones
CREATE VIEW vista_estudiantes_calificaciones AS
SELECT 
    e.id_estudiante,
    e.cedula_estudiante,
    CONCAT(e.nombre, ' ', e.apellido) as nombre_completo,
    e.sexo,
    c.cod_taller,
    c.literal,
    c.numeral,
    c.promedio
FROM estudiante e
LEFT JOIN calificaciones c ON e.id_estudiante = c.estudiante_id_estudiante
WHERE e.activo = TRUE;
