<?php

namespace Omnipay\Tests;

/**
 * Base Gateway Test class
 *
 * Ensures all gateways conform to consistent standards
 */
abstract class GatewayTestCase extends TestCase
{
    public function testGetNameNotEmpty()
    {
        $name = $this->gateway->getName();
        $this->assertNotEmpty($name);
        $this->assertEquals('string', gettype($name));
    }

    public function testGetShortNameNotEmpty()
    {
        $shortName = $this->gateway->getShortName();
        $this->assertNotEmpty($shortName);
        $this->assertEquals('string', gettype($shortName));
    }

    public function testGetDefaultParametersReturnsArray()
    {
        $this->assertNotNull($this->gateway->getDefaultParameters());
        $settings = $this->gateway->getDefaultParameters();
        $this->assertEquals('array', gettype($settings));
    }

    public function testDefaultParametersHaveMatchingMethods()
    {
        $this->assertNotNull($this->gateway->getDefaultParameters());
        $settings = $this->gateway->getDefaultParameters();
        foreach ($settings as $key => $default) {
            $getter = 'get'.ucfirst($this->camelCase($key));
            $setter = 'set'.ucfirst($this->camelCase($key));
            $value = uniqid('', true);

            $this->assertTrue(method_exists($this->gateway, $getter), "Gateway must implement $getter()");
            $this->assertTrue(method_exists($this->gateway, $setter), "Gateway must implement $setter()");

            // setter must return instance
            $this->assertSame($this->gateway, $this->gateway->$setter($value));
            $this->assertSame($value, $this->gateway->$getter());
        }
    }

    public function testTestMode()
    {
        $this->assertSame($this->gateway, $this->gateway->setTestMode(false));
        $this->assertSame(false, $this->gateway->getTestMode());

        $this->assertSame($this->gateway, $this->gateway->setTestMode(true));
        $this->assertSame(true, $this->gateway->getTestMode());
    }

    public function testCurrency()
    {
        // currency is normalized to uppercase
        $this->assertSame($this->gateway, $this->gateway->setCurrency('eur'));
        $this->assertSame('EUR', $this->gateway->getCurrency());
    }

    public function testSupportsAuthorize()
    {
        $supportsAuthorize = $this->gateway->supportsAuthorize();
        $this->assertEquals('boolean', gettype($supportsAuthorize));

        if ($supportsAuthorize) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->authorize());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'authorize'));
        }
    }

    public function testSupportsCompleteAuthorize()
    {
        $this->assertNotNull($this->gateway->supportsCompleteAuthorize());
        $supportsCompleteAuthorize = $this->gateway->supportsCompleteAuthorize();
        $this->assertEquals('boolean', gettype($supportsCompleteAuthorize));

        if ($supportsCompleteAuthorize) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->completeAuthorize());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'completeAuthorize'));
        }
    }

    public function testSupportsCapture()
    {
        $supportsCapture = $this->gateway->supportsCapture();
        $this->assertEquals('boolean', gettype($supportsCapture));

        if ($supportsCapture) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->capture());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'capture'));
        }
    }

    public function testSupportsPurchase()
    {
        $supportsPurchase = $this->gateway->supportsPurchase();
        $this->assertEquals('boolean', gettype($supportsPurchase));

        if ($supportsPurchase) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->purchase());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'purchase'));
        }
    }

    public function testSupportsCompletePurchase()
    {
        $supportsCompletePurchase = $this->gateway->supportsCompletePurchase();
        $this->assertEquals('boolean', gettype($supportsCompletePurchase));

        if ($supportsCompletePurchase) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->completePurchase());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'completePurchase'));
        }
    }

    public function testSupportsRefund()
    {
        $supportsRefund = $this->gateway->supportsRefund();
        $this->assertEquals('boolean', gettype($supportsRefund));

        if ($supportsRefund) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->refund());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'refund'));
        }
    }

    public function testSupportsVoid()
    {
        $supportsVoid = $this->gateway->supportsVoid();
        $this->assertEquals('boolean', gettype($supportsVoid));

        if ($supportsVoid) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->void());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'void'));
        }
    }

    public function testSupportsCreateCard()
    {
        $supportsCreate = $this->gateway->supportsCreateCard();
        $this->assertEquals('boolean', gettype($supportsCreate));

        if ($supportsCreate) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->createCard());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'createCard'));
        }
    }

    public function testSupportsDeleteCard()
    {
        $supportsDelete = $this->gateway->supportsDeleteCard();
        $this->assertEquals('boolean', gettype($supportsDelete));

        if ($supportsDelete) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->deleteCard());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'deleteCard'));
        }
    }

    public function testSupportsUpdateCard()
    {
        $this->assertNotNull($this->gateway->supportsUpdateCard());
        $supportsUpdate = $this->gateway->supportsUpdateCard();
        $this->assertEquals('boolean', gettype($supportsUpdate));

        if ($supportsUpdate) {
            $this->assertInstanceOf('Omnipay\Common\Message\RequestInterface', $this->gateway->updateCard());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'updateCard'));
        }
    }

    public function testAuthorizeParameters()
    {
        $this->assertNotNull($this->gateway->supportsAuthorize());
        if ($this->gateway->supportsAuthorize()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->authorize();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testCompleteAuthorizeParameters()
    {
        $this->assertNotNull($this->gateway->supportsCompleteAuthorize());
        if ($this->gateway->supportsCompleteAuthorize()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->completeAuthorize();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testCaptureParameters()
    {
        $this->assertNotNull($this->gateway->supportsCapture());
        if ($this->gateway->supportsCapture()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->capture();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testPurchaseParameters()
    {
        $this->assertNotNull($this->gateway->supportsPurchase());
        if ($this->gateway->supportsPurchase()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->purchase();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testCompletePurchaseParameters()
    {
        $this->assertNotNull($this->gateway->supportsCompletePurchase());
        if ($this->gateway->supportsCompletePurchase()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->completePurchase();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testRefundParameters()
    {
        if ($this->gateway->supportsRefund()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->refund();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testVoidParameters()
    {
        if ($this->gateway->supportsVoid()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->void();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testCreateCardParameters()
    {
        $this->assertNotNull($this->gateway->supportsCreateCard());
        if ($this->gateway->supportsCreateCard()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->createCard();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testDeleteCardParameters()
    {
        $this->assertNotNull($this->gateway->supportsDeleteCard());
        if ($this->gateway->supportsDeleteCard()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->deleteCard();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testUpdateCardParameters()
    {
        $this->assertNotNull($this->gateway->supportsUpdateCard());
        if ($this->gateway->supportsUpdateCard()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid('', true);
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->updateCard();
                $this->assertSame($value, $request->$getter());
            }
        }
    }
}
