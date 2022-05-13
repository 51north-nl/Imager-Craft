<?php

namespace aelvan\imager\models;

use craft\elements\Asset;
use craft\helpers\FileHelper;

use Imagine\Image\Box;

use aelvan\imager\helpers\ImagerHelpers;
use aelvan\imager\services\ImagerService;
use aelvan\imager\exceptions\ImagerException;

class ImgixTransformedImageModel implements TransformedImageInterface, \Stringable
{
    /**
     * @var string
     */
    public $path = '';
    
    /**
     * @var string
     */
    public $filename;
    
    /**
     * @var string
     */
    public $url;
    
    /**
     * @var string
     */
    public $extension = '';
    
    /**
     * @var string
     */
    public $mimeType = '';
    
    /**
     * @var int
     */
    public $width = 0;
    
    /**
     * @var int
     */
    public $height = 0;
    
    /**
     * @var int|float
     */
    public $size = 0;

    /**
     * ImgixTransformedImageModel constructor.
     *
     * @param string|null        $imageUrl
     * @param Asset|string|null  $source
     * @param array|null         $params
     * @param ImgixSettings|null $profileConfig
     *
     * @throws ImagerException
     */
    public function __construct($imageUrl = null, $source = null, $params = null, private ?\aelvan\imager\models\ImgixSettings $profileConfig = null)
    {
        if ($imageUrl !== null) {
            $this->url = $imageUrl;
        }


        if (isset($params['w'], $params['h'])) {
            if (($source !== null) && ($params['fit'] === 'min' || $params['fit'] === 'max')) {
                [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);

                $paramsW = (int)$params['w'];
                $paramsH = (int)$params['h'];

                if ($sourceWidth / $sourceHeight < $paramsW / $paramsH) {
                    $useW = min($paramsW, $sourceWidth);
                    $this->width = $useW;
                    $this->height = round($useW * ($paramsH / $paramsW));
                } else {
                    $useH = min($paramsH, $sourceHeight);
                    $this->width = round($useH * ($paramsW / $paramsH));
                    $this->height = $useH;
                }
            } else {
                $this->width = (int)$params['w'];
                $this->height = (int)$params['h'];
            }
        } else {
            if (isset($params['w']) || isset($params['h'])) {

                if ($source !== null && $params !== null) {
                    [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);

                    if ((int)$sourceWidth === 0 || (int)$sourceHeight === 0) {
                        if (isset($params['w'])) {
                            $this->width = (int)$params['w'];
                        }
                        if (isset($params['h'])) {
                            $this->height = (int)$params['h'];
                        }
                    } else {
                        [$w, $h] = $this->calculateTargetSize($params, $sourceWidth, $sourceHeight);

                        $this->width = $w;
                        $this->height = $h;
                    }
                }
            } else {
                // Neither is set, image is not resized. Just get dimensions and return.
                [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);
                
                $this->width = $sourceWidth;
                $this->height = $sourceHeight;
            }
        }
    }

    /**
     * @param $source
     *
     * @throws ImagerException
     */
    protected function getSourceImageDimensions($source): array
    {
        if ($source instanceof Asset) {
            return [$source->getWidth(), $source->getHeight()];
        }

        if ($this->profileConfig !== null && $this->profileConfig->getExternalImageDimensions) {
            $sourceModel = new LocalSourceImageModel($source);
            $sourceModel->getLocalCopy();

            $sourceImageInfo = @getimagesize($sourceModel->getFilePath());

            return [$sourceImageInfo[0], $sourceImageInfo[1]];
        }

        return [0, 0];
    }

    /**
     * @param $params
     * @param $sourceWidth
     * @param $sourceHeight
     */
    protected function calculateTargetSize($params, $sourceWidth, $sourceHeight): array
    {
        $fit = $params['fit']; // clamp, clip, crop, facearea, fill, fillmax, max, min, and scale. 
        $ratio = $sourceWidth / $sourceHeight;

        $w = $params['w'] ?? null;
        $h = $params['h'] ?? null;

        switch ($fit) {
            case 'clip':
            case 'fill':
            case 'crop':
            case 'clamp':
            case 'scale':
                if ($w) {
                    return [$w, round($w / $ratio)];
                }
                if ($h) {
                    return [round($h * $ratio), $h];
                }
                break;
            case 'min':
            case 'max':
                if ($w) {
                    $useWidth = min($w, $sourceWidth);

                    return [$useWidth, round($useWidth / $ratio)];
                }
                if ($h) {
                    $useHeigth = min($h, $sourceHeight);

                    return [round($useHeigth * $ratio), $useHeigth];
                }
                break;
        }

        return [$w ?: 0, $h ?: 0];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getWidth(): int
    {
        return (int)$this->width;
    }

    public function getHeight(): int
    {
        return (int)$this->height;
    }

    /**
     * @param string $unit
     * @param int    $precision
     */
    public function getSize($unit = 'b', $precision = 2): float|int
    {
        return $this->size;
    }

    public function getDataUri(): string
    {
        return '';
    }

    public function getBase64Encoded(): string
    {
        return '';
    }

    public function getIsNew(): bool
    {
        return false;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->url;
    }

}
