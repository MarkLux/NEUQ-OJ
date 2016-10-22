<?php



Route::get('/', function (\NEUQOJ\Repository\Eloquent\NewsRepository $repository) {
    return $repository->getByMult(['id' => 1, 'title' => '22'],['content']);
    return view('welcome');



});
