# Jegyfoglaló

Ez a projekt egy focimeccsekre szóló jegyfoglalás/jegyvásárlás webalkalmazás. A felületen felhasználók foglalhatnak jegyeket, az admin pedig mérkőzéseket és adatlapi tartalmakat kezelhet.

**Röviden:**
- Frontend: Vue 3 + Vite (`client` mappa)
- Backend: Laravel (`server` mappa)
- Adatbázis: MySQL/MariaDB

## Követelmények

Egy átlagos fejlesztő felhasználónak ezekre lesz szüksége:

- Git
- Node.js (LTS) + npm
- PHP 8.x (Laravelhez) és kötelező bővítmények: `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`
- Composer
- MySQL vagy MariaDB adatbázis

**Windows tipp:** XAMPP/WAMP/MAMP is megfelel, amiben van Apache, PHP és MySQL.

## Telepítés és futtatás

### 1) Kód letöltése

```bash
git clone <repo-url>
cd Jegyfoglalo
```

### 2) Backend (Laravel)

```bash
cd server
composer install
```

**.env beállítása**

Ha nincs `.env`, másolj a példából:

```bash
copy .env.example .env
```

Majd állítsd be a DB adatokat a `.env`-ben:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jegyfoglalo
DB_USERNAME=root
DB_PASSWORD=
```

Migrációk és seedek:

```bash
php artisan migrate --seed
```

Szerver indítása:

```bash
php artisan serve
```

A backend alapból a `http://localhost:8000` címen fut.

### 3) Frontend (Vue + Vite)

```bash
cd ..\client
npm install
```

A `client/.env.development` tartalmazza az API címet. Ha a backend más címen fut, állítsd be:

```
VITE_API_URL=http://localhost:8000/api
```

Frontend indítása:

```bash
npm run dev
```

A frontend alapból a `http://localhost:5173` címen érhető el.

## Gyakori hibák

- **DB hiba:** Ellenőrizd, hogy fut-e a MySQL/MariaDB, és helyesek-e a `.env` adatok.
- **CORS hiba:** Győződj meg róla, hogy a backend `http://localhost:8000`-on fut, és a `VITE_API_URL` erre mutat.
- **Composer hiba:** Ellenőrizd a PHP verziót és a bővítményeket.

## Opcionális: build (produkciós)

```bash
cd client
npm run build
```

A build kimenete a `client/.env`-ben beállított `VITE_BUILD_DIR` és `VITE_WEB_DIR` értékektől függ.
