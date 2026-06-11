@extends('emails.invoices.layout')

@section('content')
    <div style="font-size: 14px; color: #111827;">
        Salut,
    </div>
    <div style="font-size: 14px; color: #111827; margin-top: 12px;">
        <strong>{{ $memberName }}</strong> a intrat în sală cu abonamentul expirat.
        I s-a permis o singură intrare de grație — la următoarea scanare accesul va fi blocat
        până la reînnoirea abonamentului.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
           style="margin-top: 18px; border-top: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 14px 0 0; font-size: 13px; color: #6b7280;">Membru</td>
            <td style="padding: 14px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $memberName }} ({{ filled($memberCode) ? $memberCode : '—' }})
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0 0; font-size: 13px; color: #6b7280;">Ultimul plan</td>
            <td style="padding: 10px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ filled($planName) ? $planName : '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0 0; font-size: 13px; color: #6b7280;">Expirat la</td>
            <td style="padding: 10px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $expiredOn }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0 0; font-size: 13px; color: #6b7280;">Scanat la</td>
            <td style="padding: 10px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $scannedAt }}
            </td>
        </tr>
    </table>
@endsection
