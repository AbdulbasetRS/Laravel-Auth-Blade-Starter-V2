<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'username'       => $this->username,
            'email'          => $this->email,
            'mobile_number'  => $this->mobile_number,
            'national_id'    => $this->national_id,
            'nationality'    => $this->nationality,
            'passport_number'=> $this->passport_number,
            'status'         => $this->status?->value,
            'status_label'   => $this->status?->label(),
            'status_color'   => $this->status?->color(),
            'type'           => $this->type?->value,
            'type_label'     => $this->type?->label(),
            'type_color'     => $this->type?->color(),
            'credits'        => $this->credits,
            'can_login'      => $this->can_login,
            'status_details' => $this->status_details,
            'role_id'        => $this->role_id,
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'created_at'     => $this->created_at?->toDateTimeString(),
            'updated_at'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}