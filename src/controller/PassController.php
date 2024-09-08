<?php
require_once __DIR__ . '/../model/PassModel.php';
require_once __DIR__ . '/../controller/HomeController.php';
require_once __DIR__ . '/../../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordController {
    private $model;
    private $apiUrlActivity = 'http://localhost:9002/apiActivity';

    public function sendCode() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email'];
            $_SESSION['email'] = $email; 
    
            $emaiExist = PassModel::isEmailExists($email);
    
            if ($emaiExist) {
                PassModel::deleteOldResetCode($email);
                $code = PassModel::generateResetCode();
                if (PassModel::storeResetCode($email, $code)) {
                    if ($this->sendEmail($email, $code)) {
                        $_SESSION['reset_email'] = $email;
                        echo json_encode([
                            'success' => true,
                            'message' => 'Mã xác nhận đã được gửi đến email của bạn.'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Không thể gửi email. Vui lòng thử lại.'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể lưu mã xác nhận. Vui lòng thử lại.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email không tồn tại.'
                ]);
            }
            exit();
        }
    }
    

    private function sendEmail($to, $code) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'bapbap241202@gmail.com';
            $mail->Password = 'fwak arfc rlji puhc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('bapbap241202@gmail.com', 'Nhom5');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = 'Code reset password';
            $mail->Body    = "This is your code to change password: <strong>$code</strong>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            ]);
            return false;
        }
    }

    public function resetPassword() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $code = $_POST['code'];
            $newPassword = $_POST['n_password'];
            $confirmPassword = $_POST['r_password'];
            $email = $_SESSION['reset_email'] ?? null;

            if ($newPassword !== $confirmPassword) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Mật khẩu nhập lại không khớp.'
                ]);
                exit();
            }

            if (PassModel::isValidResetCode($email, $code)) {
                if (PassModel::updatePassword($email, $newPassword)) {
                    PassModel::deletePasswordReset($email, $code);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Mật khẩu đã được cập nhật thành công.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể cập nhật mật khẩu. Vui lòng thử lại.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Mã xác nhận không hợp lệ hoặc đã hết hạn.'
                ]);
            }
            exit();
        }
    }
}
?>
