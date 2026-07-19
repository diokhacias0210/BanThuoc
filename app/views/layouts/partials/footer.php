<?php if (empty($is_auth)): ?>
<footer class="bg-dark text-white mt-5">

    <div class="container py-4">

        <div class="row">

            <div class="col-md-4">

                <h5>PharmaStore</h5>

                <p>
                    Hệ thống bán thuốc trực tuyến.
                </p>

            </div>

            <div class="col-md-4">

                <h5>Liên hệ</h5>

                <p>Email: support@pharmastore.vn</p>

                <p>Hotline: 1900 1234</p>

            </div>

            <div class="col-md-4">

                <h5>Theo dõi</h5>

                <i class="bi bi-facebook fs-4"></i>

                <i class="bi bi-youtube fs-4 ms-2"></i>

                <i class="bi bi-instagram fs-4 ms-2"></i>

            </div>

        </div>

        <hr>

        <div class="text-center">

            © <?= date("Y") ?>

            PharmaStore

        </div>

    </div>

</footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>