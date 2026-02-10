<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('my-reservations.php');
}

$reservation_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get reservation details
$stmt = $pdo->prepare("
    SELECT r.*, u.name, u.email, u.phone, u.vehicle_number as user_vehicle, 
           p.slot_number, p.zone, p.slot_type, p.hourly_rate
    FROM reservations r 
    JOIN users u ON r.user_id = u.id 
    JOIN parking_slots p ON r.slot_id = p.id 
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$reservation_id, $user_id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    die("Reservation not found or access denied.");
}

// Calculate hours
$start_time = strtotime($reservation['start_time']);
$end_time = strtotime($reservation['end_time']);
$hours = ceil(($end_time - $start_time) / 3600);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $reservation_id; ?></title>
    
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
                font-size: 12px;
            }
            .container {
                max-width: 100% !important;
            }
        }
        
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .receipt-title {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .details-table {
            width: 100%;
            margin: 20px 0;
        }
        
        .details-table td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .details-table td:first-child {
            font-weight: bold;
            width: 40%;
        }
        
        .total-box {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 5px;
            margin: 30px 0;
            border-left: 4px solid #007bff;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        
        .status-badge {
            font-size: 14px;
            padding: 5px 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt-container">
            <!-- Header -->
            <div class="header">
                <div class="logo">🏢 SMART PARKING SYSTEM</div>
                <div class="receipt-title">PARKING RECEIPT</div>
                <div>Official Receipt for Parking Reservation</div>
            </div>
            
            <!-- Receipt Details -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="details-table">
                        <tr>
                            <td>Receipt Number:</td>
                            <td>PARK-<?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                        <tr>
                            <td>Issue Date:</td>
                            <td><?php echo date('F d, Y'); ?></td>
                        </tr>
                        <tr>
                            <td>Customer Name:</td>
                            <td><?php echo htmlspecialchars($reservation['name']); ?></td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td><?php echo htmlspecialchars($reservation['email']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="details-table">
                        <tr>
                            <td>Phone:</td>
                            <td><?php echo htmlspecialchars($reservation['phone'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Vehicle Number:</td>
                            <td><?php echo htmlspecialchars($reservation['vehicle_number']); ?></td>
                        </tr>
                        <tr>
                            <td>Reservation Status:</td>
                            <td>
                                <?php 
                                $status_color = [
                                    'active' => 'success',
                                    'completed' => 'secondary',
                                    'cancelled' => 'danger'
                                ][$reservation['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $status_color; ?> status-badge">
                                    <?php echo strtoupper($reservation['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Parking Details -->
            <h5 class="mb-3">Parking Details</h5>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Slot Number</th>
                        <th>Zone</th>
                        <th>Type</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Hours</th>
                        <th>Rate/Hour</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['slot_number']); ?></td>
                        <td><?php echo $reservation['zone']; ?></td>
                        <td>
                            <?php 
                            $type_badge = [
                                'regular' => 'primary',
                                'premium' => 'warning',
                                'ev' => 'success',
                                'disabled' => 'info'
                            ][$reservation['slot_type']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $type_badge; ?>">
                                <?php echo strtoupper($reservation['slot_type']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($reservation['start_time'])); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($reservation['end_time'])); ?></td>
                        <td><?php echo $hours; ?> hour(s)</td>
                        <td>RM <?php echo number_format($reservation['hourly_rate'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Payment Summary -->
            <div class="total-box">
                <h5 class="mb-3">Payment Summary</h5>
                <div class="row">
                    <div class="col-md-8">
                        <table class="details-table">
                            <tr>
                                <td>Parking Fee (<?php echo $hours; ?> hours @ RM <?php echo $reservation['hourly_rate']; ?>):</td>
                                <td class="text-end">RM <?php echo number_format($hours * $reservation['hourly_rate'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Tax (0%):</td>
                                <td class="text-end">RM 0.00</td>
                            </tr>
                            <tr style="font-size: 18px; font-weight: bold;">
                                <td>TOTAL AMOUNT:</td>
                                <td class="text-end">RM <?php echo number_format($reservation['total_cost'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-2">Payment Method</div>
                            <div class="badge bg-success p-2" style="font-size: 14px;">ONLINE PAYMENT</div>
                            <div class="mt-2">
                                <small>Paid on: <?php echo date('M d, Y', strtotime($reservation['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Terms and Conditions -->
            <div class="mt-4">
                <h6>Terms & Conditions:</h6>
                <ol style="font-size: 12px; padding-left: 20px;">
                    <li>This receipt must be presented when entering/exiting the parking area.</li>
                    <li>Parking duration must not exceed the booked time.</li>
                    <li>Overtime parking will be charged at RM <?php echo number_format($reservation['hourly_rate'] * 1.5, 2); ?>/hour.</li>
                    <li>Refunds are only available for cancellations made at least 1 hour before start time.</li>
                    <li>The management is not responsible for any loss or damage to vehicles.</li>
                </ol>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p><strong>SMART PARKING SYSTEM</strong><br>
                Address: 123 Parking Street, Kuala Lumpur, Malaysia<br>
                Phone: 03-1234 5678 | Email: support@smartparking.com<br>
                Website: www.smartparking.com</p>
                <p>Thank you for using our service!</p>
            </div>
            
            <!-- Action Buttons (Hidden when printing) -->
            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
                <a href="my-reservations.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reservations
                </a>
                <button onclick="downloadPDF()" class="btn btn-success">
                    <i class="bi bi-download"></i> Save as PDF
                </button>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.querySelector('.receipt-container');
            const opt = {
                margin:       10,
                filename:     'receipt_<?php echo $reservation_id; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>