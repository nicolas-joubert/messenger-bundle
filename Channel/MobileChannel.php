<?php

namespace Yokai\MessengerBundle\Channel;

use Sly\NotificationPusher\Adapter\AdapterInterface;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;
use Sly\NotificationPusher\PushManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Recipient\MobileRecipientInterface;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class MobileChannel implements ChannelInterface
{
    /**
     * @var PushManager
     */
    private $pushManager;

    /**
     * @var AdapterInterface[]
     */
    private $adapters;

    /**
     * @param PushManager        $pushManager
     * @param AdapterInterface[] $adapters
     */
    public function __construct(PushManager $pushManager, array $adapters)
    {
        $this->pushManager = $pushManager;
        $this->adapters = $adapters;
    }

    /**
     * @inheritDoc
     */
    public function supports($recipient)
    {
        return $recipient instanceof MobileRecipientInterface;
    }

    /**
     * @inheritDoc
     */
    public function configure(OptionsResolver $resolver)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle(Delivery $delivery)
    {
        /** @var $recipient MobileRecipientInterface */
        $recipient = $delivery->getRecipient();

        $message = new Message($delivery->getSubject());

        foreach ($this->adapters as $adapter) {
            $devices = new DeviceCollection();

            foreach ($recipient->getDevicesTokens() as $token) {
                if ($adapter->supports($token)) {
                    $devices->add(new Device($token));
                }
            }

            $push = new Push($adapter, $devices, $message);

            $this->pushManager->add($push);
        }

        $this->pushManager->push();
    }
}
