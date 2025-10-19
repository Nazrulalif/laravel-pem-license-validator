<?php

namespace Nazrulalif\LaravelPemLicenseValidator\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nazrulalif\LaravelPemLicenseValidator\LicenseValidator;
use Nazrulalif\LaravelPemLicenseValidator\Exceptions\LicenseException;
use Symfony\Component\HttpFoundation\Response;

class ValidateLicense
{
    protected LicenseValidator $validator;

    public function __construct(LicenseValidator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$this->validator->isValid()) {
                return $this->handleFailure($request);
            }

            // Check grace period warning
            if ($this->validator->isInGracePeriod()) {
                $days = $this->validator->getDaysRemaining();
                logger()->warning("License in grace period. Expires in {$days} days.");
            }

            return $next($request);
        } catch (LicenseException $e) {
            if (config('license.on_failure.log', true)) {
                logger()->error('License validation failed: ' . $e->getMessage());
            }

            return $this->handleFailure($request, $e);
        }
    }

    protected function handleFailure(Request $request, ?LicenseException $exception = null): Response
    {
        $redirectUrl = config('license.on_failure.redirect', '/license-expired');
        $abortCode = config('license.on_failure.abort_code', 403);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'License validation failed',
                'message' => $exception?->getMessage() ?? 'Invalid or expired license',
            ], $abortCode);
        }

        if ($redirectUrl) {
            return redirect($redirectUrl)->with('error', $exception?->getMessage() ?? 'Invalid license');
        }

        abort($abortCode, $exception?->getMessage() ?? 'Invalid or expired license');
    }
}
