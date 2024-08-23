<?php
require_once __DIR__ . '/../model/PassModel.php';
require_once __DIR__ . '/../controller/HomeController.php';
require_once __DIR__ . '/../../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordController {
    private $model;

    public function __construct() {
        $this->model = new PassModel();
    }

    public function sendCode() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = 'Quên mật khẩu';
            $email = $_POST['email'];
            $_SESSION['email'] = $email; 
            //$model = new PassModel();

            if ($this->model->isEmailExists($email)) {
                $code = $this->model->generateResetCode();
                if ($this->model->storeResetCode($email, $code)) {
                    // Gửi email với mã xác nhận (code) cho người dùng
                    if ($this->sendEmail($email, $code)) {
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['message'] = [
                            'success' => true,
                            'message' => 'Mã xác nhận đã được gửi đến email của bạn.'
                        ];
                    } else {
                        // $_SESSION['message'] = [
                        //     'success' => false,
                        //     'message' => 'Không thể gửi email. Vui lòng thử lại.'
                        // ];
                        $_SESSION['message']['success'] = false;
                    }
                } else {
                    $_SESSION['message'] = [
                        'success' => false,
                        'message' => 'Không thể lưu mã xác nhận. Vui lòng thử lại.'
                    ];
                }
            } else {
                $_SESSION['message'] = [
                    'success' => false,
                    'message' => 'Email không tồn tại.'
                ];
            }

            require(__DIR__ . '/../views/pages/forgot_pass.phtml');
            exit();
        }
    }

    private function sendEmail($to, $code) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Cấu hình máy chủ SMTP của bạn
            $mail->SMTPAuth = true;
            $mail->Username = 'udptnhom05@gmail.com'; // Địa chỉ email gửi
            $mail->Password = 'udptnhom4connghien'; // Mật khẩu email
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('udptnhom05@gmail.com', 'Nhom5');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Mã xác nhận đổi mật khẩu QLNV_PHP';
            $mail->Body    = "Bạn đã yêu cầu thay đổi mật khẩu từ ứng dụng QLNV_PHP. Mã xác nhận của bạn là: <strong>$code</strong>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            $_SESSION['message'] = [
                'success' => false,
                'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            ];
            return false;
        }
    }

    public function resetPassword() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = 'Quên mật khẩu';
            $code = $_POST['code'];
            $newPassword = $_POST['n_password'];
            $confirmPassword = $_POST['r_password'];
            $email = $_SESSION['reset_email'] ?? null;; // Bạn cần có cách để lưu email trong quá trình gửi mã

            if ($newPassword !== $confirmPassword) {
                $_SESSION['message'] = [
                    'success' => false,
                    'message' => 'Mật khẩu nhập lại không khớp.'
                ];
                //header('Location: /path/to/your/form.php');
                exit();
            }

            //$model = new PassModel();

            if ($this->model->isValidResetCode($email, $code)) {
                if ($this->model->updatePassword($email, password_hash($newPassword, PASSWORD_DEFAULT))) {
                    $this->model->deletePasswordReset($email, $code);
                    $_SESSION['message'] = [
                        'success' => true,
                        'message' => 'Mật khẩu đã được cập nhật thành công.'
                    ];
                    $title = 'Login';
                    require("./views/pages/login.phtml");
                    exit();
                } else {
                    $_SESSION['message'] = [
                        'success' => false,
                        'message' => 'Không thể cập nhật mật khẩu. Vui lòng thử lại.'
                    ];
                    //header('Location: /path/to/your/form.php');
                    exit();
                }
            } else {
                $_SESSION['message'] = [
                    'success' => false,
                    'message' => 'Mã xác nhận không hợp lệ hoặc đã hết hạn.'
                ];
                //header('Location: /path/to/your/form.php');
                exit();
            }
        }
    }
}
?>
