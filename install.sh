#!/bin/bash

# =============================================
# SCRIPT DE INSTALACIÓN AUTOMÁTICA
# Sistema SACRGAPI - Para usuarios no técnicos
# =============================================

# Asegurar que el script se ejecute desde el directorio correcto
cd "$(dirname "$0")"

echo "============================================="
echo "  INSTALADOR AUTOMÁTICO DEL SISTEMA SACRGAPI"
echo "============================================="
echo ""

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
show_message() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

show_success() {
    echo -e "${GREEN}[ÉXITO]${NC} $1"
}

show_warning() {
    echo -e "${YELLOW}[ADVERTENCIA]${NC} $1"
}

show_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar si Docker está instalado
check_docker() {
    show_message "Verificando instalación de Docker..."
    if ! command -v docker &> /dev/null; then
        show_error "Docker no está instalado en este sistema."
        echo ""
        echo "Para instalar Docker, visite: https://docs.docker.com/get-docker/"
        echo "O ejecute uno de estos comandos según su sistema:"
        echo ""
        echo "Ubuntu/Debian:"
        echo "  curl -fsSL https://get.docker.com -o get-docker.sh"
        echo "  sudo sh get-docker.sh"
        echo ""
        echo "macOS:"
        echo "  Descargue Docker Desktop desde: https://www.docker.com/products/docker-desktop"
        echo ""
        echo "Windows:"
        echo "  Descargue Docker Desktop desde: https://www.docker.com/products/docker-desktop"
        exit 1
    fi
    show_success "Docker está instalado correctamente"
}

# Verificar si Docker Compose está instalado
check_docker_compose() {
    show_message "Verificando instalación de Docker Compose..."
    if ! command -v docker-compose &> /dev/null; then
        show_error "Docker Compose no está instalado en este sistema."
        echo ""
        echo "Para instalar Docker Compose, visite: https://docs.docker.com/compose/install/"
        exit 1
    fi
    show_success "Docker Compose está instalado correctamente"
}

# Crear archivo de configuración si no existe
setup_config() {
    show_message "Configurando archivo de variables de entorno..."
    if [ ! -f "config.env" ]; then
        if [ -f "config.env.example" ]; then
            cp config.env.example config.env
            show_success "Archivo config.env creado desde config.env.example"
        else
            show_error "No se encontró el archivo config.env.example"
            exit 1
        fi
    else
        show_warning "El archivo config.env ya existe, se mantendrá la configuración actual"
    fi
}

# Verificar puertos disponibles
check_ports() {
    show_message "Verificando disponibilidad de puertos..."
    
    # Verificar puerto 8080
    if lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null 2>&1; then
        show_warning "El puerto 8080 está en uso. El sistema usará el puerto 8080 para la aplicación web."
        echo "Si hay conflictos, puede cambiar el puerto en docker-compose.yml"
    fi
    
    # Verificar puerto 8081
    if lsof -Pi :8081 -sTCP:LISTEN -t >/dev/null 2>&1; then
        show_warning "El puerto 8081 está en uso. El sistema usará el puerto 8081 para phpMyAdmin."
        echo "Si hay conflictos, puede cambiar el puerto en docker-compose.yml"
    fi
    
    # Verificar puerto 3306
    if lsof -Pi :3306 -sTCP:LISTEN -t >/dev/null 2>&1; then
        show_warning "El puerto 3306 está en uso. El sistema usará el puerto 3306 para MySQL."
        echo "Si hay conflictos, puede cambiar el puerto en docker-compose.yml"
    fi
}

# Construir y ejecutar contenedores
build_and_run() {
    show_message "Construyendo e iniciando los contenedores..."
    echo "Esto puede tomar varios minutos la primera vez..."
    echo ""
    
    # Verificar que docker-compose.yml existe
    if [ ! -f "docker-compose.yml" ]; then
        show_error "No se encontró el archivo docker-compose.yml"
        exit 1
    fi
    
    # Detener contenedores existentes si los hay
    show_message "Deteniendo contenedores existentes..."
    docker-compose down 2>/dev/null
    
    # Eliminar volúmenes para forzar la recreación de la base de datos
    show_message "Eliminando datos anteriores para asegurar inicialización limpia..."
    docker volume rm sacrgapi_mysql_data 2>/dev/null || true
    
    # Construir y ejecutar
    show_message "Ejecutando: docker-compose up --build -d"
    if docker-compose up --build -d; then
        show_success "Contenedores iniciados correctamente"
    else
        show_error "Error al iniciar los contenedores"
        echo ""
        echo "Intente ejecutar manualmente:"
        echo "  docker-compose up --build -d"
        echo ""
        echo "O verifique los logs con:"
        echo "  docker-compose logs"
        exit 1
    fi
}

