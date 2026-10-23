<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\ReportCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = ReportCategory::with('department:id,name,code')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return CategoryResource::collection($categories);
    }
}
