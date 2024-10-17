<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    private $transactionData;
    private $totalPrice;
    private $transactionId;
    /**
     * Create a new message instance.
     */
    public function __construct($transactionData, $totalPrice, $transactionId)
    {
        $this->transactionData = $transactionData;
        $this->totalPrice = $totalPrice;
        $this->transactionId = $transactionId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Request Approval Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $transactionId = $this->transactionData['id'];
        $baseUrl = env('APP_URL');
        $approvalLink = "{$baseUrl}/transaction/view/{$transactionId}";

        return new Content(
            view: 'Mail.RequestApproval',
            with: [
                'transactionData' => $this->transactionData,
                'totalPrice' => $this->totalPrice,
                'approvalLink' => $approvalLink,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $imagePath = public_path('images/CLISP-logo.png');

        $imageData = base64_encode(file_get_contents($imagePath));

        $imageBase64 = 'data:image/png;base64,' . $imageData;
        $this->transactionData->logo = $imageBase64;

        $pdfContent = Pdf::loadView('agreement', ['transaction' => $this->transactionData])->output();



        return [
            Attachment::fromData(fn() => $pdfContent, 'agreement.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
