<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class WebhookList
 *
 * List of webhooks.
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\Webhook[] webhooks
 */
class WebhookList extends PayPalModel
{
    /**
     * A list of webhooks.
     *
     * @param \Forminator\PayPal\Api\Webhook[] $webhooks
     * 
     * @return $this
     */
    public function setWebhooks($webhooks)
    {
        $this->webhooks = $webhooks;
        return $this;
    }

    /**
     * A list of webhooks.
     *
     * @return \Forminator\PayPal\Api\Webhook[]
     */
    public function getWebhooks()
    {
        return $this->webhooks;
    }

    /**
     * Append Webhooks to the list.
     *
     * @param \Forminator\PayPal\Api\Webhook $webhook
     * @return $this
     */
    public function addWebhook($webhook)
    {
        if (!$this->getWebhooks()) {
            return $this->setWebhooks(array($webhook));
        } else {
            return $this->setWebhooks(
                array_merge($this->getWebhooks(), array($webhook))
            );
        }
    }

    /**
     * Remove Webhooks from the list.
     *
     * @param \Forminator\PayPal\Api\Webhook $webhook
     * @return $this
     */
    public function removeWebhook($webhook)
    {
        return $this->setWebhooks(
            array_diff($this->getWebhooks(), array($webhook))
        );
    }

}
