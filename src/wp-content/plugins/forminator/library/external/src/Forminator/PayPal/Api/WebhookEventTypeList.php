<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class WebhookEventTypeList
 *
 * List of webhook events.
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\WebhookEventType[] event_types
 */
class WebhookEventTypeList extends PayPalModel
{
    /**
     * A list of webhook events.
     *
     * @param \Forminator\PayPal\Api\WebhookEventType[] $event_types
     * 
     * @return $this
     */
    public function setEventTypes($event_types)
    {
        $this->event_types = $event_types;
        return $this;
    }

    /**
     * A list of webhook events.
     *
     * @return \Forminator\PayPal\Api\WebhookEventType[]
     */
    public function getEventTypes()
    {
        return $this->event_types;
    }

    /**
     * Append EventTypes to the list.
     *
     * @param \Forminator\PayPal\Api\WebhookEventType $webhookEventType
     * @return $this
     */
    public function addEventType($webhookEventType)
    {
        if (!$this->getEventTypes()) {
            return $this->setEventTypes(array($webhookEventType));
        } else {
            return $this->setEventTypes(
                array_merge($this->getEventTypes(), array($webhookEventType))
            );
        }
    }

    /**
     * Remove EventTypes from the list.
     *
     * @param \Forminator\PayPal\Api\WebhookEventType $webhookEventType
     * @return $this
     */
    public function removeEventType($webhookEventType)
    {
        return $this->setEventTypes(
            array_diff($this->getEventTypes(), array($webhookEventType))
        );
    }

}
