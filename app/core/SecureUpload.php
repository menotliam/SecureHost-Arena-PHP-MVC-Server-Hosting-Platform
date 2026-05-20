<?php

/**
 * Hardened upload helpers: finfo MIME, getimagesize for rasters, random names, SVG sanitization.
 */
class SecureUpload
{
    const DEFAULT_MAX_BYTES = 2097152;

    /** @var int|null */
    private static $webpImageType;

    /**
     * @return int
     */
    private static function webpImageType()
    {
        if (self::$webpImageType === null) {
            self::$webpImageType = defined('IMAGETYPE_WEBP') ? IMAGETYPE_WEBP : 18;
        }
        return self::$webpImageType;
    }

    /**
     * @param string $path
     * @return string|false
     */
    public static function mimeType($path)
    {
        if (!is_readable($path)) {
            return false;
        }
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        if ($fi === false) {
            return false;
        }
        $mime = finfo_file($fi, $path);
        finfo_close($fi);
        return $mime !== false ? $mime : false;
    }

    /**
     * @return string
     */
    private static function randomFilename($prefix, $ext)
    {
        return $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
    }

    /**
     * Raster uploads (JPEG/PNG/GIF/WEBP): finfo + getimagesize + MIME/IMAGETYPE alignment.
     *
     * @param array       $file     One $_FILES element
     * @param string      $destDir  Absolute directory path with trailing slash
     * @param string      $prefix   Filename prefix (e.g. av_, n_)
     * @param int         $maxBytes
     * @return array{ok:bool, filename?:string, message?:string}
     */
    public static function storeRasterUpload(array $file, $destDir, $prefix = 'img_', $maxBytes = self::DEFAULT_MAX_BYTES)
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Tải lên không thành công.'];
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'message' => 'Tệp tải lên không hợp lệ.'];
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            return ['ok' => false, 'message' => 'Dung lượng tệp vượt quá giới hạn cho phép.'];
        }

        $info = @getimagesize($tmp);
        if ($info === false || !isset($info[2])) {
            return ['ok' => false, 'message' => 'Tệp không phải ảnh hợp lệ.'];
        }
        $imageType = (int) $info[2];

        $typeMeta = self::rasterTypeMeta($imageType);
        if ($typeMeta === null) {
            return ['ok' => false, 'message' => 'Định dạng ảnh không được hỗ trợ.'];
        }

        $detected = self::mimeType($tmp);
        if ($detected === false || !in_array($detected, $typeMeta['mimes'], true)) {
            return ['ok' => false, 'message' => 'Loại tệp không khớp với nội dung thực tế.'];
        }

        $ext = $typeMeta['ext'];
        if (!is_dir($destDir) && !@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            return ['ok' => false, 'message' => 'Không thể tạo thư mục lưu tệp.'];
        }

        $filename = self::randomFilename($prefix, $ext);
        $target = $destDir . $filename;
        if (!move_uploaded_file($tmp, $target)) {
            return ['ok' => false, 'message' => 'Không thể lưu tệp lên máy chủ.'];
        }
        @chmod($target, 0644);

        return ['ok' => true, 'filename' => $filename];
    }

    /**
     * @param int $imageType IMAGETYPE_*
     * @return array{ext:string,mimes:string[]}|null
     */
    private static function rasterTypeMeta($imageType)
    {
        $webp = self::webpImageType();
        $map = [
            IMAGETYPE_JPEG => ['ext' => 'jpg', 'mimes' => ['image/jpeg', 'image/pjpeg']],
            IMAGETYPE_PNG => ['ext' => 'png', 'mimes' => ['image/png']],
            IMAGETYPE_GIF => ['ext' => 'gif', 'mimes' => ['image/gif']],
            $webp => ['ext' => 'webp', 'mimes' => ['image/webp']],
        ];
        return isset($map[$imageType]) ? $map[$imageType] : null;
    }

    /**
     * Branding: PNG (raster checks), ICO (finfo + getimagesize), SVG (finfo + DOM sanitize).
     *
     * @param array  $file
     * @param string $uploadDir
     * @param string $currentFileName basename only
     * @return array{success:bool, filename:string, message:string}
     */
    public static function storeBrandingUpload(array $file, $uploadDir, $currentFileName = '')
    {
        if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'filename' => $currentFileName, 'message' => ''];
        }
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'filename' => $currentFileName, 'message' => 'Không thể tải logo lên. Vui lòng thử lại.'];
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['success' => false, 'filename' => $currentFileName, 'message' => 'Tệp tải lên không hợp lệ.'];
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::DEFAULT_MAX_BYTES) {
            return ['success' => false, 'filename' => $currentFileName, 'message' => 'Logo phải nhỏ hơn hoặc bằng 2MB.'];
        }

        $originalName = trim((string) ($file['name'] ?? ''));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['png', 'svg', 'ico'];
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['success' => false, 'filename' => $currentFileName, 'message' => 'Chỉ chấp nhận tệp PNG, SVG hoặc ICO.'];
        }

        $mime = self::mimeType($tmp);
        if ($mime === false) {
            return ['success' => false, 'filename' => $currentFileName, 'message' => 'Không xác định được loại tệp.'];
        }

        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'filename' => $currentFileName, 'message' => 'Không thể tạo thư mục lưu logo.'];
        }

        $newFileName = self::randomFilename('brand_', $extension);
        $targetPath = $uploadDir . $newFileName;

        if ($extension === 'svg') {
            $svgMimes = ['image/svg+xml', 'text/plain', 'text/xml', 'application/xml'];
            if (!in_array($mime, $svgMimes, true)) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Tệp SVG không hợp lệ.'];
            }
            $svgContent = file_get_contents($tmp);
            if ($svgContent === false) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Không đọc được tệp SVG.'];
            }
            $sanitized = self::sanitizeSvg($svgContent);
            if ($sanitized === false) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Tệp SVG không an toàn hoặc bị lỗi cấu trúc.'];
            }
            if (file_put_contents($targetPath, $sanitized) === false) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Không thể lưu logo lên máy chủ.'];
            }
            @chmod($targetPath, 0644);
        } elseif ($extension === 'png') {
            if ($mime !== 'image/png') {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Tệp PNG không hợp lệ.'];
            }
            $info = @getimagesize($tmp);
            if ($info === false || !isset($info[2]) || (int) $info[2] !== IMAGETYPE_PNG) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Tệp PNG không phải ảnh hợp lệ.'];
            }
            if (!move_uploaded_file($tmp, $targetPath)) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Không thể lưu logo lên máy chủ.'];
            }
            @chmod($targetPath, 0644);
        } else {
            $icoMimes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/ico', 'application/octet-stream'];
            if (!in_array($mime, $icoMimes, true)) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Tệp ICO không hợp lệ.'];
            }
            $icoType = defined('IMAGETYPE_ICO') ? IMAGETYPE_ICO : 17;
            $info = @getimagesize($tmp);
            if ($info === false || !isset($info[2]) || (int) $info[2] !== $icoType) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Tệp ICO không phải ảnh hợp lệ.'];
            }
            if (!move_uploaded_file($tmp, $targetPath)) {
                return ['success' => false, 'filename' => $currentFileName, 'message' => 'Không thể lưu logo lên máy chủ.'];
            }
            @chmod($targetPath, 0644);
        }

        $oldFile = basename((string) $currentFileName);
        if ($oldFile !== '' && $oldFile !== $newFileName) {
            $oldPath = $uploadDir . $oldFile;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return ['success' => true, 'filename' => $newFileName, 'message' => ''];
    }

    /**
     * Strip script, event handlers, and dangerous references from SVG XML.
     *
     * @param string $content
     * @return string|false
     */
    public static function sanitizeSvg($content)
    {
        $content = trim((string) $content);
        if ($content === '') {
            return false;
        }

        $prev = libxml_use_internal_errors(true);
        if (function_exists('libxml_disable_entity_loader')) {
            libxml_disable_entity_loader(true);
        }

        $doc = new DOMDocument();
        $loaded = $doc->loadXML($content, LIBXML_NONET | LIBXML_PARSEHUGE);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (!$loaded) {
            return false;
        }

        $svgNs = 'http://www.w3.org/2000/svg';
        $xlinkNs = 'http://www.w3.org/1999/xlink';

        $removeTags = ['script', 'foreignObject', 'iframe', 'object', 'embed', 'audio', 'video'];
        foreach ($removeTags as $tag) {
            $nodes = $doc->getElementsByTagNameNS($svgNs, $tag);
            while ($nodes->length > 0) {
                $n = $nodes->item(0);
                if ($n && $n->parentNode) {
                    $n->parentNode->removeChild($n);
                }
            }
            $fallback = $doc->getElementsByTagName($tag);
            while ($fallback->length > 0) {
                $n = $fallback->item(0);
                if ($n && $n->parentNode) {
                    $n->parentNode->removeChild($n);
                }
            }
        }

        $xpath = new DOMXPath($doc);
        $all = $xpath->query('//*');
        if ($all !== false) {
            foreach ($all as $el) {
                if (!$el instanceof DOMElement) {
                    continue;
                }
                $attrs = [];
                foreach ($el->attributes as $attr) {
                    $attrs[] = $attr;
                }
                foreach ($attrs as $attr) {
                    $name = $attr->name;
                    $lc = strtolower($name);
                    if (strpos($lc, 'on') === 0 && strlen($lc) > 2) {
                        $el->removeAttribute($name);
                        continue;
                    }
                    if ($lc === 'style' && preg_match('/expression\s*\(|javascript\s*:|@import/i', $attr->value)) {
                        $el->removeAttribute($name);
                        continue;
                    }
                    if (($lc === 'href' || $lc === 'xlink:href') && preg_match('/^\s*(javascript|data):/i', $attr->value)) {
                        $el->removeAttribute($name);
                    }
                }
            }
        }

        $uses = $xpath->query('//*[local-name()="use"]');
        if ($uses !== false) {
            $toRemove = [];
            foreach ($uses as $use) {
                if (!$use instanceof DOMElement || !$use->parentNode) {
                    continue;
                }
                $href = $use->getAttributeNS($xlinkNs, 'href');
                if ($href === '') {
                    $href = $use->getAttribute('href');
                }
                if ($href !== '' && preg_match('#^\s*([a-z][a-z0-9+.-]*:|//)#i', $href)) {
                    $toRemove[] = $use;
                }
            }
            foreach ($toRemove as $use) {
                if ($use->parentNode) {
                    $use->parentNode->removeChild($use);
                }
            }
        }

        $out = $doc->saveXML();
        if ($out === false || $out === '') {
            return false;
        }

        $check = strtolower($out);
        if (
            strpos($check, '<script') !== false
            || strpos($check, 'javascript:') !== false
            || preg_match('/\bon\w+\s*=/i', $out)
        ) {
            return false;
        }

        return $out;
    }
}
