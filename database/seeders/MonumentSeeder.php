<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Monument;
use App\Models\Location;
use App\Models\Dimension;
use App\Models\Image;
use App\Models\AudiovisualSource;
use App\Modules\Monuments\Services\MonumentService;
use Illuminate\Support\Facades\DB;

class MonumentSeeder extends Seeder
{
    private $_service;
    public function __construct(MonumentService $service){
        $this->_service = $service;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = fopen(storage_path('app/data/csv/monuments.csv'), 'r');
        $header = fgetcsv($file);
        $monuments = $this->parseMonuments($file, $header);
        fclose($file);
    
        $this->_service->addMultipleMonuments($monuments);
    }

    protected function parseMonuments($file, $header)
    {
        $monuments = [];

        while (($data = fgetcsv($file)) !== false) {
            $data = array_combine($header, $data);

            $monumentData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'location' => [
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'street' => $data['street'] ?: null,
                    'number' => $data['number'] ?: null,
                    'city' => $data['city'],
                ],
                'historical_significance' => $data['historical_significance'] ?: null,
                'type' => $data['type'],
                'year_of_construction' => $data['year_of_construction'],
                'monument_designer' => $data['monument_designer'],
                'accessibility' => explode(",",$data['accessibility']),
                'used_materials' => explode(",",$data['used_materials']),
                'dimensions' => [
                    'height' => $data['height'],
                    'width' => $data['width'],
                    'depth' => $data['depth'],
                ],
                'weight' => $data['weight'] ?: null,
                'cost_to_construct' => $data['cost_to_construct'] ?: null,
                'images' => [
                    'urls' => explode(",",$data['images_urls']),
                    'captions' => explode(",",$data['images_captions']),
                ],
                'audiovisual_source' => [
                    'title' => $data['audiovisual_source_title'],
                    'url' => $data['audiovisual_source_url'],
                    'type' => $data['audiovisual_source_type'],
                ],
                'language' => $data['language'],
            ];

            $monuments[] = $monumentData;
        }

        return $monuments;
    }
}
