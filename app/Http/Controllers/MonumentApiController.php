<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Movies\Services\MonumentService;


class MonumentApiController extends Controller
{
    private $_service;
    public function __construct(MonumentService $service){
        $this->_service = $service;
    }

    public function getAllMonuments(Request $request) {
        $pages = $request->get("pages", 10); 
    
        $parameter = $this->checkForQueryParameter($request);
    
        return $this->_service->getAllMonuments($pages, $parameter['name'], $parameter['value']);
    }
    
    private function checkForQueryParameter($request) {
        $allowedParams = ['name', 'year', 'designer', 'cost', 'language']; 
        $queryParameters = $request->query();
        $result = [];
    
        foreach ($queryParameters as $key => $value) {
            if (in_array($key, $allowedParams)) {
                $result['name'] = $key;
                $result['value'] = $value;
                return $result;
            }
        }
        return null; 
    }

    public function getOneMonument($id){
        return $this->_service->getOneMonument($id);
    }

    public function updateMonument($id, Request $request){
        $data = $request->all();
        return $this->_service->updateMonument($id, $data);
    }
}

