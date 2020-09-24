<?php

namespace app\components\services;

use AmoCRM\Client\AmoCRMApiClient;
use app\components\helpers\TokenHelper;
use League\OAuth2\Client\Token\AccessToken;
use Yii;

/**
 * Class ApiClientGettingService
 * @package app\components\services
 */
class ApiClientGettingService
{
    /**
     * @return AmoCRMApiClient
     */
    public function getApiClient()
    {
        $config = Yii::$app->params;
        $apiClient = new AmoCRMApiClient(
            $config['clientId'],
            $config['clientSecret'],
            $config['redirectUri']
        );
        $accessToken = new AccessToken(TokenHelper::getAccessToken());
        $apiClient->setAccessToken($accessToken);
        $apiClient->setAccountBaseDomain($config['domain']);

        return $apiClient;
    }
}