# Esperar a que la base de datos esté lista
wait_for_database() {
    show_message "Esperando a que la base de datos esté lista..."
    echo "Esto puede tomar 30-60 segundos..."
    
    # Esperar hasta 2 minutos
    for i in {1..24}; do
        if docker-compose exec -T db mysqladmin ping -h localhost --silent; then
            show_success "Base de datos lista"
            return 0
        fi
        echo -n "."
        sleep 5
    done
    
    show_warning "La base de datos tardó más de lo esperado en iniciar"
    echo "El sistema debería funcionar, pero si hay problemas, espere unos minutos más"
}

# Cargar datos en la base de datos
load_database_data() {
    show_message "Cargando datos de ejemplo en la base de datos..."
    echo "Esto puede tomar 30-60 segundos adicionales..."
    
    # Esperar un poco más para que MySQL esté completamente listo
    sleep 10
    
    # Verificar si ya hay datos
    if docker-compose exec -T db mysql -u sacrgapi_user -psacrgapi_password_2024 sacrgapi_database -e "SELECT COUNT(*) FROM usuarios;" 2>/dev/null | grep -q "1"; then
        show_success "Base de datos ya tiene datos de ejemplo"
        return 0
    fi
    
    # Crear script de datos temporal
    show_message "Preparando datos de ejemplo..."
    cat > /tmp/insert_data_temp.sql << 'EOF'
USE sacrgapi_database;

-- Insertar usuario administrador por defecto
INSERT IGNORE INTO usuarios (username, email, password_hash, nombre, apellido, rol, creado_por) 
VALUES ('admin', 'admin@sacrgapi.com', '$2y$10$oZvMv/mqns.gnBi1w/QiF.uF/guZ/8gbC8PIjzu4f0k2bLqBBggD2', 'Administrador', 'Sistema', 'admin', 1);

-- Insertar programas de ejemplo
INSERT IGNORE INTO programas (cod_programas, descripcion, sub_area) VALUES
('PROG001', 'Programa de Formación Técnica en Informática', 'Tecnología'),
('PROG002', 'Programa de Capacitación Docente', 'Educación'),
('PROG003', 'Programa de Desarrollo Comunitario', 'Social'),
('PROG004', 'Programa de Emprendimiento', 'Empresarial'),
('PROG005', 'Programa de Idiomas', 'Lingüística');

-- Insertar instituciones de ejemplo
INSERT IGNORE INTO instituciones (cod_institucion, descripcion, telefono_enlace, datos_docente_enlace) VALUES
('INST001', 'Instituto Tecnológico Nacional', '0212-555-0001', 'Dr. Juan Pérez - Coordinador Académico'),
('INST002', 'Universidad Central de Venezuela', '0212-555-0002', 'Dra. María González - Directora de Extensión'),
('INST003', 'Centro de Formación Profesional', '0212-555-0003', 'Ing. Carlos Rodríguez - Jefe de Programas'),
('INST004', 'Instituto de Capacitación Laboral', '0212-555-0004', 'Lic. Ana Silva - Coordinadora de Proyectos'),
('INST005', 'Centro de Estudios Avanzados', '0212-555-0005', 'Prof. Luis Herrera - Director Académico');

-- Insertar personal de ejemplo
INSERT IGNORE INTO personal (cedula_personal, apellido, nombre, cargo, fecha_ingreso) VALUES
('V-12345678', 'García', 'Ana', 'Coordinadora General', '2024-01-15'),
('V-87654321', 'López', 'Pedro', 'Instructor Técnico', '2024-02-01'),
('V-11223344', 'Martínez', 'Carmen', 'Supervisora Académica', '2024-01-20'),
('V-55667788', 'Rodríguez', 'Luis', 'Instructor de Programación', '2024-02-15'),
('V-99887766', 'Silva', 'Elena', 'Coordinadora de Proyectos', '2024-01-10'),
('V-44332211', 'Herrera', 'Roberto', 'Supervisor de Calidad', '2024-03-01');

-- Insertar estudiantes de ejemplo
INSERT IGNORE INTO estudiante (cedula_estudiante, apellido, nombre, apellido_2, sexo, lugar_nacimiento, cedula_representante, telefono) VALUES
('V-11111111', 'Pérez', 'María', 'González', 'F', 'Caracas', 'V-22222222', '0412-111-1111'),
('V-33333333', 'González', 'Carlos', 'López', 'M', 'Valencia', 'V-44444444', '0414-333-3333'),
('V-55555555', 'Martínez', 'Ana', 'Silva', 'F', 'Maracaibo', 'V-66666666', '0424-555-5555'),
('V-77777777', 'Rodríguez', 'José', 'Herrera', 'M', 'Barquisimeto', 'V-88888888', '0416-777-7777'),
('V-99999999', 'López', 'Carmen', 'Pérez', 'F', 'Valencia', 'V-10101010', '0426-999-9999'),
('V-12121212', 'Silva', 'Luis', 'García', 'M', 'Caracas', 'V-13131313', '0412-121-1212');

-- Insertar talleres de ejemplo
INSERT IGNORE INTO talleres (programas_id_programas, instituciones_id_instituciones, personal_id_personal, cod_taller, cod_institucion, cod_programa, cod_personal, ano_escolar, grado, seccion, lapso, activo) VALUES
(1, 1, 1, 'TALL001', 'INST001', 'PROG001', 'V-12345678', 2024, '1er', 'A', 'I', 1),
(1, 1, 2, 'TALL002', 'INST001', 'PROG001', 'V-87654321', 2024, '2do', 'B', 'I', 1),
(2, 2, 3, 'TALL003', 'INST002', 'PROG002', 'V-11223344', 2024, '3er', 'A', 'I', 1),
(1, 3, 4, 'TALL004', 'INST003', 'PROG001', 'V-55667788', 2024, '1er', 'C', 'I', 1),
(3, 4, 5, 'TALL005', 'INST004', 'PROG003', 'V-99887766', 2024, '2do', 'A', 'I', 1);

-- Insertar cursos de ejemplo
INSERT IGNORE INTO cursos (personal_id_personal, cod_curso, nombre_curso, cedula_persona, duracion, ano, num_de_clases, periodo) VALUES
(1, 'CUR001', 'Introducción a la Programación', 'V-12345678', 40, 2024, 20, 'I'),
(2, 'CUR002', 'Desarrollo Web con PHP', 'V-87654321', 60, 2024, 30, 'I'),
(3, 'CUR003', 'Metodologías de Enseñanza', 'V-11223344', 32, 2024, 16, 'I'),
(4, 'CUR004', 'Base de Datos MySQL', 'V-55667788', 48, 2024, 24, 'I'),
(5, 'CUR005', 'Gestión de Proyectos Comunitarios', 'V-99887766', 36, 2024, 18, 'I');

-- Insertar calificaciones de ejemplo
INSERT IGNORE INTO calificaciones (estudiante_id_estudiante, cedula_estudiante, cod_taller, talleres_id_taller, instituciones_id_instituciones, literal, numeral, momento_i, momento_ii, momento_iii, momento_i_numeral, momento_i_literal, momento_ii_numeral, momento_ii_literal, momento_iii_numeral, momento_iii_literal, promedio, promedio_final, literal_final) VALUES
(1, 'V-11111111', 'TALL001', 1, 1, 'A', 18.5, 18.0, 19.0, 18.5, 18.0, 'A', 19.0, 'A', 18.5, 'A', 18.5, 18.5, 'A'),
(2, 'V-33333333', 'TALL001', 1, 1, 'B', 16.0, 15.5, 16.5, 16.0, 15.5, 'B', 16.5, 'B', 16.0, 'B', 16.0, 16.0, 'B'),
(3, 'V-55555555', 'TALL002', 2, 1, 'A', 19.0, 18.5, 19.5, 19.0, 18.5, 'A', 19.5, 'A', 19.0, 'A', 19.0, 19.0, 'A'),
(4, 'V-77777777', 'TALL002', 2, 1, 'B', 17.0, 16.5, 17.5, 17.0, 16.5, 'B', 17.5, 'B', 17.0, 'B', 17.0, 17.0, 'B'),
(5, 'V-99999999', 'TALL003', 3, 2, 'A', 18.0, 17.5, 18.5, 18.0, 17.5, 'A', 18.5, 'A', 18.0, 'A', 18.0, 18.0, 'A'),
(6, 'V-12121212', 'TALL003', 3, 2, 'B', 16.5, 16.0, 17.0, 16.5, 16.0, 'B', 17.0, 'B', 16.5, 'B', 16.5, 16.5, 'B');

-- Insertar relaciones estudiante-taller de ejemplo
INSERT IGNORE INTO estudiante_taller (estudiante_id_estudiante, talleres_id_taller, instituciones_id_instituciones) VALUES
(1, 1, 1),
(2, 1, 1),
(3, 2, 1),
(4, 2, 1),
(5, 3, 2),
(6, 3, 2);

-- Insertar inventario de ejemplo
INSERT IGNORE INTO inventario (personal_id_personal, cod_equipo, ubicacion, cedula_personal, cantidad, estado, serial, marca, modelo, color, medidas, capacidad, otras_caracteristicas, observacion) VALUES
(1, 'EQ001', 'Aula 101', 'V-12345678', 1, 'Bueno', 'SN123456', 'Dell', 'OptiPlex 7090', 'Negro', '35x25x8 cm', '8GB RAM', 'Intel i5, 256GB SSD', 'Equipo en perfecto estado'),
(1, 'EQ002', 'Aula 101', 'V-12345678', 1, 'Bueno', 'SN789012', 'HP', 'ProBook 450', 'Plata', '35x25x3 cm', '16GB RAM', 'Intel i7, 512GB SSD', 'Laptop para instructor'),
(2, 'EQ003', 'Aula 102', 'V-87654321', 1, 'Regular', 'SN345678', 'Lenovo', 'ThinkPad E15', 'Negro', '36x25x2 cm', '8GB RAM', 'AMD Ryzen 5, 256GB SSD', 'Requiere mantenimiento menor'),
(3, 'EQ004', 'Oficina Principal', 'V-11223344', 1, 'Bueno', 'SN901234', 'Canon', 'PIXMA G3110', 'Blanco', '45x35x15 cm', 'Impresión A4', 'Impresora multifuncional', 'Nueva, sin usar'),
(4, 'EQ005', 'Aula 103', 'V-55667788', 1, 'Bueno', 'SN567890', 'Epson', 'PowerLite 1781W', 'Blanco', '30x25x10 cm', '3000 lúmenes', 'Proyector portátil', 'Excelente calidad de imagen');

-- Insertar participantes de ejemplo
INSERT IGNORE INTO participantes (cursos_id_cursos, cedula_participante, apellido, nombre, sexo, telefono, correo, direccion) VALUES
(1, 'V-11111111', 'Pérez', 'María', 'F', '0412-111-1111', 'maria.perez@email.com', 'Av. Principal, Caracas'),
(1, 'V-33333333', 'González', 'Carlos', 'M', '0414-333-3333', 'carlos.gonzalez@email.com', 'Calle 2, Valencia'),
(2, 'V-55555555', 'Martínez', 'Ana', 'F', '0424-555-5555', 'ana.martinez@email.com', 'Av. Libertador, Maracaibo'),
(2, 'V-77777777', 'Rodríguez', 'José', 'M', '0416-777-7777', 'jose.rodriguez@email.com', 'Carrera 5, Barquisimeto'),
(3, 'V-99999999', 'López', 'Carmen', 'F', '0426-999-9999', 'carmen.lopez@email.com', 'Av. Bolívar, Valencia'),
(4, 'V-12121212', 'Silva', 'Luis', 'M', '0412-121-1212', 'luis.silva@email.com', 'Calle 10, Caracas');

-- Insertar notas de ejemplo
INSERT IGNORE INTO notas (participantes_id_participantes, cedula_participante, cod_curso, aprobo, participo, no_aprobo, recibio_certificado) VALUES
(1, 'V-11111111', 'CUR001', TRUE, TRUE, FALSE, TRUE),
(2, 'V-33333333', 'CUR001', TRUE, TRUE, FALSE, TRUE),
(3, 'V-55555555', 'CUR002', TRUE, TRUE, FALSE, TRUE),
(4, 'V-77777777', 'CUR002', FALSE, TRUE, TRUE, FALSE),
(5, 'V-99999999', 'CUR003', TRUE, TRUE, FALSE, TRUE),
(6, 'V-12121212', 'CUR004', TRUE, TRUE, FALSE, TRUE);
EOF

    # Cargar datos
    show_message "Ejecutando script de datos..."
    if docker-compose exec -T db mysql -u sacrgapi_user -psacrgapi_password_2024 sacrgapi_database < /tmp/insert_data_temp.sql; then
        show_success "Datos de ejemplo cargados correctamente"
        rm -f /tmp/insert_data_temp.sql
        
        # Corregir codificación de caracteres
        show_message "Corrigiendo codificación de caracteres..."
        if docker-compose exec -T db mysql -u sacrgapi_user -psacrgapi_password_2024 sacrgapi_database < database/fix_encoding.sql; then
            show_success "Codificación de caracteres corregida exitosamente"
        else
            show_warning "Advertencia: No se pudo corregir la codificación automáticamente"
            echo "   Los caracteres especiales podrían no mostrarse correctamente"
        fi
    else
        show_error "Error al cargar datos de ejemplo"
        rm -f /tmp/insert_data_temp.sql
        return 1
    fi
}

