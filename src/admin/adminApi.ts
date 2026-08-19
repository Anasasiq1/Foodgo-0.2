/**
 * Robust API Client for Foodgo Admin & Backend Services.
 * - Handles API base URL configuration (VITE_API_BASE_URL / NEXT_PUBLIC_API_URL)
 * - Attaches authentication credentials and session tokens
 * - Prevents "Unexpected token '<', '<!DOCTYPE '... is not valid JSON" errors by validating Content-Type and catching HTML 404/500 pages cleanly.
 */

export function getApiBaseUrl(): string {
  try {
    const envBase = (
      (import.meta as any).env?.VITE_API_BASE_URL ||
      (import.meta as any).env?.NEXT_PUBLIC_API_URL ||
      ''
    ).trim();
    return envBase.replace(/\/+$/, '');
  } catch {
    return '';
  }
}

export function resolveApiUrl(pathOrUrl: string | URL): string {
  const str = typeof pathOrUrl === 'string' ? pathOrUrl : pathOrUrl.toString();
  if (str.startsWith('http://') || str.startsWith('https://')) {
    return str;
  }
  const base = getApiBaseUrl();
  if (!base) return str;
  const cleanPath = str.startsWith('/') ? str : `/${str}`;
  return `${base}${cleanPath}`;
}

/**
 * Enhanced fetch wrapper for Admin API requests.
 * Automatically decorates the returned Response's .json() method so that
 * unexpected HTML (e.g., 404/500 error pages from Nginx/aaPanel/Apache/Vite)
 * produces clear, helpful errors rather than raw JSON syntax crashes.
 */
export async function adminFetch(input: RequestInfo | URL, init: RequestInit = {}): Promise<Response> {
  const token = localStorage.getItem('foodgo_admin_token');
  const headers = new Headers(init.headers || {});

  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json, text/plain, */*');
  }

  if (token && !headers.has('x-admin-token')) {
    headers.set('x-admin-token', token);
  }

  const finalUrl = typeof input === 'string' || input instanceof URL 
    ? resolveApiUrl(input) 
    : (input as Request).url;

  try {
    const response = await fetch(finalUrl, {
      ...init,
      credentials: 'include',
      headers,
    });

    // Create a safe wrapper around response.json()
    const originalJson = response.json.bind(response);
    const originalText = response.text.bind(response);

    // Override .json() to guard against HTML payloads
    response.json = async () => {
      const contentType = response.headers.get('content-type') || '';
      
      // If header is explicitly HTML or not JSON
      if (contentType.includes('text/html')) {
        const text = await originalText();
        throw new Error(
          !response.ok
            ? `Server connection error (HTTP ${response.status}). Please check backend API endpoints.`
            : 'Server returned HTML instead of JSON. Please check backend API endpoints.'
        );
      }

      const text = await originalText();
      const trimmed = text.trim();

      if (!trimmed) {
        return {};
      }

      // Check if the payload starts with HTML tags like <!DOCTYPE or <html
      if (trimmed.startsWith('<') || trimmed.toLowerCase().startsWith('<!doctype') || trimmed.toLowerCase().startsWith('<html')) {
        throw new Error(
          !response.ok
            ? `Server connection error (HTTP ${response.status}). Please check backend API endpoints.`
            : 'Server connection error. Please check backend API endpoints.'
        );
      }

      try {
        return JSON.parse(text);
      } catch (err: any) {
        throw new Error(
          'Invalid response received from server. Please check backend API endpoints.'
        );
      }
    };

    return response;
  } catch (networkError: any) {
    if (networkError.message && (
      networkError.message.includes('Server connection error') ||
      networkError.message.includes('backend API endpoints')
    )) {
      throw networkError;
    }
    throw new Error('Server connection error. Please check backend API endpoints.');
  }
}

/**
 * Direct helper for JSON API requests with safe typing & error handling
 */
export async function safeFetchJson<T = any>(
  endpoint: string,
  init: RequestInit = {}
): Promise<{ success: boolean; data?: T; error?: string; status: number }> {
  try {
    const res = await adminFetch(endpoint, init);
    const data = await res.json();
    return {
      success: res.ok && (data.success !== false),
      data,
      error: !res.ok ? (data.error || `Request failed with status ${res.status}`) : undefined,
      status: res.status,
    };
  } catch (err: any) {
    return {
      success: false,
      error: err.message || 'Server connection error. Please check backend API endpoints.',
      status: 0,
    };
  }
}
