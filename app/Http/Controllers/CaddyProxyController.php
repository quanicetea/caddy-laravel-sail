<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaddyProxyController extends Controller
{
    public function verifyDomain(Request $request)
    {
        $authorizedDomains = [
            config('app.service'),           // laravel.test
            'localhost',
            // Add subdomains here
        ];

        if (in_array($request->query('domain'), $authorizedDomains)) {
            return response('Domain Authorized');
        }

        // Abort if there's no 200 response returned above
        abort(503);
    }
}
