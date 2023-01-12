<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'slug',
        'parent_id',
        'meta',
        'header',
        'lang',
    ];

    public function parent() {
        if ($this->parent_id) {
            return Page::where('id', $this->parent_id)->first();
        }
        return null;
    }

    public function hasChild() {
        if (Page::where('parent_id', $this->id)->count() > 0) {
            return true;
        }
        return false;
    }

    public function getChild() {
        return Page::where('parent_id', $this->id)->get();
    }
}
