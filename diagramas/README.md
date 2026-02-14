# 📊 Diagramas del Sistema SACRGAPI

Este directorio contiene todos los diagramas de casos de uso, flujos de trabajo y arquitectura del **Sistema Automatizado para el Control y Registro de la Gestión Administrativa (SACRGAPI)**.

## 📁 Estructura de Archivos

```
diagramas/
├── README.md                    # Este archivo
├── casos_uso_principal.md       # Diagramas de casos de uso principales
├── flujo_trabajo.md            # Diagramas de flujo de trabajo
└── arquitectura.md             # Diagramas de arquitectura del sistema
```

## 📋 Contenido de los Diagramas

### 1. **Casos de Uso Principales** (`casos_uso_principal.md`)

Contiene 13 diagramas de casos de uso que cubren:

- **Diagrama Principal del Sistema**: Vista general de todos los módulos
- **Autenticación y Autorización**: Login, logout, cambio de contraseña
- **Gestión de Usuarios**: CRUD de usuarios con roles y permisos
- **Gestión de Estudiantes**: CRUD de estudiantes con relaciones
- **Gestión de Personal**: CRUD de personal con asignaciones
- **Gestión de Talleres**: CRUD de talleres con relaciones complejas
- **Gestión de Cursos**: CRUD de cursos con instructores
- **Gestión de Inventario**: CRUD de inventario con asignaciones
- **Gestión de Programas**: CRUD de programas educativos
- **Gestión de Instituciones**: CRUD de instituciones
- **Gestión de Calificaciones**: CRUD de calificaciones con filtros
- **Dashboard y Reportes**: Estadísticas y visualizaciones
- **Relaciones entre Módulos**: Interconexiones del sistema

### 2. **Flujos de Trabajo** (`flujo_trabajo.md`)

Contiene 10 diagramas de flujo que muestran:

- **Flujo de Autenticación**: Proceso de login y validación
- **Flujo de Creación de Taller**: Proceso completo de creación
- **Flujo de Gestión de Usuarios**: Procesos de CRUD de usuarios
- **Flujo de Gestión de Calificaciones**: Procesos con filtros
- **Flujo de Dashboard y Reportes**: Carga de estadísticas
- **Flujo de Búsqueda y Filtros**: Procesos de consulta
- **Flujo de Eliminación Lógica**: Proceso de desactivación
- **Flujo de Relaciones entre Módulos**: Interconexiones
- **Flujo de Validación de Permisos**: Control de acceso
- **Flujo de Carga de Opciones**: Carga dinámica de formularios

### 3. **Arquitectura del Sistema** (`arquitectura.md`)

Contiene 8 diagramas de arquitectura que incluyen:

- **Arquitectura General**: Vista de capas del sistema
- **Arquitectura de Módulos**: Organización de componentes
- **Arquitectura de Base de Datos**: Modelo entidad-relación
- **Arquitectura de Contenedores Docker**: Infraestructura
- **Arquitectura de Seguridad**: Capas de protección
- **Arquitectura de API y Rutas**: Endpoints del sistema
- **Arquitectura de Frontend**: Componentes de interfaz
- **Flujo de Datos**: Secuencia de procesamiento

## 👥 Actores del Sistema

### 👨‍💼 **Administrador**
- **Acceso completo** a todos los módulos
- **Gestión de usuarios** (crear, editar, eliminar, activar/desactivar)
- **Configuración del sistema**
- **Todos los permisos** de Supervisor y Operador

### 👨‍💻 **Supervisor**
- **Gestión de usuarios** (excepto otros administradores)
- **Acceso a todos los módulos** operativos
- **Reportes y estadísticas**
- **Todos los permisos** de Operador

### 👨‍🔧 **Operador**
- **Acceso a módulos operativos** (estudiantes, personal, talleres, cursos, inventario, programas, instituciones, calificaciones)
- **Gestión de datos** (CRUD completo)
- **Sin acceso** a gestión de usuarios
- **Acceso limitado** a reportes

## 🎯 Módulos del Sistema

### 🔐 **Autenticación y Autorización**
- Iniciar sesión
- Cerrar sesión
- Cambiar contraseña
- Verificar permisos por rol

