import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend, Rate } from 'k6/metrics';

const latency = new Trend('mythos_latency', true);
const errors = new Rate('mythos_errors');

export const options = {
  stages: [
    { duration: '15s', target: 5 },
    { duration: '30s', target: 20 },
    { duration: '30s', target: 50 },
    { duration: '15s', target: 80 },
    { duration: '20s', target: 5 },
  ],
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<750', 'p(99)<1500'],
    mythos_errors: ['rate<0.01'],
  },
};

const base = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
  for (const path of ['/up', '/mythos/dar-hijama/health', '/mythos/dar-hijama/ready']) {
    const response = http.get(`${base}${path}`, { timeout: '5s' });
    latency.add(response.timings.duration);
    const ok = check(response, { [`${path} responded safely`]: (r) => r.status === 200 || r.status === 503 });
    errors.add(!ok);
  }
  sleep(0.25);
}
