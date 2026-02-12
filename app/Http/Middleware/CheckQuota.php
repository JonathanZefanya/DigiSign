<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckQuota
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->subscriptionPlan) {
            return $next($request);
        }

        // Check document quota for document creation
        if ($request->routeIs('documents.store') || $request->routeIs('documents.create')) {
            if ($user->hasExceededDocumentQuota()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Document limit reached',
                        'message' => 'You have reached your monthly document limit. Please upgrade your plan.',
                        'quota' => [
                            'current' => $user->documents_count_current_month,
                            'limit' => $user->subscriptionPlan->max_documents_per_month,
                        ]
                    ], 403);
                }

                return redirect()->back()->with('error', 
                    'You have reached your monthly document limit (' . 
                    $user->subscriptionPlan->max_documents_per_month . 
                    ' documents). Please upgrade your plan to continue.'
                );
            }
        }

        // Check category quota for category creation
        if ($request->routeIs('categories.store') || $request->routeIs('categories.create')) {
            if ($user->hasExceededCategoryQuota()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Category limit reached',
                        'message' => 'You have reached your category limit. Please upgrade your plan.',
                        'quota' => [
                            'current' => $user->categories()->count(),
                            'limit' => $user->subscriptionPlan->max_categories,
                        ]
                    ], 403);
                }

                return redirect()->back()->with('error', 
                    'You have reached your category limit (' . 
                    $user->subscriptionPlan->max_categories . 
                    ' categories). Please upgrade your plan to continue.'
                );
            }
        }

        // Check storage quota for file uploads
        if ($request->hasFile('document') || $request->hasFile('file')) {
            $file = $request->file('document') ?? $request->file('file');
            $fileSizeKb = (int) ceil($file->getSize() / 1024);

            if ($user->hasExceededStorageQuota($fileSizeKb)) {
                $limitMb = $user->subscriptionPlan->storage_limit_mb;
                $usedMb = round($user->storage_used_kb / 1024, 2);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Storage limit exceeded',
                        'message' => 'Uploading this file would exceed your storage limit. Please upgrade your plan.',
                        'quota' => [
                            'used_mb' => $usedMb,
                            'limit_mb' => $limitMb,
                            'file_size_mb' => round($fileSizeKb / 1024, 2),
                        ]
                    ], 403);
                }

                return redirect()->back()->with('error', 
                    'Uploading this file would exceed your storage limit (' . 
                    $limitMb . ' MB). Current usage: ' . $usedMb . ' MB. Please upgrade your plan.'
                );
            }
        }

        return $next($request);
    }
}
