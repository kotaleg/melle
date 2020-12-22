<?php
namespace pro_image_proxy;

class signed
{
    private $baseUrl;
    private $key;
    private $salt;

    private $options = array(
        'resize_type' => 'fit',
        'width' => 0,
        'height' => 0,
        'gravity' => 'sm',
        'enlarge' => false,
        'extend' => true,
        'gravity_type' => 'sm',
        'extension' => null,
        'encodeUrl' => true,
    );

    function __construct($baseUrl, $key, $salt)
    {
        $this->baseUrl = $baseUrl;

        // remove trailing slash
        if (utf8_strlen($this->baseUrl) > 0
        && strcmp(utf8_substr($this->baseUrl, -1, 1), '/') === 0) {
            $this->baseUrl = utf8_substr($this->baseUrl, 0, -1);
        }

        $this->key = pack("H*" , $key);

        if (empty($this->key)) {
            throw new \Exception('Key expected to be hex-encoded string');
        }

        $this->salt = pack("H*" , $salt);

        if (empty($this->salt)) {
            throw new \Exception('Salt expected to be hex-encoded string');
        }
    }

    public function setWidth($width)
    {
        $this->options['width'] = (int) $width;
    }

    public function setHeight($height)
    {
        $this->options['height'] = (int) $height;
    }

    public function setResizeType($resizeType)
    {
        $allowedTypes = array(
            'fit',
            'fill',
            'auto',
        );

        if (!in_array($resizeType, $allowedTypes)) {
            return;
        }

        $this->options['resize_type'] = (string) $resizeType;
    }

    public function prepareSignedPath($originalUrl)
    {
        if ($this->options['encodeUrl']) {
            $url = $this->prepareBase64($originalUrl);
        } else {
            $url = "/plain/{$originalUrl}";
        }

        $resizeParts = array(
            $this->options['resize_type'],
            $this->options['width'],
            $this->options['height'],
            $this->options['enlarge'],
            $this->options['extend'],
        );

        $resize = 'rs:' . implode(':', $resizeParts);

        $gravity = "g:{$this->options['gravity_type']}";

        $pathParts = array(
            $resize,
            $gravity,
            $url,
        );

        $path = '/' . implode('/', $pathParts);
        $signature = $this->generateSignature($path);

        return "{$this->baseUrl}/{$signature}{$path}";
    }

    private function generateSignature($path)
    {
        $hash = hash_hmac('sha256', "{$this->salt}{$path}", $this->key, true);
        return $this->prepareBase64($hash);
    }

    private function prepareBase64($string)
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }
}
