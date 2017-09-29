<?php


Route::group(['middleware' => 'web'], function () {

    Route::get('/admin/{table}', function ($table) {
        return view('Datatable::datatable.index', compact('table'));
    })->name('index');
});

