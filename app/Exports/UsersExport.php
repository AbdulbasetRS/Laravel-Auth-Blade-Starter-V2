<?php

namespace App\Exports;

use App\Repositories\Contracts\UserRepositoryInterface;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private readonly array $filters = []) {}

    public function collection()
    {
        return app(UserRepositoryInterface::class)->allFiltered($this->filters);
    }

    public function headings(): array
    {
        return [
            __('users.column_user'),
            'Email',
            __('users.column_status'),
            __('users.column_verified'),
            __('users.column_joined'),
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->status === 'active' ? __('users.active') : __('users.inactive'),
            $user->email_verified_at ? __('users.yes') : __('users.no'),
            optional($user->created_at)->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}