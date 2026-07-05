import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';
const TOKEN = __ENV.TOKEN || '';

const errorRate = new Rate('errors');
const loginDuration = new Trend('login_duration');
const insidenDuration = new Trend('insiden_duration');
const assessmentDuration = new Trend('assessment_duration');
const plenoDuration = new Trend('pleno_duration');
const suratDuration = new Trend('surat_duration');
const bootstrapDuration = new Trend('bootstrap_duration');

export const options = {
  stages: [
    { duration: '10s', target: 50 },
    { duration: '30s', target: 50 },
    { duration: '10s', target: 0 },
    { duration: '15s', target: 100 },
    { duration: '30s', target: 100 },
    { duration: '15s', target: 0 },
    { duration: '20s', target: 250 },
    { duration: '30s', target: 250 },
    { duration: '20s', target: 0 },
  ],
  thresholds: {
    errors: ['rate<0.01'],
    http_req_duration: ['p(95)<500'],
  },
};

export default function () {
  const apiParams = {
    headers: {
      'Authorization': `Bearer ${TOKEN}`,
      'Accept': 'application/json',
    },
  };

  const jsonApiParams = {
    headers: {
      'Authorization': `Bearer ${TOKEN}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  };

  // 1. LOGIN (web form login - session based)
  const loginRes = http.post(`${BASE_URL}/login`, {
    no_hp: '081200000001',
    password: 'loadtest123',
  }, { redirects: 0 });
  loginDuration.add(loginRes.timings.duration);
  errorRate.add(loginRes.status >= 400);
  check(loginRes, { 'login': (r) => r.status === 302 });

  // 2. DAFTAR INSIDEN (API: GET /api/v1/sync/status)
  const r1 = http.get(`${BASE_URL}/api/v1/sync/status`, apiParams);
  insidenDuration.add(r1.timings.duration);
  errorRate.add(r1.status >= 400);
  check(r1, { 'insiden list': (r) => r.status >= 200 && r.status < 300 });

  // 3. ASSESSMENT LIST (API: GET /api/v1/assessment)
  const r2 = http.get(`${BASE_URL}/api/v1/assessment`, apiParams);
  assessmentDuration.add(r2.timings.duration);
  errorRate.add(r2.status >= 400);
  check(r2, { 'assessment list': (r) => r.status >= 200 && r.status < 300 });

  // 4. PLENO LIST (API: no direct API, use web if available)
  const loginCookies = loginRes.cookies;
  const cookieStr = Object.entries(loginCookies).map(([k, v]) => `${k}=${v}`).join('; ');
  const webParams = { headers: { 'Cookie': cookieStr, 'Accept': 'text/html' } };
  const r3 = http.get(`${BASE_URL}/insiden/1/pleno`, webParams);
  plenoDuration.add(r3.timings.duration);
  errorRate.add(r3.status >= 400);
  check(r3, { 'pleno list': (r) => r.status >= 200 && r.status < 300 });

  // 5. SURAT LIST (web)
  const r4 = http.get(`${BASE_URL}/surat`, webParams);
  suratDuration.add(r4.timings.duration);
  errorRate.add(r4.status >= 400);
  check(r4, { 'surat list': (r) => r.status >= 200 && r.status < 300 });

  // 6. SYNC BOOTSTRAP (API: POST)
  const r5 = http.post(`${BASE_URL}/api/v1/bootstrap`, JSON.stringify({
    cursors: {},
    entities: ['penugasan', 'assessment', 'sitrep', 'klaster', 'mobilisasi'],
  }), jsonApiParams);
  bootstrapDuration.add(r5.timings.duration);
  errorRate.add(r5.status >= 400);
  check(r5, { 'bootstrap': (r) => r.status >= 200 && r.status < 300 });

  sleep(1);
}
