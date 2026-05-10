<?php
require_once(__DIR__ . '/../config/Database.php');

class PasswordResetController
{
    private $db;
    private const FROM_EMAIL = 'jobyfind.contact@gmail.com';
    private const FROM_NAME  = 'Jobyfind';
    private const CODE_TTL_MINUTES = 15;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function sendResetCode($email, $firstName)
    {
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->invalidateCodes($email);

        $sql = "INSERT INTO password_resets (email, code, expires_at, used)
                VALUES (:email, :code, DATE_ADD(NOW(), INTERVAL :ttl MINUTE), 0)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => $email,
                'code'  => $code,
                'ttl'   => self::CODE_TTL_MINUTES,
            ]);
        } catch (Exception $e) {
            error_log('PasswordResetController DB error: ' . $e->getMessage());
            return false;
        }

        return $this->sendEmail($email, $firstName, $code);
    }

    public function verifyCode($email, $code)
    {
        $sql = "SELECT * FROM password_resets
                WHERE email = :email AND code = :code AND used = 0
                ORDER BY created_at DESC
                LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email, 'code' => $code]);
            $row = $stmt->fetch();

            if (!$row) {
                return false;
            }

            if (strtotime($row['expires_at']) < time()) {
                return 'expired';
            }

            $upd = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE id = :id");
            $upd->execute(['id' => $row['id']]);

            return true;
        } catch (Exception $e) {
            error_log('PasswordResetController verify error: ' . $e->getMessage());
            return false;
        }
    }

    public function invalidateCodes($email)
    {
        $sql = "UPDATE password_resets SET used = 1 WHERE email = :email";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
        } catch (Exception $e) {
            error_log('PasswordResetController invalidate error: ' . $e->getMessage());
        }
    }

    private function getIpInfo($ip)
    {
        if ($ip === '::1' || $ip === '127.0.0.1') return 'Localhost';
        try {
            $json = file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city");
            $data = json_decode($json, true);
            if ($data && $data['status'] === 'success') {
                return "{$data['city']}, {$data['country']}";
            }
        } catch (Exception $e) {}
        return 'Emplacement inconnu';
    }

    private function sendEmail($toEmail, $firstName, $code)
    {
        $from     = self::FROM_EMAIL;
        $name     = self::FROM_NAME;
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $location = $this->getIpInfo($ip);
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $browser = "Navigateur inconnu";
        if (strpos($userAgent, 'Firefox') !== false) $browser = "Mozilla Firefox";
        elseif (strpos($userAgent, 'Chrome') !== false) $browser = "Google Chrome";
        elseif (strpos($userAgent, 'Safari') !== false) $browser = "Apple Safari";
        elseif (strpos($userAgent, 'Edge') !== false) $browser = "Microsoft Edge";

        $date = date('d/m/Y à H:i');

        $digitBlocks = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $d = $code[$i];
            $digitBlocks .= "<span style='display:inline-block;width:44px;height:52px;line-height:52px;"
                          . "text-align:center;font-size:26px;font-weight:700;background:#f0f4ff;"
                          . "border:2px solid #2d79ff;border-radius:8px;margin:0 4px;color:#0b1f4b;'>"
                          . htmlspecialchars($d) . "</span>";
        }

        $subject = '=?UTF-8?B?' . base64_encode('Code de sécurité — Jobyfind') . '?=';

        $body = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;">... (body truncated for brevity) ... Code: ' . $code . '</body></html>';
        // Note: I'll use a simplified body or the full one if needed.
        // Actually I should probably use the full body from the original file.
        // I'll just put a placeholder for now to save tokens, or I'll copy it.
        // I'll copy the full body from the original.
        
        $body = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fb;padding:40px 0;">
    <tr><td align="center">
      <table width="520" cellpadding="0" cellspacing="0"
             style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
        <tr>
          <td style="background:#0b1f4b;padding:28px 40px;text-align:center;">
            <span style="font-size:24px;font-weight:700;color:#fff;">
              Joby<span style="color:#2d79ff;">find</span>
            </span>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <h2 style="font-size:18px;color:#0b1f4b;margin:0 0 16px;">Vérification intelligente de sécurité</h2>
            <p style="font-size:15px;color:#374151;margin:0 0 8px;">
              Bonjour <strong>' . htmlspecialchars($firstName) . '</strong>,
            </p>
            <p style="font-size:14px;color:#6b7280;margin:0 0 28px;line-height:1.6;">
              Nous avons reçu une demande de réinitialisation pour votre compte. <br>
              Pour votre sécurité, nous avons analysé cette tentative :
            </p>
            
            <div style="background:#f8fafc; border-radius:12px; padding:20px; margin-bottom:28px; border:1px solid #e2e8f0;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-bottom:10px; font-size:12px; color:#94a3b8; text-transform:uppercase;">Détails de la demande</td>
                </tr>
                <tr>
                  <td style="font-size:13px; color:#334155; padding-bottom:5px;">
                    <strong style="color:#64748b;">Emplacement :</strong> ' . htmlspecialchars($location) . '
                  </td>
                </tr>
                <tr>
                  <td style="font-size:13px; color:#334155; padding-bottom:5px;">
                    <strong style="color:#64748b;">Appareil :</strong> ' . $browser . '
                  </td>
                </tr>
                <tr>
                  <td style="font-size:13px; color:#334155;">
                    <strong style="color:#64748b;">Date :</strong> ' . $date . '
                  </td>
                </tr>
              </table>
            </div>

            <p style="font-size:14px;color:#6b7280;margin:0 0 16px;text-align:center;">
              Si c\'est bien vous, utilisez ce code de vérification :
            </p>
            <div style="text-align:center;margin:0 0 32px;">' . $digitBlocks . '</div>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($name) . "?= <" . $from . ">\r\n";
        $headers .= "Reply-To: " . $from . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        return mail($toEmail, $subject, $body, $headers);
    }

    public function getRemainingSeconds($email)
    {
        $sql = "SELECT expires_at FROM password_resets 
                WHERE email = :email AND used = 0 
                ORDER BY created_at DESC LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $row = $stmt->fetch();
            if (!$row) return 0;

            $diff = strtotime($row['expires_at']) - time();
            return max(0, $diff);
        } catch (Exception $e) {
            return 0;
        }
    }
}
