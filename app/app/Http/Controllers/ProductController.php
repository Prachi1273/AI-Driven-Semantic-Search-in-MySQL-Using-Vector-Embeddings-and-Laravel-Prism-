<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism;
use App\Models\Product;
class ProductController extends Controller
{
 public function store(Request $request)
	{
	    $text = $request->description;

	    $embedding = Prism::embeddings()
		->using('openai')
		->create($text)
		->embedding;

	    Product::create([
		'title' => $request->title,
		'description' => $text,
		'embedding' => $embedding
	    ]);

	    return "Product stored with embedding.";
	}
  
}

