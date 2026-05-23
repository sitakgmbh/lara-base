<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>{{ $mailTitle ?? config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f6f6;">
    <center style="width:100%; background:#f6f6f6; padding:20px 0; font-family:Arial,sans-serif;">
        @php
            $logoPath    = public_path('assets/images/logo-mail.png');
            $logoBase64  = '';
            if (file_exists($logoPath)) {
                $type       = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data       = base64_encode(file_get_contents($logoPath));
                $logoBase64 = "data:image/{$type};base64,{$data}";
            }
            $headerColor = '#1D4E8F';
        @endphp

        @if($logoBase64)
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="text-align:center; padding-bottom:20px;">
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-height:100px;">
                    </td>
                </tr>
            </table>
        @endif

        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
               style="max-width:800px; background:#ffffff; border:1px solid #dee2e6; border-radius:4px;">
            <tr>
                <td style="padding:0;">

                    {{-- Header --}}
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td bgcolor="{{ $headerColor }}"
                                style="padding:8px 15px; color:#ffffff; font-size:16px; font-weight:bold;">
                                {{ $mailTitle ?? config('app.name') }}
                            </td>
                        </tr>
                    </table>

                    {{-- Body --}}
                    @if(!empty($mailBody))
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:20px; font-size:14px; line-height:20px; color:#333;">
                                    {!! $mailBody !!}
                                </td>
                            </tr>
                        </table>
                    @endif

                    {{-- Footer --}}
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td bgcolor="#f1f3f5"
                                style="padding:15px; font-size:12px; color:#6c757d; text-align:center;">
                                @if(!empty($mailFooter))
                                    {!! $mailFooter !!}
                                @else
                                    <p style="margin:0; font-size:12px; line-height:18px;">
                                        <strong>Bitte antworte nicht auf diese E-Mail.</strong>
                                    </p>
                                    <p style="margin:0; font-size:12px; line-height:18px;">
                                        Versendet am {{ now()->format('d.m.Y H:i') }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    </center>
</body>
</html>