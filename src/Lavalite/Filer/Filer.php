<?php namespace Lavalite\Filer;

use URL;
use View;
use File;
use Config;
use Session;
use Intervention;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Intervention\Image\Image;

/**
 *
 * @package default
 */
class Filer
{
    /*==========  Image Upload Functions  ==========*/
    /**
     * Upload file to the folder
     * @param UploadedFile $file
     * @param String $path
     * @return array
     */
    public function upload(UploadedFile $file, $path)
    {
        // Check the upload type is valid by extension and mimetype
        $this->verifyUploadType($file);

        // Check file size
        if ($file->getSize() > config('filer.max_upload_size')) {
            throw new FileException('File is too big.');
        }

        // Get the folder for uploads
        $folder = $this->checkUploadFolder($path);

        // Check to see if file exists already. If so append a random string.
        $file = $this->resolveFileName($folder, $file);

        // Upload the file to the folder. Exception thrown from move.
        $file->move($folder, $file->fileSystemName);

        $this->resizeImage($folder, $file->fileSystemName);


        // If it returns an array it's a successful upload. Otherwise an exception will be thrown.
        $array  = array('folder' => $this->relativePath($folder), 'file' => $file->fileSystemName, 'caption' => $this->getName($file));

        return $array;
    }


    /**
     * Resolve whether the file exists and if it already does, change the file name.
     * @param string $folder
     * @param $file
     * @param bool $enableObfuscation
     * @return array
     */
    public function resolveFileName($folder, UploadedFile $file, $enableObfuscation = true)
    {

        if (!isset($file->fileSystemName)) {
            $file->fileSystemName = $file->getClientOriginalName();
        }

        if (config('filer.obfuscate_filenames') && $enableObfuscation) {
            $fileName = basename($file->fileSystemName, $file->getClientOriginalExtension()) . '_' . md5(uniqid(mt_rand(), true)) . '.' . $file->getClientOriginalExtension();
        } else {
            $fileName = $file->fileSystemName;
        }


        if (File::isFile($folder . $fileName)) {


            $basename = $this->getBasename($file);
            $pose = strrpos($basename, '_');

            if ($pose) {
                $f = substr($basename, 0, $pose);
                $s = substr($basename, $pose + 1);

                if (is_numeric($s)) {
                    $s++;
                    $basename = $f;
                } else {
                    $s = 1;
                }
            } else {
                $s = 1;
            }

            $file->fileSystemName = $basename . '_' . $s . '.' . $file->getClientOriginalExtension();
            return $this->resolveFileName($folder, $file, false);
        }

        return $file;
    }

    /**
     * Get upload path with date folders
     * @param $date
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
     * @return string
     */
    public function checkUploadFolder($folder)
    {

        $folder = public_path($folder);
        $folder .= (substr($folder, -1) != '/') ? '/': '';

        // Check to see if the upload folder exists
        if (!File::exists($folder)) {
            // Try and create it
            if (!File::makeDirectory($folder, config('filer.folder_permission'), true)) {
                throw new FileException('Directory is not writable. Please make upload folder writable.');
            }
        }

        return $folder;
    }

    /**
     * Checks the upload vs the upload types in the config.
     * @param $file
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function verifyUploadType(UploadedFile $file)
    {
        if (!in_array($file->getMimeType(), config('filer.allowed_types')) &&
            !in_array(strtolower($file->getClientOriginalExtension()), config('filer.allowed_extensions'))
        ) {
            throw new FileException('Invalid upload type.');
        }
    }

    /**
     * Checks the upload vs the upload types in the config.
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return bool
     */
    public function verifyImageType($file)
    {
        if (in_array($file->getMimeType(), config('filer.image_types')) ||
            in_array(strtolower($file->getClientOriginalExtension()), config('filer.image_extensions'))
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function getBasename($file)
    {
        // Get the file bits
        $basename = basename((isset($file->fileSystemName) ? $file->fileSystemName : $file->getClientOriginalName()), $file->getClientOriginalExtension());
        // Remove trailing period
        return (substr($basename, -1) == '.' ? substr($basename, 0, strlen($basename) - 1) : $basename);
    }

    public function getName($file)
    {
        // Get the file bits
        $basename = basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension());
        // Remove trailing period
        $name =  ucfirst(strtolower(preg_replace('/[^A-Za-z0-9]/', ' ', $basename)));
        return $name;
    }

    public function resizeImage($folder, $file)
    {

        if (!config('filer.image_resize_on_upload')) return;

        if (is_string($file)) {
            $uFile = new UploadedFile($folder . $file, $file);
        }
        // Check the image type is valid by extension and mimetype
        if ($this->verifyImageType($uFile)) {
            $image = Intervention::make($folder . $file);
            //        dd(print_r($image));
            if ($image->width() > config('filer.image_max_size.w') || $image->height() > config('filer.image_max_size.h')) {

                $image->resize(config('filer.image_max_size.w'), config('filer.image_max_size.h'), true);
                $image->save($folder . $file);
            }
        }
    }

    public function relativePath($path)
    {

        $path = str_replace(public_path(), '', $path);
        // Check to see if it begins in a slash
        if (substr($path, 0, 1) != '/') $path = '/' . $path;

        // Check to see if it ends in a slash
        if (substr($path, -1) != '/') $path .= '/';

        $path   = str_replace('//', '/', $path);

        return $path;
    }

    /*==========  File display functions  ==========*/
    public function show($files, $count = -1, $view = 'filer::show')
    {

        if (!is_array($files) && !is_object($files)) $files = json_decode($files, true);

        if(empty($files)) $files = array();

        if (is_object($files))
            $files      = (array)$files;

        return View::make($view, compact('files', 'field', 'count'));
    }

    public function editor($field, $files, $count = -1, $view = 'filer::editor')
    {
        if (!is_array($files) && !is_object($files)) $files = json_decode($files, true);

        if(empty($files)) $files = array();

        if (is_object($files))
            $files      = (array)$files;

        return View::make($view, compact('files', 'field', 'count'));
    }

    public function uploader($field, $path, $files = 10, $view = 'filer::upload', $mime = 'image/*')
    {
        return View::make($view, compact('path', 'field', 'files', 'mime'));
    }

    /*==========  Image Resize Functions  ==========*/
    /**
     * @param $folder
     * @param $file
     * @param $size
     * @return mixed
     */
    public function resize($folder, $file, $size)
    {

        // pass calls to picture cache
        return Intervention::cache(function($picture) use ($folder, $file, $size) {
            if (!is_file($file)) return false;
            return $picture->make($file)->resize($size['width'], $size['height']);

        });
    }

    /**
     * @param $folder
     * @param $file
     * @param $size
     * @return mixed
     */
    public function fit($folder, $file, $size)
    {

        // pass calls to picture cache
        return Intervention::cache(function($picture) use ($folder, $file, $size) {

            $file = public_path().'/'.$folder.'/'.$file;
            return $picture->make($file)->fit($size['width'], $size['height']);

        });


    }

}