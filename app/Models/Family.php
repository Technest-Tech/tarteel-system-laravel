<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = ['family_name', 'whatsapp_number', 'family_billing_link'];

    public function students()
    {
        return $this->hasMany(User::class, 'family_id')->where('user_type', User::USER_TYPE['student']);
    }
}
