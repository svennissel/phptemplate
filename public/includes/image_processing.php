<?php
/**
 * Hilfsfunktionen für die Bildverarbeitung.
 */

/**
 * Verarbeitet ein hochgeladenes Profilbild.
 * Erstellt eine WebP-Version in 30x30.
 *
 * @param array $file Das Element aus $_FILES
 * @param array|null $crop Die Crop-Parameter [x1, y1, x2, y2]
 * @param string $targetDir Zielverzeichnis für die Bilder
 * @return string|null Der Basis-Dateiname (ohne Endung) bei Erfolg, sonst null.
 */
function processProfileImage(array $file, ?array $crop = null, string $targetDir = 'uploads/logos/'): ?string {
    if (!isset($file['tmp_name']) || $file['tmp_name'] === '') {
        return null;
    }

    $logo = uniqid() . '.webp';
    $fullTargetDir = __DIR__ . '/../' . $targetDir;
    $logoPath = $fullTargetDir . $logo;

    if (!is_dir($fullTargetDir)) {
        if (!mkdir($fullTargetDir, 0775, true)) {
            return null;
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $logoPath)) {
        return null;
    }

    // Bild verarbeiten
    $imageInfo = getimagesize($logoPath);
    if ($imageInfo) {
        $srcWidth = $imageInfo[0];
        $srcHeight = $imageInfo[1];
        $mime = $imageInfo['mime'];

        $srcImage = false;
        switch ($mime) {
            case 'image/jpeg': $srcImage = imagecreatefromjpeg($logoPath); break;
            case 'image/png':  $srcImage = imagecreatefrompng($logoPath); break;
            case 'image/gif':  $srcImage = imagecreatefromgif($logoPath); break;
            case 'image/webp': $srcImage = imagecreatefromwebp($logoPath); break;
            case 'image/avif': 
                if (function_exists('imagecreatefromavif')) {
                    $srcImage = imagecreatefromavif($logoPath); 
                }
                break;
        }

        if ($srcImage) {
            // Cropping anwenden falls vorhanden
            $cropX = 0;
            $cropY = 0;
            $cropWidth = $srcWidth;
            $cropHeight = $srcHeight;

            if ($crop && count($crop) === 4) {
                $cropX = (int)$crop[0];
                $cropY = (int)$crop[1];
                $cropWidth = (int)($crop[2] - $crop[0]);
                $cropHeight = (int)($crop[3] - $crop[1]);
            } else {
                // Quadratisch zuschneiden (Mitte), falls kein Crop angegeben
                $size = min($srcWidth, $srcHeight);
                $cropX = (int)(($srcWidth - $size) / 2);
                $cropY = (int)(($srcHeight - $size) / 2);
                $cropWidth = $size;
                $cropHeight = $size;
            }

            // Hauptbild (z.B. 200x200)
            $mainSize = 200;
            $mainThumb = imagecreatetruecolor($mainSize, $mainSize);
            imagealphablending($mainThumb, false);
            imagesavealpha($mainThumb, true);
            imagecopyresampled($mainThumb, $srcImage, 0, 0, $cropX, $cropY, $mainSize, $mainSize, $cropWidth, $cropHeight);
            imagewebp($mainThumb, $logoPath, 80); // Das Original mit dem 200x200 Bild überschreiben
            imagedestroy($mainThumb);

            // Thumbnail (30x30)
            $thumbSize = 30;
            $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            imagecopyresampled($thumb, $srcImage, 0, 0, $cropX, $cropY, $thumbSize, $thumbSize, $cropWidth, $cropHeight);
            imagewebp($thumb, $fullTargetDir . str_replace('.webp', '', $logo) . '_30.webp', 50);
            imagedestroy($thumb);

            imagedestroy($srcImage);
        }
    }

    return $logo;
}
