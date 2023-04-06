<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Movies\Services\MonumentService;


class MonumentApiController extends Controller
{
    private $_service;
    public function __construct(MonumentService $service){
        $this->_service = $service
    }

    public function getAllMonuments(Request $request) {
        $pages = $request->get("pages", 10); 

        return $this->_service->getAllMonuments($pages)
    }
}
