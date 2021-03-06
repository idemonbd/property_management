<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logName = 'expense';
    protected static $recordEvents = ['updated'];
    protected static $logAttributes = ['*','type.name','taker.name'];
    protected static $logAttributesToIgnore = ['updated_at','created_at','type_id','taker_id'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public static function nextId(int $increment = 1)
    {
        if (parent::withTrashed()->count()) {
            return parent::withTrashed()->get()->last()->id + $increment;
        }
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taker()
    {
        return $this->belongsTo(User::class,'taker_id');
    }

}
