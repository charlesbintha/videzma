<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\ServiceComment;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    /**
     * Liste des commentaires d'une demande de service.
     */
    public function index(Request $request, ServiceRequest $serviceRequest)
    {
        $driver = $request->user();

        if ($serviceRequest->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $comments = $serviceRequest->comments()
            ->with('author:id,name,role')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $comments->map(fn ($comment) => [
                'id' => $comment->id,
                'content' => $comment->content,
                'is_internal' => $comment->is_internal,
                'author' => $comment->author ? [
                    'id' => $comment->author->id,
                    'name' => $comment->author->name,
                    'role' => $comment->author->role,
                ] : null,
                'created_at' => $comment->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Ajouter un commentaire a une demande de service.
     */
    public function store(Request $request, ServiceRequest $serviceRequest)
    {
        $driver = $request->user();

        if ($serviceRequest->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        $comment = ServiceComment::create([
            'service_request_id' => $serviceRequest->id,
            'author_id' => $driver->id,
            'content' => $validated['content'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'is_internal' => $comment->is_internal,
                'author' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                ],
                'created_at' => $comment->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
