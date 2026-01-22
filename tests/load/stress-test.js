import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Counter } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const successfulLogins = new Counter('successful_logins');
const failedLogins = new Counter('failed_logins');

// Stress test configuration - targeting 3000+ concurrent users
export const options = {
    stages: [
        // Warm-up phase
        { duration: '2m', target: 500 },    // Ramp up to 500 users
        { duration: '3m', target: 500 },    // Stay at 500 users
        
        // Stress phase 1
        { duration: '2m', target: 1000 },   // Ramp up to 1000 users
        { duration: '3m', target: 1000 },   // Stay at 1000 users
        
        // Stress phase 2
        { duration: '2m', target: 2000 },   // Ramp up to 2000 users
        { duration: '3m', target: 2000 },   // Stay at 2000 users
        
        // Maximum stress - 3000+ users
        { duration: '3m', target: 3000 },   // Ramp up to 3000 users
        { duration: '5m', target: 3000 },   // Stay at 3000 users - CRITICAL TEST
        
        // Peak stress - 3500 users
        { duration: '2m', target: 3500 },   // Push beyond target
        { duration: '3m', target: 3500 },   // Hold at peak
        
        // Recovery phase
        { duration: '3m', target: 1000 },   // Ramp down to 1000
        { duration: '2m', target: 0 },      // Ramp down to 0
    ],
    thresholds: {
        http_req_duration: ['p(95)<3000'],  // 95% of requests should be below 3s under stress
        http_req_failed: ['rate<0.05'],     // Error rate should be below 5% under stress
        errors: ['rate<0.05'],
    },
    // Resource limits for stress testing
    noConnectionReuse: false,
    userAgent: 'k6-stress-test/1.0',
};

// Environment variables
const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

// Multiple test users for realistic simulation
const USERS = [
    { username: 'superadmin', password: 'password123' },
    { username: 'admin', password: 'password123' },
    { username: 'kabag.hukum', password: 'password123' },
    { username: 'staf.hukum1', password: 'password123' },
    { username: 'staf.hukum2', password: 'password123' },
    { username: 'kabag.sdm', password: 'password123' },
    { username: 'staf.sdm', password: 'password123' },
    { username: 'viewer', password: 'password123' },
];

// Helper function to get CSRF token
function getCSRFToken(html) {
    const match = html.match(/name="_token"\s+value="([^"]+)"/);
    return match ? match[1] : '';
}

// Get random user
function getRandomUser() {
    return USERS[Math.floor(Math.random() * USERS.length)];
}

