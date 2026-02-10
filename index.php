<?php
require_once __DIR__ . '/config/database.php';
include __DIR__ . '/includes/header.php';
?>

<div class="hero-section">
    <h1 class="display-4 fw-bold text-uppercase mb-4">Book Parking Space!</h1>
    
    <div class="booking-form text-dark">
        <form action="search_results.php" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="fas fa-map-marker-alt text-danger"></i> Location</label>
                    <select class="form-select" name="location" required>
                        <option value="">Select Location</option>
                        <?php
                        // Fetch locations
                        try {
                            $locs = $pdo->query("SELECT * FROM locations");
                            while($row = $locs->fetch()) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                        } catch(Exception $e) { /* silent fail or demo */ }
                        ?>
                    </select>
                </div> <!-- Location -->
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Entry Date</label>
                    <input type="date" class="form-control" name="entry_date" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Time</label>
                    <input type="time" class="form-control" name="entry_time" required>
                </div>
                
                <!-- Exit Date removed -->
                <!-- Submit -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-brand w-100 py-2">Choose Space</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container my-5 text-center">
    <h2 class="fw-bold mb-3">DON'T LET PARKING BE A HASSLE.</h2>
    <h3 class="fw-bold mb-4">RESERVE YOUR SPOT TODAY!</h3>
    <p class="text-muted w-75 mx-auto">
        We offer a variety of parking options to meet your needs, including short-term, daily, weekly, and monthly parking. 
        Whether you're traveling for business or leisure, you can count on us to provide you with safe, secure, and convenient parking.
    </p>
</div>

<!-- Map Placeholder -->
<div class="container-fluid px-0 mb-5">
    <!-- Using a Google Maps Embed for Johor Bahru -->
    <div style="width: 100%; height: 400px; overflow: hidden;">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127633.39956693524!2d103.6663273398322!3d1.495379854446416!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da1ee5f33e7b17%3A0xe54cfa2584e0307f!2sJohor%20Bahru%2C%20Johor%2C%20Malaysia!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s" 
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</div>

<!-- Offers Section -->
<div class="container mb-5">
    <h2 class="text-center fw-bold mb-2">OFFERS</h2>
    <p class="text-center text-muted mb-5">See our special offers and book a parking space at affordable price!</p>
    
    <div class="row g-4">
        <!-- City Square -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="bg-primary text-white p-3 d-inline-block position-absolute" style="width: 80px; text-align: center;">
                    <strong>RM 5<br>per hr</strong>
                </div>
                <img src="assets/images/city_square.jpg" class="card-img-top" alt="Offer" style="height: 200px; object-fit: cover;">
                <div class="card-body mt-2">
                    <h5 class="card-title fw-bold" style="font-size: 1rem;">JB CITY SQUARE</h5>
                </div>
            </div>
        </div>
        <!-- Mid Valley -->
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                 <div class="bg-danger text-white p-3 d-inline-block position-absolute" style="width: 80px; text-align: center;">
                    <strong>RM 6<br>per hr</strong>
                </div>
                <img src="assets/images/mid_valley.jpg" class="card-img-top" alt="Offer" style="height: 200px; object-fit: cover;">
                <div class="card-body mt-2">
                    <h5 class="card-title fw-bold" style="font-size: 1rem;">MID VALLEY SOUTHKEY</h5>
                </div>
            </div>
        </div>
        <!-- Paradigm -->
        <div class="col-md-3">
             <div class="card h-100 border-0 shadow-sm">
                 <div class="bg-danger text-white p-3 d-inline-block position-absolute" style="width: 80px; text-align: center;">
                    <strong>RM 3<br>per hr</strong>
                </div>
                <img src="assets/images/paradigm_mall.jpg" class="card-img-top" alt="Offer" style="height: 200px; object-fit: cover;">
                <div class="card-body mt-2">
                    <h5 class="card-title fw-bold" style="font-size: 1rem;">PARADIGM MALL JB</h5>
                </div>
            </div>
        </div>
        <!-- KSL City -->
        <div class="col-md-3">
             <div class="card h-100 border-0 shadow-sm">
                 <div class="bg-warning text-dark p-3 d-inline-block position-absolute" style="width: 80px; text-align: center;">
                    <strong>RM 2<br>per hr</strong>
                </div>
                <img src="assets/images/ksl_city.jpg" class="card-img-top" alt="Offer" style="height: 200px; object-fit: cover;">
                <div class="card-body mt-2">
                    <h5 class="card-title fw-bold" style="font-size: 1rem;">KSL CITY MALL</h5>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-5">
        <a href="#" class="btn btn-danger btn-lg px-5">Book Now</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>