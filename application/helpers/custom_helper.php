<?php

function fourDigitCode(){
    return rand(1000, 9999);
}

// if (!function_exists('send_otp_email')) {
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
                <h2>OTP Verification</h2>
                <p>Your OTP is:</p>
                <h1>$otp</h1>
                <p>This OTP is valid for 5 minutes.</p>
            ";

        $CI->email->message($message);

        if ($CI->email->send()) {
            return true;
        } else {
            log_message('error', $CI->email->print_debugger());
            return false;
        }
    }
// }
