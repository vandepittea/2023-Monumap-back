<?php
namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use Illuminate\Validation\Rule;
use App\Models\AudiovisualSource;
use Illuminate\Support\Facades\Log;

class AudioVisualSourceService extends Service
{
        protected $_rules = [
            'title' => 'required|string',
            'url' => 'required|url',
            'type' => 'required|string|in:audio,video',
        ];    

        protected $_rulesTranslations = [
            'title' => 'required|string',
        ];  

        public function __construct(AudiovisualSource $model) {
            Parent::__construct($model);
        }  
        
        public function getOrCreateAudiovisualSource($audiovisualSourceData, $monument) //TODO: hier ook translation toepassen?
        {
            $this->checkValidation($audiovisualSourceData);

            $audiovisualSourceResult = array_filter([
                'title' => $audiovisualSourceData['title'],
                'url' => $audiovisualSourceData['url'],
                'type' => $audiovisualSourceData['type']
            ]);
        
            return $monument->audiovisualSource()->create($audiovisualSourceResult); //TODO: goed zo?
        }

        public function deleteUnusedAudiovisualSources($oldAudiovisualSourceId) {
            if ($oldAudiovisualSourceId != null)
            $this->_model->where('id', $oldAudiovisualSourceId)->whereDoesntHave('monuments')->delete();
        }
}