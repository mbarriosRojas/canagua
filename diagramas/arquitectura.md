# Diagramas de Arquitectura - Sistema SACRGAPI

## 1. Arquitectura General del Sistema

```mermaid
graph TB
    %% Capa de Presentación
    subgraph "🌐 Capa de Presentación"
        UI[Interfaz de Usuario]
        Login[Página de Login]
        Dashboard[Dashboard Principal]
        Modulos[Módulos del Sistema]
    end
    
    %% Capa de Lógica de Negocio
    subgraph "⚙️ Capa de Lógica de Negocio"
        Router[Router Principal]
        Auth[Sistema de Autenticación]
        Controllers[Controladores]
        Models[Modelos de Datos]
    end
    
    %% Capa de Datos
    subgraph "🗄️ Capa de Datos"
        Database[(Base de Datos MySQL)]
        Tables[Tablas del Sistema]
    end
    
    %% Capa de Infraestructura
    subgraph "🐳 Capa de Infraestructura"
        Docker[Docker Containers]
        Apache[Apache Web Server]
        PHP[PHP 8.2]
        MySQL[MySQL 8.0]
    end
    
    %% Conexiones
    UI --> Router
    Login --> Auth
    Dashboard --> Controllers
    Modulos --> Controllers
    
    Router --> Auth
    Router --> Controllers
    Controllers --> Models
    Models --> Database
    
    Docker --> Apache
    Docker --> PHP
    Docker --> MySQL
    
    Apache --> PHP
    PHP --> MySQL
    
    %% Estilos
    classDef presentation fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    classDef business fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef data fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef infrastructure fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    
    class UI,Login,Dashboard,Modulos presentation
    class Router,Auth,Controllers,Models business
    class Database,Tables data
    class Docker,Apache,PHP,MySQL infrastructure
```

## 2. Arquitectura de Módulos

```mermaid
graph TB
    %% Módulos principales
    subgraph "📋 Módulos del Sistema"
        Usuarios[👥 Gestión de Usuarios]
        Estudiantes[👨‍🎓 Gestión de Estudiantes]
        Personal[👨‍🏫 Gestión de Personal]
        Talleres[🔧 Gestión de Talleres]
        Cursos[📚 Gestión de Cursos]
        Inventario[📦 Gestión de Inventario]
        Programas[📋 Gestión de Programas]
        Instituciones[🏫 Gestión de Instituciones]
        Calificaciones[📊 Gestión de Calificaciones]
    end
    
    %% Componentes compartidos
    subgraph "🔧 Componentes Compartidos"
        Auth[Autenticación]
        Database[Conexión BD]
        Config[Configuración]
        Router[Router]
    end
    
    %% Base de datos
    subgraph "🗄️ Base de Datos"
        Tables[Tablas del Sistema]
    end
    
    %% Conexiones
    Usuarios --> Auth
    Estudiantes --> Database
    Personal --> Database
    Talleres --> Database
    Cursos --> Database
    Inventario --> Database
    Programas --> Database
    Instituciones --> Database
    Calificaciones --> Database
    
    Auth --> Database
    Database --> Tables
    
    Router --> Usuarios
    Router --> Estudiantes
    Router --> Personal
    Router --> Talleres
    Router --> Cursos
    Router --> Inventario
    Router --> Programas
    Router --> Instituciones
    Router --> Calificaciones
    
    %% Estilos
    classDef module fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    classDef component fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef database fill:#fff3e0,stroke:#e65100,stroke-width:2px
    
    class Usuarios,Estudiantes,Personal,Talleres,Cursos,Inventario,Programas,Instituciones,Calificaciones module
    class Auth,Database,Config,Router component
    class Tables database
```

## 3. Arquitectura de Base de Datos

