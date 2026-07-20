# REQUERIMIENTO DE PROYECTO: SISTEMA DE GESTIÓN DE PROSPECTOS Y VENTAS PARA EMPRESA AUTOMOTRIZ

- **Curso:** Pruebas de Software
- **Duración:** 2 semanas
- **Cliente:** Empresa de venta de autos

---

## 1. DESCRIPCIÓN DEL PROYECTO

### 1.1 Contexto del Negocio

El cliente opera en el sector automotriz y necesita un sistema que le permita gestionar su proceso comercial desde la prospección hasta el cierre de ventas, incluyendo la gestión de seguros vehiculares asociados a cada transacción.

### 1.2 Objetivo General

Desarrollar una aplicación web que permita:

- Gestionar el ciclo completo de prospectos y ventas de autos
- Administrar seguros vehiculares vinculados a cada venta
- Automatizar procesos mediante n8n
- Visualizar un dashboard con indicadores clave de desempeño comercial
- Soportar simulaciones de carga con 50 y 100 ventas simultáneas

---

## 2. REQUERIMIENTOS FUNCIONALES

### 2.1 Gestión de Prospectos

El sistema debe permitir:

- Registrar prospectos con datos de contacto e interés vehicular
- Clasificar prospectos por etapa del embudo de ventas:
  - **Prospección inicial:** Primer contacto
  - **Calificación:** Evaluación de interés y capacidad de compra
  - **Negociación:** Cotización y seguimiento
  - **Cierre:** Venta efectiva o venta fallida

### 2.2 Gestión de Ventas

- Registrar ventas reales con datos del vehículo, cliente y monto
- Registrar ventas fallidas con motivo de pérdida
- Calcular tasa de conversión por etapa y por vendedor

### 2.3 Gestión de Seguros Vehiculares

- Asociar pólizas de seguro a cada venta efectiva
- Registrar tipo de seguro, prima esperada y prima real
- Identificar seguros prospectados vs. seguros vendidos

### 2.4 Dashboard de Indicadores

El sistema debe mostrar en tiempo real:

| Indicador | Descripción |
|-----------|-------------|
| Total de Prospectos | Número de prospectos en proceso |
| Ventas Realizadas | Cantidad de ventas cerradas exitosamente |
| Ventas Fallidas | Cantidad de oportunidades perdidas |
| Tasa de Conversión | % de prospectos que se convierten en ventas |
| Seguros Vinculados | Número de seguros asociados a ventas |
| Embudo de Ventas | Gráfico de etapas con cantidades y % de conversión |

---

## 3. REQUERIMIENTOS TÉCNICOS

### 3.1 Arquitectura de Microservicios

El backend debe estar compuesto por los siguientes microservicios:

- **Servicio de Prospectos:** Gestión de leads y etapas del embudo
- **Servicio de Ventas:** Registro y seguimiento de transacciones
- **Servicio de Seguros:** Administración de pólizas vehiculares
- **Servicio de Dashboard:** Agregación de métricas e indicadores

### 3.2 Automatización con n8n

Implementar workflows en n8n para:

- Automatizar seguimiento de prospectos inactivos
- Disparar alertas cuando un prospecto lleva más de X días sin avance
- Sincronizar datos entre microservicios

### 3.3 Base de Datos

- **Recomendación:** Oracle 23ai Free para pruebas
- Estructura con tablas: Prospectos, Ventas, Seguros, Vehículos, Vendedores
- Integridad referencial entre tablas

### 3.4 Pruebas de Estrés

El sistema debe ser probado con:

- **50 ventas simultáneas:** Simulación de carga media
- **100 ventas simultáneas:** Simulación de carga pico
- **Métricas a evaluar:** tiempo de respuesta, tasa de error, uso de recursos

---

## 4. ENTREGABLES DEL PROYECTO

### 4.1 Desarrollo

- Código fuente de la aplicación web (frontend + microservicios)
- Workflows de n8n configurados y documentados
- Scripts de base de datos (DDL + DML para datos de prueba)

### 4.2 Pruebas

- **Pruebas Unitarias:** Cobertura mínima del 80%
- **Pruebas de Integración:** Entre microservicios y con n8n
- **Pruebas de Estrés:** Reporte con resultados de 50 y 100 ventas simultáneas
- **Dashboard de Pruebas:** Visualización de métricas de rendimiento

### 4.3 Documentación

- Manual de usuario del sistema
- Guía de configuración y despliegue
- Reporte final de pruebas con resultados y recomendaciones

---

## 5. CRONOGRAMA DE 2 SEMANAS

### Semana 1: Desarrollo y Pruebas Unitarias

| Día | Actividad | Responsable |
|-----|-----------|-------------|
| 1 | Configuración de entorno, base de datos y n8n | DevOps/QA |
| 2 | Desarrollo de microservicios base | Backend Dev |
| 3 | Desarrollo de workflows en n8n | Backend Dev |
| 4 | Desarrollo frontend y dashboard | Frontend Dev |
| 5 | Pruebas unitarias y de integración inicial | QA |

### Semana 2: Integración, Estrés y Finalización

| Día | Actividad | Responsable |
|-----|-----------|-------------|
| 6 | Integración completa de componentes | Team |
| 7 | Configuración de entorno para pruebas de estrés | DevOps |
| 8 | Ejecución pruebas de estrés (50 y 100 ventas) | QA |
| 9 | Análisis de resultados y correcciones | Team |
| 10 | Pruebas de regresión y entrega final | QA |

---

## 6. ROLES Y EQUIPO DE TRABAJO

| Rol | Cantidad | Responsabilidad |
|-----|----------|-----------------|
| Backend Developer | 2 | Microservicios y n8n |
| Frontend Developer | 1 | Aplicación web y dashboard |
| QA Engineer | 2 | Pruebas unitarias, integración y estrés |
| DevOps | 1 | Configuración de entornos |

---

## 7. CRITERIOS DE ACEPTACIÓN

### 7.1 Funcionales

- El sistema permite gestionar el ciclo completo de prospectos y ventas
- El dashboard muestra correctamente los indicadores solicitados
- Los seguros vehiculares se vinculan correctamente a las ventas

### 7.2 Técnicos

- Pruebas unitarias con cobertura ≥ 80%
- Pruebas de estrés: tiempo de respuesta < 2s para 100 ventas simultáneas
- n8n workflows ejecutándose sin errores
- Microservicios comunicándose correctamente

---

## 8. RIESGOS Y MITIGACIÓN

| Riesgo | Mitigación |
|--------|------------|
| Complejidad de integración n8n-microservicios | Documentación detallada y pruebas tempranas |
| Rendimiento en pruebas de 100 ventas simultáneas | Optimización de consultas y uso de caché |
| Tiempo limitado de 2 semanas | Priorizar funcionalidades críticas |
