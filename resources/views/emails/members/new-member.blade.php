@extends('emails.invoices.layout')

@section('content')
    <div style="font-size: 14px; color: #111827;">
        Salut,
    </div>
    <div style="font-size: 14px; color: #111827; margin-top: 12px;">
        Membru nou: <strong>{{ $memberName }}</strong>, {{ $memberEmail }}, {{ filled($memberPhone) ? $memberPhone : '—' }}.
        Plan ales: <strong>{{ $planName }}</strong>.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
           style="margin-top: 18px; border-top: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 14px 0 0; font-size: 13px; color: #6b7280;">Nume</td>
            <td style="padding: 14px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $memberName }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0 0; font-size: 13px; color: #6b7280;">Email</td>
            <td style="padding: 10px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $memberEmail }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0 0; font-size: 13px; color: #6b7280;">Telefon</td>
            <td style="padding: 10px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ filled($memberPhone) ? $memberPhone : '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0 0; font-size: 13px; color: #6b7280;">Plan ales</td>
            <td style="padding: 10px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $planName }}
            </td>
        </tr>
    </table>
@endsection
