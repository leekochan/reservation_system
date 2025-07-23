<!DOCTYPE html>
<html>
<head>
    <title>Automated Form Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Automated Form Submission Test</h1>
    
    <button onclick="runTest()">Run Automated Test</button>
    <div id="status"></div>
    
    <script>
        async function runTest() {
            const status = document.getElementById('status');
            status.innerHTML = 'Starting test...';
            
            try {
                // Create form data
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('name', 'Test User');
                formData.append('email', 'test@example.com');
                formData.append('organization', 'Test Organization');
                formData.append('purpose', 'Testing the form');
                formData.append('other_details', 'Test details');
                formData.append('personal_equipment', 'no');
                formData.append('reservation_type', 'single');
                formData.append('facility_id', '1');
                formData.append('signature', 'data:image/png;base64,test');
                formData.append('need_equipment', 'no');
                formData.append('dates[0][date]', '2025-07-25');
                formData.append('dates[0][time_from]', '09:00');
                formData.append('dates[0][time_to]', '17:00');
                
                status.innerHTML = 'Submitting form data...';
                
                // Submit to reservation controller
                const response = await fetch('/reservation', {
                    method: 'POST',
                    body: formData
                });
                
                status.innerHTML = `Response status: ${response.status}`;
                const responseText = await response.text();
                status.innerHTML += `<br>Response: ${responseText.substring(0, 500)}...`;
                
            } catch (error) {
                status.innerHTML = `Error: ${error.message}`;
            }
        }
    </script>
</body>
</html>
