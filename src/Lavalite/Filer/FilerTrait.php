<?php namespace Lavalite\Filer;

use Input;
use File;
use Filer;

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
                $file   = Filer::upload($upfile, $this->getUploadFolder($this->id, $field));
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
                    if (!is_null($file[0]))
                        $files[]  = Filer::upload($file[0], $this->getUploadFolder($this->id, $field));
                }
            }
                $this -> setFileMultiple($field, $files);

        }
    }

    /**
     * @param $id
     * @param $field
     * @return string - path to the upload folder
     */
    private function getUploadFolder($id, $field)
    {
        return $this->uploadFolder . "$id/$field/";
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

        $res = $row -> save();
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
        if (!is_array($value) || !count($value)) return;

        $value      = serialize($value);
        $this -> setAttribute($field, $value);
    }

    public function setFileMultiple($field, $current)
    {
        if (!is_array($current) || !count($current)) return;

        $prev       = $this->getOriginalFile($field);
        if ($prev == '') $prev = array();

        $value      = array_merge($prev, $current);
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
        if ($this->isKeyReturningTranslationText($field))
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