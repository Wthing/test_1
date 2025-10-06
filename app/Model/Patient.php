<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 
 * @property string $first_name 
 * @property string $last_name 
 * @property string $birth_date 
 * @property string $gender 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Patient extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'patients';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'gender',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
