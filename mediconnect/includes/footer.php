<footer>
    <div class="footer-grid">
        <div>
            <h4><i class="fas fa-heartbeat"></i> MediConnect</h4>
            <p style="font-size:0.9rem; color:rgba(255,255,255,0.6); margin-bottom:1rem;">Smart Medical Access System – connecting patients with quality healthcare providers across Bangladesh.</p>
            <div style="display:flex; gap:0.8rem;">
                <a href="#" style="color:rgba(255,255,255,0.5); font-size:1.2rem;"><i class="fab fa-facebook"></i></a>
                <a href="#" style="color:rgba(255,255,255,0.5); font-size:1.2rem;"><i class="fab fa-twitter"></i></a>
                <a href="#" style="color:rgba(255,255,255,0.5); font-size:1.2rem;"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
        <div>
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= SITE_URL ?>">Home</a></li>
                <li><a href="<?= SITE_URL ?>/doctors.php">Find Doctors</a></li>
                <li><a href="<?= SITE_URL ?>/hospitals.php">Hospitals</a></li>
                <li><a href="<?= SITE_URL ?>/pharmacies.php">Pharmacies</a></li>
            </ul>
        </div>
        <div>
            <h4>Services</h4>
            <ul>
                <li><a href="<?= SITE_URL ?>/medical-tests.php">Test Fees</a></li>
                <li><a href="<?= SITE_URL ?>/register.php">Patient Portal</a></li>
                <li><a href="<?= SITE_URL ?>/doctor-login.php">Doctor Portal</a></li>
                <li><a href="<?= SITE_URL ?>/admin/login.php">Admin Portal</a></li>
            </ul>
        </div>
        <div>
            <h4>Contact</h4>
            <ul>
                <li><a href="#"><i class="fas fa-envelope"></i> info@mediconnect.com</a></li>
                <li><a href="#"><i class="fas fa-phone"></i> +880-1700-000000</a></li>
                <li><a href="#"><i class="fas fa-map-marker-alt"></i> Dhaka, Bangladesh</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> MediConnect – Smart Medical Access System. Developed by Group 4, CSE 356 – Spring 2026.</p>
    </div>
</footer>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
