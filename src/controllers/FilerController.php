<?php namespace Lavalite\Filer;

use URL;
use Input;
use Filer;
use Session;

class FilerController extends \Controller {


    /**
     * @param $url
     * @return string
     */
    public function file($table, $field, $file)
	{
		if (Input::hasFile($file)) {
            $folder     = $this->getUploadFolder($table, $field);
			$array = Filer::upload(Input::file($file), $folder);

            Session::push('upload.'.$table. '.' . $field, $array);
    		return ($array['folder'] . $array['file']);
		}
	}

    /**
     * @param $url
     * @return array
     */
    public function getUploadFolder($table, $field)
    {
        if (!Session::has('upload.'.$table.'.upload_folder')) {
            throw new Exception("Upload folder not exists", 1);
        }
        return Session::get('upload.'.$table.'.upload_folder'). '/' . $field;

    }

}