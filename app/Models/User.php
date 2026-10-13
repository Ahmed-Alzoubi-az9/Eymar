<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Booking;
use App\Models\Message;
use App\Models\PortfolioItem;
use App\Models\ProviderSchedule;
use App\Models\Review;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'location',
        'business_name',
        'bio',
        'status',
        'username',
        'age',
        'address',
        'profile_image_path',
        'company_owner',
        'company_logo_path',
        'established_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'established_date' => 'date',
        ];
    }

    public function serviceProviderProfile(): HasOne
    {
        return $this->hasOne(ServiceProviderProfile::class);
    }

    public function hasRole(string $role): bool
    {
        return match ($role) {
            'contractor', 'craftsman' => $this->serviceProviderProfile?->provider_type === $role,
            default => $this->role === $role,
        };
    }

    public function isServiceProvider(): bool
    {
        return $this->serviceProviderProfile()->exists();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function customerServiceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(ServiceQuote::class, 'provider_id');
    }

    public function receivedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviewsReceived()->avg('rating');

        return $avg === null ? null : round((float) $avg, 1);
    }

    public function providerSchedules(): HasMany
    {
        return $this->hasMany(ProviderSchedule::class, 'provider_id');
    }

    public function customerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function providerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class, 'provider_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->whereNull('read_at')->count();
    }
}
