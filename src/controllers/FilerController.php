<?php namespace Lavalite\Filer;

use Input;
use File;
use LavaliteFiler;

class FilerController extends \Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */


	public function postFile($module, $id, $field = 'file')
	{
		if (Input::hasFile($field) && is_array(Input::file($field))) {

			foreach (Input::file($field) as $file) {

				File::upload($file, $module, $id, $field);
			}

		} elseif (Input::hasFile($field)) {

			File::upload(Input::file($field), $module, $id, $field);

		}

	}

	public function postFileOf($module, $id, $field = 'file')
	{
		if (Input::hasFile($field) && is_array(Input::file($field))) {

			foreach (Input::file($field) as $file) {

				File::insert($file, $module, $id, $field);
			}

		} elseif (Input::hasFile($field)) {

			File::insert(Input::file($field), $module, $id, $field);

		}

	}

	public function getFiler($module, $id)
	{
		return File::filer($module, $id);
	}

	public function getImages($module, $id)
	{
		return File::images($module, $id);
	}

	public function getAllFiler($module, $id)
	{
		return File::allFiler($module, $id);
	}



}