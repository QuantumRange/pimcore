<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Image\Adapter;

use Jcupitt\Vips;
use Pimcore\Image\Adapter;
use function rename;
use function strtolower;

class Libvips extends Adapter
{
    protected string $path;

    /**
     * @var null|Vips\Image
     */
    protected mixed $resource = null;

    public function load(string $imagePath, array $options = []): static|false
    {
        $this->path = $imagePath;
        $this->resource = Vips\Image::newFromFile($this->path);

        // set dimensions
        $this->setWidth($this->resource->width);
        $this->setHeight($this->resource->height);

        if (!$this->sourceImageFormat) {
            $this->sourceImageFormat = pathinfo($imagePath, PATHINFO_EXTENSION);
        }

        if (in_array(pathinfo($imagePath, PATHINFO_EXTENSION), ['png', 'gif'])) {
            // in GD only gif and PNG can have an alphachannel
            $this->setIsAlphaPossible(true);
        }

        $this->setModified(false);

        return $this;
    }

    public function getContentOptimizedFormat(): string
    {
        $format = 'pjpeg';
        if ($this->hasAlphaChannel()) {
            $format = 'png';
        }

        return $format;
    }

    public function save(string $path, string $format = null, int $quality = null): static
    {
        $format = strtolower($format);

        if (!$format || $format == 'png32') {
            $format = 'png';
        }

        if ($format == 'original') {
            $format = $this->sourceImageFormat;
        }

        $savePath = $path . "." . $format;

        $this->resource->writeToFile($savePath, [
            'Q' => $quality,
        ]);

        rename($savePath, $path);

        return $this;
    }

    private function hasAlphaChannel(): bool
    {
        if ($this->isAlphaPossible) {
            return $this->resource->hasAlpha();
        }

        return false;
    }

    protected function destroy(): void
    {

    }

    public function resize(int $width, int $height): static
    {
        $this->preModify();

        $scaleWidth = $width / $this->resource->width;
        $scaleHeight = $height / $this->resource->height;

        // Resize the image with separate scales for width and height
        $resizedImage = $this->resource->resize($scaleWidth, [
            'vscale' => $scaleHeight
        ]);

        $this->resource = $resizedImage;

        $this->postModify();

        return $this;
    }

    public function crop(int $x, int $y, int $width, int $height): static
    {
        // @TODO
        return $this;
    }

    public function frame(int $width, int $height, bool $forceResize = false): static
    {
        // @TODO
        return $this;
    }

    public function setBackgroundColor(string $color): static
    {
        // @TODO
        return $this;
    }

    public function setBackgroundImage(string $image, string $mode = null): static
    {
        // @TODO
        return $this;
    }

    public function grayscale(): static
    {
        // @TODO
        return $this;
    }

    public function sepia(): static
    {
        // @TODO
        return $this;
    }

    public function addOverlay(mixed $image, int $x = 0, int $y = 0, int $alpha = 100, string $composite = 'COMPOSITE_DEFAULT', string $origin = 'top-left'): static
    {
        // @TODO
        return $this;
    }

    public function mirror(string $mode): static
    {
        // @TODO
        return $this;
    }

    public function rotate(int $angle): static
    {
        // @TODO
        return $this;
    }

    public function supportsFormat(string $format, bool $force = false): bool
    {
        // @TODO
        return true;
    }
}
