const http = require('http');
const crypto = require('crypto');

const TARGET_URL = 'http://127.0.0.1:8000/api/v1/sync';
const DURATION_SEC = 10;
const CONCURRENCY = 100;

console.log(`==== STARTING API LOAD TEST (Gate C) ====`);
console.log(`Target: ${TARGET_URL}`);
console.log(`Concurrency: ${CONCURRENCY} workers`);
console.log(`Duration: ${DURATION_SEC} seconds\n`);

let totalRequests = 0;
let successRequests = 0;
let errorRequests = 0;
const latencies = [];

const startTime = Date.now();
const endTime = startTime + DURATION_SEC * 1000;

const agent = new http.Agent({
    keepAlive: true,
    maxSockets: CONCURRENCY
});

function sendRequest(workerId) {
    if (Date.now() >= endTime) {
        return;
    }

    const startReq = Date.now();
    const requestId = crypto.randomUUID();
    const deviceUuid = `device-${workerId}-${Math.floor(Math.random() * 10000)}`;

    const payload = JSON.stringify({
        request_id: requestId,
        device_uuid: deviceUuid,
        cursors: { penugasan: 0 },
        changes: []
    });

    const options = {
        hostname: '127.0.0.1',
        port: 8000,
        path: '/api/v1/sync',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Content-Length': Buffer.byteLength(payload)
        },
        agent: agent
    };

    const req = http.request(options, (res) => {
        let body = '';
        res.on('data', (chunk) => { body += chunk; });
        res.on('end', () => {
            const latency = Date.now() - startReq;
            latencies.push(latency);
            totalRequests++;

            if (res.statusCode === 200 || res.statusCode === 201) {
                successRequests++;
            } else {
                errorRequests++;
            }

            // Immediately schedule next request
            setImmediate(() => sendRequest(workerId));
        });
    });

    req.on('error', (err) => {
        errorRequests++;
        totalRequests++;
        setImmediate(() => sendRequest(workerId));
    });

    req.write(payload);
    req.end();
}

// Start workers
for (let i = 0; i < CONCURRENCY; i++) {
    sendRequest(i);
}

// Print status every second
const progressInterval = setInterval(() => {
    const elapsedSec = (Date.now() - startTime) / 1000;
    const rps = totalRequests / elapsedSec;
    console.log(`Progress: ${elapsedSec.toFixed(1)}s elapsed | Req: ${totalRequests} | RPS: ${rps.toFixed(0)}`);
}, 1000);

setTimeout(() => {
    clearInterval(progressInterval);
    const duration = (Date.now() - startTime) / 1000;
    const rps = totalRequests / duration;
    const errorRate = (errorRequests / totalRequests) * 100;

    // Calculate percentiles
    latencies.sort((a, b) => a - b);
    const p50 = latencies[Math.floor(latencies.length * 0.50)] || 0;
    const p95 = latencies[Math.floor(latencies.length * 0.95)] || 0;
    const p99 = latencies[Math.floor(latencies.length * 0.99)] || 0;

    console.log(`\n==== LOAD TEST RESULTS ====`);
    console.log(`Total Requests  : ${totalRequests}`);
    console.log(`Successful      : ${successRequests}`);
    console.log(`Failed          : ${errorRequests}`);
    console.log(`Throughput      : ${rps.toFixed(0)} req/sec`);
    console.log(`Error Rate      : ${errorRate.toFixed(2)}%`);
    console.log(`P50 Latency     : ${p50} ms`);
    console.log(`P95 Latency     : ${p95} ms`);
    console.log(`P99 Latency     : ${p99} ms\n`);

    if (rps >= 4000 && errorRate < 0.1 && p95 < 500) {
        console.log(`✅ GATE C PASSED: High-throughput target met successfully.`);
    } else {
        console.log(`❌ GATE C FAILED: Target metrics not achieved.`);
    }
}, DURATION_SEC * 1000 + 100);
