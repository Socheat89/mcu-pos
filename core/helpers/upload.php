<?php
// core/helpers/upload.php

if (!function_exists('mc_store_uploaded_image_as_webp')) {
    function mc_store_uploaded_image_as_webp(array $file, string $uploadDir, string $prefix, int $maxBytes = 5242880): string {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed.');
        }

        $tmpName = $file['tmp_name'] ?? '';
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('Invalid uploaded image.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new RuntimeException('Image must be 5 MB or smaller.');
        }

        $info = @getimagesize($tmpName);
        if ($info === false || empty($info['mime'])) {
            throw new RuntimeException('Invalid image file.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName) ?: $info['mime'];

        if ($mime !== $info['mime']) {
            throw new RuntimeException('Invalid image file.');
        }

        switch ($mime) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($tmpName);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($tmpName);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($tmpName);
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($tmpName);
                break;
            default:
                throw new RuntimeException('Only JPG, PNG, GIF, and WebP images are allowed.');
        }

        if (!$image) {
            throw new RuntimeException('Unable to process image.');
        }

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            imagedestroy($image);
            throw new RuntimeException('Upload directory is not available.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $safePrefix = preg_replace('/[^a-z0-9_-]+/i', '_', $prefix);
        $fileName = $safePrefix . '_' . bin2hex(random_bytes(12)) . '.webp';
        $targetPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

        if (!imagewebp($image, $targetPath, 80)) {
            imagedestroy($image);
            throw new RuntimeException('Unable to save image.');
        }

        imagedestroy($image);
        @chmod($targetPath, 0644);

        return $fileName;
    }
}
