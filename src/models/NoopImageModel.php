<?php

namespace aelvan\imager\models;

use craft\helpers\FileHelper;

use aelvan\imager\helpers\ImagerHelpers;
use aelvan\imager\services\ImagerService;
use aelvan\imager\exceptions\ImagerException;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;

use yii\base\InvalidConfigException;

class NoopImageModel implements TransformedImageInterface, \Stringable
{
    public $path;
    public $filename;
    public $url;
    public $extension;
    public $mimeType;
    
    /**
     * @var int
     */
    public $width;
    
    /**
     * @var int
     */
    public $height;
    
    /**
     * @var int|float
     */
    public $size;
    
    /**
     * @var bool
     */
    public $isNew = false;

    /**
     * Constructor
     *
     * @param LocalSourceImageModel $sourceModel
     * @param array $transform
     *
     * @throws ImagerException
     */
    public function __construct($sourceModel, $transform)
    {
        $this->path = $sourceModel->getFilePath();
        $this->filename = $sourceModel->filename;
        $this->url = $sourceModel->url;

        $this->extension = $sourceModel->extension;
        $this->size = @filesize($sourceModel->getFilePath());

        try {
            $this->mimeType = FileHelper::getMimeType($sourceModel->getFilePath());
        } catch (InvalidConfigException $e) {
            // just ignore
        }

        $imageInfo = @getimagesize($sourceModel->getFilePath());

        /** @var ConfigModel $settings */
        $config = ImagerService::getConfig();

        $sourceImageInfo = @getimagesize($sourceModel->getFilePath());

        try {
            $sourceSize = new Box($sourceImageInfo[0], $sourceImageInfo[1]);
            $targetCrop = ImagerHelpers::getCropSize($sourceSize, $transform, $config->getSetting('allowUpscale', $transform));
            $this->width = $targetCrop->getWidth();
            $this->height = $targetCrop->getHeight();
        } catch (InvalidArgumentException $e) {
            throw new ImagerException($e->getMessage(), $e->getCode(), $e);
        }
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
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    /**
     * @param string $unit
     * @param int $precision
     */
    public function getSize($unit = 'b', $precision = 2): float|int
    {
        $unit = strtolower($unit);
        return match ($unit) {
            'g', 'gb' => round(((int)$this->size) / 1024 / 1024 / 1024, $precision),
            'm', 'mb' => round(((int)$this->size) / 1024 / 1024, $precision),
            'k', 'kb' => round(((int)$this->size) / 1024, $precision),
            default => $this->size,
        };
    }

    public function getDataUri(): string
    {
        $imageData = $this->getBase64Encoded();
        return sprintf('data:image/%s;base64,%s', $this->extension, $imageData);
    }

    public function getBase64Encoded(): string
    {
        $image = @file_get_contents($this->path);
        return base64_encode($image);
    }

    public function __toString(): string
    {
        return (string)$this->url;
    }
}
