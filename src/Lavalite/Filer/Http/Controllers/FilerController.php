<?php namespace Lavalite\Filer;

use Lavalite\Http\Controllers\Controller;
use URL;
use Input;
use Filer;
use Session;
use Response;

class FilerController extends Controller {


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
            return $array;
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

    /*==========  Image Resize Functions  ==========*/

    public function resize($url)
    {
        $arr    = $this->parseUrl($url);

        $filer        = Filer::resize($arr['folder'], $arr['file'], $arr['size']);

        $header         = array(
                                'Content-Type'  => 'image/jpg',
                                'Cache-Control' => 'max-age=864000, public',
                                'Expires'       => gmdate('D, d M Y H:i:s \G\M\T', time() + 864000),
                                'Pragma'        => 'public');

        return Response::make($filer, 200, $header);

    }

    public function fit($url)
    {

        $arr    = $this->parseUrl($url);
        if (!is_file($arr['folder'] . '/' . $arr['file'])) return false;

        $filer            = Filer::fit($arr['folder'], $arr['file'], $arr['size']);

        $header         = array(
            'Content-Type'  => 'image/jpg',
            'Cache-Control' => 'max-age=864000, public',
            'Expires'       => gmdate('D, d M Y H:i:s \G\M\T', time() + 864000),
            'Pragma'        => 'public');

        return Response::make($filer, 200, $header);

    }

    public function parseUrl($url)
    {
        $arr            = array();
        $parts          = explode('/', $url);
        $file           = end($parts);
        $fparts         = explode('_', $file, 2);

        $arr['size']    = config('filer.size.'.$fparts[0]);
        $arr['file']    = $fparts[1];
        $arr['folder']  = str_replace($file, '', $url);
        return $arr;
    }

}