<?php
namespace DreamFactory\Core\Components;

use DreamFactory\Library\Utility\Enums\Verbs;
use DreamFactory\Core\Enums\DataFormats;
use DreamFactory\Library\Utility\Scalar;

/**
 * Trait InternalServiceRequest
 *
 */
trait InternalServiceRequest
{
    use ApiVersion;

    /**
     * @var string
     */
    protected $method = null;
    /**
     * @var array
     */
    protected $parameters = null;
    /**
     * @var array
     */
    protected $headers = null;
    /**
     * @var null|string
     */
    protected $content = null;
    /**
     * @var null|string
     */
    protected $contentType = null;
    /**
     * @var array
     */
    protected $contentAsArray = [];

    /**
     * @param $verb
     *
     * @return $this
     * @throws \Exception
     */
    public function setMethod($verb)
    {
        if (!Verbs::contains($verb)) {
            throw new \Exception("Invalid method '$verb'");
        }

        $this->method = $verb;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return (is_null($this->parameters)) ? [] : $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($key = null, $default = null)
    {
        if (is_null($this->parameters)) {
            return $default;
        }

        if (null === $key) {
            return $this->parameters;
        } else {
            return array_get($this->parameters, $key, $default);
        }
    }

    /**
     * @param mixed $key
     * @param bool  $default
     *
     * @return mixed
     */
    public function getParameterAsBool($key, $default = false)
    {
        if (is_null($this->parameters)) {
            return $default;
        }

        return Scalar::boolval(array_get($this->parameters, $key, $default));
    }

    /**
     * @param mixed $data
     * @param int   $type
     *
     * @return $this
     */
    public function setContent($data, $type = DataFormats::PHP_ARRAY)
    {
        $this->content = $data;
        $this->contentType = $type;

        switch ($type) {
            case DataFormats::PHP_ARRAY:
                $this->contentAsArray = $data;
                break;
            case DataFormats::JSON:
                $this->contentAsArray = json_decode($data, true);
                break;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPayloadData(array $data)
    {
        $this->contentAsArray = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setPayloadKeyValue($key, $value)
    {
        $this->contentAsArray[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadData($key = null, $default = null)
    {
        if (null === $key) {
            return $this->contentAsArray;
        } else {
            return array_get($this->contentAsArray, $key, $default);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($key, $data)
    {
        $this->headers[$key] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($key = null, $default = null)
    {
        if (is_null($this->headers)) {
            return $default;
        }

        if (null === $key) {
            return $this->headers;
        } else {
            return array_get($this->headers, $key, $default);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return (is_null($this->headers)) ? [] : $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($key = null, $default = null)
    {
        //Todo:Experiment Request::file()...
        return null;
    }

    /**
     * @return array All attributes as an array
     */
    public function toArray()
    {
        try {
            $payload = $this->getPayloadData();
        } catch (\Exception $ex) {
            $payload = null;
        }

        return [
            'api_version'  => $this->getApiVersion(),
            'method'       => $this->getMethod(),
            'parameters'   => $this->getParameters(),
            'headers'      => $this->getHeaders(),
            'payload'      => $payload,
            'content'      => $this->getContent(),
            'content_type' => $this->getContentType(),
        ];
    }

    /**
     * @param array $data Merge some attributes from an array
     */
    public function mergeFromArray(array $data)
    {
        if (array_key_exists('method', $data)) {
            $this->setMethod(array_get($data, 'method'));
        }
        if (array_key_exists('parameters', $data)) {
            $this->setParameters(array_get($data, 'parameters'));
        }
        if (array_key_exists('headers', $data)) {
            $this->setHeaders(array_get($data, 'headers'));
        }
        if (array_key_exists('payload', $data)) {
            $this->setPayloadData(array_get($data, 'payload'));
        }
        if (array_key_exists('content', $data)) {
            $this->setContent(array_get($data, 'content'), array_get($data, 'content_type'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiKey()
    {
        //Check for API key in request parameters.
        $apiKey = $this->getParameter('api_key');
        if (empty($apiKey)) {
            //Check for API key in request HEADER.
            $apiKey = $this->getHeader('X_DREAMFACTORY_API_KEY');
        }

        return $apiKey;
    }

    /**
     * Returns request input.
     *
     * @param null $key
     * @param null $default
     *
     * @return array|string
     */
    public function input($key = null, $default = null)
    {
        return $this->getParameter($key, $this->getPayloadData($key, $default));
    }
}