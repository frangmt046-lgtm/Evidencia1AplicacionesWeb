<?php

namespace App\Models;


use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    use HasFactory, Notifiable;

    /**
     *
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    /**
     *
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     *
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function evidencePhotos()
    {
        return $this->hasMany(EvidencePhoto::class);
    }


    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }

    /**
     * Name of the user's role, or null when it has none.
     */
    public function roleName(): ?string
    {
        return $this->role?->name;
    }

    /**
     * true when the user has one of the given roles.
     * Example: $user->hasRole('Sales', 'Administrator')
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->roleName(), $roles, true);
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(Role::ADMINISTRATOR);
    }

    public function canManageOrders(): bool
    {
        return $this->hasRole(...config('halcon.orders.manage'));
    }

    public function editableOrderFields(Order $order): array
    {
        $fields = config('halcon.orders.editable_fields.'.$this->roleName(), []);

        if ($order->trashed()) {
            $fields = array_values(array_diff($fields, ['status']));
        }

        if (in_array('status', $fields, true) && $this->allowedOrderStatuses($order) === []) {
            $fields = array_values(array_diff($fields, ['status']));
        }

        return $fields;
    }

    public function canEditOrderField(Order $order, string $field): bool
    {
        return in_array($field, $this->editableOrderFields($order), true);
    }

    public function allowedOrderStatuses(Order $order): array
    {
        $next = $order->nextStatus();
        $targets = config('halcon.orders.status_targets.'.$this->roleName(), []);

        return $next !== null && in_array($next, $targets, true) ? [$next] : [];
    }
}
