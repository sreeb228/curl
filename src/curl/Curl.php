<?php

namespace curl;

class Curl
{
    public $curl;


    private $_headers;
    private $_cookies;

    /**
     * 返回当前实例化对象
     * @return Curl
     */
    public static function getInstance()
    {
        $object = new self();
        return $object->init();
    }

    /**
     * 初始化
     * @return $this
     */
    public function init()
    {
        if (!extension_loaded('cURL')) {
            throw new \RuntimeException('The cURL extension is not loaded, please see URL: https://www.php.net/manual/zh/book.curl.php');
        }
        $this->curl = curl_init();
        $this->setOpt(CURLOPT_HEADER, true);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLINFO_HEADER_OUT, true);
        return $this;
    }


    /**
     * @param $url
     * @param string $method
     * @param array $requestData
     * @param bool $asJson
     * @return $this
     */
    public function request(string $url, string $method = 'GET', $requestData = array(), $asJson = false)
    {
        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
                return $this->get($url, $requestData);
            case 'POST':
                return $this->post($url, $requestData, $asJson);
            case 'PUT':
                return $this->put($url, $requestData, $asJson);
            case 'DELETE':
                return $this->delete($url, $requestData, $asJson);
            default:
                throw new \RuntimeException('Please check the parameter value  \'method\' must be in get,post,put,delete');
        }

    }


    /**
     * @param $url
     * @param array $requestData
     * @return $this
     */
    public function get($url, $requestData = array())
    {
        $this->setOpt(CURLOPT_HTTPGET, true);
        if (empty($requestData)) {
            $this->setOpt(CURLOPT_URL, $url);
        } else {
            if (is_string($requestData)) {
                $this->setOpt(CURLOPT_URL, $url . '?' . $requestData);
            } else {
                $this->setOpt(CURLOPT_URL, $url . '?' . http_build_query($requestData));
            }
        }
        return $this;
    }

    /**
     * @param $url
     * @param array $requestData
     * @param bool $asJson
     * @return $this
     */
    public function post($url, $requestData = array(), $asJson = false)
    {
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_URL, $url);
        if ($asJson) {
            $this->setOpt(CURLOPT_POSTFIELDS, json_encode($requestData));
        } else {
            $this->payloadData($requestData);
        }
        return $this;
    }

    /**
     * @param $url
     * @param $requestData
     * @param $asJson
     * @return $this
     */
    public function put($url, $requestData, $asJson)
    {
        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_PUT, true);
        if ($asJson) {
            $this->setOpt(CURLOPT_POSTFIELDS, json_encode($requestData));
        } else {
            $this->payloadData($requestData);
        }
        return $this;
    }

    /**
     * @param $url
     * @param $requestData
     * @param $asJson
     * @return $this
     */
    public function delete($url, $requestData, $asJson)
    {
        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        if ($asJson) {
            $this->setOpt(CURLOPT_POSTFIELDS, json_encode($requestData));
        } else {
            $this->payloadData($requestData);
        }
        return $this;
    }

    /**
     * 设置请求header
     * @param $key
     * @param $value
     * @return $this
     */
    public function setHeader($key, $value = '')
    {
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $this->_headers[$k] = $k . ': ' . $v;
            }
        } else {
            $this->_headers[$key] = $key . ': ' . $value;
        }
        $this->setOpt(CURLOPT_HTTPHEADER, array_values($this->_headers));
        return $this;
    }


    /**
     * 设置请求cookie
     * @param $key
     * @param string $value
     * @return $this
     */
    public function setCookie($key, $value = '')
    {
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $this->_cookies[$k] = $v;
            }
            $this->setOpt(CURLOPT_COOKIE, http_build_query($this->_cookies, '', '; '));
        } elseif (is_file($key)) {
            $this->setOpt(CURLOPT_COOKIEFILE, $key);
        } else {
            $this->_cookies[$key] = $value;
            $this->setOpt(CURLOPT_COOKIE, http_build_query($this->_cookies, '', '; '));
        }
        return $this;
    }


    /**
     * 设置响应后返回的cookie储存文件
     * @param $filepath
     * @return $this
     */
    public function setResponseCookieStorable($filepath)
    {
        $this->setOpt(CURLOPT_COOKIEJAR, $filepath);
        return $this;
    }


    /**
     * 设置超时时间
     * @param int $connectTime //链接超时时间
     * @param int $timeout //响应超时时间
     * @return $this
     */
    public function setTimeout($connectTime = 5, $timeout = 5)
    {
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $connectTime);
        $this->setOpt(CURLOPT_TIMEOUT, $timeout);
        return $this;
    }


    /**
     * 设置SSL请求
     * @param bool $verifySSL //是否验证SSL
     * @param string $certType //证书类型
     * @param string $certPath //证书路径
     * @param string $certPassword //证书密码
     * @param string $keyType //私钥类型
     * @param string $keyPath //私钥存放路径
     * @return $this
     */
    public function setSSL(bool $verifySSL = false, string $certType = 'PEM', string $certPath = '', string $certPassword = '', string $keyType = 'PEM', string $keyPath = '')
    {
        if (!$verifySSL) {
            $this->setOpt(CURLOPT_SSL_VERIFYHOST, false);
            $this->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        } else {
            $this->setOpt(CURLOPT_SSLCERTTYPE, $certType);
            $this->setOpt(CURLOPT_SSLCERT, $certPath);
            $this->setOpt(CURLOPT_SSLCERTPASSWD, $certPassword);
            $this->setOpt(CURLOPT_SSLKEYTYPE, $keyType);
            $this->setOpt(CURLOPT_SSLKEY, $keyPath);
        }
        return $this;
    }


    /**
     * 设置代理
     * @param $ip
     * @param $port
     * @param $username
     * @param $password
     * @return $this
     */
    public function setProxy($ip, $port, $username, $password)
    {
        $this->setOpt(CURLOPT_PROXY, $ip);
        $this->setOpt(CURLOPT_PROXYPORT, $port);
        $this->setOpt(CURLOPT_PROXYUSERPWD, "$username:$password");
        return $this;
    }


    /**
     *  获取重定向后的数据
     * @param bool $asLocation 是否获取重定向后的数据
     * @param int $maxredirs 最多重定向几次，为0则不限制
     * @return $this
     */
    public function setLocation(bool $asLocation = true, int $maxredirs = 0)
    {
        if ($asLocation) {
            $this->setOpt(CURLOPT_FOLLOWLOCATION, true);
        }
        if ($maxredirs > 0) {
            $this->setOpt(CURLOPT_MAXREDIRS, $maxredirs);
        }
        return $this;
    }


    /**
     * 装载发送数据
     * @param $requestData
     */
    private function payloadData($requestData)
    {
        $tab = false;
        if (is_array($requestData) || is_object($requestData)) {
            foreach ($requestData as $data) {
                if ($data instanceof \CURLFile) {
                    $tab = true;
                    break;
                }
            }
            if ($tab) {
                $this->setOpt(CURLOPT_POSTFIELDS, $requestData);
            } else {
                $this->setOpt(CURLOPT_POSTFIELDS, http_build_query($requestData));
            }
        }
    }

    /**
     * 设置curl配置项
     * @param $option
     * @param $value
     * @return $this
     */
    public function setOpt($option, $value)
    {
        curl_setopt($this->curl, $option, $value);
        return $this;
    }


    /**
     *  发送请求
     * @param bool $isClose 是否关闭curl资源
     * @return Result
     */
    public function send(bool $isClose = true)
    {
        $response = curl_exec($this->curl);
        $info = curl_getinfo($this->curl);
        $errno = curl_errno($this->curl);
        $error = curl_error($this->curl);

        $result = new Result($response, $info, $errno, $error);
        if ($isClose === true) {
            $this->close();
        }
        return $result;
    }


    /**
     * 关闭curl资源
     */
    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }
}