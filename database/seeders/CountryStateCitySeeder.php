<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class CountryStateCitySeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('countries+states+cities.json'));
        $countries = json_decode($json, true);

        foreach ($countries as $countryData) {
            $country = Country::create([
                'id' => $countryData['id'],
                'name' => $countryData['name'],
                'iso2' => $countryData['iso2'] ?? null,
                'phonecode' => $countryData['phonecode'] ?? null,
                'latitude' => $countryData['latitude'] ?? null,
                'longitude' => $countryData['longitude'] ?? null,
            ]);

            if (!empty($countryData['states'])) {
                foreach ($countryData['states'] as $stateData) {
                    $state = State::create([
                        'id' => $stateData['id'],
                        'country_id' => $country->id,
                        'name' => $stateData['name'],
                        'iso2' => $stateData['iso2'] ?? null,
                        'latitude' => $stateData['latitude'] ?? null,
                        'longitude' => $stateData['longitude'] ?? null,
                    ]);

                    if (!empty($stateData['cities'])) {
                        foreach ($stateData['cities'] as $cityData) {
                            City::create([
                                'id' => $cityData['id'],
                                'state_id' => $state->id,
                                'name' => $cityData['name'],
                                'latitude' => $cityData['latitude'] ?? null,
                                'longitude' => $cityData['longitude'] ?? null,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
