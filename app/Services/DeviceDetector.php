<?php

namespace App\Services;

use DeviceDetector\DeviceDetector as MatomoDeviceDetector;

class DeviceDetector
{
    /**
     * Get detailed device information from user agent string.
     */
    public static function getDeviceInfo(?string $userAgent): array
    {
        if (! $userAgent) {
            return [
                'device_type' => 'unknown',
                'device_name' => 'Unknown',
                'brand' => null,
                'model' => null,
                'browser' => null,
                'browser_version' => null,
                'os' => null,
                'os_version' => null,
            ];
        }

        $dd = new MatomoDeviceDetector($userAgent);
        $dd->parse();

        $deviceType = self::normalizeDeviceType($dd->getDeviceName());
        $brand = $dd->getBrandName();
        $model = $dd->getModel();

        // Build a human-readable device name
        $deviceName = self::buildDeviceName($deviceType, $brand, $model);

        return [
            'device_type' => $deviceType,
            'device_name' => $deviceName,
            'brand' => $brand,
            'model' => $model,
            'browser' => $dd->getClient('name'),
            'browser_version' => $dd->getClient('version'),
            'os' => $dd->getOs('name'),
            'os_version' => $dd->getOs('version'),
        ];
    }

    /**
     * Detect device type from user agent string (legacy method for backward compatibility).
     */
    public static function detect(?string $userAgent): string
    {
        $info = self::getDeviceInfo($userAgent);

        return $info['device_type'];
    }

    /**
     * Get browser name from user agent (legacy method for backward compatibility).
     */
    public static function getBrowser(?string $userAgent): ?string
    {
        $info = self::getDeviceInfo($userAgent);

        return $info['browser'];
    }

    /**
     * Get OS from user agent (legacy method for backward compatibility).
     */
    public static function getOS(?string $userAgent): ?string
    {
        $info = self::getDeviceInfo($userAgent);

        return $info['os'];
    }

    /**
     * Normalize device type to our standard types.
     */
    protected static function normalizeDeviceType(?string $deviceType): string
    {
        if (! $deviceType) {
            return 'unknown';
        }

        $deviceType = strtolower($deviceType);

        return match ($deviceType) {
            'smartphone' => 'mobile',
            'tablet', 'phablet' => 'tablet',
            'desktop' => 'desktop',
            'tv', 'smart display', 'car browser', 'console', 'portable media player', 'camera' => 'other',
            'bot' => 'bot',
            default => 'unknown',
        };
    }

    /**
     * Build a human-readable device name.
     */
    protected static function buildDeviceName(string $deviceType, ?string $brand, ?string $model): string
    {
        // For bots, just return "Bot"
        if ($deviceType === 'bot') {
            return 'Bot';
        }

        // If we have brand and model, combine them
        if ($brand && $model) {
            // Clean up the model name (sometimes it includes the brand)
            $cleanModel = $model;
            if (stripos($model, $brand) === 0) {
                $cleanModel = trim(substr($model, strlen($brand)));
            }

            return trim("$brand $cleanModel");
        }

        // If we only have brand
        if ($brand) {
            return "$brand ".ucfirst($deviceType);
        }

        // Fallback to just the type
        return match ($deviceType) {
            'mobile' => 'Mobile Device',
            'tablet' => 'Tablet',
            'desktop' => 'Desktop',
            'other' => 'Other Device',
            default => 'Unknown',
        };
    }
}
