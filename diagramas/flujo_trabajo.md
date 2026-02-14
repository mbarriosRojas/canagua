# Diagramas de Flujo de Trabajo - Sistema SACRGAPI

## 1. Flujo de Autenticación

```mermaid
sequenceDiagram
    participant U as Usuario
    participant S as Sistema
    participant DB as Base de Datos
    participant A as Auth
    
    U->>S: Acceder al sistema
    S->>U: Mostrar formulario de login
    U->>S: Ingresar credenciales
    S->>A: Validar credenciales
    A->>DB: Consultar usuario
    DB-->>A: Datos del usuario
    A->>A: Verificar contraseña
    alt Credenciales válidas
        A->>DB: Actualizar último acceso
        A->>S: Crear sesión
        S->>U: Redirigir al dashboard
    else Credenciales inválidas
        A->>S: Error de autenticación
        S->>U: Mostrar mensaje de error
    end
```

## 2. Flujo de Creación de Taller

```mermaid
sequenceDiagram
    participant U as Usuario
    participant S as Sistema
    participant T as Modelo Taller
    participant DB as Base de Datos
    
    U->>S: Acceder a módulo de talleres
    S->>T: Cargar opciones del formulario
    T->>DB: Consultar programas, instituciones, personal
    DB-->>T: Datos disponibles
    T-->>S: Opciones para selects
    S->>U: Mostrar formulario con opciones
    U->>S: Llenar formulario y enviar
    S->>T: Crear taller
    T->>DB: Insertar nuevo taller
    DB-->>T: ID del taller creado
    T-->>S: Confirmación de creación
    S->>U: Mostrar mensaje de éxito
    S->>S: Recargar lista de talleres
```

## 3. Flujo de Gestión de Usuarios

```mermaid
sequenceDiagram
    participant A as Administrador
    participant S as Sistema
    participant Auth as Auth
    participant DB as Base de Datos
    
    A->>S: Acceder a gestión de usuarios
    S->>Auth: Verificar permisos
    Auth-->>S: Permisos confirmados
    S->>DB: Consultar lista de usuarios
    DB-->>S: Lista de usuarios
    S->>A: Mostrar lista de usuarios
    
    alt Crear nuevo usuario
        A->>S: Solicitar crear usuario
        S->>A: Mostrar formulario
        A->>S: Enviar datos del usuario
        S->>Auth: Crear usuario
        Auth->>DB: Insertar usuario
        DB-->>Auth: Usuario creado
        Auth-->>S: Confirmación
        S->>A: Mostrar mensaje de éxito
    else Editar usuario existente
        A->>S: Seleccionar usuario
        S->>A: Mostrar formulario con datos
        A->>S: Enviar cambios
        S->>Auth: Actualizar usuario
        Auth->>DB: Actualizar datos
        DB-->>Auth: Usuario actualizado
        Auth-->>S: Confirmación
        S->>A: Mostrar mensaje de éxito
    end
```

## 4. Flujo de Gestión de Calificaciones

```mermaid
sequenceDiagram
    participant U as Usuario
    participant S as Sistema
    participant C as Modelo Calificación
    participant DB as Base de Datos
    
    U->>S: Acceder a módulo de calificaciones
    S->>C: Cargar opciones del formulario
    C->>DB: Consultar estudiantes e instituciones
    DB-->>C: Datos disponibles
    C-->>S: Opciones para selects
    S->>U: Mostrar formulario con filtros
    
    alt Crear nueva calificación
        U->>S: Llenar formulario
        S->>C: Crear calificación
        C->>DB: Insertar calificación
        DB-->>C: Calificación creada
        C-->>S: Confirmación
        S->>U: Mostrar mensaje de éxito
    else Filtrar calificaciones
        U->>S: Aplicar filtros
        S->>C: Buscar calificaciones
        C->>DB: Consultar con filtros
        DB-->>C: Resultados filtrados
        C-->>S: Datos filtrados
        S->>U: Mostrar resultados
    end
```

## 5. Flujo de Dashboard y Reportes

```mermaid
sequenceDiagram
    participant U as Usuario
    participant S as Sistema
    participant D as Dashboard
    participant M as Modelos
    
    U->>S: Acceder al dashboard
    S->>D: Cargar dashboard
    D->>M: Solicitar estadísticas
    
    par Estadísticas de Estudiantes
        M->>M: Contar estudiantes activos
    and Estadísticas de Personal
        M->>M: Contar personal activo
    and Estadísticas de Talleres
        M->>M: Contar talleres por lapso
    and Estadísticas de Cursos
        M->>M: Contar cursos por periodo
    and Estadísticas de Inventario
        M->>M: Contar items de inventario
    and Estadísticas de Calificaciones
        M->>M: Calcular promedios
    end
    
    M-->>D: Todas las estadísticas
    D-->>S: Dashboard completo
    S->>U: Mostrar dashboard con estadísticas
```

## 6. Flujo de Búsqueda y Filtros

```mermaid
sequenceDiagram
    participant U as Usuario
    participant S as Sistema
    participant M as Modelo
    participant DB as Base de Datos
    
    U->>S: Acceder a módulo
    S->>U: Mostrar interfaz con filtros
    
    alt Búsqueda por texto
        U->>S: Ingresar término de búsqueda
        S->>M: Buscar con término
        M->>DB: Consulta con LIKE
        DB-->>M: Resultados de búsqueda
        M-->>S: Datos encontrados
        S->>U: Mostrar resultados
    else Filtro por criterio específico
        U->>S: Seleccionar filtro
        S->>M: Aplicar filtro
        M->>DB: Consulta con WHERE
        DB-->>M: Resultados filtrados
        M-->>S: Datos filtrados
        S->>U: Mostrar resultados
    else Combinación de filtros
        U->>S: Aplicar múltiples filtros
        S->>M: Combinar filtros
        M->>DB: Consulta compleja
        DB-->>M: Resultados combinados
        M-->>S: Datos filtrados
        S->>U: Mostrar resultados
    end
```

