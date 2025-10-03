<?php

namespace Modules\Users\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Users\Database\factories\UserGroupFactory;

class UserGroup extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'user_group';
    protected $fillable = ['name', 'saved_by', 'date'];
    public $timestamps = false;
}