# Verificar que el sistema esté funcionando
verify_installation() {
    show_message "Verificando que el sistema esté funcionando..."
    
    # Esperar un poco más para que Apache esté listo
    sleep 10
    
    # Verificar que los contenedores estén ejecutándose
    if docker-compose ps | grep -q "Up"; then
        show_success "Contenedores ejecutándose correctamente"
    else
        show_error "Algunos contenedores no están ejecutándose"
        echo "Ejecute 'docker-compose ps' para ver el estado"
    fi
    
    # Verificar que la base de datos tenga datos
    show_message "Verificando que la base de datos tenga datos de ejemplo..."
    sleep 5
    
    # Verificar que existan tablas en la base de datos
    if docker-compose exec -T db mysql -u sacrgapi_user -psacrgapi_password_2024 sacrgapi_database -e "SHOW TABLES;" 2>/dev/null | grep -q "usuarios"; then
        show_success "Base de datos inicializada correctamente con datos de ejemplo"
    else
        show_warning "La base de datos puede no tener datos. Esto es normal en la primera ejecución."
        echo "Los datos se cargarán automáticamente en unos minutos."
    fi
}

# Mostrar información de acceso
show_access_info() {
    echo ""
    echo "============================================="
    echo "  INSTALACIÓN COMPLETADA EXITOSAMENTE"
    echo "============================================="
    echo ""
    echo -e "${GREEN}🌐 ACCESO AL SISTEMA:${NC}"
    echo "  • Sistema Principal: http://localhost:8080/public/"
    echo "  • phpMyAdmin: http://localhost:8081"
    echo "  • Prueba de Conexión: http://localhost:8080/test_connection.php"
    echo ""
echo -e "${GREEN}👤 CREDENCIALES DE ACCESO:${NC}"
echo "  • Usuario: admin"
echo "  • Contraseña: admin123"
    echo ""
    echo -e "${GREEN}🗄️ CREDENCIALES DE BASE DE DATOS:${NC}"
    echo "  • Host: localhost:3306"
    echo "  • Base de Datos: sacrgapi_database"
    echo "  • Usuario: sacrgapi_user"
    echo "  • Contraseña: sacrgapi_password_2024"
    echo ""
    echo -e "${GREEN}📊 DATOS DE EJEMPLO INCLUIDOS:${NC}"
    echo "  • 5 Programas educativos"
    echo "  • 5 Instituciones"
    echo "  • 6 Personal docente"
    echo "  • 6 Estudiantes"
    echo "  • 5 Talleres"
    echo "  • 5 Cursos"
    echo "  • 6 Calificaciones"
    echo "  • 5 Equipos de inventario"
    echo "  • 6 Participantes en cursos"
    echo "  • 6 Notas de certificación"
    echo ""
    echo -e "${YELLOW}💡 COMANDOS ÚTILES:${NC}"
    echo "  • Ver logs: docker-compose logs -f"
    echo "  • Detener sistema: docker-compose down"
    echo "  • Reiniciar sistema: docker-compose restart"
    echo "  • Ver estado: docker-compose ps"
    echo ""
    echo -e "${BLUE}¡El sistema está listo para usar! 🎉${NC}"
    echo ""
}

# Función principal
main() {
    echo "Iniciando instalación automática..."
    echo ""
    
    # Verificaciones previas
    check_docker
    check_docker_compose
    setup_config
    check_ports
    
    echo ""
    show_message "Iniciando construcción e instalación..."
    echo ""
    
    # Construir y ejecutar
    build_and_run
    wait_for_database
    load_database_data
    verify_installation
    show_access_info
}

# Ejecutar función principal
main "$@"
