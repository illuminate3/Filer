<?php

// Cabinet routes
Route::post('upload/{package}/{module}/{id}/{category}', 'Lavalite\Filer\FilerController@file');