```mermaid
erDiagram
    usuarios {
        int id_usuario PK
        string username UK
        string email UK
        string password_hash
        string nombre
        string apellido
        enum rol
        boolean activo
        int creado_por FK
        timestamp fecha_creacion
        timestamp ultimo_acceso
    }
    
    programas {
        int id_programas PK
        string cod_programas UK
        string descripcion
        string sub_area
        boolean activo
        timestamp fecha_creacion
    }
    
    instituciones {
        int id_instituciones PK
        string cod_institucion UK
        string descripcion
        string telefono_enlace
        text datos_docente_enlace
        boolean activo
        timestamp fecha_creacion
    }
    
    estudiante {
        int id_estudiante PK
        string cedula_estudiante UK
        string nombre
        string apellido
        date fecha_nacimiento
        enum sexo
        string telefono
        string email
        int instituciones_id_instituciones FK
        boolean activo
        timestamp fecha_creacion
    }
    
    personal {
        int id_personal PK
        string cedula_personal UK
        string nombre
        string apellido
        string cargo
        string telefono
        string email
        boolean activo
        timestamp fecha_creacion
    }
    
    talleres {
        int id_taller PK
        int programas_id_programas FK
        int instituciones_id_instituciones FK
        int personal_id_personal FK
        string cod_taller UK
        year ano_escolar
        string grado
        string seccion
        enum lapso
        boolean activo
        timestamp fecha_creacion
    }
    
    cursos {
        int id_cursos PK
        int personal_id_personal FK
        string cod_curso UK
        string nombre_curso
        string cedula_persona
        int duracion
        year ano
        int num_de_clases
        enum periodo
        boolean activo
        timestamp fecha_creacion
    }
    
    inventario {
        int id_inventario PK
        int personal_id_personal FK
        string cod_inventario UK
        string descripcion
        int cantidad
        string unidad
        decimal precio_unitario
        boolean activo
        timestamp fecha_creacion
    }
    
    calificaciones {
        int id_calificaciones PK
        int estudiante_id_estudiante FK
        int instituciones_id_instituciones FK
        string materia
        decimal nota
        year ano_escolar
        enum lapso
        boolean activo
        timestamp fecha_creacion
    }
    
    %% Relaciones
    usuarios ||--o{ usuarios : "creado_por"
    programas ||--o{ talleres : "programas_id_programas"
    instituciones ||--o{ talleres : "instituciones_id_instituciones"
    instituciones ||--o{ estudiante : "instituciones_id_instituciones"
    instituciones ||--o{ calificaciones : "instituciones_id_instituciones"
    personal ||--o{ talleres : "personal_id_personal"
    personal ||--o{ cursos : "personal_id_personal"
    personal ||--o{ inventario : "personal_id_personal"
    estudiante ||--o{ calificaciones : "estudiante_id_estudiante"
```

## 4. Arquitectura de Contenedores Docker

```mermaid
graph TB
    subgraph "🐳 Docker Environment"
        subgraph "🌐 Web Container"
            Apache[Apache Web Server]
            PHP[PHP 8.2]
            App[Aplicación SACRGAPI]
        end
        
        subgraph "🗄️ Database Container"
            MySQL[MySQL 8.0]
            Data[Volumen de Datos]
        end
        
        subgraph "🔧 Admin Container"
            phpMyAdmin[phpMyAdmin]
        end
        
        subgraph "📁 Volúmenes"
            AppVolume[Volumen de Aplicación]
            DBVolume[Volumen de Base de Datos]
            ConfigVolume[Volumen de Configuración]
        end
    end
    
    %% Conexiones
    Apache --> PHP
    PHP --> App
    App --> MySQL
    phpMyAdmin --> MySQL
    
    App --> AppVolume
    MySQL --> DBVolume
    App --> ConfigVolume
    
    %% Estilos
    classDef container fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    classDef volume fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef service fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    
    class Apache,PHP,App,MySQL,phpMyAdmin service
    class AppVolume,DBVolume,ConfigVolume volume
```

## 5. Arquitectura de Seguridad

