<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Team;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $teamIds = Team::query()->pluck('id')->all();
        if (count($teamIds) < 2) {
            $this->command->warn('Legalabb 2 csapat szukseges a meccsek generalasahoz.');
            return;
        }

        $weeksToGenerate = 10;
        $matchesPerDay = 3;
        $kickoffTimes = ['14:00:00', '16:30:00', '19:00:00', '21:00:00'];

        $startOfSchedule = now()->startOfWeek()->addWeek();
        $games = [];
        $usedSlots = [];

        for ($week = 0; $week < $weeksToGenerate; $week++) {
            for ($day = 0; $day < 7; $day++) {
                $baseDate = $startOfSchedule->copy()->addWeeks($week)->addDays($day);
                $dailyTimes = $kickoffTimes;
                shuffle($dailyTimes);

                for ($i = 0; $i < $matchesPerDay; $i++) {
                    $kickoffAt = $baseDate->copy()->setTimeFromTimeString($dailyTimes[$i]);
                    $homeTeam = null;
                    $awayTeam = null;
                    $created = false;

                    for ($attempt = 0; $attempt < 40; $attempt++) {
                        $homeTeam = $teamIds[array_rand($teamIds)];
                        $awayTeam = $teamIds[array_rand($teamIds)];

                        if ($homeTeam === $awayTeam) {
                            continue;
                        }

                        $slotKey = $homeTeam . '-' . $awayTeam . '-' . $kickoffAt->format('Y-m-d H:i:s');
                        if (isset($usedSlots[$slotKey])) {
                            continue;
                        }

                        $usedSlots[$slotKey] = true;
                        $created = true;
                        break;
                    }

                    if (!$created) {
                        continue;
                    }

                    $games[] = [
                        'team_home_id' => $homeTeam,
                        'team_away_id' => $awayTeam,
                        'game_date' => $kickoffAt->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        Game::query()->insert($games);
        $this->command->info(count($games) . ' meccs sikeresen generalva.');
    }
}
