<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Monument;
use App\Models\Location;
use App\Models\Dimensions;
use App\Models\Image;
use App\Models\AudiovisualSource;
use App\Models\MonumentLanguage;

class MonumentSeeder extends Seeder
{
    public function run(): void
    {
        $file = fopen(storage_path('app/data/csv/monuments.csv'), 'r');
        $header = fgetcsv($file);

        while (($data = fgetcsv($file)) !== false) {
            $data = array_combine($header, $data);

            $location = $this->createLocation($data);
            $dimensions = $this->parseDimensions($data);
            $audiovisualSource = $this->parseAudiovisualSource($data);

            $monument = $this->createMonument($data, $location, $dimensions, $audiovisualSource);

            $this->parseImages($data, $monument);

            $monumentLanguages = $this->parseMonumentLanguage($data);
            $this->saveMonumentLanguages($monumentLanguages, $monument);
        }

        fclose($file);
    }

    private function createLocation($data)
    {
        $locationData = [
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'street' => $data['street'],
            'number' => (int) $data['number'],
            'city' => $data['city']
        ];

        return Location::firstOrCreate($locationData);
    }

    private function parseDimensions($data)
    {
        $height = (float) $data['height'];
        $width = (float) $data['width'];
        $depth = (float) $data['depth'];

        if ($height == null && $width == null && $depth == null) {
            return null;
        }

        $dimensionsData = [
            'height' => $height,
            'width' => $width,
            'depth' => $depth,
        ];

        return Dimensions::firstOrCreate($dimensionsData);
    }

    private function parseAudiovisualSource($data)
    {
        if (empty($data['audiovisual_source_url']) && empty($data['audiovisual_source_type']) && empty($data['audiovisual_source_language_title_dutch']) && empty($data['audiovisual_source_language_title_english'])) {
            return null;
        }

        $audiovisualSourceData = [
            'url' => $data['audiovisual_source_url'],
            'type' => $data['audiovisual_source_type'],
        ];

        $audiovisualSource = AudiovisualSource::firstOrCreate($audiovisualSourceData);

        $audiovisualSourceLanguage = [
            [
                'title' => $data['audiovisual_source_language_title_dutch'],
                'language' => 'Dutch'
            ],
            [
                'title' => $data['audiovisual_source_language_title_english'],
                'language' => 'English'
            ],
        ];

        foreach ($audiovisualSourceLanguage as $language) {
            $audiovisualSource->audiovisualSourceLanguage()
                ->updateOrCreate(['title' => $language['title']], $language);
        }

        return $audiovisualSource;
    }

    private function createMonument($data, $location, $dimensions, $audiovisualSource)
    {
        $monumentData = [
            'year_of_construction' => (int) $data['year_of_construction'],
            'monument_designer' => $data['monument_designer'],
            'weight' => (int) $data['weight'],
            'cost_to_construct' => (float) $data['cost_to_construct'],
            'location_id' => $location->id,
            'dimensions_id' => $dimensions ? $dimensions->id : null,
        'audiovisual_source_id' => $audiovisualSource ? $audiovisualSource->id : null,
        ];

        return Monument::create($monumentData);
    }

    private function parseImages($data, $monument)
    {
        $images = [];
    
        $imageUrlKey = "images_url";
        $captionDutchKey = "captions_dutch";
        $captionEnglishKey = "captions_english";
    
        $imageUrls = explode(',', $data[$imageUrlKey]);
        $captionsDutch = explode(',', $data[$captionDutchKey]);
        $captionsEnglish = explode(',', $data[$captionEnglishKey]);
    
        foreach ($imageUrls as $index => $imageUrl) {
            $imageUrl = trim($imageUrl);
            $captionDutch = trim($captionsDutch[$index] ?? '');
            $captionEnglish = trim($captionsEnglish[$index] ?? '');
    
            if (!empty($imageUrl)) {
                $imageData = [
                    'url' => $imageUrl,
                    'monument_id' => $monument->id,
                ];
    
                $image = Image::create($imageData);
    
                $imageLanguages = [
                    [
                        'caption' => $captionDutch,
                        'language' => 'Dutch',
                    ],
                    [
                        'caption' => $captionEnglish,
                        'language' => 'English',
                    ],
                ];

                $image->imageLanguage()->createMany($imageLanguages);
    
                $images[] = $image;
            }
        }
    
        return $images;
    }

    private function parseMonumentLanguage($data)
    {
        return [
            MonumentLanguage::make([
                'language' => 'Dutch',
                'name' => $data['monument_language_name_dutch'],
                'description' => $data['monument_language_description_dutch'],
                'historical_significance' => $data['monument_language_historical_significance_dutch'],
                'type' => $data['monument_language_type_dutch'],
                'accessibility' => $this->parseAccessibility($data, 'dutch'),
                'used_materials' => $this->parseUsedMaterials($data, 'dutch'),
            ]),
            MonumentLanguage::make([
                'language' => 'English',
                'name' => $data['monument_language_name_english'],
                'description' => $data['monument_language_description_english'],
                'historical_significance' => $data['monument_language_historical_significance_english'],
                'type' => $data['monument_language_type_english'],
                'accessibility' => $this->parseAccessibility($data, 'english'),
                'used_materials' => $this->parseUsedMaterials($data, 'english'),
            ]),
        ];
    }

    private function parseAccessibility($data, $language)
    {
        $accessibilityKey = "monument_language_accessibility_{$language}";
        return explode(',', $data[$accessibilityKey]);
    }

    private function parseUsedMaterials($data, $language)
    {
        $usedMaterialsKey = "monument_language_used_materials_{$language}";
        return explode(',', $data[$usedMaterialsKey]);
    }

    private function saveMonumentLanguages($monumentLanguages, $monument)
    {
        foreach ($monumentLanguages as $monumentLanguage) {
            $monumentLanguage->monument_id = $monument->id;
            $monumentLanguage->save();
        }
    }
}