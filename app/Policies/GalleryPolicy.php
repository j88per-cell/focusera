<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    /**
     * Determine whether the user can view any galleries.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Listing is allowed; controller filters public items
    }

    /**
     * Determine whether the user can view the gallery.
     */
    public function view(?User $user, Gallery $gallery): bool
    {
        // Admins can always view
        if ($user && $user->role_id === 3) {
            return true;
        }

        // Public galleries with no legacy access code are viewable
        $hasCode = is_string($gallery->access_code) && trim($gallery->access_code) !== '';
        if ($gallery->public && !$hasCode) {
            return true;
        }

        // If this session has unlocked the gallery via access code, ensure still valid
        $session = request()->session();
        $flagKey = 'gallery.access.' . $gallery->id;
        if ($session) {
            $flag = $session->get($flagKey, false);
            // Backward-compatible boolean unlock
            if ($flag === true) {
                return true;
            }
            // Structured unlock with code_id and optional expiry
            if (is_array($flag)) {
                try {
                    $codeId = $flag['code_id'] ?? null;
                    if ($codeId) {
                        /** @var \App\Models\GalleryAccessCode|null $code */
                        $code = \App\Models\GalleryAccessCode::query()
                            ->where('gallery_id', $gallery->id)
                            ->where('id', (int) $codeId)
                            ->first();
                        if ($code && (!$code->expires_at || $code->expires_at->isFuture())) {
                            return true;
                        }
                    }
                } catch (\Throwable $e) {
                    // fall through to deny
                }
            }
        }

        return false;
    }

    /**
     * Only admins can create galleries.
     */
    public function create(User $user): bool
    {
        return $user->role_id === 3;
    }

    /**
     * Only admins can update galleries.
     */
    public function update(User $user, Gallery $gallery): bool
    {
        return $user->role_id === 3;
    }

    /**
     * Only admins can delete galleries.
     */
    public function delete(User $user, Gallery $gallery): bool
    {
        return $user->role_id === 3;
    }
}
