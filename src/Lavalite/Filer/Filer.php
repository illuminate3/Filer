<?php namespace  Lavalite\Filer;
use URL;
use View;
use File;
use Config;
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

    protected $fileModel    = 'Lavalite\Filer\Models\Filer';

    /**
     * Function to upload filer and make an entry in filer table
     * @param file UploadedFile $file
     * @param string $package
     * @param string $module
     * @param int $id
     * @param string $category
     * @return type
     */
    public function insert(UploadedFile $file, $package, $module, $id, $category)
    {
        $model       = new $this->fileModel();

        // File extension
        $model->extension = $file->getClientOriginalExtension();

        // File extension
        $model->name        = $this->getBasename($file);

        $model->category    = $category;
        // Mimetype for the file
        $model->mimetype = $file->getMimeType();
        // Current user or 0
        $model->user_id = $this->getUserId();

        $model->size    = $file->getSize();
        $model->of_type = $this->getModel($package, $module);
        $model->of_id   = $id;

        list($model->path, $model->file) = $this->upload( $file, $package, $module, $id, $category);

        $model->save();
    }

    /**
     * Upload file to appropriate folder
     * @param type UploadedFile $file
     * @param type $package
     * @param type $module
     * @param type $id
     * @param type $category
     * @return type
     */
    public function upload(UploadedFile $file, $package, $module, $id, $category)
    {
        // Check the upload type is valid by extension and mimetype
        $this->verifyUploadType($file);

        // Get the folder for uploads
        $folder = $this->getUploadFolder($package, $module, $id, $category);

        // Check file size
        if ($file->getSize() > Config::get('filer::max_upload_size')) {
            throw new FileException('File is too big.');
        }

        // Check to see if file exists already. If so append a random string.
        list($folder, $file) = $this->resolveFileName($folder, $file);

        $folder     = $this->cleanPath($folder);

        // Upload the file to the folder. Exception thrown from move.
        $file->move($folder, $file->fileSystemName);
        $this->resizeImage($folder, $file->fileSystemName);
        // If it returns an array it's a successful upload. Otherwise an exception will be thrown.
        return array($this->relativePath($folder), $file->fileSystemName);
    }

    /**
    * Get upload path with date folders
    * @param $date
    * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
    * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
    * @return string
    */
    public function getUploadFolder($package, $module, $id, $category)
    {

       // Get the configuration value for the upload path
        $folder = Config::get('filer::folder');

        // Add the public path base to the path
        $folder = public_path().'/'.$folder;

        $arr[':provider']   = Config::get("{$package}::provider");
        $arr[':package']    = $package;
        $arr[':module']     = $module;
        $arr[':id']         = $id;
        $arr[':category']   = $category;

        $folder   = $this->buildPath($folder, $arr);
        $folder   = $this->cleanPath($folder);

        // Check to see if the upload folder exists
        if (! File::exists($folder)) {
            // Try and create it
            if (! File::makeDirectory($folder, Config::get('filer::folder_permission'), true)) {
                throw new FileException('Directory is not writable. Please make upload folder writable.');
            }
        }

    // Check that the folder is writable
        if (! File::isWritable($folder)) {
            throw new FileException('Folder is not writable.');
        }
        return $folder;
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
        if(! isset($file->fileSystemName)) {
            $file->fileSystemName = $file->getClientOriginalName();
        }

        if(Config::get('filer::obfuscate_filenames') && $enableObfuscation) {
            $fileName = basename($file->fileSystemName, $file->getClientOriginalExtension()) . '_' . md5( uniqid(mt_rand(), true) ) . '.' . $file->getClientOriginalExtension();
        } else {
            $fileName = $file->fileSystemName;
        }


        if (File::isFile($folder.$fileName)) {


            $basename   = $this->getBasename($file);
            $pose       = strrpos($basename, '_');

            if ($pose){
                $f   = substr($basename, 0, $pose);
                $s   = substr($basename, $pose+1);

                if (is_numeric($s)){
                    $s++;
                    $basename = $f;
                } else {
                    $s = 1;
                }
            } else {
                $s = 1;
            }

            $file->fileSystemName = $basename . '_' . $s . '.' . $file->getClientOriginalExtension();
            list($folder, $file) = $this->resolveFileName($folder, $file, false);
        }

        return array($folder, $file);
    }

    /**
    * Checks the upload vs the upload types in the config.
    * @param $file
    * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
    */
    public function verifyUploadType(UploadedFile $file)
    {
        if (! in_array($file->getMimeType() , Config::get('filer::allowed_types')) &&
            ! in_array(strtolower($file->getClientOriginalExtension()), Config::get('filer::allowed_extensions')))
        {
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
        if (in_array($file->getMimeType() , Config::get('filer::image_types')) ||
            in_array(strtolower($file->getClientOriginalExtension()), Config::get('filer::image_extensions')))
        {
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
        return (substr($basename, -1) == '.' ? substr($basename,0,strlen($basename)-1) : $basename);
    }

    public function resizeImage($folder, $file)
    {

        if (!Config::get('filer::image_resize_on_upload'))  return;

        if( is_string($file) ) {
            $uFile = new UploadedFile($folder.$file, $file);
        }
        // Check the image type is valid by extension and mimetype
        if($this->verifyImageType($uFile))
        {
            $image = Intervention::make($folder.$file);
    //        dd(print_r($image));
            if ($image->width() > Config::get('filer::image_max_size.w') || $image->height() > Config::get('filer::image_max_size.h')) {

                $image->resize(Config::get('filer::image_max_size.w'), Config::get('filer::image_max_size.h'), true);
                $image->save($folder.$file);
            }
        }
    }

    public function cleanPath($path)
    {

        // Check to see if it begins in a slash
        // if(substr($path, 0, 1) != '/')  $path = '/' . $path;

        // Check to see if it ends in a slash
        if(substr($path, -1) != '/')  $path .= '/';

        return $path;
    }

    public function buildPath($path, $arr = array())
    {
        $path    = str_replace(array_keys($arr), array_values($arr), $path);
        return $path;
    }

    public function relativePath($path)
    {

        $path = str_replace(public_path(), '', $path);
        // Check to see if it begins in a slash
        if(substr($path, 0, 1) != '/')  $path = '/' . $path;

        // Check to see if it ends in a slash
        if(substr($path, -1) != '/')  $path .= '/';

        return $path;
    }

    public function files($package, $id){
        $model  = $this->getModel($package);
        $model  = new $model();
        $model  = $model->find($id);
        return $model -> filer() ;
    }

    public function images($package, $module, $id){

        $model  = $this->getModel($package, $module);
        $model  = new $model();
        $model  = $model->find($id);
        return $model -> images();
    }

    public function allFiles($package, $id){
        $model  = $this->getModel($package);
        $model  = new $model();
        $model  = $model->find($id);
        return $model -> allFiler();
    }

    public function dropZone(){
        return View::make('filer::dropzone');
    }

    public function getModel($package, $module){
        $model  = Config::get("{$package}::{$module}.model");

        return $model;
    }


    /**
     * Attempt to find the user id of the currently logged in user
     * Supports Sentry based authentication, as well as stock Auth
     **/
    private function getUserId()
    {

        try {
            if (class_exists('Sentry') && \Sentry::check()) {
                $user = \Sentry::getUser();
                return $user->id;
            } else if (\Auth::check()) {
                return \Auth::user()->id;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }



    public function imageGrid($settings, $view ='default') {

        $data['images']   = $this->images($settings['package'], $settings['module'], $settings['id'])->toArray();
        $data['settings'] = $settings;
        return View::make('filer::grid.'.$view, $data);

    }
    

      public function userImageGrid($imageAlbum,$settings, $view ='galleryView') {

        foreach($settings as $key=>$row){
             $imageAlbum[$key]['imageGallery']   =  $this->images($row['package'], $row['module'], $row['id'])->toArray();
             $imageAlbum[$key]['settings']       =  $row;
        }
      
         $data['images']   = $imageAlbum->toArray();
    
         return View::make('gallery::gallery.public.grid.'.$view, $data);

 
    }

}