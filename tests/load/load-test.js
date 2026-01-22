import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const loginDuration = new Trend('login_duration');
const dashboardDuration = new Trend('dashboard_duration');
const documentsListDuration = new Trend('documents_list_duration');

// Test configuration
export const options = {
    stages: [
        // Ramp-up
        { duration: '1m', target: 100 },   // Ramp up to 100 users
        { duration: '2m', target: 100 },   // Stay at 100 users
        { duration: '1m', target: 300 },   // Ramp up to 300 users
        { duration: '3m', target: 300 },   // Stay at 300 users
        { duration: '1m', target: 500 },   // Ramp up to 500 users
        { duration: '3m', target: 500 },   // Stay at 500 users
        // Ramp-down
        { duration: '1m', target: 0 },     // Ramp down to 0 users
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // 95% of requests should be below 2s
        http_req_failed: ['rate<0.01'],    // Error rate should be below 1%
        errors: ['rate<0.01'],
    },
};

// Environment variables
const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';
const USERNAME = __ENV.USERNAME || 'admin';
const PASSWORD = __ENV.PASSWORD || 'password123';

// Helper function to get CSRF token
function getCSRFToken(html) {
    const match = html.match(/name="_token"\s+value="([^"]+)"/);
    return match ? match[1] : '';
}

// Helper function to get cookies
function getCookies(response) {
    const cookies = {};
    const cookieHeader = response.headers['Set-Cookie'];
    if (cookieHeader) {
        const cookieArray = Array.isArray(cookieHeader) ? cookieHeader : [cookieHeader];
        cookieArray.forEach(cookie => {
            const parts = cookie.split(';')[0].split('=');
            if (parts.length === 2) {
                cookies[parts[0]] = parts[1];
            }
        });
    }
    return cookies;
}

export default function () {
    // Group: Login Flow
    group('Login Flow', function () {
        // Step 1: Get login page
        let loginPageRes = http.get(`${BASE_URL}/login`);
        
        check(loginPageRes, {
            'login page status is 200': (r) => r.status === 200,
            'login page has form': (r) => r.body.includes('name="username"'),
        });

        const csrfToken = getCSRFToken(loginPageRes.body);

        // Step 2: Submit login
        const startLogin = Date.now();
        let loginRes = http.post(`${BASE_URL}/login`, {
            _token: csrfToken,
            username: USERNAME,
            password: PASSWORD,
        }, {
            redirects: 0,
        });
        loginDuration.add(Date.now() - startLogin);

        const loginSuccess = check(loginRes, {
            'login redirects': (r) => r.status === 302,
        });

        if (!loginSuccess) {
            errorRate.add(1);
            return;
        }
        errorRate.add(0);

        sleep(1);
    });

    // Group: Dashboard
    group('Dashboard', function () {
        const startDashboard = Date.now();
        let dashboardRes = http.get(`${BASE_URL}/dashboard`);
        dashboardDuration.add(Date.now() - startDashboard);

        check(dashboardRes, {
            'dashboard status is 200': (r) => r.status === 200,
            'dashboard has statistics': (r) => r.body.includes('Total Dokumen') || r.body.includes('dashboard'),
        });

        sleep(2);
    });

    // Group: Documents List
    group('Documents List', function () {
        const startDocuments = Date.now();
        let documentsRes = http.get(`${BASE_URL}/documents`);
        documentsListDuration.add(Date.now() - startDocuments);

        check(documentsRes, {
            'documents status is 200': (r) => r.status === 200,
            'documents has table': (r) => r.body.includes('table') || r.body.includes('document'),
        });

        sleep(1);
    });

    // Group: Search Documents
    group('Search Documents', function () {
        let searchRes = http.get(`${BASE_URL}/documents?search=perjanjian`);

        check(searchRes, {
            'search status is 200': (r) => r.status === 200,
        });

        sleep(1);
    });

    // Group: Filter Documents
    group('Filter Documents', function () {
        let filterRes = http.get(`${BASE_URL}/documents?status=active&type_id=1`);

        check(filterRes, {
            'filter status is 200': (r) => r.status === 200,
        });

        sleep(1);
    });

    // Group: View Document Detail
    group('View Document Detail', function () {
        // Try to view document with ID 1 (may not exist)
        let detailRes = http.get(`${BASE_URL}/documents/1`);

        check(detailRes, {
            'detail status is 200 or 404': (r) => r.status === 200 || r.status === 404,
        });

        sleep(1);
    });

    // Group: Notifications
    group('Notifications', function () {
        let notifRes = http.get(`${BASE_URL}/notifications`);

        check(notifRes, {
            'notifications status is 200': (r) => r.status === 200,
        });

        sleep(1);
    });

    // Group: Profile
    group('Profile', function () {
        let profileRes = http.get(`${BASE_URL}/profile`);

        check(profileRes, {
            'profile status is 200': (r) => r.status === 200,
        });

        sleep(1);
    });

    // Group: Logout
    group('Logout', function () {
        // Get fresh CSRF token
        let pageRes = http.get(`${BASE_URL}/dashboard`);
        const csrfToken = getCSRFToken(pageRes.body);

        let logoutRes = http.post(`${BASE_URL}/logout`, {
            _token: csrfToken,
        }, {
            redirects: 0,
        });

        check(logoutRes, {
            'logout redirects': (r) => r.status === 302,
        });
    });

    // Random think time between iterations
    sleep(Math.random() * 3 + 1);
}

// Setup function - runs once before all VUs
export function setup() {
    console.log(`Starting load test against ${BASE_URL}`);
    console.log('Testing with user:', USERNAME);
    
    // Verify the application is accessible
    let res = http.get(`${BASE_URL}/login`);
    if (res.status !== 200) {
        throw new Error(`Application not accessible. Status: ${res.status}`);
    }
    
    return { startTime: Date.now() };
}

// Teardown function - runs once after all VUs complete
export function teardown(data) {
    const duration = (Date.now() - data.startTime) / 1000;
    console.log(`Load test completed in ${duration.toFixed(2)} seconds`);
}
