# Análisis y Planificación del Proyecto "Halcón" — Sistema de Seguimiento de Pedidos

## 1. Metodología de trabajo

### 1.1 Metodología elegida: **Scrum (marco ágil)**

**Justificación:**

Los requerimientos están organizados en módulos bien diferenciados, los cuales se pueden convertir directamente en un Product Backlog con historias de usuario por rol. Además, dado que el cliente no es técnico y necesita ver avances tangibles, los Sprints cortos con entregas incrementales permiten validar continuamente con el dueño del negocio. Por otro lado, el flujo de estados del pedido es un proceso vivo que probablemente se ajuste al usarlo en campo, y la naturaleza iterativa de Scrum permite adaptar reglas de negocio  sin retrabajo mayor. También hay funcionalidades de distinto riesgo técnico, por lo que Scrum permite priorizar en el Sprint Planning lo de mayor valor o riesgo primero .


## 2. Diagrama BPMN — Ciclo de vida del pedido

```mermaid
flowchart TD
    subgraph Cliente
        A1([Cliente llama para hacer pedido])
    end

    subgraph Ventas
        B1[Registra pedido en el sistema]
        B2[Asigna número de factura y cliente]
        B3((Estatus: Ordered))
    end

    subgraph Almacen["Almacén"]
        C1{¿Hay stock?}
        C2[Prepara materiales]
        C3[Solicita compra a Compras]
        C4((Estatus: In process))
        C5[Carga unidad con transportista]
        C6((Estatus: In route))
    end

    subgraph Compras
        D1[Gestiona compra a proveedor externo]
        D2[Notifica a Almacén disponibilidad]
    end

    subgraph Ruta
        E1[Toma foto de unidad cargada]
        E2[Sube foto de carga]
        E3[Transporta pedido]
        E4[Entrega en domicilio del cliente]
        E5[Toma foto de evidencia de entrega]
        E6[Sube foto de entrega]
        E7((Estatus: Delivered))
    end

    A1 --> B1 --> B2 --> B3
    B3 --> C1
    C1 -- Sí --> C2
    C1 -- No --> C3 --> D1 --> D2 --> C2
    C2 --> C4
    C4 --> C5 --> C6
    C6 --> E1 --> E2 --> E3 --> E4 --> E5 --> E6 --> E7
```


## 3. Diagrama de Casos de Uso

```mermaid
flowchart LR
    Cliente((Cliente))
    Ventas((Ventas))
    Almacen((Almacén))
    Compras((Compras))
    Ruta((Ruta))
    Admin((Administrador))

    UC1([Consultar estatus de pedido])
    UC2([Ver evidencia de entrega])
    UC3([Registrar nuevo pedido])
    UC4([Listar y buscar pedidos])
    UC5([Cambiar estatus de pedido])
    UC6([Notificar falta de stock])
    UC7([Gestionar compra a proveedor])
    UC8([Subir foto de carga])
    UC9([Subir foto de entrega])
    UC10([Editar pedido])
    UC11([Eliminar pedido - baja lógica])
    UC12([Ver pedidos eliminados])
    UC13([Restaurar pedido])
    UC14([Registrar usuario])
    UC15([Asignar rol a usuario])

    Cliente --> UC1
    Cliente --> UC2

    Ventas --> UC3
    Ventas --> UC4
    Ventas --> UC10

    Almacen --> UC4
    Almacen --> UC5
    Almacen --> UC6

    Compras --> UC4
    Compras --> UC7

    Ruta --> UC4
    Ruta --> UC5
    Ruta --> UC8
    Ruta --> UC9

    Admin --> UC14
    Admin --> UC15
    Admin --> UC4
    Admin --> UC10
    Admin --> UC11
    Admin --> UC12
    Admin --> UC13

    UC1 -.include.-> UC2
    UC5 -.include.-> UC6
```



## 4. Diagrama de Clases

```mermaid
classDiagram
    class Usuario {
        +int id
        +string nombre
        +string email
        +string passwordHash
        +Rol rol
        +bool activo
        +datetime fechaCreacion
        +login()
        +logout()
    }

    class Rol {
        +int id
        +string nombre
        +string descripcion
    }

    class Cliente {
        +int numeroCliente
        +string nombreORazonSocial
        +string rfcODatosFiscales
        +string direccionFiscal
        +string email
        +string telefono
    }

    class Pedido {
        +int id
        +string numeroFactura
        +int numeroCliente
        +string direccionEntrega
        +string notas
        +datetime fechaHoraPedido
        +EstatusPedido estatus
        +bool eliminadoLogicamente
        +int creadoPorUsuarioId
        +crearPedido()
        +cambiarEstatus()
        +eliminarLogicamente()
        +restaurar()
    }

    class EstatusPedido {
        <<enumeration>>
        ORDERED
        IN_PROCESS
        IN_ROUTE
        DELIVERED
    }

    class EvidenciaFoto {
        +int id
        +int pedidoId
        +TipoEvidencia tipo
        +string urlImagen
        +datetime fechaCarga
        +int subidaPorUsuarioId
        +subirFoto()
    }

    class TipoEvidencia {
        <<enumeration>>
        CARGA
        ENTREGA
    }

    class HistorialEstatus {
        +int id
        +int pedidoId
        +EstatusPedido estatusAnterior
        +EstatusPedido estatusNuevo
        +datetime fechaCambio
        +int usuarioId
    }

    Usuario "1" --> "1" Rol : tiene
    Pedido "1" --> "1" Cliente : pertenece a
    Pedido "1" --> "0..*" EvidenciaFoto : contiene
    Pedido "1" --> "0..*" HistorialEstatus : registra
    Pedido "1" --> "1" EstatusPedido : posee
    EvidenciaFoto "1" --> "1" TipoEvidencia : es de tipo
    Usuario "1" --> "0..*" Pedido : crea/gestiona
```


