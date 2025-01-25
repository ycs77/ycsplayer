<?php

namespace App\PasswordlessLogin\Http\Controllers;

use Grosv\LaravelPasswordlessLogin\Exceptions\ExpiredSignatureException;
use Grosv\LaravelPasswordlessLogin\Exceptions\InvalidSignatureException;
use Grosv\LaravelPasswordlessLogin\PasswordlessLoginService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\UrlGenerator;

class PasswordlessController extends Controller
{
    public function __construct(
        protected PasswordlessLoginService $passwordlessLoginService,
        protected UrlGenerator $urlGenerator,
    ) {
        //
    }

    protected function preparePasswordless(Request $request): void
    {
        if (! $this->urlGenerator->hasCorrectSignature($request) ||
            ($this->urlGenerator->signatureHasNotExpired($request) && ! $this->passwordlessLoginService->requestIsNew())
        ) {
            throw new InvalidSignatureException;
        } elseif (! $this->urlGenerator->signatureHasNotExpired($request)) {
            throw new ExpiredSignatureException;
        }

        $this->passwordlessLoginService->cacheRequest($request);
    }

    protected function getUser()
    {
        /** @var mixed $user */
        $user = $this->passwordlessLoginService->user;

        return $user;
    }
}
