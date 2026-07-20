import http from 'k6/http';
import { check, sleep } from 'k6';
import { textSummary } from 'https://jslib.k6.io/k6-summary/0.0.2/index.js';

// ============================================================
// PRUEBA DE ESTRÉS: 50 y 100 Ventas Simultáneas
// Métricas: tiempo de respuesta, tasa de error, uso de recursos
// Criterio de aceptación 7.2: p(95) < 2s para 100 VUs
// ============================================================

const BASE_URL = 'http://localhost';

// Configuración de las fases de la prueba
export const options = {
  stages: [
    { duration: '10s', target: 50 },  // Rampa de subida a 50 usuarios simultáneos (carga media)
    { duration: '20s', target: 50 },  // Mantener 50 usuarios simultáneos
    { duration: '10s', target: 100 }, // Rampa de subida a 100 usuarios simultáneos (carga pico)
    { duration: '20s', target: 100 }, // Mantener 100 usuarios simultáneos
    { duration: '10s', target: 0 },   // Rampa de bajada a 0 usuarios
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% de las peticiones deben responder en menos de 2 segundos (<2s)
    http_req_failed: ['rate<0.05'],    // Tasa de error de red o servidor menor al 5%
  },
};

// Función setup: Se ejecuta una vez al inicio. Ideal para obtener el token JWT.
// Obtener lista de vehículos
export function setup() {
  // 1. Login
  const loginRes = http.post(`${BASE_URL}/api/auth/login`, JSON.stringify({
    email: 'juan.perez@automotriz.com',
    password: 'password123',
  }), {
    headers: { 'Content-Type': 'application/json' },
  });
  if (loginRes.status !== 200) {
    console.error(`LOGIN FALLÓ: status=${loginRes.status}`);
    return { token: null, vehiculos: [] };
  }

  const token = loginRes.cookies['auth_token']?.[0]?.value;
  if (!token) {
    console.error('LOGIN FALLÓ: No se obtuvo auth_token cookie');
    return { token: null, vehiculos: [] };
  }

  console.log('LOGIN exitoso. Obteniendo vehículos...');

  // 2. Obtener vehículos
  const vehiculosRes = http.get(`${BASE_URL}/api/vehiculos`, {
    headers: { 'Authorization': `Bearer ${token}` },
    cookies: { auth_token: token },
  });

  let vehiculos = [];
  if (vehiculosRes.status === 200) {
    vehiculos = JSON.parse(vehiculosRes.body);
    console.log(`Vehículos encontrados: ${vehiculos.length}`);
  } else {
    console.error(`Error al obtener vehículos: ${vehiculosRes.status}`);
  }

  return { token, vehiculos };
}

// Función principal de carga ejecutada por cada Usuario Virtual (VU)
export default function (data) {
  if (!data.token || !data.vehiculos || data.vehiculos.length === 0) {
    sleep(1);
    return;
  }

  // Seleccionar vehículo round-robin (distribuye carga entre todos)
  const vehiculoIndex = __VU % data.vehiculos.length;
  const vehiculo = data.vehiculos[vehiculoIndex];

  // Seleccionar prospecto aleatorio (IDs 1-7)
  const randomProspectId = Math.floor(Math.random() * 7) + 1;

  // 70% ventas efectivas / 30% fallidas
  const esEfectiva = Math.random() > 0.3;
  const estado = esEfectiva ? 'efectiva' : 'fallida';

  const payload = JSON.stringify({
    prospecto_id: randomProspectId,
    vehiculo_id: vehiculo.id,
    monto: vehiculo.precio,
    estado: estado,
    motivo_perdida: !esEfectiva ? 'Presupuesto insuficiente en fase de prueba de estrés' : null,
  });

  const params = {
    headers: {
      'Content-Type': 'application/json',
    },
    cookies: {
      auth_token: data.token,
    },
    responseCallback: http.expectedStatuses(201, 400),
  };

  const res = http.post(`${BASE_URL}/api/ventas`, payload, params);

  // Aserciones: 201 (creada) o 400 (stock agotado / prospecto ya en cierre)
  check(res, {
    'status es 201 o 400 (comportamiento esperado)': (r) => r.status === 201 || r.status === 400,
    'tiempo de respuesta < 2s': (r) => r.timings.duration < 2000,
  });

  // Espaciar las peticiones entre 0.5s y 1.5s de forma realista
  sleep(Math.random() * 1 + 0.5);
}

// Exporta resultados a JSON + stdout
export function handleSummary(data) {
  const summary = {
    // Métricas globales
    total_requests: data.metrics.http_reqs?.values?.count || 0,
    avg_duration_ms: data.metrics.http_req_duration?.values?.avg?.toFixed(2) || 0,
    p50_ms: data.metrics.http_req_duration?.values?.['p(50)']?.toFixed(2) || 0,
    p75_ms: data.metrics.http_req_duration?.values?.['p(75)']?.toFixed(2) || 0,
    p90_ms: data.metrics.http_req_duration?.values?.['p(90)']?.toFixed(2) || 0,
    p95_ms: data.metrics.http_req_duration?.values?.['p(95)']?.toFixed(2) || 0,
    p99_ms: data.metrics.http_req_duration?.values?.['p(99)']?.toFixed(2) || 0,
    max_duration_ms: data.metrics.http_req_duration?.values?.max?.toFixed(2) || 0,
    error_rate: ((data.metrics.http_req_failed?.values?.rate || 0) * 100).toFixed(2) + '%',
    http_reqs_per_second: data.metrics.http_reqs?.values?.rate?.toFixed(2) || 0,

    // Evaluación contra criterios de aceptación
    criterio_aceptacion: {
      p95_less_than_2s: (data.metrics.http_req_duration?.values?.['p(95)'] || 0) < 2000,
      error_rate_less_than_5pct: (data.metrics.http_req_failed?.values?.rate || 0) < 0.05,
    },

    // Datos raw para análisis posterior
    raw_metrics: data.metrics,
  };

  return {
    'tests/reportes/stress-result.json': JSON.stringify(summary, null, 2),
    stdout: textSummary(data, { indent: ' ', enableColors: true }),
  };
}
