# CRM & Sistema de Gestión Automotriz (Microservicios + BFF)

Este es un sistema de gestión comercial automotriz diseñado para administrar el ciclo completo de prospectos (leads), ventas de autos y pólizas de seguro vehiculares asociadas, con un dashboard de indicadores clave en tiempo real. 

El proyecto cuenta con un blindaje de seguridad robusto alineado a las directrices del **OWASP Top 10** y una arquitectura contenerizada completa.

---

## 🛠️ Arquitectura y Tecnologías

El sistema está estructurado mediante un enfoque de microservicios con base de datos compartida y un API Gateway central:

*   **Frontend:** Vue.js 3 (Vite, Pinia, Vue Router, Tailwind CSS, FontAwesome) siguiendo el patrón Repositorio-Servicio.
*   **Backend (Microservicios):** 4 microservicios independientes desarrollados en **Laravel 10** (PHP 8.2):
    *   `prospects-service`: Gestión de prospectos, vehículos y autenticación.
    *   `sales-service`: Control transaccional de ventas y actualización de stock.
    *   `insurance-service`: Vinculación de pólizas de seguro.
    *   `dashboard-service`: Agregación de métricas y KPI comerciales.
*   **Base de Datos Centralizada:** MySQL 8.0 compartida para asegurar integridad referencial y simplificar transacciones complejas.
*   **API Gateway:** NGINX actuando como proxy inverso y router único.
*   **Automatización:** n8n para flujos de trabajo en segundo plano (alertas de inactividad de leads).
*   **Suite de Pruebas:** PestPHP (pruebas unitarias/integración) y K6 (pruebas de estrés).

---

## 🔒 Características de Seguridad (OWASP Top 10 Hardening)

*   **Mitigación de XSS (OWASP A03:2021-Injection):**
    *   **Backend:** Middleware global `SanitizeInput` en todos los microservicios que purga etiquetas HTML (`strip_tags`) y codifica caracteres especiales (`htmlspecialchars`) de todas las peticiones recursivamente.
    *   **Frontend:** Uso exclusivo de la interpolación segura de Vue (`{{ }}`) evitando `v-html`.
*   **Prevención de Robo de Tokens JWT (OWASP A07:2021-Identification and Authentication Failures):**
    *   **BFF (Backend-for-Frontend) Pattern:** El JWT no se almacena en `localStorage`. Al iniciar sesión, se establece en una cookie de red segura con bandera `HttpOnly=true` (JavaScript no puede leerla, impidiendo el robo por XSS) y `SameSite=Lax` (protección contra CSRF).
    *   **Token Relay:** NGINX intercepta automáticamente la cookie de la petición del navegador y la inyecta como cabecera `Authorization: Bearer <token>` hacia los microservicios de forma transparente.
*   **Aislamiento de Datos contra BOLA (OWASP A01:2021-Broken Access Control):**
    *   Todos los controladores y consultas SQL filtran los registros forzosamente según el `vendedor_id` autenticado (`Auth::id()`). Un vendedor no puede acceder o editar prospectos, ventas ni seguros de otros asesores comerciales.
*   **Gateway Hardening (OWASP A05:2021-Security Misconfiguration):**
    *   NGINX oculta la firma de su versión (`server_tokens off;`), limita el body a `2MB` para mitigar DoS, implementa filtros por expresiones regulares para denegar ataques en la URL (SQLi, path traversal), activa cabeceras estrictas (CSP, X-Frame-Options, HSTS) y limita la velocidad de peticiones (Rate Limiting) en `/api/auth/login` (máximo 10r/m por IP).

---

## 📋 Requisitos Previos

Para levantar el proyecto en tu máquina local necesitas tener instalado:

1.  **Docker Desktop** (con soporte para Docker Compose).
2.  **Git** (para control de versiones).
3.  **K6** (opcional, si deseas ejecutar las pruebas de estrés localmente).

---

## 🚀 Guía de Instalación y Despliegue Rápido

1.  **Clonar el Repositorio:**
    ```bash
    git clone https://github.com/D-Leon-P/Automotriz.git
    cd Automotriz
    ```

2.  **Construir y Levantar los Contenedores:**
    ```bash
    docker-compose up --build
    ```
    *Nota: Este comando descargará las imágenes necesarias, instalará las dependencias de Node.js en el frontend y ejecutará Composer en los 4 microservicios de forma totalmente automatizada.*

3.  **Inicialización de la Base de Datos:**
    Al iniciar el contenedor `db`, este ejecutará automáticamente el script SQL `database/schema.sql` para crear la estructura de las tablas e insertar los datos iniciales y vehículos de prueba.

---

## 🌐 Puertos y Accesibilidad de Servicios

Una vez que todos los contenedores estén levantados, podrás acceder a los siguientes servicios locales:

*   **Aplicación Web (Frontend Vue):** [http://localhost](http://localhost) (Puerto 80, enrutado por NGINX).
*   **Flujos de Automatización n8n:** [http://localhost:5678](http://localhost:5678).
*   **Base de Datos MySQL:** `localhost:3306` (Credenciales: root / `rootpassword`, usuario normal: `automotriz_user` / `automotriz_pass`).

### Credenciales de Vendedor de Prueba
Para ingresar al sistema desde la pantalla de login:
*   **Usuario:** `juan.perez@automotriz.com`
*   **Contraseña:** `password123`

---

## 🧪 Ejecución de Pruebas

### Pruebas Unitarias e Integración (Pest / PHPUnit)
Puedes correr las pruebas de sanitización y control de acceso del microservicio de prospectos ejecutando:
```bash
docker-compose exec prospects-service ./vendor/bin/phpunit
```
Para correr las pruebas de lógica comercial y stock del microservicio de ventas:
```bash
docker-compose exec sales-service ./vendor/bin/phpunit
```

### Pruebas de Estrés (K6)
Para realizar simulaciones de carga de 50 y 100 ventas simultáneas y verificar que la latencia se mantenga < 2 segundos:
```bash
k6 run tests/stress-test.js
```
