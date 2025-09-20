<?php

namespace App\Mail;

use App\Models\Gallery;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GalleryAccessCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Gallery $gallery,
        public string $code,
        public string $link,
        public ?\Carbon\Carbon $expiresAt = null,
    ) {}

    public function build()
    {
        return $this->subject('Access to gallery: ' . ($this->gallery->title ?? 'Gallery'))
            ->view('emails.gallery_access_code');
    }
}

