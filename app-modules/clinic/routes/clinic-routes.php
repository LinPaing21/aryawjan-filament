<?php

use Illuminate\Support\Facades\Route;
use Stella\Clinic\Livewire\PublicQueueBoard;

Route::middleware('web')->get('/queue-board', PublicQueueBoard::class)->name('clinic.queue-board');
