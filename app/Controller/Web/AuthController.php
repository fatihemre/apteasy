<?php

namespace Apteasy\Controller\Web;

use Apteasy\Controller\BaseController;
use Apteasy\Model\User;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends BaseController
{

    public function login(): string
    {
        return $this->view('login');
    }

    public function logout(): RedirectResponse
    {
        session('user', -1);
        return redirectTo('/');
    }

    public function mfa(): string
    {
        return $this->view('mfa');
    }

    public function mfaCheck(Request $request): RedirectResponse
    {
        $authenticatedUser = session('mfa_check');

        $user = (new User())->fetch($authenticatedUser->email);
        $secret = $request->get('secret');
        $recovery_codes = json_decode(decrypt($user->mfa_recovery_codes), true, 512, JSON_THROW_ON_ERROR);

        $is_recovery_code = array_search($secret, $recovery_codes, true);

        if($is_recovery_code !== false) {
            unset($recovery_codes[$is_recovery_code]);
            $recovery_codes = array_values($recovery_codes);
            (new User)->saveRecoveryCodes($user->id, encrypt(json_encode($recovery_codes, JSON_THROW_ON_ERROR)));

            $valid = true;
        } else {
            $google2fa = (new Google2FA());
            $valid = $google2fa->verifyKey(decrypt($user->mfa_secret_key), $secret);

        }

        if($valid) {
            $user->password = "*****";
            session('user', $user);
            cookie('lang', $user->language);
            return redirectTo('/admin');
        }

        flash('danger', __('Your 2FA code is not valid'));
        return redirectTo('/auth/2fa');
    }

    public function check(Request $request): RedirectResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');

        $user = (new User())->fetch($email);
        session('mfa_check', null);
        if(!$user) {
            flash('warning', __('Username/Password incorrect.'));
            return redirectTo('/auth/login');
        }

        if(!password()->verify($user->password, $password)) {
            flash('warning', __('Username/Password incorrect.'));
            return redirectTo('/auth/login');
        }

        if($user->is_mfa_enabled && !is_null($user->mfa_recovery_codes)) {
            flash('warning', __('Please enter 2FA'));
            session('mfa_check', $user);
            return redirectTo('/auth/2fa');
        }

        $user->password = "*****";
        session('user', $user);
        cookie('lang', $user->language);

        return redirectTo('/admin');

    }

}