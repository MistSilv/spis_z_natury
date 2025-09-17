<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    

    

    /**
     * Atrybuty, które mogą być masowo przypisywane.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Ukryte atrybuty przy serializacji.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Rzutowania atrybutów.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relacje.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function produktSkany()
    {
        return $this->hasMany(ProduktSkany::class);
    }

    public function spisZNatury()
    {
        return $this->hasMany(SpisZNatury::class);
    }

    public function spisProdukty()
    {
        return $this->hasMany(SpisProdukty::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{

}

