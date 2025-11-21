<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PickingWave extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'picking_waves';

    protected $fillable = [
        'wave_number',
        'do_ids',
        'status',
        'assigned_to',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'do_ids' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $dates = [
        'started_at',
        'completed_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the assigned operator
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get picking tasks for this wave
     */
    public function pickingTasks()
    {
        return $this->hasMany(PickingTask::class, 'wave_id');
    }

    /**
     * Get delivery orders
     */
    public function deliveryOrders()
    {
        return DeliveryOrder::whereIn('_id', $this->do_ids ?? [])->get();
    }

    /**
     * Start wave
     */
    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete wave
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if wave is fully completed
     */
    public function isFullyCompleted()
    {
        $tasks = $this->pickingTasks;
        foreach ($tasks as $task) {
            if ($task->status !== 'completed') {
                return false;
            }
        }
        return true;
    }
}

