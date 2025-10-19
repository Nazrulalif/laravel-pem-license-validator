<?php

// File: app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Nazrulalif\LaravelPemLicenseValidator\Facades\License;

class DashboardController extends Controller
{
    public function index()
    {
        // Get license data
        $license = License::getLicenseData();

        // Check specific features
        $hasSSO = License::hasFeature('sso');
        $hasAPI = License::hasFeature('api_access');

        // Get feature values with defaults
        $userLimit = License::getFeature('UserLimit', 10);
        $storageGB = License::getFeature('storage_gb', 10);

        // Feature-based logic
        if ($hasAPI) {
            // Enable API routes
            $apiEnabled = true;
        }

        if ($userLimit > 50) {
            // Show enterprise features
            $showEnterpriseFeatures = true;
        }

        // Pass to view
        return view('dashboard', [
            'license' => $license,
            'daysRemaining' => License::getDaysRemaining(),
            'inGracePeriod' => License::isInGracePeriod(),
            'features' => [
                'sso' => $hasSSO,
                'api' => $hasAPI,
                'userLimit' => $userLimit,
                'storage' => $storageGB,
            ],
        ]);
    }
}

<?php

// File: resources/views/dashboard.blade.php

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard</h1>
    
    @if($inGracePeriod)
        <div class="alert alert-warning">
            ⚠️ Your license is in grace period. {{ $daysRemaining }} days remaining.
        </div>
    @endif
    
    <div class="card">
        <div class="card-header">License Information</div>
        <div class="card-body">
            <p><strong>Company:</strong> {{ $license['companyName'] }}</p>
            <p><strong>Type:</strong> {{ $license['licenseType'] }}</p>
            <p><strong>Expires:</strong> {{ $license['expiryDate'] }}</p>
            <p><strong>Days Remaining:</strong> {{ $daysRemaining }}</p>
        </div>
    </div>
    
    <div class="card mt-3">
        <div class="card-header">Available Features</div>
        <div class="card-body">
            @if($features['sso'])
                <span class="badge bg-success">SSO Enabled</span>
            @endif
            
            @if($features['api'])
                <span class="badge bg-success">API Access</span>
            @endif
            
            <p class="mt-2">
                <strong>User Limit:</strong> {{ $features['userLimit'] }}<br>
                <strong>Storage:</strong> {{ $features['storage'] }} GB
            </p>
        </div>
    </div>
</div>
@endsection