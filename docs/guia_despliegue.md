# Guía de Configuración y Despliegue - CRM Automotriz Distribuido

Esta guía detalla los pasos para levantar e integrar el entorno de desarrollo y pruebas del sistema en un ambiente local dockerizado.

---

## 1. Arquitectura del Entorno

El sistema se compone de un frontend, un API Gateway (NGINX), y 4 microservicios independientes que operan con el patrón de **base de datos por servicio (Database-per-Service)** sobre una misma instancia de MySQL segregada en 4 esquemas:

*   `prospects_service` ➔ Conecta a `prospects_db` (Maestro de prospectos, vehículos y accesos).
*   `sales_service` ➔ Conecta a `sales_db` (Ventas, clientes y réplicas de catálogo y accesos).
*   `insurance_service` ➔ Conecta a `insurance_db` (Seguros y réplicas de ventas y accesos).
*   `dashboard_service` ➔ Conecta a `dashboard_db` (Réplica total de lectura de todas las entidades para reportes).
*   `n8n` ➔ Se encarga de la replicación asíncrona eventual entre las bases de datos mediante webhooks automáticos.

---

## 2. Requisitos Previos

Antes de comenzar, asegúrate de tener instalado en tu máquina host:
1.  **Docker Desktop** (con soporte para docker-compose).
2.  **Git** (para control de versiones).
3.  **Terminal PowerShell o CMD** (con permisos de administrador).

---

## 3. Configuración de Variables de Entorno (`.env`)

Cada uno de los microservicios de backend tiene un archivo de configuración `.env` en su respectiva carpeta dentro de `backend/`. 

### Clave de Webhook de n8n
Para la comunicación interna dentro de la red puente de Docker (`automotriz-network`), se debe usar el host **`n8n`** (el nombre del servicio en `docker-compose.yml`) en lugar de `localhost`:

*   **Archivo [prospects-service/.env](file:///c:/Users/Ysarmiento/Desktop/Marcaciones/Automotriz/backend/prospects-service/.env#L61):**
    ```env
    N8N_WEBHOOK_URL=http://n8n:5678/webhook/prospectos
    ```
*   **Archivo [sales-service/.env](file:///c:/Users/Ysarmiento/Desktop/Marcaciones/Automotriz/backend/sales-service/.env#L61):**
    ```env
    N8N_WEBHOOK_URL=http://n8n:5678/webhook/ventas
    ```
*   **Archivo [insurance-service/.env](file:///c:/Users/Ysarmiento/Desktop/Marcaciones/Automotriz/backend/insurance-service/.env#L61):**
    ```env
    N8N_WEBHOOK_URL=http://n8n:5678/webhook/seguros
    ```

---

## 4. Despliegue del Sistema con Docker

### Paso 1: Levantar los contenedores
Abre tu terminal en la raíz del proyecto y ejecuta el comando de construcción y arranque de Docker Compose:
```powershell
docker-compose up -d --build
```
*Esto levantará el NGINX Gateway, el frontend de Vite, las 4 API de Laravel, la base de datos MySQL y la instancia de n8n.*

### Paso 2: Importar la Base de Datos
Dado que la base de datos es persistente mediante un volumen de Docker, debes ejecutar el script DDL/DML para crear y sembrar las bases de datos por primera vez. Ejecuta en PowerShell:
```powershell
Get-Content ./database/schema.sql | docker exec -i automotriz-db mysql -u root -prootpassword
```

### Paso 3: Regenerar autoloader de Composer (Opcional)
Si añades controladores en caliente o reconstruyes desde cero:
```powershell
docker-compose exec -T prospects-service composer dump-autoload
docker-compose exec -T prospects-service php artisan route:clear
docker-compose exec -T prospects-service php artisan config:clear
```

---

## 5. Integración y Configuración de n8n

### Paso 1: Acceder al Panel
Abre tu navegador e ingresa a: **`http://localhost:5678`**

### Paso 2: Importar el Workflow
1.  Crea una cuenta inicial de propietario (si es el primer inicio).
2.  Ve a **Workflows** ➔ **Add Workflow** ➔ Haz clic en el menú de los tres puntos de la esquina superior derecha ➔ Selecciona **Import from File**.
3.  Selecciona el archivo [workflows.json](file:///c:/Users/Ysarmiento/Desktop/Marcaciones/Automotriz/n8n/workflows.json) ubicado en la carpeta `n8n/` de tu proyecto.

### Paso 3: Configurar Credenciales de Base de Datos en n8n
En el editor visual de n8n, verás nodos de base de datos MySQL (por ejemplo, `Sync: Upsert Prospecto (Sales)`). Debes vincular tu conexión MySQL a cada uno de ellos:
1.  Haz doble clic en cualquier nodo MySQL.
2.  En el campo **Credential for MySQL**, selecciona **Create New Credential** con los siguientes datos:
    *   **Host:** `db` *(Nombre del contenedor MySQL de Docker)*
    *   **Database:** *Cualquier base de datos base (ej: `prospects_db`)*
    *   **User:** `automotriz_user`
    *   **Password:** `automotriz_pass`
    *   **Port:** `3306`
3.  Guarda la credencial. Esta misma credencial se asociará automáticamente a los demás nodos de base de datos MySQL del workflow.

### Paso 4: Activar el Workflow
Cambia el interruptor de la esquina superior derecha de **Inactive** a **Active**.
*   **Modo Producción (Webhook):** Laravel llamará a `/webhook/...` y n8n procesará la sincronización en segundo plano de manera continua.
*   **Modo Pruebas / Depuración (Webhook-Test):** Si deseas inspeccionar la ejecución paso a paso, cambia temporalmente las URLs en los archivos `.env` a `/webhook-test/...`, entra al nodo correspondiente en n8n, presiona **Listen for test event** y gatilla el evento desde la UI web antes de 120 segundos.
