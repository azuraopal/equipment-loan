<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Generate QR Code SVG string.
     *
     * @param string $data The data to encode in the QR code
     * @param int $size The size of the QR code in pixels
     * @return string SVG string
     */
    public static function generateSvg(string $data, int $size = 200): string
    {
        $class = 'SimpleSoftwareIO\QrCode\Generator';
        $generator = new $class();

        return $generator
            ->size($size)
            ->style('round')
            ->eye('circle')
            ->format('svg')
            ->generate($data);
    }
}

