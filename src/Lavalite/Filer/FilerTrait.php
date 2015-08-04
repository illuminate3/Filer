<?php namespace Lavalite\Filer;

use Session;
use Filer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Request;
Use Input;

trait FilerTrait
{

    /**
    *
    * Root folder for uploading files
    *
    **/
    protected $uploadRootFolder;

    /**
    *
    * Upload field variable.
    *
    **/
    public $uploads    = [];

    public static function upload($model)
    {
            if (empty($model->uploads)) return;
            if (isset($model->uploads['single'])) $model->uploadSingle();
            if (isset($model->uploads['multiple'])) $model->uploadMultiple();
    }

    public function uploadSingle()
    {
        foreach ($this->uploads['single'] as $field) {
            $file = array();
            if (Request::hasFile($field)) {
                $upfile = Request::file($field);
                if ($upfile instanceof  UploadedFile)
                    $file   = Filer::upload($upfile, $this->upload_folder . '/' . $field);
           }
           $this->setFileSingle($field, $file);
        }
    }

    public function uploadMultiple()
    {
        foreach ($this->uploads['multiple'] as $field) {

            $files = array();
            if (is_array(Request::file($field))) {
                foreach (Request::file($field) as $file) {
                    if ($file instanceof  UploadedFile)
                        $files[]  = Filer::upload($file, $this->upload_folder. '/' . $field);
                }
            }
            $this -> setFileMultiple($field, $files);
        }
    }

    /**
     * @param $value
     * @return string - path to the upload folder
     */
    public function getUploadFolderAttribute($value)
    {
        if (!empty($value)){
            Session::put('upload.'.$this->table.'.upload_folder', $value);
            return $value;
        } else {
            $uploadFolder   = $this->getUploadRootFolder();
            $this->attributes['upload_folder']   = $uploadFolder;
            Session::put('upload.'.$this->table.'.upload_folder', $uploadFolder);
            return $uploadFolder;
        }
    }

    /**
     * Return upload forder for the table
     *
     * @return void
     * @author 
     **/
    public function getUploadRootFolder()
    {
        if (!empty($this->uploadRootFolder))
            return $this->uploadRootFolder . '/' . date("Y/m/d/His").rand(100,999);

        return 'uploads/'. $this->table . '/' . date("Y/m/d/His").rand(100,999);
    }

    /**
     * @param $value
     * @return string - path to the upload folder
     */
    public function getUploadURL($field, $file ='file')
    {
        $this->upload_folder;
        return 'upload/' . $this->table . '/' . $field . '/' . $file;
    }

    public function removeFile($id, $field, $no)
    {
        $row    = $this->find($id);
        $value  = $row->getFile($field);
        if ($this->isSingle($field)) {
            @unlink(public_path() . $value['folder'] . $value['file'] );
            $value  = array();
        } elseif ($this->isMultiple($field)) {
            @unlink(public_path() . $value[$no]['folder'] . $value[$no]['file'] );
            unset($value[$no]);
        }
        $row->setAttribute($field, json_encode($value));

        return $row -> saveAfterImageRemoved();
    }

    public function saveAfterImageRemoved($options = array()){
        if (parent::save($options))
        {
            return $this->saveTranslations();
        }
    }

    public function isSingle($field)
    {
        return in_array($field, $this->uploads['single']);
    }

    public function isMultiple($field)
    {
        return in_array($field, $this->uploads['multiple']);
    }

    public function getFile($field)
    {
        $value  = $this->$field;
        if (is_array($value)) return $value;
        if ($value == '') return array();
        return  json_decode($value);
    }
 
    public function setFileSingle($field, $value)
    {
        if (Session::has('upload.'.$this->table.'.'.$field)){
            $value      = Session::pull('upload.'.$this->table.'.'.$field);
            $value      = end($value);
        } elseif (!empty($value)){
            $value      = $value;
        } elseif (Input::has($field)) {
            $value       = Input::get($field); 
        } else {
            return;
        }

        if (empty($value)){
            $value = array();
        }
        
        $this -> setAttribute($field, $value);
    }

    public function setFileMultiple($field, $current)
    {
        if (empty($current)) {
            $current = array();
        }

        $session    = array();
        if (Session::has('upload.'.$this->table.'.'.$field)){
            $session    = Session::pull('upload.'.$this->table.'.'.$field);
        }

        if (empty($current) && empty($session) && !Input::has($field))
            return ;

        if (Input::has($field))
            $prev       = Input::get($field);
        else
            $prev       = $this->getOriginalFile($field);

        if (empty($prev)){
            $prev = array();
        }

        $value      = array_merge($prev, $current, $session);

        $this->setAttribute($field, $value);
    }

    public function resetFile($field)
    {
        $value       = $this->getOriginalFile($field);

        $this->setAttribute($field, $value);
    }

    public function getOriginalFile($field)
    {
        $original = parent::getOriginal($field);
        return json_decode($original);
    }
}