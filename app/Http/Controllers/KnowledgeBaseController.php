<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $articles = KnowledgeBase::where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'category', 'summary', 'created_at']);

        return response()->json([
            'articles' => $articles,
            'categories' => KnowledgeBase::distinct('category')->pluck('category')
        ]);
    }

    public function show($id)
    {
        $article = KnowledgeBase::where('id', $id)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->json($article);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:breeding,feeding,health,business,equipment',
            'summary' => 'required|string|max:255',
            'is_published' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $article = KnowledgeBase::create([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'summary' => $request->summary,
            'is_published' => $request->is_published ?? false,
            'author_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article
        ], 201);
    }

    public function search(Request $request)
    {
        $query = KnowledgeBase::where('is_published', true);

        if ($request->has('q')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%'.$request->q.'%')
                    ->orWhere('content', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $results = $query->get(['id', 'title', 'category', 'summary']);

        return response()->json($results);
    }
}
