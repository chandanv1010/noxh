<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'title',
        'zalo',
        'public_email',
        'email',
        'password',
        'phone',
        'province_id',
        'district_id',
        'ward_id',
        'address',
        'birthday',
        'image',
        'description',
        'user_agent',
        'ip',
        'user_catalogue_id',
        'publish',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function user_catalogues(){
        return $this->belongsTo(UserCatalogue::class, 'user_catalogue_id', 'id');
    }

    public function hasPermission($permissionCanonical){
        return $this->user_catalogues->permissions->contains('canonical', $permissionCanonical);
    }

    /**
     * Cac du an nguoi nay duoc quan tri giao phu trach.
     *
     * Khac voi cot products.user_id (nguoi TAO ban ghi): mot du an do quan tri
     * tao van co the giao cho vai nhan vien, va mot nhan vien co the phu trach
     * nhieu du an.
     */
    public function duAnPhuTrach()
    {
        return $this->belongsToMany(Product::class, 'product_user', 'user_id', 'product_id')
            ->withPivot('order')
            ->withTimestamps();
    }

    /**
     * Nguoi nay co phai nhan vien kinh doanh khong.
     *
     * Doc co is_sale tren nhom thanh vien chu khong so id nhom: quan tri doi
     * ten nhom hay tao nhom sale thu hai thi ma nguon van dung.
     */
    public function laNhanVienKinhDoanh(): bool
    {
        // optional() vi tai khoan co the chua duoc xep nhom nao - doc thang
        // thuoc tinh tren null se sinh canh bao.
        return (bool) optional($this->user_catalogues)->is_sale;
    }


}
