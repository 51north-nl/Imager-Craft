<?php
/**
 * Imager plugin for Craft CMS 3.x
 *
 * Image transforms gone wild
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2017 André Elvan
 */

namespace aelvan\imager\services;

use craft\base\Component;
use craft\elements\Asset;

use aelvan\imager\models\LocalSourceImageModel;
use aelvan\imager\exceptions\ImagerException;

use ColorThief\ColorThief;

use SSNepenthe\ColorUtils\Colors\Rgba as RGBA;
use SSNepenthe\ColorUtils\Colors\Color as Color;
use function SSNepenthe\ColorUtils\{
    alpha,
    blue,
    brightness,
    brightness_difference,
    color,
    color_difference,
    contrast_ratio,
    green,
    hsl,
    hsla,
    hue,
    is_bright,
    is_light,
    lightness,
    looks_bright,
    name,
    opacity,
    perceived_brightness,
    red,
    relative_luminance,
    rgb,
    rgba,
    saturation
};

/**
 * ImagerColorService Service
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    André Elvan
 * @package   Imager
 * @since     2.0.0
 */
class ImagerColorService extends Component
{
    /**
     * Get dominant color of image
     *
     * @param int $quality
     * @param string $colorValue
     * @return string|array|boolean|null
     */
    public function getDominantColor(\craft\elements\Asset|string $image, $quality, $colorValue)
    {
        try {
            $source = new LocalSourceImageModel($image);
            $source->getLocalCopy();
        } catch (ImagerException) {
            return null;
        }

        $dominantColor = ColorThief::getColor($source->getFilePath(), $quality);

        ImagerService::cleanSession();

        if (!\is_array($dominantColor)) {
            return null;
        }

        return $colorValue === 'hex' ? self::rgb2hex($dominantColor) : $dominantColor;
    }

    /**
     * Gets color palette for image
     *
     * @param int $colorCount
     * @param int $quality
     * @param string $colorValue
     * @return array|null
     */
    public function getColorPalette(\craft\elements\Asset|string $image, $colorCount, $quality, $colorValue)
    {
        try {
            $source = new LocalSourceImageModel($image);
            $source->getLocalCopy();
        } catch (ImagerException) {
            return null;
        }

        $palette = ColorThief::getPalette($source->getFilePath(), $colorCount, $quality);

        ImagerService::cleanSession();

        return $colorValue === 'hex' ? $this->paletteToHex($palette) : $palette;
    }

    /**
     * Calculates color brightness (https://www.w3.org/TR/AERT#color-contrast) on a scale from 0 (black) to 255 (white). 
     *
     */
    public function getBrightness(string|array $color): float
    {
        $c = color($color);
        return brightness($c);
    }

    /**
     * Get the hue channel of a color.
     */
    public function getHue(string|array $color): float
    {
        $c = color($color);
        return hue($c);
    }

    /**
     * Get the lightness channel of a color
     */
    public function getLightness(string|array $color): float
    {
        $c = color($color);
        return lightness($c);
    }

    /**
     * Checks brightness($color) >= $threshold. Accepts an optional $threshold float as the last parameter with a default of 127.5. 
     *
     * @param float $threshold
     */
    public function isBright(string|array $color, $threshold=127.5): bool
    {
        $c = color($color);
        return is_bright($c, $threshold);
    }

    /**
     * Checks lightness($color) >= $threshold. Accepts an optional $threshold float as the last parameter with a default of 50.0. 
     *
     * @param int $threshold
     */
    public function isLight(string|array $color, $threshold=50): bool
    {
        $c = color($color);
        return is_light($c, $threshold);
    }

    /**
     * Checks perceived_brightness($color) >= $threshold. Accepts an optional $threshold float as the last parameter with a default of 127.5. 
     *
     * @param float $threshold
     */
    public function looksBright(string|array $color, $threshold = 127.5): bool
    {
        $c = color($color);
        return looks_bright($c, $threshold);
    }

    /**
     * Calculates the perceived brightness (http://alienryderflex.com/hsp.html) of a color on a scale from 0 (black) to 255 (white).
     */
    public function getPercievedBrightness(string|array $color): float 
    {
        $c = color($color);
        return perceived_brightness($c);
    }

    /**
     * Calculates the relative luminance (https://www.w3.org/TR/WCAG20/#relativeluminancedef) of a color on a scale from 0 (black) to 1 (white).
     */
    public function getRelativeLuminance(string|array $color): float 
    {
        $c = color($color);
        return relative_luminance($c);
    }

    /**
     * Get the saturation channel of a color.
     */
    public function getSaturation(string|array $color): float 
    {
        $c = color($color);
        return saturation($c);
    }

    /**
     * Calculates brightness difference (https://www.w3.org/TR/AERT#color-contrast) on a scale from 0 to 255.
     */
    public function getBrightnessDifference(string|array $color1, string|array $color2): float
    {
        $c1 = color($color1);
        $c2 = color($color2);
        return brightness_difference($c1, $c2);
    }

    /**
     * Calculates color difference (https://www.w3.org/TR/AERT#color-contrast) on a scale from 0 to 765.
     */
    public function getColorDifference(string|array $color1, string|array $color2): int
    {
        $c1 = color($color1);
        $c2 = color($color2);
        return color_difference($c1, $c2);
    }

    /**
     * Calculates the contrast ratio (https://www.w3.org/TR/WCAG20/#contrast-ratiodef) between two colors on a scale from 1 to 21.
     */
    public function getContrastRatio(string|array $color1, string|array $color2): float
    {
        $c1 = color($color1);
        $c2 = color($color2);
        return contrast_ratio($c1, $c2);
    }
    
    /**
     * Convert rgb color to hex
     *
     * @param array $rgb
     */
    public static function rgb2hex($rgb): string
    {
        return '#' . sprintf('%02x', $rgb[0]) . sprintf('%02x', $rgb[1]) . sprintf('%02x', $rgb[2]);
    }

    /**
     * Convert hex color to rgb
     *
     * @param string $hex
     */
    public static function hex2rgb($hex): array
    {
        $hex = str_replace('#', '', $hex);

        if (\strlen($hex) === 3) {
            $r = hexdec($hex[0] . $hex[0]);
            $g = hexdec($hex[1] . $hex[1]);
            $b = hexdec($hex[2] . $hex[2]);
        } else {
            $r = hexdec($hex[0] . $hex[1]);
            $g = hexdec($hex[2] . $hex[3]);
            $b = hexdec($hex[4] . $hex[5]);
        }

        return [$r, $g, $b];
    }

    /**
     * Convert palette to array of hex colors
     *
     * @param array $palette
     */
    private function paletteToHex($palette): array
    {
        $r = [];
        foreach ($palette as $paletteColor) {
            $r[] = self::rgb2hex($paletteColor);
        }

        return $r;
    }
}
