<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration\Controller;

class AccountActionsTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 0
     */
    public function testIndexModuleDisabled()
    {
        $this->dispatch('/amgcard/account/index');

        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 0
     */
    public function testAddCardModuleDisabled()
    {
        $this->dispatch('/amgcard/account/addcard/');
        $response = json_decode($this->getResponse()->getBody());

        $this->assertEquals($response->message, __('Invalid Request')->getText());
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 0
     */
    public function testRemoveModuleDisabled()
    {
        $this->dispatch('/amgcard/account/remove/');
        $response = json_decode($this->getResponse()->getBody());

        $this->assertEquals($response->message, __('Invalid Request')->getText());
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 1
     */
    public function testIndexNotLoggedIn()
    {
        $this->dispatch('/amgcard/account/index/');

        $this->assertRedirect($this->stringContains('customer/account/login'));
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 1
     */
    public function testAddCardNotLoggedIn()
    {
        $this->dispatch('/amgcard/account/addcard/');
        $response = json_decode($this->getResponse()->getBody());

        $this->assertEquals($response->message, __('The session has expired. Please refresh the page.')->getText());
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 1
     */
    public function testRemoveNotLoggedIn()
    {
        $this->dispatch('/amgcard/account/remove/');
        $response = json_decode($this->getResponse()->getBody());

        $this->assertEquals($response->message, __('The session has expired. Please refresh the page.')->getText());
    }
}
