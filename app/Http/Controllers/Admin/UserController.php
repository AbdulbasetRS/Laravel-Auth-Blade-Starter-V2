<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /** Blade page shell — the table itself loads its rows via XHR from data(). */
    public function index(): View
    {
        return view('admin.users.index');
    }

    /** XHR JSON endpoint — search / status / verified / date range / sort / pagination. */
    public function data(Request $request): JsonResponse
    {
        $users = $this->users->getFiltered($request->only([
            'search', 'status', 'verified', 'date_from', 'date_to', 'sort', 'per_page',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Intentionally left minimal — Create User form/validation is a
        // separate approved requirement, not part of this implementation pass.
        return redirect()->route('admin.users.index');
    }

    /** Server-side is the source of truth — the modal's item details are for visual review only. */
    public function destroy(User $user): JsonResponse
    {
        $this->users->delete($user);

        return response()->json([
            'success' => true,
            'message' => __('users.delete_success'),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', ['user' => $user]);
    }

    /**
     * Export placeholder. Wiring real Excel/CSV export needs the
     * maatwebsite/excel package (composer require maatwebsite/excel) —
     * not added yet since it wasn't part of this approved pass.
     */
    public function export(string $format, Request $request)
    {
        abort_unless(in_array($format, ['excel', 'csv'], true), 404);

        $filters = $request->only(['search', 'status', 'verified', 'date_from', 'date_to', 'sort']);
        $filename = 'users_'.now()->format('Y-m-d_His');

        return $format === 'excel'
            ? Excel::download(new UsersExport($filters), "{$filename}.xlsx")
            : Excel::download(new UsersExport($filters), "{$filename}.csv", ExcelFormat::CSV);
    }
}
