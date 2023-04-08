<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Monument;
use App\Models\Location;
use App\Models\Dimension;
use App\Models\Image;
use App\Models\AudiovisualSource;

class MonumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = fopen(storage_path('app/data/csv/monuments.csv'), 'r');

        $header = fgetcsv($file);

        while (($data = fgetcsv($file)) !== false) {
            $data = array_combine($header, $data);

            $usedMaterials = explode(',', $data['used_materials']);

            $location = Location::create([
                'latitude' => $data['location_latitude'],
                'longitude' => $data['location_longitude'],
                'street' => $data['location_street'] ?: null,
                'number' => $data['location_number'] ?: null,
                'city' => $data['location_city'],
            ]);

            $dimension = Dimension::create([
                'height' => $data['dimensions_height'],
                'width' => $data['dimensions_width'],
                'depth' => $data['dimensions_depth'],
            ]);

            $audiovisual = AudiovisualSource::create([
                'title' => $data['audiovisual_source_title'],
                'url' => $data['audiovisual_source_url'],
                'type' => $data['audiovisual_source_type'],
            ]);

            $monument = Monument::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'location_id' => $location->id,
                'historical_significance' => $data['historical_significance'] ?: null,
                'type' => $data['type'],
                'year_of_construction' => $data['year_of_construction'],
                'monument_designer' => $data['monument_designer'],
                'accessibility' => $data['accessibility'],
                'used_materials' => $usedMaterials,
                'dimensions_id' => $dimension->id,
                'weight' => $data['weight'] ?: null,
                'cost_to_construct' => $data['cost_to_construct'] ?: null,
                'audiovisual_source_id' => $audiovisual->id,
                'language' => $data['language'],
            ]);

            $images = json_decode($data['images_url'], true);
            $captions = json_decode($data['images_caption'], true);
            
            foreach ($images as $key => $image) {
                $img = Image::create([
                    'monument_id' => $monument->id,
                    'url' => $image,
                    'caption' => $captions[$key],
                ]);
            }
        }

        fclose($file);
    }
}
