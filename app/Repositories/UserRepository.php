<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    public function store(array $data): User
    {
        return User::create($data);
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
}