<!DOCTYPE html>
<html>
<head>
    <title>AJAX Endpoint Test</title>
</head>
<body>
    <h1>Testing AJAX Endpoint</h1>
    <button id="test-btn">Test Geekbench AJAX</button>
    <button id="test-simple-btn">Test Simple AJAX</button>
    <pre id="output"></pre>

    <script>
        // Test Simple AJAX
        document.getElementById('test-simple-btn').addEventListener('click', function() {
            const output = document.getElementById('output');
            output.textContent = 'Testing Simple AJAX...\n';

            const data = new FormData();
            data.append('action', 'test_ajax_simple');

            fetch('https://macnerdxyz-05-25.local/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                output.textContent += `Status: ${response.status} ${response.statusText}\n`;
                output.textContent += `Content-Type: ${response.headers.get('content-type')}\n\n`;

                return response.text();
            })
            .then(text => {
                output.textContent += 'Response Body:\n';
                output.textContent += text;
                output.textContent += '\n\nResponse Length: ' + text.length + ' bytes';
            })
            .catch(error => {
                output.textContent += `Error: ${error.message}\n`;
            });
        });

        // Test Geekbench AJAX
        document.getElementById('test-btn').addEventListener('click', function() {
            const output = document.getElementById('output');
            output.textContent = 'Testing Geekbench AJAX...\n';

            const data = new FormData();
            data.append('action', 'geekbench_scraper_fetch');
            data.append('query', 'iphone18');
            data.append('limit', '10');

            fetch('https://macnerdxyz-05-25.local/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                output.textContent += `Status: ${response.status} ${response.statusText}\n`;
                output.textContent += `Content-Type: ${response.headers.get('content-type')}\n`;
                output.textContent += `Server: ${response.headers.get('server')}\n`;
                output.textContent += `X-Powered-By: ${response.headers.get('x-powered-by')}\n\n`;

                output.textContent += 'All Headers:\n';
                for (let [key, value] of response.headers.entries()) {
                    output.textContent += `  ${key}: ${value}\n`;
                }
                output.textContent += '\n';

                return response.text();
            })
            .then(text => {
                output.textContent += 'Response Body:\n';
                output.textContent += text;
                output.textContent += '\n\nResponse Length: ' + text.length + ' bytes';
            })
            .catch(error => {
                output.textContent += `Error: ${error.message}\n`;
            });
        });
    </script>
</body>
</html>

