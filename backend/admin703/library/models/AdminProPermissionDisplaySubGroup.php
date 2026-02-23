<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class AdminProPermissionDisplaySubGroup extends Model {

    protected $primaryKey  = "id";

    protected $table = "admin_pro_permission_display_sub_groups";

    public $timestamps = false;

    protected $fillable = [
        'name', 'status'
    ];

    public function permission()
    {
        return $this->hasMany(AdminProPermission::class,'display_sub_group_id','id')->where('status','Active')->orderBy('display_order');
    }

}