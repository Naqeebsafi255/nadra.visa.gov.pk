<script>
function handleLogin(event) {
    event.preventDefault();
    let user = document.getElementById('username').value;
    let pass = document.getElementById('password').value;

    if (user === "admin" && pass === "admin123") {
        // دلته د .php فایل نوم ورکړئ
        window.location.href = "admin_dashboard.html"; 
    } else {
        document.getElementById('error-msg').innerText = "❌ غلط Username یا Password!";
    }
}
</script>
