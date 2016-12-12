<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\TrainingBundle\Service;

use Commercetools\Core\Client;
use Commercetools\Core\Model\Channel\Channel;
use Commercetools\Core\Model\Channel\ChannelCollection;
use Commercetools\Core\Model\Channel\ChannelDraft;
use Commercetools\Core\Request\Channels\ChannelByIdGetRequest;
use Commercetools\Core\Request\Channels\ChannelCreateRequest;
use Commercetools\Core\Request\Channels\ChannelQueryRequest;

class ChannelRepository
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $key
     * @return Channel
     */
    public function getChannel($key)
    {
        $request = ChannelQueryRequest::of()->where(sprintf('key="%s"', $key))->limit(1);
        $response = $this->client->execute($request);
        $channels = $request->mapFromResponse($response);
        $channel = $channels->current();
        return $channel;
    }

    /**
     * @param ChannelDraft $channelDraft
     * @return Channel
     */
    public function createChannel(ChannelDraft $channelDraft)
    {
        $request = ChannelCreateRequest::ofDraft($channelDraft);
        $response = $this->client->execute($request);
        $channel = $request->mapFromResponse($response);
        return $channel;
    }

    /**
     * @param $lng
     * @param $lat
     * @param $distance
     * @return ChannelCollection
     */
    public function queryChannelsAtLocation($lng, $lat, $distance)
    {
        $request = ChannelQueryRequest::of()
            ->where(sprintf('geoLocation within circle(%s, %s, %s)', $lng, $lat, $distance));
        $response = $this->client->execute($request);
        $channels = $request->mapFromResponse($response);

        return $channels;
    }
}
