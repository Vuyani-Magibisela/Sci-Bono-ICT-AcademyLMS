<footer id="contact">
    <div class="container">
        <div class="footer-content">
            <div class="footer-info">
                <h3>YDP Training</h3>
                <p>Sci-Bono Youth Development Program providing quality ICT education to unemployed youth.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/courses">Courses</a></li>
                        <li><a href="/benefits">Benefits</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Courses</h4>
                    <ul>
                        <li><a href="/courses/ui-ux-design-systems" class="<?= isset($activeCourse) && $activeCourse === 'ui-ux' ? 'active-link' : '' ?>">UI/UX Design Systems</a></li>
                        <li><a href="#" class="disabled-link">Web Development</a></li>
                        <li><a href="#" class="disabled-link">Programming</a></li>
                        <li><a href="#" class="disabled-link">Internet of Things</a></li>
                        <li><a href="#" class="disabled-link">Linux Essentials</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> Sci-Bono Discovery Centre, Newtown, Johannesburg</li>
                        <li><i class="fas fa-phone"></i> (011) 639-8400</li>
                        <li><i class="fas fa-envelope"></i> info@sci-bono.co.za</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?= date('Y') ?> YDP Training - Sci-Bono Youth Development Program. Created by Vuyani Magibisela.</p>
        </div>
    </div>
</footer>