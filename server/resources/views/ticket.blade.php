<!DOCTYPE html>
<html lang="hu">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1c2c5b; margin: 0; padding: 0; font-size: 12px; }
        .ticket-page { padding: 30px; border: 1px solid #ddd; border-radius: 10px; }
        .header { background-color: #00529f; color: white; padding: 20px; border-radius: 8px 8px 0 0; position: relative; }
        .logo { position: absolute; top: 15px; right: 20px; width: 60px; }
        h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .match-box { border: 2px solid #00529f; margin-top: 20px; padding: 20px; border-radius: 8px; }
        .details-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .details-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .label { font-size: 10px; color: #777; text-transform: uppercase; display: block; }
        .value { font-size: 16px; font-weight: bold; color: #00529f; }
        .qr-section { text-align: center; margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }
        .price-tag { font-size: 18px; color: #1c2c5b; font-weight: bold; }
    </style>
</head>
<body>
    <div class="ticket-page">
        <div class="header">
            <img src="https://upload.wikimedia.org/wikipedia/en/thumb/5/56/Real_Madrid_CF.svg/150px-Real_Madrid_CF.svg.png" class="logo">
            <h1>BELÉPŐJEGY</h1>
            <p>Santiago Bernabéu Stadion</p>
        </div>

        <div class="match-box">
            <span class="label">Mérkőzés</span>
            <span class="value">{{ $ticket->game->team_home->team_name }} vs {{ $ticket->game->team_away->team_name }}</span>
            <br><br>
            <span class="label">Mérkőzés időpontja</span>
            <span class="value">{{ $ticket->game->game_date }}</span>
        </div>

        <table class="details-table">
            <tr>
                <td><span class="label">Szektor</span><span class="value">{{ $ticket->seat->sector->sector_number }}</span></td>
                <td><span class="label">Sor</span><span class="value">{{ $ticket->seat->row }}</span></td>
                <td><span class="label">Szék</span><span class="value">{{ $ticket->seat->seat_number }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Vásárló</span><span class="value">{{ $ticket->user->name }}</span></td>
                <td><span class="label">Jegy ára</span><span class="value price-tag">{{ number_format($ticket->seat->sector->sector_price, 0, ',', ' ') }} Ft</span></td>
                <td><span class="label">Azonosító</span><span class="value">#{{ $ticket->id }}</span></td>
            </tr>
        </table>

        <div class="qr-section">
            <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::size(150)->generate('TICKET-ID:'.$ticket->id)) }}">
            <p style="margin-top: 10px;">Belépéskor a fenti kódot olvassa be!</p>
        </div>

        <div class="footer">
            <p>Hala Madrid y nada más! - &copy; 2026 Real Madrid C.F.</p>
        </div>
    </div>
</body>
</html>