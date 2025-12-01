<?php
// edit_profile.php --> presentation folder
session_start();

// Check if user is logged in
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Volunteer') {
    header('Location: sign_in.php');
    exit;
}

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/ProfileLogic.php";

$profileLogic = new ProfileLogic($conn);
$userId = $_SESSION['userId'];

// Fetch current profile data
$profileResult = $profileLogic->getVolunteerProfile($userId);
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
    <title>Edit Profile</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/edit_pro1.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">

</head>
<body>
    <div class="container">
        <a href="volunteer_dashboard.php" class="back-link">
            <i class="ri-arrow-left-line"></i> Back to Dashboard
        </a>

        <!-- Profile Edit Form -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Edit Profile</h2>
                <p>Update your personal information</p>
            </div>
            <div class="card-body">
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

                    <div class="form-group">
                        <label class="form-label">Skills (Optional)</label>
                        <div class="skills-section">
                            <p style="margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                                Select or update your skills
                            </p>
                            <div class="skills-grid">
                                <?php
                                $availableSkills = [
                                    'teaching' => 'Teaching',
                                    'event-organizing' => 'Event Organizing',
                                    'first-aid' => 'First Aid',
                                    'photography' => 'Photography',
                                    'cooking' => 'Cooking',
                                    'fundraising' => 'Environmental Work',
                                    'social-media' => 'Social Media',
                                    'graphic-design' => 'Graphic Design',
                                    'writing' => 'Elderly care',
                                    'translation' => 'Translation',
                                    'it-support' => 'IT and technical support',
                                    'mentoring' => 'Public speaking or presenting'
                                ];
                                
                                foreach ($availableSkills as $value => $label) {
                                    $checked = in_array($value, $profile['skills']) ? 'checked' : '';
                                    echo "<div class='skill-checkbox'>
                                            <input type='checkbox' id='$value' name='skills[]' value='$value' $checked>
                                            <label for='$value'>$label</label>
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Password Reset Section -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Change Password</h2>
                <p>Reset your account password</p>
            </div>
            <div class="card-body">
                <div id="passwordMessage" class="message"></div>
                <p style="margin-bottom: 1.5rem; color: #6c757d;">
                    Click the button below to receive an OTP via email to reset your password.
                </p>
                <button type="button" id="sendOtpBtn" class="btn btn-secondary">Send OTP to Email</button>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div id="otpModal" class="otp-modal">
        <div class="otp-modal-content">
            <h3 style="text-align: center; margin-bottom: 1rem;">Enter OTP</h3>
            <p style="text-align: center; color: #6c757d; margin-bottom: 1.5rem;">
                Enter the 6-digit code sent to your email
            </p>
            <div id="otpMessage" class="message"></div>
            
            <form id="otpForm">
                <div class="otp-inputs">
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom: 1rem;">Verify OTP</button>
                <button type="button" id="closeOtpModal" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div id="passwordModal" class="otp-modal">
        <div class="otp-modal-content">
            <h3 style="text-align: center; margin-bottom: 1rem;">Reset Password</h3>
            <div id="resetMessage" class="message"></div>
            
            <form id="resetPasswordForm">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" id="newPassword" class="form-control" placeholder="Enter new password" required>
                        <i class="ri-eye-line password-toggle" id="toggleNewPassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm new password" required>
                        <i class="ri-eye-line password-toggle" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <div class="password-requirements">
                    <p>Password must contain:</p>
                    <ul>
                        <li id="length">At least 5 characters</li>
                        <li id="number">At least one number</li>
                        <li id="special">At least one special character</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-bottom: 1rem;">Reset Password</button>
                <button type="button" id="closePasswordModal" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/edit_profile.js"></script>
</body>
</html>