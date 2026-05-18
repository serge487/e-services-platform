<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeSvg
{
    public static function trackingUrl(string $token): string
    {
        $path = route('qr.scan', ['token' => $token], false);
        $baseUrl = rtrim((string) config('qr.public_url', ''), '/');

        if ($baseUrl === '') {
            return url($path);
        }

        return $baseUrl.$path;
    }

    public static function render(string $content, int $size = 140): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($content);
    }
}
