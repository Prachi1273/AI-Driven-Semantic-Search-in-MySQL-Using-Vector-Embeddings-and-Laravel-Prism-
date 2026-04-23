<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = strtolower($request->query('query'));

        // ✅ Validate query
        if (!$query) {
            return response()->json([
                'error' => 'Query parameter is required. Example: /search?query=cheap phone'
            ], 400);
        }

        // ✅ Generate FAKE embedding (NO API)
        $queryEmbedding = $this->fakeEmbedding($query);

        // ✅ Fetch products
        $products = Product::whereNotNull('embedding')->get();

        $results = $products->map(function ($product) use ($queryEmbedding, $query) {

            // ✅ INTENT FILTER (🔥 NEW)
            if (!$this->isRelevant($query, $product)) {
                return null;
            }

            $productEmbedding = json_decode($product->embedding, true);

            // ✅ Skip invalid data
            if (!$productEmbedding || !is_array($productEmbedding)) {
                return null;
            }

            $similarity = $this->cosineSimilarity(
                $queryEmbedding,
                $productEmbedding
            );

            return [
                'title' => $product->title,
                'description' => $product->description,
                'score' => round($similarity, 3)
            ];
        })->filter();

        // ✅ RETURN ONLY BEST MATCH
        return response()->json(
            $results
                ->sortByDesc('score')
                ->take(1) // 🔥 ONLY TOP RESULT
                ->values()
                ->map(function ($item, $index) {
                    $item['rank'] = 1;
                    return $item;
                })
        );
    }

    /**
     * ✅ INTENT FILTER LOGIC (SMART MATCHING)
     */
    private function isRelevant($query, $product)
    {
        $text = strtolower($product->title . ' ' . $product->description);

        $keywords = [
            'phone' => ['phone', 'smartphone', 'android', 'mobile'],
            'laptop' => ['laptop', 'computer'],
            'watch' => ['watch', 'smartwatch'],
            'earbuds' => ['earbuds', 'headphones', 'audio']
        ];

        foreach ($keywords as $intent => $words) {
            if (str_contains($query, $intent)) {
                foreach ($words as $word) {
                    if (str_contains($text, $word)) {
                        return true;
                    }
                }
                return false; // intent found but product doesn't match
            }
        }

        return true; // fallback if no intent detected
    }

    /**
     * ✅ FAKE EMBEDDING GENERATOR (DEMO SAFE)
     */
    public function fakeEmbedding($text)
    {
        $hash = md5(strtolower($text));
        $vector = [];

        for ($i = 0; $i < 50; $i++) {
            $char = $hash[$i % strlen($hash)];
            $vector[] = (ord($char) % 10) / 10;
        }

        return $vector;
    }

    /**
     * ✅ COSINE SIMILARITY
     */
    private function cosineSimilarity($vec1, $vec2)
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;

        $length = min(count($vec1), count($vec2));

        for ($i = 0; $i < $length; $i++) {
            $dot += $vec1[$i] * $vec2[$i];
            $normA += $vec1[$i] * $vec1[$i];
            $normB += $vec2[$i] * $vec2[$i];
        }

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
