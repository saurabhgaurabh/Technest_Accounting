<?php

function fourDigitCode()
{
    return rand(1000, 9999);
}

function send_otp_email($email, $otp)
{
    $CI = &get_instance();
    $CI->load->library('email');

    $config = array(
        'protocol'  => 'smtp',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_user' => 'leadchainsaurabh7@gmail.com',
        'smtp_pass' => 'viqc qaim yhtg ngmj',
        'smtp_crypto' => 'tls',
        'mailtype'  => 'html',
        'charset'   => 'utf-8',
        'newline'   => "\r\n",
        'crlf'      => "\r\n"
    );

    $CI->email->initialize($config);
    $CI->email->from('leadchainsaurabh7@gmail.com', 'Billing App');
    $CI->email->to($email);
    $CI->email->subject('Your OTP Code');

    $message = "
    <div style='background-color: #f4f7fa; padding: 40px 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
        <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08);'>
            
            <!-- Gradient Header -->
            <div style='background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 40px 20px; text-align: center;'>
            <div style='background: rgba(255, 255, 255, 0.2); width: 60px; height: 60px; border-radius: 12px; margin: 0 auto 15px; display: table;'>
                <span style='display: table-cell; vertical-align: middle; font-size: 30px;'>🔐</span>
            </div>
            <h1 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 0.5px;'>Security Verification</h1>
            </div>

            <!-- Body Section -->
            <div style='padding: 40px 35px; text-align: center;'>
            <p style='color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0;'>
                Hello there, <br>
                To keep your billing account secure, use the One-Time Password below to verify your identity.
            </p>

            <!-- OTP Display -->
            <div style='margin: 35px 0;'>
                <div style='background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px; display: inline-block;'>
                <span style='font-family: \"Courier New\", Courier, monospace; font-size: 38px; font-weight: 700; color: #1e3a8a; letter-spacing: 12px; margin-left: 12px;'>
                    $otp
                </span>
                </div>
            </div>

            <p style='color: #9ca3af; font-size: 14px; margin: 0;'>
                This code expires in <span style='color: #ef4444; font-weight: 600;'>5 minutes</span>.
            </p>
            
            <div style='margin-top: 30px; padding-top: 30px; border-top: 1px solid #f1f5f9;'>
                <p style='color: #6b7280; font-size: 13px; margin: 0;'>
                    If you didn't request this, please secure your account immediately or contact our support team.
                </p>
            </div>
            </div>

            <!-- Footer -->
            <div style='background-color: #f8fafc; padding: 25px; text-align: center;'>
            <p style='color: #64748b; font-size: 12px; font-weight: 600; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 1px;'>
                Billing App PRO
            </p>
            <p style='color: #94a3b8; font-size: 11px; margin: 0;'>
                Powered by TechNest Services • India
            </p>
            </div>
        </div>
    </div>
";

    $CI->email->message($message);

    if ($CI->email->send()) {
        return true;
    } else {
        log_message('error', $CI->email->print_debugger());
        return false;
    }
}
