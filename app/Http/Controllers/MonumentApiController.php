<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Monuments\Services\MonumentService;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use Illuminate\Validation\ValidationException;

class MonumentApiController extends Controller
{
    private $_service;

    public function __construct(MonumentService $service){
        $this->_service = $service;
    }

    public function getAllMonuments(Request $request) {
        $perPage = $request->get('perPage', 10);
        $page = $request->get('page', 1);
        $name = $request->query('name');
        $type = $request->query('type');
        $year = $request->query('year');
        $designer = $request->query('designer');
        $cost = $request->query('cost');
        $language = $request->query('language');

        $name = mb_strtolower($name, 'UTF-8');
        $designer = mb_strtolower($designer, 'UTF-8');
        $language = mb_strtolower($language, 'UTF-8');
    
        return $this->_service->getAllMonuments($perPage, $page, $name, $type, $year, $designer, $cost, $language);
    }

    public function addMonument(Request $request)
    {    
        try {
            $data = $request->all();

            $monument = $this->_service->addMonument($data);
            $monument->load('location', 'dimensions', 'images', 'audiovisualSource', 'monumentLanguage', 'images.imageLanguage', 'audiovisualSource.audiovisualSourceLanguage');

            return response()->json($monument);
        } catch (AlreadyExistsException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatus());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        }
    }

    public function getOneMonument(Request $request) {
        $id = $request->route('id');
        $language = $request->query('language');

        try {
            return $this->_service->getOneMonument($id, $language);
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatus());
        }
    }    

    public function updateMonument($id, Request $request){
        try {
            $data = $request->all();
            
            $monument = $this->_service->updateMonument($id, $data);
            $monument->load('location', 'dimensions', 'images', 'audiovisualSource', 'monumentLanguage', 'images.imageLanguage', 'audiovisualSource.audiovisualSourceLanguage');

            return response()->json($monument);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], $e->status);
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatus());
        }
    }

    public function deleteMonument($id){
        try {
            $this->_service->deleteMonument($id);

            return response()->json([
                'message' => "Monument deleted."
            ]);
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatus());
        }
    }

    public function deleteMultipleMonuments($ids)
    {
        try {
            $this->_service->deleteMultipleMonuments($ids);

            return response()->json([
                'message' => "Monuments deleted."
            ]);
        } catch (NotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatus());
        }
    }      
}
