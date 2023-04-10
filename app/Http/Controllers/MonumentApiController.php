<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Monuments\Services\MonumentService;
use App\Exceptions\AlreadyExistsException;

class MonumentApiController extends Controller
{
    private $_service;
    public function __construct(MonumentService $service){
        $this->_service = $service;
    }

    public function getAllMonuments(Request $request) {
        $pages = $request->get('pages', 10);
    
        $type = $request->query('type');
        $year = $request->query('year');
        $designer = $request->query('designer');
        $cost = $request->query('cost');
        $language = $request->query('language');
    
        return $this->_service->getAllMonuments($pages, $type, $year, $designer, $cost, $language);
    }

    public function addMonument(Request $request)
    {    
        try {
            $data = $request->all();

            $monument = $this->_service->addMonument($data);

            return $monument;
        } catch (MOnumentAlreadyExists $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], $e->getStatusCode());
        }
    }    

    public function getOneMonument($id) {
        try {
            return $this->_service->getOneMonument($id);
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }    

    public function updateMonument($id, Request $request){
        try {
            $data = $request->all();
            $monument = $this->_service->updateMonument($id, $data);
            
            return $monument;
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], $e->getStatusCode());
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    public function deleteMonument($id){
        try {
            $this->_service->deleteMonument($id);
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    public function deleteMultipleMonuments($ids)
    {
        try {
            $this->_service->deleteMultipleMonuments($ids);
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }          
}

