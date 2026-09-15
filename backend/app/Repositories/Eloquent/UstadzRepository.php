<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Ustadz;
use App\Repositories\Contracts\UstadzRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

readonly class UstadzRepository implements UstadzRepositoryInterface
{
    /**
     * Get paginated Ustadz list with search and status filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;

        return Ustadz::with('user.roles')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', '%' . $search . '%')
                        ->orWhere('nik_hash', hash_sensitive($search))
                        ->orWhere('nigm', 'like', '%' . $search . '%')
                        ->orWhere('kode_ustadz', 'like', '%' . $search . '%');
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'aktif') {
                    return $query->where('is_active', true);
                } elseif ($status === 'tidak_aktif') {
                    return $query->where('is_active', false);
                }
                return $query;
            })
            ->orderBy('nigm', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get available Users for creating Ustadz profile.
     */
    public function getAvailableUsersForCreate(): Collection
    {
        return User::doesntHave('ustadz')->orderBy('name', 'asc')->get();
    }

    /**
     * Get available Users for editing Ustadz profile.
     */
    public function getAvailableUsersForEdit(?int $currentUserId = null): Collection
    {
        return User::whereDoesntHave('ustadz')
            ->when($currentUserId, function ($query) use ($currentUserId) {
                $query->orWhere('id', $currentUserId);
            })
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Find Ustadz by ID.
     */
    public function findById(int $id): ?Ustadz
    {
        return Ustadz::findOrFail($id);
    }

    /**
     * Create new Ustadz record.
     */
    public function create(array $data): Ustadz
    {
        return Ustadz::create($data);
    }

    /**
     * Update existing Ustadz record.
     */
    public function update(Ustadz $ustadz, array $data): bool
    {
        return $ustadz->update($data);
    }

    /**
     * Delete Ustadz record.
     */
    public function delete(Ustadz $ustadz): bool
    {
        return $ustadz->delete();
    }

    /**
     * Create new User account and assign 'ustadz' role.
     */
    public function createUserAccount(array $userData): User
    {
        $user = User::create($userData);
        $user->assignRole('ustadz');

        return $user;
    }

    /**
     * Update User account data.
     */
    public function updateUserAccount(User $user, array $userData): bool
    {
        return $user->update($userData);
    }

    /**
     * Find User by username.
     */
    public function findUserByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    /**
     * Find Ustadz by NIGM or NIK.
     */
    public function findByNigmOrNik(?string $nigm, ?string $nik): ?Ustadz
    {
        $ustadz = null;

        if ($nigm) {
            $ustadz = Ustadz::where('nigm', $nigm)->first();
        }

        if (!$ustadz && $nik) {
            $ustadz = Ustadz::whereNik($nik)->first();
        }

        return $ustadz;
    }
}
