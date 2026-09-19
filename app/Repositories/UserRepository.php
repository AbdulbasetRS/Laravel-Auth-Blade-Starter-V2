<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserRepository implements UserRepositoryInterface
{
    public function getFiltered(array $filters = []): LengthAwarePaginator
    {
        $query = $this->applyFilters(User::query(), $filters);

        $perPage = (int) ($filters['per_page'] ?? 10);

        return $query->paginate($perPage > 0 ? $perPage : 10);
    }

    public function allFiltered(array $filters = []): Collection
    {
        return $this->applyFilters(User::query(), $filters)->get();
    }

    /**
     * Shared filter/sort logic for both getFiltered() (paginated, table XHR)
     * and allFiltered() (unpaginated, Export) — keeps them from drifting apart.
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['verified'])) {
            $filters['verified'] === 'yes'
                ? $query->whereNotNull('email_verified_at')
                : $query->whereNull('email_verified_at');
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest'    => $query->oldest(),
            'name_asc'  => $query->orderBy('username', 'asc'),
            'name_desc' => $query->orderBy('username', 'desc'),
            default     => $query->latest(),
        };

        return $query;
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * Create a new user along with their profile and optional avatar.
     * Password is hashed automatically via the model's 'hashed' cast.
     * Slug is auto-generated from username.
     */
    public function store(array $data): User
    {
        $profileData = $data['profile'] ?? [];
        unset($data['profile']);

        // Auto-generate a unique slug from username
        $data['slug'] = $this->generateUniqueSlug($data['username'] ?? '');

        // Track who created the record
        $actorId = Auth::id();
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        // Handle avatar file upload — stored separately, path goes on the profile
        $avatarPath = null;
        if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            $avatarPath = $data['avatar']->store('avatars', 'public');
            unset($data['avatar']);
        } else {
            unset($data['avatar']);
        }

        $user = User::create($data);

        // Create the linked profile record
        $profileData['user_id']    = $user->id;
        $profileData['created_by'] = $actorId;
        $profileData['updated_by'] = $actorId;
        if ($avatarPath !== null) {
            $profileData['avatar'] = $avatarPath;
        }

        $user->profile()->create($profileData);

        return $user->fresh(['profile']);
    }

    public function update(User $user, array $data): User
    {
        $password = $data['password'] ?? null;

        unset($data['password']);

        $user->fill($data);

        if (! empty($password)) {
            $user->password = $password;
        }

        $user->save();

        return $user;
    }

    /**
     * Generate a unique slug by appending a numeric suffix when collisions occur.
     */
    protected function generateUniqueSlug(string $base): string
    {
        $slug      = Str::slug($base);
        $candidate = $slug;
        $counter   = 1;

        while (User::where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}