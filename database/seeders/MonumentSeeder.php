<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Monuments\Services\MonumentService;
use Illuminate\Support\Facades\Log;

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
        $jsonMonuments = json_encode($monuments, JSON_PRETTY_PRINT);
        Log::info($jsonMonuments);
        fclose($file);
    
        $this->_service->addMultipleMonuments($monuments);
    }

    protected function parseMonuments($file, $header)
    {
        $monuments = [];

        while (($data = fgetcsv($file)) !== false) {
            $data = array_combine($header, $data);

            $monumentData = [
                'year_of_construction' => (int) $data['year_of_construction'],
                'monument_designer' => $data['monument_designer'],
                'weight' => (int) $data['weight'],
                'cost_to_construct' => (float) $data['cost_to_construct'],
                'location' => [
                    'latitude' => (float) $data['latitude'],
                    'longitude' => (float) $data['longitude'],
                    'street' => $data['street'],
                    'number' => (int) $data['number'],
                    'city' => $data['city'],
                ],
                'dimensions' => $this->parseDimensions($data),
                'images' => $this->parseImages($data),
                'audiovisual_source' => $this->parseAudiovisualSource($data),
                'monument_language' => $this->parseMonumentLanguage($data),
            ];

            $monuments[] = $monumentData;
        }

        return $monuments;
    }

    protected function parseDimensions($data)
    {
        $height = (float) $data['height'];
        $width = (float) $data['width'];
        $depth = (float) $data['depth'];

        if ($height == null && $width == null && $depth == null) {
            return null;
        }

        return (object) [
            'height' => $height,
            'width' => $width,
            'depth' => $depth,
        ];
    }

    protected function parseImages($data)
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
                $image = [
                    'url' => $imageUrl,
                    'image_language' => [
                        [
                            'caption' => $captionDutch,
                            'language' => 'Dutch',
                        ],
                        [
                            'caption' => $captionEnglish,
                            'language' => 'English',
                        ],
                    ],
                ];

                $images[] = $image;
            }
        }

        return $images;
    }           

    protected function parseAudiovisualSource($data)
    {
        if (empty($data['audiovisual_source_url']) && empty($data['audiovisual_source_type']) && empty($data['audiovisual_source_language_title_dutch']) && empty($data['audiovisual_source_language_title_english'])) {
            return null;
        }

        return [
            'url' => $data['audiovisual_source_url'],
            'type' => $data['audiovisual_source_type'],
            'audiovisual_source_language' => [
                [
                    'title' => $data['audiovisual_source_language_title_dutch'],
                    'language' => 'Dutch',
                ],
                [
                    'title' => $data['audiovisual_source_language_title_english'],
                    'language' => 'English',
                ],
            ],
        ];
    }

    protected function parseAccessibility($data, $language)
    {
        $accessibilityKey = "monument_language_accessibility_{$language}";
        return explode(',', $data[$accessibilityKey]);
    }

    protected function parseUsedMaterials($data, $language)
    {
        $usedMaterialsKey = "monument_language_used_materials_{$language}";
        return explode(',', $data[$usedMaterialsKey]);
    }

    protected function parseMonumentLanguage($data)
    {
        return [
            [
                'language' => 'Dutch',
                'name' => $data['monument_language_name_dutch'],
                'description' => $data['monument_language_description_dutch'],
                'historical_significance' => $data['monument_language_historical_significance_dutch'],
                'type' => $data['monument_language_type_dutch'],
                'accessibility' => $this->parseAccessibility($data, 'dutch'),
                'used_materials' => $this->parseUsedMaterials($data, 'dutch'),
            ],
           [
                'language' => 'English',
                'name' => $data['monument_language_name_english'],
                'description' => $data['monument_language_description_english'],
                'historical_significance' => $data['monument_language_historical_significance_english'],
                'type' => $data['monument_language_type_english'],
                'accessibility' => $this->parseAccessibility($data, 'english'),
                'used_materials' => $this->parseUsedMaterials($data, 'english'),
            ],
        ];
    }
}
