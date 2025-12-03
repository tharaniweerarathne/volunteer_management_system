<?php

session_start();

// checking if user is logged in as Coordinator
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Coordinator') {
    header('Location: sign_in.php');
    exit;
}

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/ProfileLogic.php";

$profileLogic = new ProfileLogic($conn);
$userId = $_SESSION['userId'];

// fetch current profile data
$profileResult = $profileLogic->getCoordinatorProfile($userId);
$profile = $profileResult['success'] ? $profileResult['data'] : null;

if (!$profile) {
    die("Error loading profile");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Coordinator</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/edit_pro1.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">

</head>
<body>
    <div class="container">
        <a href="about.html" class="back-link" onclick="history.back(); return false;">
            <i class="ri-arrow-left-line"></i> Back to Dashboard
        </a>

        <div class="profile-card">
            <div class="card-header">
                <h2>Edit Profile</h2>
                <p>Update your personal information</p>
            </div>
            <div class="card-body">
                <div class="info-note">
                    <i class="ri-information-line"></i>
                    <strong>Note:</strong> Coordinators cannot change their password. Please contact the administrator if you need to reset your password.
                </div>

                <div id="profileMessage" class="message"></div>
                
                <form id="profileForm">
                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Full Name
                        </label>
                        <div class="input-group">
                            <i class="ri-user-line input-icon"></i>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Email Address
                        </label>
                        <div class="input-group">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Telephone Number
                        </label>
                        <div class="input-group">
                            <i class="ri-phone-line input-icon"></i>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['telephoneNo']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Gender
                        </label>
                        <select name="gender" class="form-select" required>
                            <option value="Male" <?php echo $profile['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $profile['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $profile['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            <option value="Prefer not to say" <?php echo $profile['gender'] === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Location
                        </label>
                        <div class="input-group">
                            <i class="ri-map-pin-line input-icon"></i>
                            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($profile['location']); ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                action: 'update_profile',
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                gender: formData.get('gender'),
                location: formData.get('location')
            };
            
            const btn = this.querySelector('.btn-primary');
            const originalText = btn.textContent;
            btn.textContent = 'Updating...';
            btn.disabled = true;
            
            try {
                const response = await fetch('handle_edit_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('profileMessage', result.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('profileMessage', result.message, 'error');
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('profileMessage', 'An error occurred. Please try again.', 'error');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });

        function showMessage(elementId, message, type) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = message;
                element.className = `message ${type}`;
                element.style.display = 'block';
                setTimeout(() => {
                    element.style.display = 'none';
                }, 5000);
            }
        }
    </script>
</body>
</html>