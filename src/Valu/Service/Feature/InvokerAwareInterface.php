<?php
namespace Valu\Service\Feature;

use Valu\Service\Invoker\InvokerInterface;

interface InvokerAwareInterface
{
    public function setInvoker(InvokerInterface $invoker);
}