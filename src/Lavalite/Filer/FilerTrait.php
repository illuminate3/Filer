<?php namespace  Lavalite\Filer;

use Input;
use File;
use Filer;

trait FilerTrait
{
    public function file() {
        return $this->morphMany('Lavalite\Filer\Models\Filer', 'of')->orderBy('default', 'DESC');
    }

    public function oneFile() {

      return $this->morphOne('Lavalite\Filer\Models\Filer', 'of')->where('default','=',1);

    }

    public function filer(){
        return $this -> file()->where('mimetype', 'NOT LIKE', 'image%')->get();
    }

    public function images(){

        return $this -> file()->where('mimetype', 'LIKE', 'image%')->get();
    }

    public function allFiler(){
        return $this -> file() -> get();
    }

    public function upload() {

        if (!isset($this->uploads)) return;
        if (isset($this->uploads['single'])) $this->uploadSingle();
        if (isset($this->uploads['multiple'])) $this->uploadMultiple();
        if (isset($this->uploads['nostore'])) $this->uploadNoTable();
    }

    public function uploadSingle() {

        foreach($this->uploads['single'] as $field) {

            if (Input::hasFile($field)) {

                $upfile   = Input::file($field);
                list($folder, $file) = Filer::upload($upfile, $this->getPackage(), $this->getModule(), $this->id, $field);
                    if (in_array($field, $this -> translatedAttributes)) {
                        $this -> setAttribute($field,  $folder.$file);
                    } else {
                        $this -> $field = $folder.$file;
                    }
            }
        }
    }

    public function uploadMultiple() {
        foreach($this->uploads['multiple'] as $field) {

            if (Input::hasFile($field)  && is_array(Input::file($field))) {
                foreach (Input::file($field) as $file) {
                    Filer::insert($file, $this->getPackage(), $this->getModule(), $this->id, $field);
                }
            }
        }
    }

    public function uploadNoTable() {
        foreach($this->uploads['nostore'] as $field) {

            if (Input::hasFile($field)  && is_array(Input::file($field))) {
                foreach (Input::file($field) as $file) {
                    Filer::upload($file, $this->getPackage(), $this->getModule(), $this->id, $field);
                }
            }
        }
    }

    public function setDefault($type_id, $type_of, $id){

        \Lavalite\Filer\Models\Filer::where('of_id', '=', $type_id)
            ->where('of_type', '=', $type_of)
            ->update(array('default' => 0 ));

        \Lavalite\Filer\Models\Filer::where('of_id', '=', $type_id)
            ->where('id', '=', $id)
            ->where('of_type', '=', $type_of)
            ->update(array('default' => 1 ));
    }

     public function imageDelete($type_id, $of_type, $id){

        \Lavalite\Filer\Models\Filer::where('of_id', '=', $type_id)
            ->where('id', '=', $id)
            ->where('of_type', '=', $of_type)
            ->delete();

     }
     public function imageEdit($type_id, $of_type, $id){

        return \Lavalite\Filer\Models\Filer::where('of_id', '=', $type_id)
            ->where('id', '=', $id)
            ->where('of_type', '=', $of_type);

     }
}