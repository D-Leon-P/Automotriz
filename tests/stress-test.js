import http from 'k6/http';
import { check, sleep } from 'k6';

// Configuración de las fases de la prueba de estrés
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
export function setup() {
  const loginUrl = 'http://localhost/api/auth/login';
  const payload = JSON.stringify({
    email: 'juan.perez@automotriz.com',
    password: 'password123',
  });
  const params = {
    headers: { 'Content-Type': 'application/json' },
  };

  const res = http.post(loginUrl, payload, params);
  
  if (res.status === 200) {
    const token = res.json('access_token');
    return { token: token };
  } else {
    console.error('ERROR EN SETUP: No se pudo iniciar sesión para la prueba de estrés');
    return { token: null };
  }
}

// Función principal de carga ejecutada por cada Usuario Virtual (VU)
export default function (data) {
  if (!data.token) {
    sleep(1);
    return;
  }

  const url = 'http://localhost/api/ventas';
  
  // Simular la venta del vehículo #1 por parte de prospectos aleatorios (IDs de prueba 1 al 7)
  const randomProspectId = Math.floor(Math.random() * 7) + 1;
  const randomStatus = Math.random() > 0.3 ? 'efectiva' : 'fallida';
  
  const payload = JSON.stringify({
    prospecto_id: randomProspectId,
    vehiculo_id: 1, // Corolla Hybrid
    vendedor_id: 1, // Juan Pérez
    monto: 26500.00,
    estado: randomStatus,
    motivo_perdida: randomStatus === 'fallida' ? 'Presupuesto insuficiente en esta fase' : null,
  });

  const params = {
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${data.token}`,
    },
  };

  const res = http.post(url, payload, params);

  // Aserciones
  check(res, {
    'status es 201 o 400 (stock agotado esperado)': (r) => r.status === 201 || r.status === 400,
    'tiempo de respuesta < 2s': (r) => r.timings.duration < 2000,
  });

  // Espaciar las peticiones entre 0.5s y 1.5s de forma realista
  sleep(Math.random() * 1 + 0.5);
}
