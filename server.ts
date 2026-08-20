import express from 'express';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = Number(process.env.PORT) || 3000;

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const baseDir = __dirname;
const configDir = path.join(baseDir, 'config');
const connectionConfigFile = path.join(configDir, 'connection.json');
const publicConfigFile = path.join(configDir, 'connection-public.json');
const pluginZipPath = path.join(baseDir, 'public/foodgo-headless-connector.zip');

if (!fs.existsSync(configDir)) {
  fs.mkdirSync(configDir, { recursive: true });
}

// 1. Download Plugin ZIP endpoint
app.get(['/foodgo-headless-connector.zip', '/public/foodgo-headless-connector.zip'], (_req, res) => {
  if (fs.existsSync(pluginZipPath)) {
    res.download(pluginZipPath, 'foodgo-headless-connector.zip');
  } else {
    res.status(404).send('Plugin zip not found. Run npm run package-plugin first.');
  }
});

// 2. Safe Public Connection Endpoint (Frontend auto-discovery)
app.get('/api/connection/public', (_req, res) => {
  let wpUrl = process.env.VITE_WP_URL || '';
  let connected = false;

  if (fs.existsSync(publicConfigFile)) {
    try {
      const publicData = JSON.parse(fs.readFileSync(publicConfigFile, 'utf-8'));
      if (publicData.wpUrl) wpUrl = publicData.wpUrl;
      connected = !!publicData.connected;
    } catch {
      // ignore
    }
  }

  res.json({
    wpUrl,
    connected,
    time: new Date().toISOString(),
  });
});

// Helper for testing WP endpoints
async function testEndpoint(url: string, headers: Record<string, string> = {}) {
  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 6000);
    const resp = await fetch(url, { headers, signal: controller.signal });
    clearTimeout(timeout);
    return {
      connected: resp.ok,
      code: resp.status,
      details: resp.ok ? 'Reachable & active' : `HTTP ${resp.status} ${resp.statusText}`,
    };
  } catch (err: any) {
    return {
      connected: false,
      code: 0,
      details: err.message || 'Connection failed / unreachable',
    };
  }
}

// 3. Test & Save Connection API
app.post('/api/connection', async (req, res) => {
  const { wpUrl, wpUsername, wpAppPassword, action } = req.body;
  const cleanUrl = String(wpUrl || '').replace(/\/+$/, '');

  const headers: Record<string, string> = {
    'User-Agent': 'Foodgo-Connector/3.0',
    Accept: 'application/json',
  };

  if (wpUsername && wpAppPassword && !wpAppPassword.includes('••••')) {
    const auth = Buffer.from(`${wpUsername.trim()}:${wpAppPassword.trim().replace(/\s+/g, '')}`).toString('base64');
    headers['Authorization'] = `Basic ${auth}`;
  }

  const [wpCore, wcStore, foodgoPlugin, wcCart] = await Promise.all([
    testEndpoint(`${cleanUrl}/wp-json/`, headers),
    testEndpoint(`${cleanUrl}/wp-json/wc/store/v1/products?per_page=1`, headers),
    testEndpoint(`${cleanUrl}/wp-json/foodgo/v1/config`, headers),
    testEndpoint(`${cleanUrl}/wp-json/wc/store/v1/cart`, headers),
  ]);

  const testResults = {
    wpCore: { name: 'WordPress Core REST API', endpoint: '/wp-json/', ...wpCore },
    wcStore: { name: 'WooCommerce Store API', endpoint: '/wp-json/wc/store/v1/products', ...wcStore },
    foodgoPlugin: { name: 'Foodgo Connector Plugin', endpoint: '/wp-json/foodgo/v1/config', ...foodgoPlugin },
    wooCommerce: { name: 'WooCommerce Core Engine', endpoint: '/wp-json/wc/store/v1/cart', connected: wcStore.connected || wcCart.connected, code: wcStore.code, details: wcStore.connected ? 'Active' : 'Not detected' },
  };

  if (action === 'connect') {
    const isConn = wpCore.connected && wcStore.connected;
    fs.writeFileSync(connectionConfigFile, JSON.stringify({
      wpUrl: cleanUrl,
      wpUsername,
      wpAppPassword,
      connected: isConn,
      lastTested: new Date().toLocaleString(),
    }, null, 2));

    fs.writeFileSync(publicConfigFile, JSON.stringify({
      wpUrl: cleanUrl,
      connected: isConn,
      foodgoPlugin: foodgoPlugin.connected,
    }, null, 2));
  }

  res.json({
    success: true,
    results: testResults,
  });
});

// 4. Handle /admin.php when served through Express
app.get('/admin.php', (_req, res) => {
  const adminPhpPath = path.join(baseDir, 'admin.php');
  if (fs.existsSync(adminPhpPath)) {
    // If PHP is installed on machine, can execute, or return the static HTML template
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    // Let's send the admin.php content or rendered HTML
    const content = fs.readFileSync(adminPhpPath, 'utf-8');
    // Extract everything from <!DOCTYPE html> onwards for clean browser rendering
    const htmlStart = content.indexOf('<!DOCTYPE html>');
    if (htmlStart !== -1) {
      let html = content.substring(htmlStart);
      // Replace basic PHP tags with live values
      let savedUrl = process.env.VITE_WP_URL || '';
      let savedUser = '';
      if (fs.existsSync(connectionConfigFile)) {
        try {
          const cfg = JSON.parse(fs.readFileSync(connectionConfigFile, 'utf-8'));
          if (cfg.wpUrl) savedUrl = cfg.wpUrl;
          if (cfg.wpUsername) savedUser = cfg.wpUsername;
        } catch {}
      }
      html = html.replace(/<\?php echo esc_attr\(\$savedConfig\['wpUrl'\]\); \?>/g, savedUrl);
      html = html.replace(/<\?php echo esc_attr\(\$savedConfig\['wpUsername'\]\); \?>/g, savedUser);
      html = html.replace(/<\?php echo !empty\(\$savedConfig\['wpAppPassword'\]\) \? '••••••••••••••••••••••••' : 'abcd efgh ijkl mnop qrst uvwx'; \?>/g, '••••••••••••••••••••••••');
      res.send(html);
      return;
    }
  }
  res.status(404).send('admin.php not found');
});

// Serve compiled static assets
const distPath = path.resolve(__dirname, 'dist');
const publicDirPath = path.resolve(__dirname, 'public');
app.use(express.static(distPath));
app.use(express.static(publicDirPath));

// SPA fallback for frontend customer routes
app.get('*', (req, res, next) => {
  if (req.path.startsWith('/api') || req.path.endsWith('.php')) {
    return next();
  }
  res.sendFile(path.join(distPath, 'index.html'));
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Foodgo Platform running on http://localhost:${PORT}`);
});
