<?php namespace Lavalite\Filer;

use URL;
use Input;
use Filer;

class FilerController extends \Controller {


    /**
     * @param $url
     * @return string
     */
    public function file($url)
	{
        $arr = $this->parseUrl($url);

		if (Input::hasFile($field)) {
			$arr = Filer::upload(Input::file($arr['file']), $arr['folder']);
			return ($arr[0] . $arr[1]);
		}
	}

    /**
     * @param $url
     * @return array
     */
    public function parseUrl($url)
    {
        $parts      = explode('/', $url);
        $file       = end($parts);
        $folder     = str_finish(public_path(), '/').str_replace($file, '', $url);
        return compact('file', 'folder');
    }

}