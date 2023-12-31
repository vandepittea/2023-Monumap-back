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

    protected $_audioVisualSourceLanguageService;

    public function __construct(AudiovisualSource $model, AudioVisualSourceLanguageService $audioVisualSourceLanguageService)
    {
        parent::__construct($model);
        $this->_audioVisualSourceLanguageService = $audioVisualSourceLanguageService;
    }

    public function getOrCreateAudiovisualSource($audiovisualSourceData)
    {
        $this->validate($audiovisualSourceData);

        $audiovisualSourceResult = array_filter([
            'url' => $audiovisualSourceData['url'],
            'type' => $audiovisualSourceData['type']
        ]);

        $audiovisualSource = $this->_model->firstOrCreate($audiovisualSourceResult);

        $this->_audioVisualSourceLanguageService->getOrCreateAudiovisualSourceLanguage($audiovisualSourceData['audiovisual_source_language'], $audiovisualSource);

        return $audiovisualSource;
    }

    public function deleteUnusedAudiovisualSources()
    {
        $this->_model->doesntHave('monuments')->delete();
    }
}