```mermaid
graph TB
    subgraph "🔐 Capa de Seguridad"
        subgraph "🛡️ Autenticación"
            Login[Formulario de Login]
            Session[Gestión de Sesiones]
            Password[Encriptación de Contraseñas]
        end
        
        subgraph "🔑 Autorización"
            Roles[Control de Roles]
            Permissions[Verificación de Permisos]
            Access[Control de Acceso]
        end
        
        subgraph "🔒 Protección de Datos"
            Validation[Validación de Entrada]
            Sanitization[Sanitización de Datos]
            Encryption[Encriptación de Datos]
        end
    end
    
    subgraph "🌐 Capa de Aplicación"
        Router[Router Principal]
        Controllers[Controladores]
        Models[Modelos]
    end
    
    subgraph "🗄️ Capa de Datos"
        Database[Base de Datos]
        Queries[Consultas Seguras]
    end
    
    %% Flujo de seguridad
    Login --> Session
    Session --> Roles
    Roles --> Permissions
    Permissions --> Access
    Access --> Router
    
    Router --> Validation
    Validation --> Sanitization
    Sanitization --> Controllers
    Controllers --> Models
    Models --> Queries
    Queries --> Database
    
    Password --> Encryption
    Encryption --> Database
    
    %% Estilos
    classDef security fill:#ffebee,stroke:#c62828,stroke-width:2px
    classDef application fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    classDef data fill:#fff3e0,stroke:#e65100,stroke-width:2px
    
    class Login,Session,Password,Roles,Permissions,Access,Validation,Sanitization,Encryption security
    class Router,Controllers,Models application
    class Database,Queries data
```

## 6. Arquitectura de API y Rutas

```mermaid
graph TB
    subgraph "🌐 API Routes"
        subgraph "🔐 Autenticación"
            LoginRoute[POST /login]
            LogoutRoute[POST /logout]
        end
        
        subgraph "👥 Usuarios"
            UserCRUD[CRUD Usuarios]
            UserAuth[Cambio de Contraseña]
            UserStatus[Activar/Desactivar]
        end
        
        subgraph "👨‍🎓 Estudiantes"
            StudentCRUD[CRUD Estudiantes]
            StudentSearch[Búsqueda y Filtros]
            StudentStats[Estadísticas]
        end
        
        subgraph "👨‍🏫 Personal"
            PersonalCRUD[CRUD Personal]
            PersonalSearch[Búsqueda y Filtros]
            PersonalStats[Estadísticas]
        end
        
        subgraph "🔧 Talleres"
            TallerCRUD[CRUD Talleres]
            TallerOptions[Opciones de Formulario]
            TallerStats[Estadísticas]
        end
        
        subgraph "📚 Cursos"
            CursoCRUD[CRUD Cursos]
            CursoOptions[Opciones de Formulario]
            CursoStats[Estadísticas]
        end
        
        subgraph "📦 Inventario"
            InventarioCRUD[CRUD Inventario]
            InventarioOptions[Opciones de Formulario]
            InventarioStats[Estadísticas]
        end
        
        subgraph "📋 Programas"
            ProgramaCRUD[CRUD Programas]
            ProgramaOptions[Opciones de Formulario]
            ProgramaStats[Estadísticas]
        end
        
        subgraph "🏫 Instituciones"
            InstitucionCRUD[CRUD Instituciones]
            InstitucionOptions[Opciones de Formulario]
            InstitucionStats[Estadísticas]
        end
        
        subgraph "📊 Calificaciones"
            CalificacionCRUD[CRUD Calificaciones]
            CalificacionFilters[Filtros Avanzados]
            CalificacionStats[Estadísticas]
        end
    end
    
    %% Estilos
    classDef auth fill:#ffebee,stroke:#c62828,stroke-width:2px
    classDef crud fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    classDef options fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef stats fill:#fff3e0,stroke:#e65100,stroke-width:2px
    
    class LoginRoute,LogoutRoute auth
    class UserCRUD,StudentCRUD,PersonalCRUD,TallerCRUD,CursoCRUD,InventarioCRUD,ProgramaCRUD,InstitucionCRUD,CalificacionCRUD crud
    class TallerOptions,CursoOptions,InventarioOptions,ProgramaOptions,InstitucionOptions options
    class StudentStats,PersonalStats,TallerStats,CursoStats,InventarioStats,ProgramaStats,InstitucionStats,CalificacionStats stats
```

