<?php

// Cabinet routes
Route::post('upload/{table}/{field}/{file}', 'Lavalite\Filer\FilerController@file');
