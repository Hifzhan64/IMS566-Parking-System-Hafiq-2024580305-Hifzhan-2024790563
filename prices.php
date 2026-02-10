<?php
require_once __DIR__ . '/config/database.php';
$page_title = "Parking Rates";
include __DIR__ . '/includes/header.php';

// Fetch rates by location. Grouping by location to get distinct rates.
// We select the location ID, name, and the typical price (MAX used effectively as distinct here since prices are uniform per loc)
$stmt = $pdo->query("
    SELECT l.id, l.name, MAX(p.price_per_hour) as price
    FROM locations l
    JOIN parking_slots p ON l.id = p.location_id
    GROUP BY l.id, l.name
    ORDER BY p.price_per_hour DESC
");
$rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to get static features for the demo look
function getFeatures($name) {
    if (stripos($name, 'City Square') !== false) {
        return ['Prime Location', 'Covered Parking', 'CCTV Surveillance', 'Access to CIQ'];
    } elseif (stripos($name, 'Mid Valley') !== false) {
        return ['Premium Mall', 'Wide Parking Bays', '24/7 Security Patrol', 'Valet Services'];
    } elseif (stripos($name, 'Paradigm') !== false) {
        return ['Large Capacity', 'Cinema & Skating', 'Touch \'n Go', 'Family Zones'];
    } else { // KSL
        return ['Budget Friendly', 'Open & Covered', 'Hotel Connected', 'Easy Access'];
    }
}
?>

<!-- Glassmorphism Styles -->
<style>
    /* Force Dark Theme for this page structure */
    body {
        background: radial-gradient(circle at top left, #1e293b, #0f172a);
        color: #fff;
        min-height: 100vh;
    }
    
    /* Override Navbar for this page if it's transparent/light by default */
    /* Assuming header is included above, we might need adjustments. 
       If header has bg-white, it might look stark. Ideally we'd make header transparent but let's stick to page content. */

    .pricing-header {
        text-align: center;
        margin-bottom: 4rem;
        padding-top: 3rem;
    }
    .pricing-header h2 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        background: linear-gradient(to right, #ffffff, #94a3b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: 'Inter', sans-serif;
    }
    .pricing-header p {
        color: #94a3b8;
        font-size: 1.2rem;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 2.5rem;
        height: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .glass-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.2);
    }
    
    /* Highlight effect for 'Best Value' or similar if we wanted specific styles */
    
    .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #fff;
    }
    
    .card-subtitle {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 2rem;
        display: block;
    }

    .price-display {
        display: flex;
        align-items: baseline;
        margin-bottom: 2rem;
    }
    .currency {
        font-size: 1.5rem;
        font-weight: 600;
        margin-right: 5px;
        color: #fff;
    }
    .amount {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1;
        color: #fff;
    }
    .duration {
        color: #64748b;
        font-size: 1rem;
        margin-left: 10px;
    }

    .features-label {
        color: #fff;
        font-weight: 600;
        margin-bottom: 1rem;
        display: block;
    }

    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0 0 2.5rem 0;
    }
    .feature-list li {
        margin-bottom: 1rem;
        color: #cbd5e1;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
    }
    .feature-list li i {
        color: #38bdf8; /* Sky blue checkmark */
        margin-right: 12px;
    }

    .btn-action {
        width: 100%;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s;
        text-align: center;
        text-decoration: none;
        display: block;
    }
    .btn-action:hover {
        background: #fff;
        color: #0f172a;
    }

    /* Footer Fixes for Dark Mode */
    footer {
        background: transparent !important;
        border-top: 1px solid rgba(255,255,255,0.05);
        color: #94a3b8 !important; 
        margin-top: 0 !important;
    }
    footer p { color: #94a3b8 !important; }
</style>

<div class="container pb-5">
    <div class="pricing-header">
        <h2>Choose Your Parking Spot</h2>
        <p>Transparent hourly rates for the best locations in Johor Bahru</p>
    </div>

    <div class="row g-4 justify-content-center">
        <?php foreach($rates as $rate): 
            $features = getFeatures($rate['name']);
            $delay = 0; // Animation delay could be added
        ?>
        <div class="col-lg-3 col-md-6">
            <div class="glass-card">
                <h3 class="card-title"><?php echo htmlspecialchars($rate['name']); ?></h3>
                <span class="card-subtitle">For shoppers & visitors</span>
                
                <div class="price-display">
                    <span class="currency">RM</span>
                    <span class="amount"><?php echo number_format($rate['price'], 0); ?></span>
                    <span class="duration">/ Hour</span>
                </div>
                
                <span class="features-label">What You Get</span>
                <ul class="feature-list">
                    <?php foreach($features as $f): ?>
                    <li><i class="fas fa-check"></i> <?php echo $f; ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Link acts as form submission prep or just redirect -->
                <a href="index.php?location_id=<?php echo $rate['id']; ?>" class="btn-action">
                    Book Now
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