## 7. Arquitectura de Frontend

```mermaid
graph TB
    subgraph "🎨 Frontend Architecture"
        subgraph "📱 Interfaz de Usuario"
            Login[Página de Login]
            Dashboard[Dashboard Principal]
            Modules[Módulos del Sistema]
        end
        
        subgraph "🔧 Componentes"
            Forms[Formularios]
            Tables[Tablas de Datos]
            Modals[Modales]
            Alerts[Alertas]
        end
        
        subgraph "📚 Librerías"
            Bootstrap[Bootstrap 5]
            jQuery[jQuery]
            DataTables[DataTables]
            FontAwesome[Font Awesome]
        end
        
        subgraph "⚡ JavaScript"
            AJAX[AJAX Requests]
            Validation[Validación Cliente]
            UI[Interacciones UI]
        end
    end
    
    %% Conexiones
    Login --> Forms
    Dashboard --> Tables
    Modules --> Modals
    Modules --> Alerts
    
    Forms --> Bootstrap
    Tables --> DataTables
    Modals --> Bootstrap
    Alerts --> Bootstrap
    
    AJAX --> jQuery
    Validation --> jQuery
    UI --> jQuery
    
    Bootstrap --> FontAwesome
    
    %% Estilos
    classDef interface fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    classDef component fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef library fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef javascript fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    
    class Login,Dashboard,Modules interface
    class Forms,Tables,Modals,Alerts component
    class Bootstrap,jQuery,DataTables,FontAwesome library
    class AJAX,Validation,UI javascript
```

## 8. Flujo de Datos del Sistema

```mermaid
sequenceDiagram
    participant U as Usuario
    participant F as Frontend
    participant R as Router
    participant A as Auth
    participant C as Controller
    participant M as Model
    participant D as Database
    
    U->>F: Interacción con UI
    F->>R: Request HTTP
    R->>A: Verificar autenticación
    A-->>R: Usuario autenticado
    R->>C: Procesar request
    C->>M: Lógica de negocio
    M->>D: Consulta SQL
    D-->>M: Resultados
    M-->>C: Datos procesados
    C-->>R: Response JSON
    R-->>F: Response HTTP
    F->>U: Actualizar UI
    
    Note over U,D: Flujo completo de datos desde usuario hasta base de datos
```

## Resumen de Arquitectura

### 🏗️ **Arquitectura en Capas**
- **Capa de Presentación**: Interfaz de usuario con Bootstrap 5
- **Capa de Lógica de Negocio**: Controladores y modelos PHP
- **Capa de Datos**: Base de datos MySQL con relaciones normalizadas
- **Capa de Infraestructura**: Contenedores Docker

### 🔧 **Componentes Principales**
- **Router**: Manejo de rutas y enrutamiento
- **Autenticación**: Sistema de login y control de sesiones
- **Modelos**: Lógica de negocio y acceso a datos
- **Vistas**: Interfaz de usuario responsive

### 🗄️ **Base de Datos**
- **MySQL 8.0** con estructura normalizada
- **Relaciones** entre entidades mediante claves foráneas
- **Índices** para optimización de consultas
- **Eliminación lógica** para preservar integridad

### 🐳 **Infraestructura**
- **Docker Compose** para orquestación
- **Apache** como servidor web
- **PHP 8.2** para lógica de aplicación
- **phpMyAdmin** para administración de BD

### 🔐 **Seguridad**
- **Autenticación** con hash de contraseñas
- **Autorización** basada en roles
- **Validación** de entrada de datos
- **Sanitización** de datos para prevenir inyecciones

### 📱 **Frontend**
- **Bootstrap 5** para diseño responsive
- **jQuery** para interacciones
- **DataTables** para tablas dinámicas
- **AJAX** para comunicación asíncrona
