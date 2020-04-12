<?php


namespace sreeb;


class Result
{
    private $response;
    private $info;
    private $errno;
    private $error;

    public function __construct($response, $info, $errno, $error)
    {
        $this->response = $response;
        $this->info = $info;
        $this->errno = $errno;
        $this->error = $error;
    }

    /**
     *  获取响应数据
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 获取一个cUrl连接资源句柄的信息
     * @param string $key
     * @return mixed
     */
    public function getInfo($key = 'ALL')
    {
        if ($key == 'ALL') {
            return $this->info;
        } elseif (isset($this->info[$key])) {
            return $this->info[$key];
        } else {
            throw new \RuntimeException($key . 'is bond that doesn\'t exist！');
        }
    }

    /**
     * 获取响应头
     * @return false|string
     */
    public function getHeader()
    {
        $headerSize = $this->info['header_size'];
        return substr($this->response, 0, $headerSize);
    }

    /**
     * 获取响应体
     * @return false|string
     */
    public function getBody()
    {
        $headerSize = $this->info['header_size'];
        return substr($this->response, $headerSize);
    }

    /**
     * 获取curl请求错误号
     * @return mixed
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * 获取curl请求错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

}