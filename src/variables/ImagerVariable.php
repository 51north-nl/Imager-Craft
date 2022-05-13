<?php
/**
 * Imager plugin for Craft CMS 3.x
 *
 * Image transforms gone wild
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2017 André Elvan
 */

namespace aelvan\imager\variables;

use Craft;

use aelvan\imager\Imager as Plugin;
use aelvan\imager\services\ImagerColorService;
use aelvan\imager\services\ImagerService;
use \aelvan\imager\exceptions\ImagerException;
use craft\elements\Asset;

class ImagerVariable
{
    /**
     * Transforms an image
     *
     * @param array        $transform
     * @param array        $transformDefaults
     * @param array        $configOverrides
     *
     * @return mixed
     * @throws ImagerException
     */
    public function transformImage(\craft\elements\Asset|string $file, $transform, $transformDefaults = null, $configOverrides = null)
    {
        $image = Plugin::$plugin->imager->transformImage($file, $transform, $transformDefaults, $configOverrides);

        return $image;
    }

    /**
     * Takes an array of models that supports getUrl() and getWidth(), and returns a srcset
     * and returns a srcset string
     *
     * @param array  $images
     * @param string $descriptor
     */
    public function srcset($images, $descriptor = 'w'): string
    {
        return Plugin::$plugin->imager->srcset($images, $descriptor);
    }

    /**
     * Returns a base64 encoded transparent pixel.
     *
     * @param int    $width
     * @param int    $height
     * @param string $color
     */
    public function base64Pixel($width = 1, $height = 1, $color = 'transparent'): string
    {
        return 'data:image/svg+xml;charset=utf-8,'.rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' width='$width' height='$height' style='background:$color'/>");
    }

    /**
     * Returns an image placeholder.
     *
     * @param array|null $config
     *
     * @throws ImagerException
     */
    public function placeholder($config = null): string
    {
        return Plugin::$plugin->placeholder->placeholder($config);
    }

    /**
     * Gets the dominant color of an image
     *
     * @param string       $colorValue
     * @param int          $quality
     * @return mixed
     */
    public function getDominantColor(\craft\elements\Asset|string $image, $quality = 10, $colorValue = 'hex')
    {
        return Plugin::$plugin->color->getDominantColor($image, $quality, $colorValue);
    }

    /**
     * Gets a palette of colors from an image
     *
     * @param string       $colorValue
     * @param int          $colorCount
     * @param int          $quality
     * @return mixed
     */
    public function getColorPalette(\craft\elements\Asset|string $image, $colorCount = 8, $quality = 10, $colorValue = 'hex')
    {
        return Plugin::$plugin->color->getColorPalette($image, $colorCount, $quality, $colorValue);
    }

    /**
     * Converts a hex color value to rgb
     *
     * @param string $color
     */
    public function hex2rgb($color): array
    {
        return ImagerColorService::hex2rgb($color);
    }

    /**
     * Converts a rgb color value to hex
     *
     * @param array $color
     */
    public function rgb2hex($color): string
    {
        return ImagerColorService::rgb2hex($color);
    }

    /**
     * Calculates color brightness (https://www.w3.org/TR/AERT#color-contrast) on a scale from 0 (black) to 255 (white). 
     *
     */
    public function getBrightness(string|array $color): float
    {
        return Plugin::$plugin->color->getBrightness($color);
    }
    
    /**
     * Get the hue channel of a color.
     */
    public function getHue(string|array $color): float
    {
        return Plugin::$plugin->color->getHue($color);
    }
    
     /**
     * Get the lightness channel of a color
     */
    public function getLightness(string|array $color): float
    {
        return Plugin::$plugin->color->getLightness($color);
    }
    
    /**
     * Checks brightness($color) >= $threshold. Accepts an optional $threshold float as the last parameter with a default of 127.5. 
     *
     * @param float $threshold
     */
    public function isBright(string|array $color, $threshold=127.5): bool
    {
        return Plugin::$plugin->color->isBright($color, $threshold);
    }
    
    /**
     * Checks lightness($color) >= $threshold. Accepts an optional $threshold float as the last parameter with a default of 50.0. 
     *
     * @param int $threshold
     */
    public function isLight(string|array $color, $threshold=50): bool
    {
        return Plugin::$plugin->color->isLight($color, $threshold);
    }
    
    /**
     * Checks perceived_brightness($color) >= $threshold. Accepts an optional $threshold float as the last parameter with a default of 127.5. 
     *
     * @param float $threshold
     */
    public function looksBright(string|array $color, $threshold=127.5): bool
    {
        return Plugin::$plugin->color->looksBright($color, $threshold);
    }
    
    /**
     * Calculates the perceived brightness (http://alienryderflex.com/hsp.html) of a color on a scale from 0 (black) to 255 (white).
     */
    public function getPercievedBrightness(string|array $color): float
    {
        return Plugin::$plugin->color->getPercievedBrightness($color);
    }
    
    /**
     * Calculates the relative luminance (https://www.w3.org/TR/WCAG20/#relativeluminancedef) of a color on a scale from 0 (black) to 1 (white).
     */
    public function getRelativeLuminance(string|array $color): float
    {
        return Plugin::$plugin->color->getRelativeLuminance($color);
    }
    
    /**
     * Get the saturation channel of a color.
     */
    public function getSaturation(string|array $color): float
    {
        return Plugin::$plugin->color->getSaturation($color);
    }
    
    /**
     * Calculates brightness difference (https://www.w3.org/TR/AERT#color-contrast) on a scale from 0 to 255.
     */
    public function getBrightnessDifference(string|array $color1, string|array $color2): float
    {
        return Plugin::$plugin->color->getBrightnessDifference($color1, $color2);
    }
    
    /**
     * Calculates color difference (https://www.w3.org/TR/AERT#color-contrast) on a scale from 0 to 765.
     */
    public function getColorDifference(string|array $color1, string|array $color2): int
    {
        return Plugin::$plugin->color->getColorDifference($color1, $color2);
    }
    
    /**
     * Calculates the contrast ratio (https://www.w3.org/TR/WCAG20/#contrast-ratiodef) between two colors on a scale from 1 to 21.
     */
    public function getContrastRatio(string|array $color1, string|array $color2): float
    {
        return Plugin::$plugin->color->getContrastRatio($color1, $color2);
    }
    
    /**
     * Checks for webp support in image driver
     */
    public function serverSupportsWebp(): bool
    {
        return ImagerService::hasSupportForWebP();
    }

    /**
     * Checks for webp support in browser
     */
    public function clientSupportsWebp(): bool
    {
        $request = Craft::$app->getRequest();

        return $request->accepts('image/webp');
    }

    /**
     * Checks if asset is animated (only gif support atm)
     *
     *
     *
     * @throws ImagerException
     */
    public function isAnimated(\craft\elements\Asset|string $asset): bool
    {
        return Plugin::$plugin->imager->isAnimated($asset);
    }

    /**
     * Checks if Imgix is enabled
     */
    public function imgixEnabled(): bool
    {
        return Plugin::$plugin->getSettings()->transformer === 'imgix';
    }
}
