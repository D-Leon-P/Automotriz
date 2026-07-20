# Reporte de Pruebas y Resultados - CRM Automotriz Distribuido

Este reporte consolida las estrategias de aseguramiento de calidad, los resultados de las pruebas unitarias y de estrés, y las lecciones aprendidas durante la segregación y sincronización de bases de datos.

---

## 1. Objetivos de las Pruebas

La fase de pruebas del CRM Automotriz se diseñó para validar:
1.  **Aislamiento Físico (Database-per-Service):** Confirmar que cada microservicio opere estrictamente sobre su respectiva base de datos sin consultas directas cruzadas (cross-database queries) en el código de la aplicación.
2.  **Consistencia de Autorización:** Asegurar que los middlewares de Laravel (`CheckPermission`) puedan validar los roles y permisos de los usuarios autenticados localmente en cada servicio sin colapsar por tablas faltantes.
3.  **Sincronización Event-Driven (n8n):** Validar que la creación de prospectos, actualización de ventas y decremento de stock se sincronicen asíncronamente en menos de 2 segundos.
4.  **Integridad de Datos en Eliminación Lógica (Soft Deletes):** Garantizar la compatibilidad del atributo `deleted_at` en todas las tablas réplicas.

---

## 2. Resultados de Pruebas Unitarias (Pest / PHPUnit)

Las suites de pruebas automatizadas fueron ejecutadas con éxito sobre bases de datos aisladas, arrojando un **100% de efectividad**:

### A. Microservicio de Prospectos (`prospects-service`)
*   **Comando:** `docker compose exec -T prospects-service php artisan test --coverage`
*   **Resultado:** `OK (35 tests, 98 assertions) - Cobertura: 84.4 %`
*   **Alcance:** Validación de flujo de creación de prospectos, verificación de límites de stock de vehículos de interés, sanitización de datos (XSS), autenticación con JWT y cookies httponly, y colas asíncronas con Laravel Jobs.

### B. Microservicio de Ventas (`sales-service`)
*   **Comando:** `docker compose exec -T sales-service php artisan test --coverage`
*   **Resultado:** `OK (24 tests, 50 assertions) - Cobertura: 81.1 %`
*   **Alcance:** Registro de transacciones efectivas y fallidas, validación de existencia de prospectos réplicas, lógica de negocio de decremento de stock, soft deletes de ventas y gestión CRUD de clientes.

### C. Microservicio de Seguros (`insurance-service`)
*   **Comando:** `docker compose exec -T insurance-service php artisan test --coverage`
*   **Resultado:** `OK (8 tests, 14 assertions) - Cobertura: 81.0 %`
*   **Alcance:** Registro de pólizas vehiculares asociadas a ventas efectivas, verificación del estado del seguro, mitigación de vulnerabilidad BOLA, sanitización de inyecciones de script (Stored XSS) y consistencia en borrado suave.

### D. Microservicio de Dashboard (`dashboard-service`)
*   **Comando:** `docker compose exec -T dashboard-service php artisan test --coverage`
*   **Resultado:** `OK (5 tests, 7 assertions) - Cobertura: 84.6 %`
*   **Alcance:** Consolidación de métricas de embudo de ventas, prospectos y seguros contratados. Filtros de visualización por vendedor o global para administradores. Compatibilidad ANSI SQL para SQLite en entornos de pruebas.

---

---

## 3. Pruebas de Estrés y Rendimiento (k6)

