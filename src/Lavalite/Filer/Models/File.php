<?php namespace Lavalite\Filer\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
class Filer extends \Eloquent {

    use SoftDeletingTrait;
    
    protected $table = 'files';
    public $timestamps = true;
    protected $SoftDeletingTrait = true;
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