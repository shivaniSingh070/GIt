<?php
/**
 * https://trello.com/c/jpShH5U4/136-08einrichtung-newsletter-tool-in-den-shop-setup-newsletter-tool
 * created by NA on 11.11.19
 * to remove the default newsletter email from the magento  
 * */ 
namespace Pixelmechanics\Engelsrufer\Newsletter\Model;
 
use Magento\Newsletter\Model\Subscriber as MageSubscriber;
 
/**
 * Don't send any newsletter-related emails.
 * These will all go out through our marketing platform.
 */
class Subscriber
{
    /**
     * @param MageSubscriber $oSubject
     * @param callable $proceed
     */
    public function aroundSendConfirmationRequestEmail(MageSubscriber $oSubject, callable $proceed) {}
 
    /**
     * @param MageSubscriber $oSubject
     * @param callable $proceed
     */
    public function aroundSendConfirmationSuccessEmail(MageSubscriber $oSubject, callable $proceed) {}
 
    /**
     * @param MageSubscriber $oSubject
     * @param callable $proceed
     */
    public function aroundSendUnsubscriptionEmail(MageSubscriber $oSubject, callable $proceed)      {}
}