// lib/privado.js — limpio y estable
export const PRIVATE_FLAG_KEY = 'inbolsa:qr:ok';
export const PRIVATE_EXPIRES_KEY = 'inbolsa:qr:exp';
export const PRIVATE_PRODUCTS_KEY = 'inbolsa:qr:products';

function hasCookie(name) {
  try {
    return document.cookie.split(';').some((c) => c.trim().startsWith(name + '='));
  } catch {
    return false;
  }
}

export function enablePrivate(minutes = 120) {
  try {
    localStorage.setItem(PRIVATE_FLAG_KEY, '1');
    const exp = Date.now() + minutes * 60_000;
    localStorage.setItem(PRIVATE_EXPIRES_KEY, String(exp));
    document.cookie = `${PRIVATE_FLAG_KEY}=1; path=/; SameSite=Lax; max-age=${minutes * 60}`;
    window.dispatchEvent(new CustomEvent('inbolsa:private:change', { detail: { enabled: true } }));
  } catch (e) {
    console.error('Error habilitando privado:', e);
  }
}

export function disablePrivate() {
  try {
    localStorage.removeItem(PRIVATE_FLAG_KEY);
    localStorage.removeItem(PRIVATE_EXPIRES_KEY);
    localStorage.removeItem(PRIVATE_PRODUCTS_KEY);

    // Borrar posibles cookies usadas por el back
    document.cookie = `${PRIVATE_FLAG_KEY}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
    document.cookie = `qrauth=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
    document.cookie = `inb_access=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
    document.cookie = `priv_mode=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;

    window.dispatchEvent(new CustomEvent('inbolsa:private:change', { detail: { enabled: false } }));

    if (location.pathname === '/privado' || location.pathname === '/productos') {
      location.href = '/';
    }
  } catch (e) {
    console.error('Error deshabilitando privado:', e);
  }
}

export function isPrivateEnabled() {
  try {
    if (localStorage.getItem(PRIVATE_FLAG_KEY) === '1') {
      const exp = Number(localStorage.getItem(PRIVATE_EXPIRES_KEY) || '0');
      if (!exp || Date.now() <= exp) return true;
      disablePrivate();
      return false;
    }
    if (hasCookie(PRIVATE_FLAG_KEY)) return true;
    if (hasCookie('qrauth') || hasCookie('inb_access') || hasCookie('priv_mode')) return true;
    return false;
  } catch (e) {
    console.error('Error verificando privado:', e);
    return hasCookie(PRIVATE_FLAG_KEY) || hasCookie('qrauth') || hasCookie('inb_access') || hasCookie('priv_mode');
  }
}

export function setGrantProducts(ids) {
  try {
    const arr = Array.isArray(ids) ? ids.filter(Boolean) : [];
    localStorage.setItem(PRIVATE_PRODUCTS_KEY, JSON.stringify(arr));
    window.dispatchEvent(new CustomEvent('inbolsa:products:change', { detail: { products: arr } }));
  } catch (e) {
    console.error('Error guardando productos:', e);
  }
}

export function getGrantProducts() {
  try {
    const raw = localStorage.getItem(PRIVATE_PRODUCTS_KEY);
    if (raw) {
      const arr = JSON.parse(raw);
      return Array.isArray(arr) ? arr : [];
    }
    try {
      const params = new URLSearchParams(window.location.search);
      const pParam = params.get('p');
      if (pParam) {
        const products = pParam.split(',').filter(Boolean);
        setGrantProducts(products);
        return products;
      }
    } catch {}
    return [];
  } catch (e) {
    console.error('Error obteniendo productos:', e);
    return [];
  }
}

// Verificación periódica (usa la ruta fija del back en XAMPP/iPage)
export async function checkAccessValid() {
  try {
    const API_BASE = '/inbolsa-api/api';
    const res = await fetch(`${API_BASE}/access/payload`, { credentials: 'include' });
    if (!res.ok) return false;
    const data = await res.json();
    return data.ok === true;
  } catch (e) {
    console.error('Error verificando acceso:', e);
    return false;
  }
}

export function startRevocationCheck() {
  setInterval(async () => {
    if (!isPrivateEnabled()) return;
    try {
      const ok = await checkAccessValid();
      if (!ok) disablePrivate();
    } catch (e) {
      console.error('Error en verificación de revocación:', e);
    }
  }, 30000);
}

if (typeof window !== 'undefined') {
  setTimeout(() => startRevocationCheck(), 5000);
}

