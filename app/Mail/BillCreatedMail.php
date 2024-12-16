<?php

namespace App\Mail;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BillCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $bill;
    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đơn Hàng của bạn đã được tạo',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.bill_created',
        );

        // return new Content(
        //     text: 'emails.bill_created',
        // );
    }


    public function attachments(): array
    {
        return [];
    }
}
