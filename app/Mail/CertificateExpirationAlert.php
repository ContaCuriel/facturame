<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateExpirationAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $alerts;

    public function __construct(Company $company, array $alerts)
    {
        $this->company = $company;
        $this->alerts = $alerts;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Acción Requerida: Certificados SAT de ' . ($this->company->commercial_name ?? $this->company->name),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.certificates.expiration',
        );
    }
}