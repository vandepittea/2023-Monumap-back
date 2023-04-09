<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Monuments\Services\MonumentService;

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

    public function addMonument(Request $request){
        $data = $request->all();
        $monument = $this->_service->addMonument($data);

        if($this->_service->hasErrors()){
            return ["errors" => $this->_service->getErrors()];
        }
        
        return $monument;
    }

    public function getOneMonument($id){
        if($this->_service->hasErrors()){
            return ["errors" => $this->_service->getErrors()];
        }

        return $this->_service->getOneMonument($id);

    }

    public function updateMonument($id, Request $request){
        $data = $request->all();
        return $this->_service->updateMonument($id, $data);
    }

    public function deleteMonument($id){
        $result = $this->_service->deleteMonument($id);
        if ($result) {
            return response()->json(['message' => 'Monument deleted successfully']);
        } else {
            return response()->json(['message' => 'Monument not found'], 404);
        }
    }

    public function deleteMultipleMonuments($ids)
    {
        $result = $this->_service->deleteMultipleMonuments($ids);
    
        if ($result) {
            return response()->json(['message' => 'Multiple monuments deleted successfully']);
        } else {
            return response()->json(['message' => 'Monuments not found'], 404);
        }
    }
            
}

