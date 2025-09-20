@php
    $title = $gallery->title ?? 'Gallery';
@endphp

<h1>Access to "{{ $title }}"</h1>

<p>Hello,</p>

<p>You have been granted access to the gallery "{{ $title }}".</p>

<p><strong>Code:</strong> {{ $code }}</p>

<p>
    You can open the gallery directly using this link:<br>
    <a href="{{ $link }}">{{ $link }}</a>
    <br>
    If the link doesn’t work, go to the gallery page and enter the code manually.
    @if($expiresAt)
        <br>
        <em>This code expires on {{ $expiresAt->toDayDateTimeString() }}.</em>
    @else
        <br>
        <em>This code does not expire.</em>
    @endif
    
</p>

<p>Best regards,<br>{{ config('app.name') }}</p>