// Simulate user session
export default function () {
    const user = getRandomUser();
    
    // Scenario: Complete user session
    group('User Session', function () {
        
        // 1. Login
        group('Authentication', function () {
            let loginPageRes = http.get(`${BASE_URL}/login`, {
                tags: { name: 'LoginPage' },
            });
            
            if (loginPageRes.status !== 200) {
                errorRate.add(1);
                return;
            }

            const csrfToken = getCSRFToken(loginPageRes.body);

            let loginRes = http.post(`${BASE_URL}/login`, {
                _token: csrfToken,
                username: user.username,
                password: user.password,
            }, {
                redirects: 0,
                tags: { name: 'LoginSubmit' },
            });

            const success = check(loginRes, {
                'login successful': (r) => r.status === 302,
            });

            if (success) {
                successfulLogins.add(1);
                errorRate.add(0);
            } else {
                failedLogins.add(1);
                errorRate.add(1);
            }

            sleep(0.5);
        });

        // 2. Dashboard
        group('Dashboard', function () {
            let res = http.get(`${BASE_URL}/dashboard`, {
                tags: { name: 'Dashboard' },
            });
            
            check(res, {
                'dashboard loaded': (r) => r.status === 200,
            });

            sleep(1);
        });

        // 3. Browse Documents (multiple pages)
        group('Document Browsing', function () {
            // Page 1
            let res1 = http.get(`${BASE_URL}/documents`, {
                tags: { name: 'DocumentsList' },
            });
            check(res1, { 'documents page 1': (r) => r.status === 200 });
            sleep(0.5);

            // Page 2
            let res2 = http.get(`${BASE_URL}/documents?page=2`, {
                tags: { name: 'DocumentsPage2' },
            });
            check(res2, { 'documents page 2': (r) => r.status === 200 || r.status === 404 });
            sleep(0.5);

            // Search
            let searchRes = http.get(`${BASE_URL}/documents?search=kontrak`, {
                tags: { name: 'DocumentsSearch' },
            });
            check(searchRes, { 'search works': (r) => r.status === 200 });
            sleep(0.5);

            // Filter by type
            let filterRes = http.get(`${BASE_URL}/documents?type_id=1&status=active`, {
                tags: { name: 'DocumentsFilter' },
            });
            check(filterRes, { 'filter works': (r) => r.status === 200 });
            sleep(0.5);
        });

        // 4. View Document Details
        group('Document Details', function () {
            // Try multiple document IDs
            for (let i = 1; i <= 3; i++) {
                let res = http.get(`${BASE_URL}/documents/${i}`, {
                    tags: { name: 'DocumentDetail' },
                });
                check(res, { 
                    'document detail accessible': (r) => r.status === 200 || r.status === 404 || r.status === 403,
                });
                sleep(0.3);
            }
        });

        // 5. Notifications
        group('Notifications', function () {
            let res = http.get(`${BASE_URL}/notifications`, {
                tags: { name: 'Notifications' },
            });
            check(res, { 'notifications loaded': (r) => r.status === 200 });
            
            // AJAX notification count
            let countRes = http.get(`${BASE_URL}/notifications/count`, {
                tags: { name: 'NotificationCount' },
            });
            check(countRes, { 'notification count': (r) => r.status === 200 });

            sleep(0.5);
        });

        // 6. Profile
        group('Profile', function () {
            let res = http.get(`${BASE_URL}/profile`, {
                tags: { name: 'Profile' },
            });
            check(res, { 'profile loaded': (r) => r.status === 200 });
            sleep(0.5);
        });

        // 7. Admin actions (only for admin users)
        if (user.username === 'superadmin' || user.username === 'admin') {
            group('Admin Actions', function () {
                let usersRes = http.get(`${BASE_URL}/admin/users`, {
                    tags: { name: 'AdminUsers' },
                });
                check(usersRes, { 'admin users': (r) => r.status === 200 });
                sleep(0.3);

                let rolesRes = http.get(`${BASE_URL}/admin/roles`, {
                    tags: { name: 'AdminRoles' },
                });
                check(rolesRes, { 'admin roles': (r) => r.status === 200 });
                sleep(0.3);

                let auditRes = http.get(`${BASE_URL}/admin/audit-logs`, {
                    tags: { name: 'AuditLogs' },
                });
                check(auditRes, { 'audit logs': (r) => r.status === 200 });
                sleep(0.3);
            });
        }

        // 8. Logout
        group('Logout', function () {
            let pageRes = http.get(`${BASE_URL}/dashboard`);
            const csrfToken = getCSRFToken(pageRes.body);

            let logoutRes = http.post(`${BASE_URL}/logout`, {
                _token: csrfToken,
            }, {
                redirects: 0,
                tags: { name: 'Logout' },
            });

            check(logoutRes, { 'logout successful': (r) => r.status === 302 });
        });
    });

    // Random think time - simulates real user behavior
    sleep(Math.random() * 2 + 0.5);
}

export function setup() {
    console.log('='.repeat(60));
    console.log('STRESS TEST - 3000+ Concurrent Users');
    console.log('='.repeat(60));
    console.log(`Target URL: ${BASE_URL}`);
    console.log(`Test Users: ${USERS.length}`);
    console.log('');
    
    // Verify application is accessible
    let res = http.get(`${BASE_URL}/login`);
    if (res.status !== 200) {
        throw new Error(`Application not accessible! Status: ${res.status}`);
    }
    
    console.log('✓ Application is accessible');
    console.log('Starting stress test...');
    console.log('');
    
    return { startTime: Date.now() };
}

export function teardown(data) {
    const duration = (Date.now() - data.startTime) / 1000 / 60;
    console.log('');
    console.log('='.repeat(60));
    console.log('STRESS TEST COMPLETED');
    console.log('='.repeat(60));
    console.log(`Total Duration: ${duration.toFixed(2)} minutes`);
    console.log('');
    console.log('Review the metrics above to determine if the application');
    console.log('can handle 3000+ concurrent users.');
    console.log('');
    console.log('Key metrics to check:');
    console.log('  - http_req_duration (p95) should be < 3000ms');
    console.log('  - http_req_failed should be < 5%');
    console.log('  - errors rate should be < 5%');
}
