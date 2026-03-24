<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use Illuminate\Support\Facades\File;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        // A fájl elérése a server/resources/assets mappában
        $path = resource_path('assets/stadium-map.svg');

        if (!File::exists($path)) {
            $this->command->error("Hiba: Még mindig nem találom a fájlt itt: " . $path);
            return;
        }

        $svgContent = File::get($path);

        /**
         * FONTOS: Csak a <g> (csoport) tagek ID-it keressük ki.
         * Ez megakadályozza, hogy a belső polygon/path elemek (-2, -3 végűek) bekerüljenek.
         */
        preg_match_all('/<g[^>]+id=["\'](sector-[^"\']+)["\']/', $svgContent, $matches);

        if (empty($matches[1])) {
            $this->command->warn("A fájl megvan, de nem találtam benne '<g id=\"sector-...\">' típusú ID-kat.");
            return;
        }

        // Duplikációk kiszűrése (ha az SVG-ben valamiért többször szerepelne ugyanaz a csoport ID)
        $sectors = array_unique($matches[1]);
        $count = 0;

        foreach ($sectors as $rawId) {
            // 1. "sector-134-3" -> "134-3"
            $tempId = str_replace('sector-', '', $rawId);

            // 2. "134-3" -> "134" (levágunk mindent az első kötőjel után)
            $cleandId = explode('-', $tempId)[0];

            Sector::updateOrCreate(
                ['id' => $cleandId], // Itt már fixen csak a "134" lesz
                [
                    'sector_name'  => $cleandId,
                    'sector_price' => 0
                ]
            );
            $count++;
        }
        $this->command->info("Siker! $count fő szektor beolvasva az SVG csoportokból.");
    }
}
