import React, { useState } from 'react';
import { Lock, User, Eye, EyeOff, AlertCircle, ArrowRight, ShieldCheck, KeyRound } from 'lucide-react';
import { adminFetch, resolveApiUrl } from './adminApi';

interface AdminLoginProps {
  onLoginSuccess: (admin: { username: string; name: string; role: string }) => void;
}

export const AdminLogin: React.FC<AdminLoginProps> = ({ onLoginSuccess }) => {
  const [username, setUsername] = useState('Anasasiq');
  const [password, setPassword] = useState('Anasasiq4302@');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username.trim() || !password.trim()) {
      setError('Please enter both username and password.');
      return;
    }

    setLoading(true);
    setError(null);

    const endpoints = ['/api/admin/login', '/api/login', '/api/auth/login'];
    let lastError = 'Server connection error. Please check backend API endpoints.';

    for (const endpoint of endpoints) {
      try {
        const fullUrl = resolveApiUrl(endpoint);
        const res = await fetch(fullUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json, text/plain, */*',
          },
          body: JSON.stringify({ username: username.trim(), password }),
        });

        const contentType = res.headers.get('content-type') || '';
        const responseText = await res.text();
        const trimmed = responseText.trim();

        // Check if server returned HTML (e.g. 404 / 500 error page or SPA fallback)
        if (
          contentType.includes('text/html') ||
          trimmed.startsWith('<') ||
          trimmed.toLowerCase().startsWith('<!doctype') ||
          trimmed.toLowerCase().startsWith('<html')
        ) {
          lastError = 'Server connection error. Please check backend API endpoints.';
          continue; // Try alternative endpoint alias if available
        }

        let data: any = {};
        try {
          data = JSON.parse(responseText);
        } catch {
          lastError = 'Server connection error. Please check backend API endpoints.';
          continue;
        }

        if (!res.ok || !data.success) {
          throw new Error(data.error || 'Invalid username or password.');
        }

        if (data.token) {
          localStorage.setItem('foodgo_admin_token', data.token);
        }

        onLoginSuccess(data.admin || { username: username.trim(), name: 'Administrator', role: 'Super Administrator' });
        return; // Success!
      } catch (err: any) {
        if (err.message && !err.message.includes('Unexpected token') && !err.message.includes('JSON')) {
          lastError = err.message;
        } else {
          lastError = 'Server connection error. Please check backend API endpoints.';
        }
        // If it's explicit bad credentials, break early without trying fallbacks
        if (err.message && (err.message.toLowerCase().includes('password') || err.message.toLowerCase().includes('username') || err.message.toLowerCase().includes('credentials'))) {
          break;
        }
      }
    }

    setError(lastError);
    setLoading(false);
  };

  const handleFillDemo = (user: string, pass: string) => {
    setUsername(user);
    setPassword(pass);
    setError(null);
  };

  return (
    <div className="min-h-screen w-full bg-[#F4F5F8] flex items-center justify-center p-4">
      <div className="w-full max-w-md bg-white rounded-3xl shadow-[0_12px_40px_rgba(0,0,0,0.08)] border border-gray-100/80 overflow-hidden">
        {/* Header Ribbon */}
        <div className="bg-[#EF2A39] px-8 pt-8 pb-7 text-center relative overflow-hidden">
          <div className="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none" />
          <div className="absolute -left-8 -bottom-8 w-32 h-32 rounded-full bg-black/10 blur-xl pointer-events-none" />

          <div className="relative z-10 flex flex-col items-center">
            <h1 className="text-3xl font-black italic tracking-wider text-white drop-shadow-xs">
              Foodgo
            </h1>
            <div className="inline-flex items-center gap-1.5 px-3 py-1 bg-white/20 backdrop-blur-xs rounded-full text-xs font-bold text-white mt-2">
              <ShieldCheck className="w-3.5 h-3.5" />
              <span>Admin Management Console</span>
            </div>
          </div>
        </div>

        {/* Login Form Body */}
        <div className="p-8">
          <div className="mb-6">
            <h2 className="text-xl font-black text-[#322A2E]">
              Administrator Sign In
            </h2>
            <p className="text-xs text-[#8E8E93] mt-1">
              Enter your authorized server credentials to access system controls.
            </p>
          </div>

          {error && (
            <div className="mb-5 p-3.5 rounded-2xl bg-red-50 border border-red-200/80 flex items-start gap-2.5 text-xs text-red-700 animate-in fade-in">
              <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
              <span className="font-semibold leading-relaxed">{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Username */}
            <div>
              <label className="block text-xs font-bold text-[#322A2E] mb-1.5">
                Admin Username
              </label>
              <div className="relative flex items-center bg-[#F8F9FA] rounded-2xl border border-gray-200/90 focus-within:border-[#EF2A39] focus-within:bg-white transition-all">
                <User className="w-4 h-4 text-gray-400 absolute left-4 pointer-events-none" />
                <input
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  placeholder="Enter admin username"
                  className="w-full py-3.5 pl-11 pr-4 text-sm font-semibold text-[#322A2E] bg-transparent outline-none"
                  autoComplete="username"
                  required
                />
              </div>
            </div>

            {/* Password */}
            <div>
              <label className="block text-xs font-bold text-[#322A2E] mb-1.5">
                Master Password
              </label>
              <div className="relative flex items-center bg-[#F8F9FA] rounded-2xl border border-gray-200/90 focus-within:border-[#EF2A39] focus-within:bg-white transition-all">
                <Lock className="w-4 h-4 text-gray-400 absolute left-4 pointer-events-none" />
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter master password"
                  className="w-full py-3.5 pl-11 pr-11 text-sm font-semibold text-[#322A2E] bg-transparent outline-none"
                  autoComplete="current-password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3.5 text-gray-400 hover:text-gray-600 transition-colors p-1 cursor-pointer"
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                >
                  {showPassword ? (
                    <EyeOff className="w-4 h-4" />
                  ) : (
                    <Eye className="w-4 h-4" />
                  )}
                </button>
              </div>
            </div>

            {/* Submit Button */}
            <div className="pt-3">
              <button
                type="submit"
                disabled={loading}
                className="w-full h-12 bg-[#322A2E] hover:bg-[#201A1D] text-white rounded-2xl text-sm font-extrabold flex items-center justify-center gap-2 shadow-[0_6px_20px_rgba(50,42,46,0.25)] transition-transform active:scale-[0.98] disabled:opacity-70 cursor-pointer"
              >
                {loading ? (
                  <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                  <>
                    <span>Authenticate & Access</span>
                    <ArrowRight className="w-4 h-4 stroke-[2.5]" />
                  </>
                )}
              </button>
            </div>
          </form>

          {/* Quick Credential Preset Bar */}
          <div className="mt-5 p-3 rounded-2xl bg-gray-50 border border-gray-200/70 flex flex-col gap-1.5">
            <div className="flex items-center gap-1.5 text-[11px] font-bold text-[#322A2E]">
              <KeyRound className="w-3.5 h-3.5 text-[#EF2A39]" />
              <span>Authorized Admin Credentials:</span>
            </div>
            <div className="flex flex-wrap gap-2 text-[11px]">
              <button
                type="button"
                onClick={() => handleFillDemo('Anasasiq', 'Anasasiq4302@')}
                className="px-2.5 py-1 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg text-gray-700 font-semibold cursor-pointer active:scale-95 transition-all"
              >
                Anasasiq / Anasasiq4302@
              </button>
              <button
                type="button"
                onClick={() => handleFillDemo('admin', 'admin123')}
                className="px-2.5 py-1 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg text-gray-700 font-semibold cursor-pointer active:scale-95 transition-all"
              >
                admin / admin123
              </button>
            </div>
          </div>

          {/* Security Information Footer */}
          <div className="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-[11px] text-[#8E8E93]">
            <span>Secured via Bcrypt & HTTP Sessions</span>
            <span className="font-semibold text-emerald-600">● Server Online</span>
          </div>
        </div>
      </div>
    </div>
  );
};
