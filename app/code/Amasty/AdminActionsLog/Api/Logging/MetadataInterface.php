<?php

namespace Amasty\AdminActionsLog\Api\Logging;

use Magento\Framework\App\RequestInterface;

interface MetadataInterface
{
    /**
     * Basic event actions identifiers.
     */
    const EVENT_DISPATCH = 'dispatch';
    const EVENT_SAVE_BEFORE = 'save_before';
    const EVENT_SAVE_AFTER = 'save_after';
    const EVENT_DELETE = 'delete';
    const EVENT_LOGIN = 'login';
    const EVENT_LAYOUT_RENDER_BEFORE = 'layout_render_before';

    /**
     * Current action's request.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     * Action's event name.
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Action's logging object.
     *
     * @return object|null
     */
    public function getObject(): ?object;
}
