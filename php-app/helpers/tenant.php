<?php
declare(strict_types=1);

/**
 * Tenant helper — multi-tenant resolution and context.
 * Resolves active tenant from:
 * 1. HTTP header X-Tenant-ID
 * 2. Query param ?tenant=...
 * 3. Subdomain / Hostname
 * 4. Default fallback: 'doctor'
 */

function current_tenant_id(): string
{
    // 1. Header sent by frontend Axios
    if (!empty($_SERVER['HTTP_X_TENANT_ID'])) {
        $headerTenant = strtolower(trim((string)$_SERVER['HTTP_X_TENANT_ID']));
        if (in_array($headerTenant, ['doctor', 'marketplace', 'enterprise'], true)) {
            return $headerTenant;
        }
    }

    // 2. Query param override
    if (!empty($_GET['tenant'])) {
        $paramTenant = strtolower(trim((string)$_GET['tenant']));
        if (in_array($paramTenant, ['doctor', 'marketplace', 'enterprise'], true)) {
            return $paramTenant;
        }
    }

    // 3. Subdomain detection
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    if (str_starts_with($host, 'market.') || str_starts_with($host, 'shop.')) {
        return 'marketplace';
    }
    if (str_starts_with($host, 'enterprise.') || str_starts_with($host, 'apex.')) {
        return 'enterprise';
    }
    if (str_starts_with($host, 'clinic.') || str_starts_with($host, 'health.')) {
        return 'doctor';
    }

    // 4. Default
    return 'doctor';
}

function tenant_config(?string $id = null): array
{
    $id = $id ?? current_tenant_id();

    $tenants = [
        'doctor' => [
            'id'          => 'doctor',
            'name'        => 'MediCare Clinical Portal',
            'appName'     => 'MediCare Clinic',
            'theme'       => 'doctor',
            'primaryColor'=> '#0f66f0',
        ],
        'marketplace' => [
            'id'          => 'marketplace',
            'name'        => 'OmniStore E-Commerce',
            'appName'     => 'OmniStore Market',
            'theme'       => 'marketplace',
            'primaryColor'=> '#f97316',
        ],
        'enterprise' => [
            'id'          => 'enterprise',
            'name'        => 'Apex Enterprise Hub',
            'appName'     => 'Apex Enterprise',
            'theme'       => 'enterprise',
            'primaryColor'=> '#6366f1',
        ],
    ];

    return $tenants[$id] ?? $tenants['doctor'];
}
