</div>
<div class="toast" id="toast">
    <div class="icon icon-toast-success"></div>
    <span id="toastMsg">Thao tác thành công</span>
</div>

<script>
    // Hàm hiển thị Toast thông báo toàn hệ thống
    let toastTimer;

    function showToast(msg) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');
        if (toastMsg) toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
    }
</script>
</body>

</html>