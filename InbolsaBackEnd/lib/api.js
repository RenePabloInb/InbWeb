// lib/api.js — limpio y estable
// Base por defecto (XAMPP/iPage con subcarpeta inbolsaNeo)
const DEFAULT_API_BASE = '/inbolsa-api/api';

// Lee de variables de Astro si existen, si no usa la base por defecto
export const API_BASE =
  (typeof import.meta !== 'undefined' &&
    import.meta.env &&
    import.meta.env.PUBLIC_API_BASE) ||
  DEFAULT_API_BASE;

// Útil si necesitas el folder de la API sin /api al final
export const BACK_BASE = API_BASE.replace(/\/api$/, '');

// Helper para JSON con credenciales
async function fetchJSON(path, init = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    credentials: 'include',
    ...init,
    headers: {
      'Content-Type': 'application/json',
      ...(init.headers || {}),
    },
  });

  const text = await res.text();
  let data = {};
  if (text) {
    try {
      data = JSON.parse(text);
    } catch {
      // Si no es JSON, conserva el texto por si el backend envía un mensaje plano
      data = text;
    }
  }

  if (!res.ok) {
    const msg =
      (data && typeof data === 'object' && data.error) ||
      (typeof data === 'string' ? data : 'Request failed');
    throw new Error(msg);
  }
  return data;
}

// Endpoints expuestos (igual que ya usas)
export const api = {
  // Health
  health: () => fetchJSON('/health'),

  // Auth
  login: (email, password) =>
    fetchJSON('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) }),
  logout: () => fetchJSON('/auth/logout', { method: 'POST' }),
  me: () => fetchJSON('/auth/me'),

  // QR
  qrCreate: (input) =>
    fetchJSON('/qr/create', { method: 'POST', body: JSON.stringify(input) }),
  qrList: () => fetchJSON('/qr/list'),
  qrRevoke: (code) =>
    fetchJSON('/qr/revoke', { method: 'POST', body: JSON.stringify({ code }) }),
  qrValidate: (code) =>
    fetchJSON(`/qr/validate?code=${encodeURIComponent(code)}`),

  // Landing / acceso
  accessPayload: (token) =>
    fetchJSON(`/access/payload${token ? `?token=${encodeURIComponent(token)}` : ''}`),
};
