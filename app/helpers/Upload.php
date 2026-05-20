<?php
class Upload
{
    protected $file;
    protected $errors = [];
    protected $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    protected $maxSize = 21097152; // 20MB

    public function __construct($file = null)
    {
        $this->file = $file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setMaxSize($bytes)
    {
        $this->maxSize = (int)$bytes;
    }

    public function uploadImage($targetDir)
    {
        if (!$this->file || empty($this->file['name'])) {
            return ['success' => false, 'error' => 'No file provided'];
        }

        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error code: ' . $this->file['error']];
        }

        if ($this->file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => 'File too large'];
        }

        $ext = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }

        // normalize target dir separators and ensure absolute path
        $targetDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetDir);
        if (!preg_match('/^([A-Za-z]:\\\\|\\\\\\\\|\/)/', $targetDir)) {
            $targetDir = rtrim(dirname(APPROOT), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($targetDir, DIRECTORY_SEPARATOR);
        }
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0755, true)) {
                error_log('Upload: unable to create target directory: ' . $targetDir);
                return ['success' => false, 'error' => 'Unable to create target directory: ' . $targetDir];
            }
        }

        $basename = uniqid('img_') . '.' . $ext;
        $targetPath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $basename;
        // Diagnostics and safe move with fallbacks
        $tmp = isset($this->file['tmp_name']) ? $this->file['tmp_name'] : null;
        if (empty($tmp) || (!is_uploaded_file($tmp) && !file_exists($tmp))) {
            error_log('Upload: tmp file is not a valid uploaded file: ' . ($tmp ?? 'null'));
            return ['success' => false, 'error' => 'Temporary upload file not found or invalid'];
        }

        if (!is_writable($targetDir)) {
            error_log('Upload: target directory not writable: ' . $targetDir);
            return ['success' => false, 'error' => 'Target directory is not writable: ' . $targetDir];
        }

        // try the proper move first
        if (is_uploaded_file($tmp) && @move_uploaded_file($tmp, $targetPath)) {
            @chmod($targetPath, 0644);
            return ['success' => true, 'filename' => $basename];
        }

        // fallback: rename (works when tmp is on same filesystem)
        if (file_exists($tmp) && @rename($tmp, $targetPath)) {
            @chmod($targetPath, 0644);
            return ['success' => true, 'filename' => $basename];
        }

        // fallback: copy then unlink
        if (file_exists($tmp) && @copy($tmp, $targetPath)) {
            @chmod($targetPath, 0644);
            @unlink($tmp);
            return ['success' => true, 'filename' => $basename];
        }

        error_log('Upload: move/rename/copy failed. tmp: ' . ($tmp ?? '') . ' -> target: ' . $targetPath);
        return ['success' => false, 'error' => 'Unable to move uploaded file to ' . $targetPath];
    }
}
