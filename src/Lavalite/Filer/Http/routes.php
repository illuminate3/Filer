<?php

// File upload routes
Route::post('upload/{table}/{field}/{file}', 'Lavalite\Filer\FilerController@file');

//Image resize routes
Route::get('image/{url?}', 'Lavalite\Filer\FilerController@fit')
    ->where('url', '(.*)');;