### 👥 **Gestión de Usuarios**
- CRUD completo de usuarios
- Asignación de roles
- Activación/desactivación
- Control de acceso

### 👨‍🎓 **Gestión de Estudiantes**
- CRUD completo de estudiantes
- Relación con instituciones
- Filtros y búsquedas
- Integración con calificaciones

### 👨‍🏫 **Gestión de Personal**
- CRUD completo de personal
- Asignación a talleres y cursos
- Gestión de inventario
- Relaciones con otros módulos

### 🔧 **Gestión de Talleres**
- CRUD completo de talleres
- Relación con programas, instituciones e instructores
- Filtros por año escolar y lapso
- Estadísticas y reportes

### 📚 **Gestión de Cursos**
- CRUD completo de cursos
- Asignación de instructores
- Gestión de participantes y notas
- Filtros por año y periodo

### 📦 **Gestión de Inventario**
- CRUD completo de inventario
- Relación con talleres y personal
- Control de stock
- Filtros y búsquedas

### 📋 **Gestión de Programas**
- CRUD completo de programas
- Asignación de sub-áreas
- Relación con talleres
- Estadísticas

### 🏫 **Gestión de Instituciones**
- CRUD completo de instituciones
- Gestión de datos de enlace
- Relación con estudiantes y talleres
- Estadísticas

### 📊 **Gestión de Calificaciones**
- CRUD completo de calificaciones
- Filtros por estudiante e institución
- Cálculo de promedios
- Reportes y estadísticas

### 📈 **Dashboard y Reportes**
- Vista general del sistema
- Estadísticas por módulo
- Gráficos y visualizaciones
- Exportación de reportes

## 🔗 Relaciones entre Módulos

### **Relaciones Principales:**
- **Usuarios** → Todos los módulos (control de acceso)
- **Programas** → Talleres
- **Instituciones** → Talleres, Estudiantes, Calificaciones
- **Personal** → Talleres, Cursos, Inventario
- **Estudiantes** → Calificaciones
- **Talleres** → Inventario

### **Flujos de Datos:**
1. **Creación**: Usuario → Formulario → Validación → Base de Datos
2. **Consulta**: Usuario → Filtros → Consulta SQL → Resultados
3. **Actualización**: Usuario → Formulario → Validación → Actualización
4. **Eliminación**: Usuario → Confirmación → Eliminación lógica

## 🛠️ Tecnologías Utilizadas

### **Backend:**
- **PHP 8.2** - Lógica de aplicación
- **MySQL 8.0** - Base de datos
- **Apache** - Servidor web

### **Frontend:**
- **HTML5/CSS3** - Estructura y estilos
- **Bootstrap 5** - Framework CSS
- **jQuery** - JavaScript
- **DataTables** - Tablas dinámicas
- **Font Awesome** - Iconos

### **Infraestructura:**
- **Docker** - Contenedores
- **Docker Compose** - Orquestación
- **phpMyAdmin** - Administración de BD

## 📊 Cómo Visualizar los Diagramas

Los diagramas están escritos en **Mermaid** y se pueden visualizar en:

1. **GitHub**: Los diagramas se renderizan automáticamente
2. **Mermaid Live Editor**: https://mermaid.live/
3. **VS Code**: Con extensión de Mermaid
4. **Herramientas de documentación**: GitBook, Notion, etc.

## 🔄 Actualización de Diagramas

Los diagramas deben actualizarse cuando:

- Se agreguen nuevos módulos al sistema
- Se modifiquen las relaciones entre entidades
- Se cambien los permisos de usuarios
- Se actualice la arquitectura del sistema
- Se modifiquen los flujos de trabajo

## 📝 Notas de Desarrollo

- Los diagramas reflejan el estado actual del sistema
- Se mantienen sincronizados con el código fuente
- Incluyen todos los casos de uso implementados
- Documentan las relaciones entre módulos
- Muestran la arquitectura completa del sistema

---

**Sistema SACRGAPI** - Sistema Automatizado para el Control y Registro de la Gestión Administrativa