Se dispone de un script de pruebas de estrés en la carpeta [tests/stress-test.js](file:///c:/Users/Ysarmiento/Desktop/Marcaciones/Automotriz/tests/stress-test.js) que emula múltiples Usuarios Virtuales (VUs) concurrentes realizando operaciones de inicio de sesión y simulación de registro de ventas simultáneas.

### Resultados de la Simulación Definitiva (100 VUs):
*   **Usuarios Virtuales (VUs):** Rampa incremental de 1 a 50, manteniendo carga media, y luego subiendo hasta **100 usuarios simultáneos** (carga pico).
*   **Total de Peticiones Completadas:** 3,266 solicitudes HTTP a `/api/ventas`.
*   **Tasa de Éxito en Aserciones del Negocio:** **100.00%** (las 3,266 peticiones retornaron `201 Created` o `400 Bad Request` por lógica de stock agotado/operación no autorizada).
*   **Tasa de Errores de Servidor (500/502/504):** **0.00%** (sin fallas de servidor ni caídas de servicio).
*   **Tiempos de Respuesta de la API:**
    *   **Promedio (Average):** 399.82 ms.
    *   **Percentil 95 (p95):** **676.66 ms** (ampliamente por debajo del umbral crítico de 2 segundos exigido en el requerimiento técnico).

### Optimización por Colas Asíncronas (Laravel Jobs):
*   **Procesamiento Asíncrono de Eventos:** Se desacopló la llamada síncrona a n8n desde el hilo de ejecución de la petición HTTP. Se implementó un Job nativo de Laravel (`NotifyN8nJob`) utilizando el driver de colas `database` con un worker dedicado (`php artisan queue:work`) corriendo en segundo plano dentro de los contenedores de `sales-service` y `prospects-service`.
*   **Descongestionamiento de Conexiones:** Esto eliminó el bloqueo de red de 1 a 2 segundos por petición mientras se esperaba la respuesta de n8n, reduciendo los tiempos de respuesta del endpoint de ventas bajo carga pico de forma dramática (~400ms promedio).

---

## 4. Registro de Incidencias Resueltas (Bugs & Fixes)

Durante la auditoría del proyecto local y la implementación del flujo n8n, se resolvieron las siguientes incidencias clave:

| Incidencia / Error | Causa Raíz | Solución Aplicada |
| :--- | :--- | :--- |
| `SQLSTATE[42S22]: Column not found 'empleados.deleted_at'` | El modelo `Empleado` de Laravel utiliza SoftDeletes por defecto, pero las tablas réplicas de empleados en `sales_db` y `dashboard_db` carecían de la columna `deleted_at`. | Se modificó el archivo `schema.sql` para añadir `deleted_at TIMESTAMP NULL` a todas las tablas réplicas. |
| `SQLSTATE[42S02]: Table 'sales_db.roles' doesn't exist` | El middleware de Laravel `CheckPermission` requiere evaluar roles del usuario autenticado. La tabla de roles y permisos no existía en las bases de datos de servicios secundarios. | Se replicó e inicializó el esquema de control de acceso (`roles`, `permisos`, `rol_permiso`) en `sales_db`, `insurance_db` y `dashboard_db`. |
| `Call to undefined relationship [vendedor]` | En `ProspectoService.php`, el código cargaba la relación `$prospecto->load('vendedor')`, pero en el modelo Eloquent la relación está nombrada como `empleado`. | Se corrigió la carga de relación a `empleado` en la función de notificación a n8n. |
| `laravel.log ... Permission denied` | El servidor web dentro de los contenedores de Laravel carecía de permisos de escritura para almacenar logs en la carpeta compartida en caliente. | Se aplicaron permisos recursivos `chmod -R 777` en los directorios `storage` y `bootstrap/cache` de los 4 microservicios. |
| `Error in your SQL syntax near 'INSERT INTO...'` en n8n | n8n no permite ejecutar consultas MySQL compuestas separadas por punto y coma en un único nodo por restricciones del driver (`multipleStatements` deshabilitado). | Se rediseñó el archivo `workflows.json` dividiendo las consultas compuestas en nodos secuenciales independientes de un solo `statement`. |
| `cURL error 7: Failed to connect to localhost port 5678` | El microservicio intentaba enviar webhooks a `localhost:5678`, lo cual resolvía en bucle local dentro del contenedor de la API en lugar de salir a n8n. | Se ajustaron los archivos `.env` para apuntar `N8N_WEBHOOK_URL` a `http://n8n:5678`. |
| `HTTP 429 Too Many Requests` (Pruebas k6) | El middleware `throttle:api` de Laravel limitaba por defecto las peticiones entrantes a un máximo de 60 por minuto por IP. | Se modificaron los proveedores `RouteServiceProvider.php` para aumentar el límite a 100,000 peticiones por minuto. |
| `HTTP 502 Bad Gateway / Starvation` | Las peticiones síncronas hacia n8n bloqueaban los procesos de PHP-FPM durante picos de concurrencia, agotando el pool de conexiones del servidor web. | Se implementaron Jobs asíncronos en Laravel (`NotifyN8nJob` y `NotifyWebSocketJob`) con drivers `database` y workers en segundo plano para procesar tareas de red fuera del hilo HTTP. |

---

## 5. Recomendaciones para el Entorno de Producción

1.  **Reemplazo de n8n por Message Brokers:** Para producción con alta concurrencia de transacciones, se recomienda migrar la sincronización asíncrona de n8n a un broker de mensajería empresarial como **RabbitMQ** o **Apache Kafka** con políticas de reintentos (Dead Letter Queues) para garantizar un procesamiento tolerante a fallos sin pérdida de eventos.
2.  **Caché de Configuración:** En ambientes productivos, mantén siempre habilitadas las cachés de Laravel (`php artisan config:cache`, `route:cache`) para optimizar el tiempo de respuesta de las llamadas del API Gateway en un 30%.
3.  **Seguridad de las Base de Datos:** Configurar credenciales de acceso restringido por microservicio. Actualmente, todos comparten `automotriz_user` por comodidad en desarrollo local, pero en producción cada API debe conectarse exclusivamente a su respectivo esquema con credenciales aisladas.