## 5. Diagrama de Actividades — Registro y avance de un pedido

```mermaid
flowchart TD
    Start((Inicio)) --> A[Cliente llama a Ventas]
    A --> B[Ventas captura datos del pedido]
    B --> C{¿Cliente nuevo?}
    C -- Sí --> D[Asignar número de cliente único]
    C -- No --> E[Usar número de cliente existente]
    D --> F[Guardar pedido con estatus 'Ordered']
    E --> F
    F --> G[Pedido visible para todos los empleados]
    G --> H[Almacén revisa el pedido]
    H --> I{¿Hay stock suficiente?}
    I -- No --> J[Almacén notifica a Compras]
    J --> K[Compras adquiere el material]
    K --> L[Almacén recibe material]
    I -- Sí --> L
    L --> M[Almacén prepara pedido]
    M --> N[Cambia estatus a 'In process']
    N --> O[Almacén carga unidad con transportista]
    O --> P[Cambia estatus a 'In route']
    P --> Q[Ruta toma foto de carga y la sube]
    Q --> R[Unidad se traslada al domicilio del cliente]
    R --> S[Se entrega el material]
    S --> T[Ruta toma foto de evidencia de entrega y la sube]
    T --> U[Cambia estatus a 'Delivered']
    U --> End((Fin))
```


## 6. Base de datos y Diagrama Entidad-Relación

### 6.1 Elección del motor de base de datos

**Motor elegido: PostgreSQL relacional**

**Justificación:**
Los datos del negocio son altamente estructurados y relacionales es decir, un pedido pertenece a un cliente, tiene un historial de estatus, y puede tener evidencias fotográficas asociadas, relaciones típicas de un modelo relacional con integridad referencial fuerte. Además los números de factura y de cliente deben ser únicos, consecutivos y consistentes, lo cual se garantiza fácilmente con restricciones `UNIQUE`, `SEQUENCE` y validaciones a nivel de esquema.

PostgreSQL soporta el almacenamiento de metadatos de archivos y es una tecnología robusta, gratuita, con gran soporte para roles y permisos a nivel de base de datos, lo cual complementa el control de roles de la aplicación.

### 6.2 Diagrama Entidad-Relación

```mermaid
erDiagram
    ROL {
        int id PK
        string nombre
        string descripcion
    }

    USUARIO {
        int id PK
        string nombre
        string email
        string password_hash
        int rol_id FK
        boolean activo
        datetime fecha_creacion
    }

    CLIENTE {
        int numero_cliente PK
        string nombre_razon_social
        string datos_fiscales
        string direccion_fiscal
        string email
        string telefono
    }

    PEDIDO {
        int id PK
        string numero_factura
        int numero_cliente FK
        string direccion_entrega
        string notas
        datetime fecha_hora_pedido
        string estatus
        boolean eliminado_logicamente
        int creado_por_usuario_id FK
    }

    EVIDENCIA_FOTO {
        int id PK
        int pedido_id FK
        string tipo
        string url_imagen
        datetime fecha_carga
        int subida_por_usuario_id FK
    }

    HISTORIAL_ESTATUS {
        int id PK
        int pedido_id FK
        string estatus_anterior
        string estatus_nuevo
        datetime fecha_cambio
        int usuario_id FK
    }

    ROL ||--o{ USUARIO : "clasifica"
    CLIENTE ||--o{ PEDIDO : "realiza"
    USUARIO ||--o{ PEDIDO : "crea/gestiona"
    PEDIDO ||--o{ EVIDENCIA_FOTO : "tiene"
    PEDIDO ||--o{ HISTORIAL_ESTATUS : "registra"
    USUARIO ||--o{ EVIDENCIA_FOTO : "sube"
    USUARIO ||--o{ HISTORIAL_ESTATUS : "efectua"
```


## 7. Reflexión personal

Este caso, aunque parte de un problema aparentemente simple revela, al analizarlo a fondo, la complejidad típica de cualquier sistema de negocio real, es decir, múltiples roles con visibilidad y permisos distintos, un flujo de estados que debe respetarse estrictamente, evidencia documental que sirve como prueba legal/operativa, y la necesidad de no perder información histórica.


Elegir Scrum como metodología también obliga a pensar el proyecto en incrementos de valor real para el negocio, en lugar de en módulos técnicos aislados, cada sprint entrega algo que Halcón puede probar y retroalimentar, lo cual reduce el riesgo de construir funcionalidades que no resuelven el problema real del cliente. En conjunto, este ejercicio confirma que el tiempo invertido en análisis y modelado no es un paso burocrático, sino la base que evita retrabajo costoso en etapas posteriores del desarrollo.
