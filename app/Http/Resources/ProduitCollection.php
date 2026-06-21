<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProduitCollection extends ResourceCollection
{
   public $collects = ProduitResource::class;
 
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data'    => $this->collection,
            'meta'    => [
                'total'        => $this->total(),
                'per_page'     => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page'    => $this->lastPage(),
                'from'         => $this->firstItem(),
                'to'           => $this->lastItem(),
            ],
        ];
    }
}
