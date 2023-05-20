<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use App\Modules\Monuments\Services\AudioVisualSourceLanguageService;
use App\Models\AudiovisualSource;

class AudioVisualSourceService extends Service
{
    protected $_rules = [
        'url' => 'required|url',
        'type' => 'required|string|in:audio,video',
    ];

    protected $audioVisualSourceLanguageService;

    public function __construct(AudiovisualSource $model, AudioVisualSourceLanguageService $audioVisualSourceLanguageService)
    {
        parent::__construct($model);
        $this->audioVisualSourceLanguageService = $audioVisualSourceLanguageService;
    }

    public function getOrCreateAudiovisualSource($audiovisualSourceData, $monument)
    {
        $this->checkValidation($audiovisualSourceData);

        $audiovisualSourceResult = array_filter([
            'url' => $audiovisualSourceData['url'],
            'type' => $audiovisualSourceData['type']
        ]);

        $audiovisualSource = $monument->audiovisualSource()->create($audiovisualSourceResult);

        $this->getOrCreateAudiovisualSourceTranslations($audiovisualSource, $audiovisualSourceData['translations']);

        return $audiovisualSource;
    }

    public function deleteUnusedAudiovisualSources($oldAudiovisualSourceId)
    {
        if ($oldAudiovisualSourceId) {
            $unusedAudiovisualSource = $this->_model
                ->where('id', $oldAudiovisualSourceId)
                ->whereDoesntHave('monuments')
                ->first();
    
            if ($unusedAudiovisualSource) {
                $this->audioVisualSourceLanguageService->deleteTranslations($unusedAudiovisualSource);
                $unusedAudiovisualSource->delete();
            }
        }
    }    

    protected function getOrCreateAudiovisualSourceTranslations($audiovisualSource, $translations)
    {
        foreach ($translations as $translationData) {
            $this->audioVisualSourceLanguageService->getOrCreateAudiovisualSourceLanguage($translationData, $audiovisualSource);
        }
    }
}