<?php

// Cabinet routes
Route::post('upload/{url?}', 'Lavalite\Filer\FilerController@file')
    ->where('url', '(.*)');
