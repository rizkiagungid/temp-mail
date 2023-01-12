<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'link',
        'target',
        'parent_id',
        'order',
        'status'
    ];

    public function hasChildAll() {
        if (Menu::where('parent_id', $this->id)->count() > 0) {
            return true;
        }
        return false;
    }

    public function hasChild() {
        if (Menu::where('parent_id', $this->id)->where('status', true)->count() > 0) {
            return true;
        }
        return false;
    }

    public function getChildAll() {
        return Menu::where('parent_id', $this->id)->orderBy('order', 'asc')->get();
    }

    public function getChild() {
        return Menu::where('parent_id', $this->id)->orderBy('order', 'asc')->where('status', true)->get();
    }
}
