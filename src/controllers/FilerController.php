<?php namespace Lavalite\Filer;

use URL;
use Input;
use Filer;

class FilerController extends \Controller {


	public function file($package, $module, $id, $category, $field = 'file')
	{
		if (Input::hasFile($field)) {
			$arr = Filer::upload(Input::file($field), $package, $module, $id, $category);
			return ($arr[0] . $arr[1]);
		}
	}

}