<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1c2c5b; margin: 0; padding: 0; background-color: #f4f7f9; }
        .container { width: 100%; max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; border-top: 6px solid #00529f; }
        .header { padding: 24px 30px 10px; }
        .content { padding: 20px 30px 30px; }
        h1 { margin: 0; font-size: 22px; color: #00529f; }
        .highlight { color: #00529f; font-weight: 700; }
        .details-box { background-color: #00529f; color: #ffffff; padding: 16px; border-radius: 10px; margin: 16px 0; }
        .label { font-size: 11px; opacity: 0.8; text-transform: uppercase; display: block; }
        .value { font-size: 16px; font-weight: 700; }
        .footer { background-color: #f8f9fa; color: #a0a0a0; padding: 16px; text-align: center; font-size: 11px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ticket purchase confirmed</h1>
        </div>
        <div class="content">
            <p>Hello <span class="highlight">{{ $ticket->user->name }}</span>,</p>
            <p>Your ticket purchase has been confirmed.</p>
            <div class="details-box">
                <span class="label">Match</span>
                <span class="value">{{ $ticket->game->homeTeam->team_name }} vs {{ $ticket->game->awayTeam->team_name }}</span>
            </div>
            <p>Your ticket is attached as a PDF to this email.</p>
        </div>
        <div class="footer"><p>&copy; 2026 Ticketing</p></div>
    </div>
</body>
</html>
