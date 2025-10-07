<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 
 * @property int $patient_id 
 * @property string $doctor_name 
 * @property string $specialization 
 * @property string $date_time 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Appointment extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'appointments';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'patient_id',
        'doctor_name',
        'specialization',
        'date_time',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'patient_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }
}
