<?php
namespace App\Http\Controllers;

use App\Models\SatProductKey;
use Illuminate\Http\Request;

class SatCatalogController extends Controller
{
    public function searchProductKeys(Request $request)
    {
        $query = $request->input('query');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $results = SatProductKey::where('name', 'LIKE', "%{$query}%")
                                ->orWhere('code', 'LIKE', "%{$query}%")
                                ->limit(10)
                                ->get();

        return response()->json($results);
    }
}