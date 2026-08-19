import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import { defineConfig, Plugin } from 'vite';
import express from 'express';
import cookieParser from 'cookie-parser';
import { apiRouter } from './src/server/api';

function expressApiPlugin(): Plugin {
  const app = express();
  app.use(express.json({ limit: '10mb' }));
  app.use(express.urlencoded({ extended: true, limit: '10mb' }));
  app.use(cookieParser());
  app.use('/api', apiRouter);

  // Catch-all for unhandled /api calls in Express so Vite never serves index.html for API routes
  app.use('/api', (req, res) => {
    if (!res.headersSent) {
      res.status(404).json({
        success: false,
        error: `API route not found: ${req.method} ${req.originalUrl || req.url}`,
      });
    }
  });

  return {
    name: 'express-api-plugin',
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        const url = req.url || '';
        const originalUrl = (req as any).originalUrl || '';
        if (url.startsWith('/api') || originalUrl.startsWith('/api')) {
          app(req as any, res as any, (err) => {
            if (res.headersSent) return;
            if (err) {
              res.statusCode = 500;
              res.setHeader('Content-Type', 'application/json');
              res.end(JSON.stringify({ success: false, error: err.message || 'Internal API Error' }));
            } else {
              res.statusCode = 404;
              res.setHeader('Content-Type', 'application/json');
              res.end(JSON.stringify({ success: false, error: `Endpoint not found: ${req.method} ${url}` }));
            }
          });
        } else {
          next();
        }
      });
    },
  };
}

export default defineConfig(() => {
  return {
    plugins: [react(), tailwindcss(), expressApiPlugin()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      },
    },
    build: {
      rollupOptions: {
        input: {
          main: path.resolve(__dirname, 'index.html'),
          admin: path.resolve(__dirname, 'admin.html'),
          merchant: path.resolve(__dirname, 'merchant.html'),
          delivery: path.resolve(__dirname, 'delivery.html'),
        },
      },
    },
    server: {
      hmr: process.env.DISABLE_HMR !== 'true',
      watch: process.env.DISABLE_HMR === 'true' ? null : {},
    },
  };
});
