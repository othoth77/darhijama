# Production load-test plan

Run only against an isolated environment with synthetic patients and provider
sandboxes.

## Scenarios

1. Concurrent booking: 25, 50, and 100 clients target the same and distinct
   practitioner slots. There must be zero overlapping accepted bookings.
2. Notification burst: enqueue 1,000 appointment/reminder notifications and
   measure queue depth, oldest-job age, retries, duplicates, and recovery.
3. Reminder execution: run overlapping scheduler invocations and verify
   `withoutOverlapping` plus notification idempotency.
4. Payment callbacks: blocked until a signed sandbox callback route exists.

## Acceptance thresholds

- Health endpoints: p95 below 250 ms and error rate below 0.1%.
- Authenticated reads: p95 below 500 ms.
- Booking: p95 below 1 second with no duplicate slot acceptance.
- Queue: drain 1,000 database notifications within 10 minutes with two workers.
- No deadlocks left unhandled, no duplicate notifications, no patient data in
  logs, and no persistent failed jobs after recovery.

Capture application version, infrastructure sizes, dataset size, concurrency,
results, slow queries, deadlocks, queue metrics, and bottlenecks. Production
approval requires an executed report; this plan alone is not evidence.
