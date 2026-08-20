<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Filtering, sorting, pagination — everything the Users table XHR needs.
     *
     * Expected $filters keys: search, status, verified, date_from, date_to, sort, per_page
     */
    public function getFiltered(array $filters = []): LengthAwarePaginator;

    /**
     * Delete a user.
     */
    public function delete(User $user): bool;

    /**
     * Create a new user. The 'password' key is hashed automatically by the
     * model's 'hashed' cast.
     */
    public function store(array $data): User;

    /**
     * Update a user's profile data. A non-empty 'password' key is re-hashed;
     * an empty/missing password keeps the current one unchanged.
     */
    public function update(User $user, array $data): User;
}
