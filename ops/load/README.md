# Production-like load test

Run against an isolated production-configured deployment:

```bash
k6 run -e BASE_URL=https://staging.example.com ops/load/k6-production.js
```

When k6 is unavailable, the dependency-free PHP cURL runner provides executable
HTTP concurrency evidence:

```bash
php ops/load/http-load.php http://127.0.0.1:8000 300 20
```

Run `ops/probes/run-booking-contention.ps1 -Attempts 50` with the isolated
MySQL test environment for write contention. Record k6 request rate, p50, p95,
p99, errors, database connections, lock waits, deadlocks, queue depth, worker
throughput and memory alongside the run. Never point write probes at production.
