# Manual de Usuario - CRM Automotriz Distribuido

Este manual proporciona las instrucciones para el uso y navegación de la plataforma del **CRM Automotriz Distribuido**.

---

## 1. Acceso al Sistema (Login)

La aplicación web está disponible en la dirección **`http://localhost`** (o **`http://localhost:5173`** en desarrollo).

### Credenciales de Acceso
El sistema cuenta con dos perfiles de usuario preconfigurados:

| Usuario (Email) | Contraseña | Rol / Perfil | Permisos Principales |
| :--- | :--- | :--- | :--- |
| **`carlos.rodriguez@automotriz.com`** | `password123` | **Administrador** | Visualización y edición completa de todos los módulos (Prospectos, Ventas, Seguros, Empleados, Roles, Dashboard). |
| **`juan.perez@automotriz.com`** | `password123` | **Vendedor** | Visualización y gestión de prospectos propios, registro de ventas de sus clientes y seguros asignados. |

---

## 2. Panel de Navegación

Una vez iniciada la sesión, en el menú lateral izquierdo tendrás acceso a las siguientes secciones de acuerdo con tu nivel de privilegios:

1.  **Dashboard:** Panel analítico consolidado con métricas clave (total de ventas, conversión, alertas de inactividad de prospectos).
2.  **Prospectos:** Registro de potenciales clientes interesados en vehículos.
3.  **Ventas:** Gestión de transacciones comerciales efectivas o fallidas.
4.  **Seguros:** Registro de pólizas de seguro vehiculares asociadas a las ventas concretadas.
5.  **Administración (Solo Admin):** Gestión de Empleados, Roles y Permisos del sistema.

---

## 3. Módulos y Flujos de Trabajo

### A. Gestión de Prospectos
En este módulo se capta y sigue el ciclo de vida del cliente potencial:
*   **Registrar Prospecto:** Al presionar "Nuevo Prospecto", ingresa el nombre, email, teléfono, vehículo de interés y el asesor asignado.
*   **Reserva de Stock (Regla de Negocio):** El sistema valida en tiempo real la disponibilidad física del vehículo. No se permite registrar ni reabrir prospectos si la cantidad de reservas activas en etapas previas al cierre supera el stock físico del catálogo.
*   **Transición de Etapas:** Un prospecto progresa secuencialmente a través de 4 etapas:
    `Prospección` ➔ `Calificación` ➔ `Negociación` ➔ `Cierre`

### B. Registro de Ventas
Cuando un prospecto llega a la etapa de `Cierre` y decide realizar la compra:
*   **Nueva Venta:** Selecciona el prospecto y el vehículo. 
*   **Estado de la Venta:**
    *   **Efectiva (Ganada):** Concreta la compra. Esta acción dispara automáticamente la **reducción del stock físico** en el catálogo general mediante la sincronización asíncrona de n8n.
    *   **Fallida (Perdida):** Exige registrar el motivo de pérdida del cliente para análisis estratégico en el Dashboard.

### C. Contratación de Seguros Vehiculares
Una vez concretada una venta efectiva, el cliente es candidato para contratar un seguro:
*   **Asociación:** Registra una póliza de seguro vinculada a la venta.
*   **Valores:** Declara la prima esperada y la prima real acordada.
*   **Estado del Seguro:** Pasa de `Prospectado` a `Vendido`.

### D. Alertas y Seguimiento de Inactividad (Automatizado)
*   De fondo, el sistema (coordinado con n8n de forma diaria) busca prospectos que lleven **más de 5 días sin interacción ni cambio de estado**.
*   El sistema genera automáticamente alertas de advertencia para que los asesores reactiven la negociación.

---

## 4. Resolución de Problemas Frecuentes

*   **Error 404 en el Login:** Si ingresas por el puerto `5173` y experimentas rechazo de conexión, asegúrate de que el API Gateway de Docker esté arriba. El sistema ya cuenta con un proxy inverso en el frontend que redirige el tráfico `/api` automáticamente.
*   **ID de Prospecto Inválido al crear una venta:** Este error ocurre si el prospecto recién creado no se ha replicado todavía en la base de datos de ventas (`sales_db`). Asegúrate de que el workflow de n8n esté en estado **Active** o que estés ejecutando una prueba interactiva con "Listen for test event" encendido en el panel de n8n.
