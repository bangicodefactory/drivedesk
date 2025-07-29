?>

<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .urgent { background: #dc3545; color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #ffc107; color: #212529; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #17a2b8; color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Notification de Rappel de Maintenance</h2>
            <p>Bonjour {{ $user->name }},</p>
        </div>

        @if($reminder->status === 'overdue')
        <div class="urgent">
            <strong>🚨 URGENT - Rappel en retard</strong>
        </div>
        @elseif($reminder->status === 'urgent')
        <div class="warning">
            <strong>⚠️ ATTENTION - Rappel urgent</strong>
        </div>
        @else
        <div class="info">
            <strong>ℹ️ Information - Rappel à venir</strong>
        </div>
        @endif

        <p><strong>{{ $message }}</strong></p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr style="background: #f8f9fa;">
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Nom du rappel:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $reminder->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Date d'échéance:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ Carbon\Carbon::parse($reminder->reminder_date)->format('d/m/Y') }}</td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Statut:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    @switch($reminder->status)
                        @case('overdue') <span style="color: #dc3545;">En retard</span> @break
                        @case('urgent') <span style="color: #ffc107;">Urgent</span> @break
                        @case('upcoming') <span style="color: #17a2b8;">À venir</span> @break
                        @default <span style="color: #28a745;">En attente</span>
                    @endswitch
                </td>
            </tr>
            @if($reminder->note)
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Notes:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $reminder->note }}</td>
            </tr>
            @endif
        </table>

        <p>Veuillez prendre les mesures nécessaires pour résoudre ce rappel de maintenance dans les plus brefs délais.</p>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par le système de gestion de location de voitures.</p>
            <p>Ne pas répondre à cet email.</p>
        </div>
    </div>
</body>
</html>

<?php