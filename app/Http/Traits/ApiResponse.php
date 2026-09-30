<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function ok(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function fail(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $status);
    }

    /** Daftar berpaginasi. $resource = kelas Resource (opsional). */
    protected function paged(LengthAwarePaginator $page, ?string $resource = null, string $message = 'OK'): JsonResponse
    {
        $items = $resource
            ? $resource::collection($page->getCollection())->resolve()
            : $page->getCollection();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    protected function perPage(\Illuminate\Http\Request $request): int
    {
        return max(1, min((int) $request->query('per_page', 50), 100));
    }
}