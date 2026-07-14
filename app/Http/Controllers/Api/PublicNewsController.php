<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicNewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $kategori = $request->query('kategori');

        $query = Berita::published()->orderBy('published_at', 'desc');

        if ($kategori) {
            // we don't have kategori column in the schema yet, but if needed we can add later.
            // for now just ignore or add to schema.
        }

        $news = $query->paginate($limit);

        $data = collect($news->items())->map(function ($item) {
            return [
                'id' => $item->id_berita,
                'title' => $item->judul,
                'slug' => $item->slug,
                'excerpt' => $item->ringkasan,
                'content' => $item->konten,
                'image_url' => $item->gambar,
                'source' => $item->sumber,
                'is_featured' => $item->unggulan,
                'published_at' => $item->published_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'total' => $news->total(),
            ]
        ]);
    }

    public function show($slug): JsonResponse
    {
        $item = Berita::published()->where('slug', $slug)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id_berita,
                'title' => $item->judul,
                'slug' => $item->slug,
                'excerpt' => $item->ringkasan,
                'content' => $item->konten,
                'image_url' => $item->gambar,
                'source' => $item->sumber,
                'is_featured' => $item->unggulan,
                'published_at' => $item->published_at->toIso8601String(),
            ]
        ]);
    }
}
