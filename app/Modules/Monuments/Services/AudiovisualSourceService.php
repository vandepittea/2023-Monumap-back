<?php
namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use Illuminate\Validation\Rule;
use App\Models\AudiovisualSource;

class AudioVisualSourceService extends Service
{
        protected $_rules = [
            'title' => 'required|string',
            'url' => 'required|url',
            'type' => 'required|string|in:audio,video',
        ];    

        public function __construct(AudiovisualSource $model) {
            Parent::__construct($model);
        }  
        
        public function getOrCreateAudiovisualSource($audiovisualSourceData)
        {
            $this->validate($audiovisualSourceData);

            if ($this->hasErrors()) {
                return;
            }

            $audiovisualSourceResult = array_filter([
                'title' => $audiovisualSourceData['title'],
                'url' => $audiovisualSourceData['url'],
                'type' => $audiovisualSourceData['type']
            ]);
        
            $audiovisualSource = $this->_model->firstOrCreate($audiovisualSourceResult);
        
            return $audiovisualSource;
        }

        public function deleteUnusedAudiovisualSources($oldAudiovisualSourceId) {
            $this->_model->where('id', $oldAudiovisualSourceId)->whereDoesntHave('monuments')->delete();
        }
}