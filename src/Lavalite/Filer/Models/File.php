<?php namespace Lavalite\Filer\Models;

use Illuminate\Support\Str;

class Filer extends \Eloquent {

    protected $table = 'files';
    public $timestamps = true;
    protected $softDelete = true;
    public $fillable = ['name'];


    public function of() {
        return $this->morphTo();
    }

    /**
     * Name auto-mutator
     */

    public function setNameAttribute($value) {
        $this->attributes['name'] = Str::title($value);
    }

    public function files(){
        return $this ->where('mimetype', 'NOT LIKE', 'image/%')->get();
    }

    public function images(){
        return $this ->has('mimetype', 'LIKE', 'image/%')->get()->toArray();
    }

    public function allFiles(){

        return $this -> file()->get()->toArray();
    }


}