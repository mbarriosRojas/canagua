#!/bin/bash

# Script de verificación de instalación
# Sistema SACRGAPI

echo "============================================="
echo "  VERIFICADOR DE INSTALACIÓN SACRGAPI"
echo "============================================="
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

show_message() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

show_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

show_error() {
    echo -e "${RED}[✗]${NC} $1"
}

show_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Verificar Docker
echo "1. Verificando Docker..."
if command -v docker &> /dev/null; then
    show_success "Docker está instalado"
    docker --version
else
    show_error "Docker NO está instalado"
    exit 1
fi

# Verificar Docker Compose
echo ""
echo "2. Verificando Docker Compose..."
if command -v docker-compose &> /dev/null; then
    show_success "Docker Compose está instalado"
    docker-compose --version
else
    show_error "Docker Compose NO está instalado"
    exit 1
fi

# Verificar archivos necesarios
echo ""
echo "3. Verificando archivos del proyecto..."
files=("docker-compose.yml" "Dockerfile" "config.env" "database/init.sql" "install.sh")
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        show_success "$file existe"
    else
        show_error "$file NO existe"
    fi
done

# Verificar permisos del script
echo ""
echo "4. Verificando permisos..."
if [ -x "install.sh" ]; then
    show_success "install.sh tiene permisos de ejecución"
else
    show_warning "install.sh NO tiene permisos de ejecución"
    echo "Ejecutando: chmod +x install.sh"
    chmod +x install.sh
    show_success "Permisos agregados"
fi

# Verificar estado de Docker
echo ""
echo "5. Verificando estado de Docker..."
if docker info &> /dev/null; then
    show_success "Docker está ejecutándose"
else
    show_error "Docker NO está ejecutándose"
    echo "Inicie Docker Desktop o el servicio de Docker"
    exit 1
fi

# Verificar contenedores existentes
echo ""
echo "6. Verificando contenedores existentes..."
if docker ps -a | grep -q sacrgapi; then
    show_warning "Hay contenedores SACRGAPI existentes:"
    docker ps -a | grep sacrgapi
    echo ""
    echo "¿Desea detenerlos y recrearlos? (y/n)"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        docker-compose down
        show_success "Contenedores detenidos"
    fi
else
    show_success "No hay contenedores SACRGAPI existentes"
fi

echo ""
echo "============================================="
echo "  VERIFICACIÓN COMPLETADA"
echo "============================================="
echo ""
echo "Si todo está correcto, puede ejecutar:"
echo "  ./install.sh"
echo ""
echo "O si prefiere hacerlo paso a paso:"
echo "  1. docker-compose up --build -d"
echo "  2. Esperar a que la base de datos esté lista"
echo "  3. Acceder a http://localhost:8080"
echo ""
