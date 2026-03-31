<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1c2c5b;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .ticket-page {
            padding: 24px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .header {
            background-color: #0b57d0;
            color: white;
            padding: 16px;
            border-radius: 8px 8px 0 0;
        }
        h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .match-box {
            border: 2px solid #0b57d0;
            margin-top: 16px;
            padding: 16px;
            border-radius: 8px;
        }
        .details-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-size: 10px;
            color: #777;
            text-transform: uppercase;
            display: block;
        }
        .value {
            font-size: 14px;
            font-weight: bold;
            color: #0b57d0;
        }
        .qr-section {
            text-align: center;
            margin-top: 20px;
            padding: 16px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .price-tag {
            font-size: 16px;
            color: #1c2c5b;
            font-weight: bold;
        }
        .meta {
            color: #475569;
            font-size: 12px;
            margin-top: 4px;
        }
        .tips {
            margin-top: 16px;
            padding: 10px 12px;
            background: #eef4ff;
            border: 1px solid #c7d7f5;
            border-radius: 8px;
            font-size: 11px;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="ticket-page">
        <div class="header">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td>
                        <h1>Ticket</h1>
                        <p>Santiago Bernabeu Stadium</p>
                    </td>
                    <td style="text-align:right;">
                        @php
                            $homeLogo = $ticket->game->homeTeam->team_logo ?? null;
                            $awayLogo = $ticket->game->awayTeam->team_logo ?? null;
                            $homePath = $homeLogo ? public_path('csapat kepek/' . $homeLogo) : null;
                            $awayPath = $awayLogo ? public_path('csapat kepek/' . $awayLogo) : null;
                            $homeSrc = $homePath && file_exists($homePath) ? 'file:///' . str_replace('\\', '/', $homePath) : null;
                            $awaySrc = $awayPath && file_exists($awayPath) ? 'file:///' . str_replace('\\', '/', $awayPath) : null;
                        @endphp
                        @if($homeSrc)
                            <img src="{{ $homeSrc }}" alt="Home logo" style="height:36px; margin-right:6px;">
                        @endif
                        @if($awaySrc)
                            <img src="{{ $awaySrc }}" alt="Away logo" style="height:36px;">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="match-box">
            <span class="label">Match</span>
            <span class="value">{{ $ticket->game->homeTeam->team_name }} vs {{ $ticket->game->awayTeam->team_name }}</span>
            <div class="meta">Date: {{ $ticket->game->game_date }}</div>
            @if($ticket->game->game_time)
                <div class="meta">Time: {{ $ticket->game->game_time }}</div>
            @endif
            <div class="meta">Venue: {{ $ticket->game->venue ?? 'TBD' }}</div>
        </div>

        <table class="details-table">
            <tr>
                <td><span class="label">Sector</span><span class="value">{{ $ticket->seat->sector->sector_name }}</span></td>
                <td><span class="label">Row</span><span class="value">{{ $ticket->seat->row }}</span></td>
                <td><span class="label">Seat</span><span class="value">{{ $ticket->seat->col }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Buyer</span><span class="value">{{ $ticket->user->name }}</span></td>
                <td><span class="label">Price</span><span class="value price-tag">{{ number_format($ticket->seat->sector->sector_price, 2, ',', ' ') }} €</span></td>
                <td><span class="label">Ticket ID</span><span class="value">#{{ $ticket->id }}</span></td>
            </tr>
        </table>

        <div class="details-table" style="margin-top: 12px;">
            <div class="label">Order reference</div>
            <div class="value">{{ $orderRef }}</div>
            <div class="label" style="margin-top: 6px;">Order total</div>
            <div class="value price-tag">{{ number_format($orderTotal, 2, ',', ' ') }} €</div>
        </div>

        <div class="qr-section">
            <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::size(150)->generate($qrPayload ?? ('TICKET-ID:' . $ticket->id))) }}">
            <p style="margin-top: 10px;">Scan this code at entry.</p>
        </div>

        <div class="tips">
            Arrive 30 minutes before kickoff. Bring your PDF (phone or print). QR code will be scanned at entry.
        </div>

        <div class="footer">
            <p>&copy; 2026 Ticketing</p>
        </div>
    </div>
</body>
</html>

