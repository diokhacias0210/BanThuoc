</div>
<div class="toast" id="toast">
    <div class="icon icon-check"></div>
    <span id="toastMsg">Thao tác thành công</span>
</div>

<script>
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