## 7. Flujo de Eliminación Lógica

```mermaid
sequenceDiagram
    participant U as Usuario
    participant S as Sistema
    participant M as Modelo
    participant DB as Base de Datos
    
    U->>S: Seleccionar item para eliminar
    S->>U: Mostrar confirmación
    U->>S: Confirmar eliminación
    S->>M: Eliminar item
    M->>DB: UPDATE activo = 0
    DB-->>M: Item desactivado
    M-->>S: Confirmación de eliminación
    S->>U: Mostrar mensaje de éxito
    S->>S: Recargar lista (sin mostrar item eliminado)
```

## 8. Flujo de Relaciones entre Módulos

```mermaid
graph TB
    %% Entidades principales
    Usuario[👤 Usuario]
    Estudiante[👨‍🎓 Estudiante]
    Personal[👨‍🏫 Personal]
    Taller[🔧 Taller]
    Curso[📚 Curso]
    Inventario[📦 Inventario]
    Programa[📋 Programa]
    Institucion[🏫 Institución]
    Calificacion[📊 Calificación]
    
    %% Relaciones
    Usuario --> Estudiante
    Usuario --> Personal
    Usuario --> Taller
    Usuario --> Curso
    Usuario --> Inventario
    Usuario --> Programa
    Usuario --> Institucion
    Usuario --> Calificacion
    
    Programa --> Taller
    Institucion --> Taller
    Personal --> Taller
    Personal --> Curso
    Personal --> Inventario
    Taller --> Inventario
    
    Institucion --> Estudiante
    Estudiante --> Calificacion
    Institucion --> Calificacion
    
    %% Estilos
    classDef entity fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    classDef relation fill:#f3e5f5,stroke:#7b1fa2,stroke-width:1px
    
    class Usuario,Estudiante,Personal,Taller,Curso,Inventario,Programa,Institucion,Calificacion entity
```

## 9. Flujo de Validación de Permisos

```mermaid
flowchart TD
    A[Usuario accede al sistema] --> B{¿Está autenticado?}
    B -->|No| C[Redirigir a login]
    B -->|Sí| D{¿Tiene permisos para la acción?}
    D -->|No| E[Mostrar error de permisos]
    D -->|Sí| F[Permitir acceso]
    
    C --> G[Usuario ingresa credenciales]
    G --> H{¿Credenciales válidas?}
    H -->|No| I[Mostrar error de login]
    H -->|Sí| J[Crear sesión]
    J --> K[Redirigir al dashboard]
    
    I --> G
    E --> L[Mostrar mensaje de acceso denegado]
    F --> M[Ejecutar acción solicitada]
    
    %% Estilos
    classDef process fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef decision fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef error fill:#ffebee,stroke:#c62828,stroke-width:2px
    classDef success fill:#e3f2fd,stroke:#0277bd,stroke-width:2px
    
    class A,G,J,K,M process
    class B,D,H decision
    class C,E,I,L error
    class F success
```

## 10. Flujo de Carga de Opciones para Formularios

```mermaid
sequenceDiagram
    participant U as Usuario
    participant S as Sistema
    participant M as Modelo
    participant DB as Base de Datos
    
    U->>S: Abrir modal de creación
    S->>M: Solicitar opciones del formulario
    M->>DB: Consultar datos relacionados
    
    par Cargar Programas
        DB-->>M: Lista de programas activos
    and Cargar Instituciones
        DB-->>M: Lista de instituciones activas
    and Cargar Personal
        DB-->>M: Lista de personal activo
    end
    
    M-->>S: Opciones completas
    S->>S: Llenar selects del formulario
    S->>U: Mostrar formulario con opciones cargadas
    
    U->>S: Enviar formulario
    S->>M: Procesar datos
    M->>DB: Insertar/Actualizar datos
    DB-->>M: Confirmación
    M-->>S: Resultado de la operación
    S->>U: Mostrar mensaje de resultado
```

## Resumen de Flujos Principales

### 🔐 Autenticación
1. Usuario accede al sistema
2. Sistema valida credenciales
3. Se crea sesión si es válido
4. Se redirige al dashboard

### 📝 CRUD Operations
1. Usuario accede al módulo
2. Sistema carga opciones del formulario
3. Usuario llena y envía formulario
4. Sistema valida y procesa datos
5. Se actualiza la base de datos
6. Se muestra confirmación

### 🔍 Búsqueda y Filtros
1. Usuario aplica filtros
2. Sistema construye consulta
3. Base de datos ejecuta consulta
4. Resultados se muestran al usuario

### 📊 Dashboard y Reportes
1. Usuario accede al dashboard
2. Sistema solicita estadísticas de todos los módulos
3. Se ejecutan consultas en paralelo
4. Se consolidan los resultados
5. Se muestra dashboard completo

### 🔗 Relaciones entre Módulos
- Los módulos están interconectados a través de claves foráneas
- Las opciones de formularios se cargan dinámicamente
- Los filtros permiten navegar entre entidades relacionadas
- Las estadísticas se calculan considerando todas las relaciones
