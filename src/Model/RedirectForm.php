<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 07/08/2017
 * Time: 17:33
 */

namespace PaymentGateway\VPosGaranti\Model;


class RedirectForm
{
    private $action;
    private $method;
    private $parameters;

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
}
