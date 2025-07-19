<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail;
    public string $fromName;
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol;

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost;

    /**
     * SMTP Username
     */
    public string $SMTPUser;

    /**
     * SMTP Password
     */
    public string $SMTPPass;

    /**
     * SMTP Port
     */
    public int $SMTPPort;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     */
    public string $SMTPCrypto;

    /**
     * Enable word-wrap
     */
    public bool $wordWrap;

    /**
     * Character count to wrap at
     */
    public int $wrapChars;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType;

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset;

    /**
     * Whether to validate the email address
     */
    public bool $validate;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority;

    /**
     * Newline character. (Use "\r\n" to comply with RFC 822)
     */
    public string $CRLF;

    /**
     * Newline character. (Use "\r\n" to comply with RFC 822)
     */
    public string $newline;

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    /**
     * Constructor - Load configuration from .env file
     */
    public function __construct()
    {
        parent::__construct();
        
        // Load email configuration from .env file
        $this->fromEmail = env('email.fromEmail', 'noreply@anvarinmobiliaria.com');
        $this->fromName = env('email.fromName', 'ANVAR - Sistema de Presupuestos');
        $this->protocol = env('email.protocol', 'smtp');
        $this->SMTPHost = env('email.SMTPHost', 'mail.anvarinmobiliaria.com');
        $this->SMTPUser = env('email.SMTPUser', 'noreply@anvarinmobiliaria.com');
        $this->SMTPPass = env('email.SMTPPass', '');
        $this->SMTPPort = (int) env('email.SMTPPort', 465);
        $this->SMTPTimeout = (int) env('email.SMTPTimeout', 30);
        $this->SMTPCrypto = env('email.SMTPCrypto', 'ssl');
        $this->wordWrap = (bool) env('email.wordWrap', true);
        $this->wrapChars = (int) env('email.wrapChars', 76);
        $this->mailType = env('email.mailType', 'html');
        $this->charset = env('email.charset', 'utf-8');
        $this->validate = (bool) env('email.validate', false);
        $this->priority = (int) env('email.priority', 3);
        $this->CRLF = env('email.CRLF', "\r\n");
        $this->newline = env('email.newline', "\r\n");
        $this->BCCBatchMode = (bool) env('email.BCCBatchMode', false);
        $this->BCCBatchSize = (int) env('email.BCCBatchSize', 200);
    }
}