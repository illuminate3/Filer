<?php namespace Lavalite\Filer;

use Input;
use File;
use Filer;
use Session;

trait FilerTrait
{


    public function upload()
    {
        if (!isset($this->uploads)) return;
        if (isset($this->uploads['single'])) $this->uploadSingle();
        if (isset($this->uploads['multiple'])) $this->uploadMultiple();

    }

    public function uploadSingle()
    {
        foreach ($this->uploads['single'] as $field) {
            $file = array();
            if (Input::hasFile($field)) {
                $upfile = Input::file($field);
                if (!is_null($upfile))
                $file   = Filer::upload($upfile, $this->upload_folder . '/' . $field);
            }
            $this->setFileSingle($field, $file);
        }
    }

    public function uploadMultiple()
    {
        foreach ($this->uploads['multiple'] as $field) {

            $files = array();
            if (is_array(Input::file($field))) {
                foreach (Input::file($field) as $file) {
                    if (!empty($file))
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
            $uploadFolder   = $this->uploadRootFolder . date("Y/m/d/His").rand(100,999);
            $this->attributes['upload_folder']   = $uploadFolder;
            Session::put('upload.'.$this->table.'.upload_folder', $uploadFolder);
            return $uploadFolder;
        }
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
        $row->setAttribute($field, serialize($value));

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
        return  unserialize($value);
    }

    public function setFileSingle($field, $value)
    {
        if (Session::has('upload.'.$this->table.'.'.$field)){
            $value      = serialize(Session::pull('upload.'.$this->table.'.'.$field));
        } elseif (!empty($value)){
            $value      = serialize($value);
        } else {
            return;
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

        if (empty($current) && empty($session))
            return ;

        $prev       = $this->getOriginalFile($field);

        if (empty($prev)){
            $prev = array();
        }

        $value      = array_merge($prev, $current, $session);

        $value      = serialize($value);
        $this->setAttribute($field, $value);
    }

    public function resetFile($field)
    {
        $value       = $this->getOriginalFile($field);

        if (is_array($value)) $value = serialize($value);

        $this->setAttribute($field, $value);
    }

    public function getOriginalFile($field)
    {
        if (method_exists($this, 'isKeyReturningTranslationText') && $this->isKeyReturningTranslationText($field))
        {
            if ($this->getTranslation() === null)
            {
                return null;
            }
            $original = $this->getTranslation()->getOriginal($field);
        } else {

            $original = parent::getOriginal($field);
        }
        return unserialize($original);
    }
}