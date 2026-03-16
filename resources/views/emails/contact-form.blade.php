<x-mail::message>
# New Contact Form Message

<x-mail::table>
| | |
|:--|:--|
| **From** | {{ $senderName }} |
| **Email** | {{ $senderEmail }} |
| **Phone** | {{ $phone ?: 'N/A' }} |
| **Subject** | {{ $contactSubject }} |
</x-mail::table>

### Message

{{ $body }}

<x-mail::button :url="'mailto:' . $senderEmail">
Reply to {{ $senderName }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
