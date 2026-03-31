<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 0; padding: 0; background-color: #f4f7f9; }
        .container { width: 100%; max-width: 680px; margin: 24px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; border-top: 6px solid #0b57d0; }
        .header { padding: 22px 28px 8px; }
        .content { padding: 20px 28px 28px; }
        h1 { margin: 0; font-size: 22px; color: #0b57d0; }
        .lead { margin: 8px 0 18px; color: #475569; }
        .summary { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; }
        .ticket-block { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; }
        .label { font-size: 11px; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 2px; }
        .value { font-weight: 700; color: #0f172a; }
        .meta { color: #475569; font-size: 13px; margin-top: 6px; }
        .tips { background: #eef4ff; border: 1px solid #c7d7f5; padding: 12px 14px; border-radius: 10px; margin-top: 16px; }
        .footer { background-color: #f8fafc; color: #94a3b8; padding: 14px 20px; text-align: center; font-size: 11px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your tickets are ready</h1>
        </div>
        <div class="content">
            <p class="lead">We have attached your PDF ticket(s) to this email.</p>
            <div class="summary">
                <div class="value">Order reference: {{ $orderRef }}</div>
                <div class="meta">Total tickets: {{ count($tickets) }}</div>
                <div class="meta">Order total: {{ number_format($orderTotal, 2, ',', ' ') }} €</div>
                <div class="meta">Purchased for: {{ $tickets[0]->user->name }} ({{ $tickets[0]->user->email }})</div>
            </div>
            @foreach ($tickets as $ticket)
                <div class="ticket-block">
                    <span class="label">Match</span>
                    <div class="value">{{ $ticket->game->homeTeam->team_name }} vs {{ $ticket->game->awayTeam->team_name }}</div>
                    <div class="meta">Date: {{ $ticket->game->game_date }} @if($ticket->game->game_time) | Time: {{ $ticket->game->game_time }} @endif</div>
                    <div class="meta">Venue: {{ $ticket->game->venue ?? 'TBD' }}</div>
                    <span class="label" style="margin-top:8px;">Seat</span>
                    <div class="value">Sector {{ $ticket->seat->sector->sector_name }}, Row {{ $ticket->seat->row }}, Seat {{ $ticket->seat->col }}</div>
                    <div class="meta">Ticket ID: #{{ $ticket->id }}</div>
                </div>
            @endforeach
            <div class="tips">
                <div class="value">Entry instructions</div>
                <div class="meta">Arrive 30 minutes before kickoff. Have your PDF ready on your phone or printed.</div>
                <div class="meta">At the gate, the QR code will be scanned for entry.</div>
            </div>
        </div>
        <div class="footer">Thank you for your purchase.</div>
    </div>
</body>
</html>

