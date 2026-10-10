<?php

namespace Waypoint\Flash\Tests;

use Waypoint\Attributes\{Controller, Get, Inject, Post, SkipCsrf};
use Waypoint\Flash\Flash;
use Waypoint\Http\{Redirect, View};

#[Controller]
#[SkipCsrf]
class FlashController
{
    #[Inject]
    private Flash $flash;

    #[Post('/save')]
    public function save(): Redirect
    {
        $this->flash->success('Saved <b>ok</b>');
        return new Redirect('/page');
    }

    #[Post('/fail')]
    public function fail(): Redirect
    {
        $this->flash->error('Nope');
        return new Redirect('/page');
    }

    #[Get('/page')]
    public function page(): View
    {
        return new View('Page');
    }

    #[Get('/now')]
    public function now(): View
    {
        $this->flash->info('Right away');
        return new View('Page');
    }

    #[Get('/api')]
    public function api(): array
    {
        return ['ok' => true];
    }
}
